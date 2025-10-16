<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$action = $_POST['action'] ?? '';
$appointment_id = intval($_POST['appointment_id'] ?? 0);

if (!$appointment_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid appointment ID']);
    exit();
}

// Get appointment details
$stmt = $conn->prepare("SELECT * FROM appointments WHERE appointment_id = ?");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();

if (!$appointment) {
    echo json_encode(['success' => false, 'error' => 'Appointment not found']);
    exit();
}

switch ($action) {
    case 'confirm':
        // Admin can confirm pending appointments
        if ($_SESSION['user_type'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit();
        }
        
        if ($appointment['status'] !== 'pending') {
            echo json_encode(['success' => false, 'error' => 'Appointment is not pending']);
            exit();
        }
        
        $update_stmt = $conn->prepare("UPDATE appointments SET status = 'confirmed' WHERE appointment_id = ?");
        $update_stmt->bind_param("i", $appointment_id);
        
        if ($update_stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        break;
        
    case 'cancel':
        // Admin or client can cancel. If cancelled, the availability slot should be freed.
        $can_cancel = false;
        if ($_SESSION['user_type'] === 'admin') {
            $can_cancel = true;
        } elseif ($_SESSION['user_type'] === 'client' && $appointment['client_email'] === $_SESSION['email']) {
            $appointment_date = new DateTime($appointment['appointment_date']);
            $current_date = new DateTime();
            if ($current_date->diff($appointment_date)->days >= 4) {
                $can_cancel = true;
            } else {
                echo json_encode(['success' => false, 'error' => 'Appointments can only be cancelled at least 4 days prior.']);
                exit();
            }
        }

        if (!$can_cancel) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit();
        }

        // Use a transaction to ensure data integrity
        $conn->begin_transaction();
        try {
            // Update appointment status to 'cancelled'
            $update_appt_stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE appointment_id = ?");
            $update_appt_stmt->bind_param("i", $appointment_id);
            $update_appt_stmt->execute();

            // Free up the availability slot
            $update_avail_stmt = $conn->prepare("UPDATE therapist_availability SET is_booked = 0, appointment_id = NULL WHERE appointment_id = ?");
            $update_avail_stmt->bind_param("i", $appointment_id);
            $update_avail_stmt->execute();

            $conn->commit();
            echo json_encode(['success' => true]);

        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'error' => 'Database error during cancellation.']);
        }
        break;
        
    case 'complete':
        // Admin can mark confirmed appointments as completed
        if ($_SESSION['user_type'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit();
        }
        
        if ($appointment['status'] !== 'confirmed') {
            echo json_encode(['success' => false, 'error' => 'Appointment is not confirmed']);
            exit();
        }
        
        $update_stmt = $conn->prepare("UPDATE appointments SET status = 'completed' WHERE appointment_id = ?");
        $update_stmt->bind_param("i", $appointment_id);
        
        if ($update_stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        break;
        
    case 'approve':
        // Therapist can approve pending appointments assigned to them
        if ($_SESSION['user_type'] !== 'therapist') {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit();
        }
        // Ensure the appointment is assigned to the logged-in therapist
        $therapist_id = $_SESSION['user_id'];
        if ($appointment['therapist_id'] != $therapist_id) {
            echo json_encode(['success' => false, 'error' => 'This appointment is not assigned to you.']);
            exit();
        }
        if ($appointment['status'] !== 'pending') {
            echo json_encode(['success' => false, 'error' => 'Appointment is not pending']);
            exit();
        }
        $update_stmt = $conn->prepare("UPDATE appointments SET status = 'confirmed' WHERE appointment_id = ?");
        $update_stmt->bind_param("i", $appointment_id);
        if ($update_stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}
?> 