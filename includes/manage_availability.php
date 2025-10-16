<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header('Location: ../pages/login.php');
    exit();
}

include 'db.php';
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Add new availability slot (therapist only)
if ($user_type === 'therapist' && isset($_POST['add_availability'])) {
    $date = $_POST['available_date'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];
    $now = date('Y-m-d H:i:s');
    $slot_start = "$date $start:00";
    $slot_end = "$date $end:00";

    // Validate: start < end, not in the past
    if ($slot_start >= $slot_end) {
        $_SESSION['availability_error'] = 'Start time must be before end time.';
        header('Location: ../pages/dashboard/therapist.php#availability');
        exit();
    }
    if ($slot_start < $now) {
        $_SESSION['availability_error'] = 'Cannot add slots in the past.';
        header('Location: ../pages/dashboard/therapist.php#availability');
        exit();
    }
    // Check for overlap
    $overlap_stmt = $conn->prepare("SELECT * FROM therapist_availability WHERE therapist_id = ? AND available_date = ? AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?) OR (start_time >= ? AND end_time <= ?))");
    $overlap_stmt->bind_param("isssssss", $user_id, $date, $end, $start, $end, $start, $start, $end);
    $overlap_stmt->execute();
    $overlap_result = $overlap_stmt->get_result();
    if ($overlap_result->num_rows > 0) {
        $_SESSION['availability_error'] = 'This slot overlaps with an existing slot.';
        header('Location: ../pages/dashboard/therapist.php#availability');
        exit();
    }
    // Insert slot
    $insert_stmt = $conn->prepare("INSERT INTO therapist_availability (therapist_id, available_date, start_time, end_time) VALUES (?, ?, ?, ?)");
    $insert_stmt->bind_param("isss", $user_id, $date, $start, $end);
    $insert_stmt->execute();
    $_SESSION['availability_success'] = 'Availability slot added.';
    header('Location: ../pages/dashboard/therapist.php#availability');
    exit();
}

// Delete availability slot (therapist only)
if ($user_type === 'therapist' && isset($_POST['delete_availability']) && isset($_POST['slot_id'])) {
    $slot_id = intval($_POST['slot_id']);
    // Only allow delete if not booked and belongs to this therapist
    $check_stmt = $conn->prepare("SELECT * FROM therapist_availability WHERE id = ? AND therapist_id = ? AND is_booked = 0");
    $check_stmt->bind_param("ii", $slot_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    if ($result->num_rows === 1) {
        $del_stmt = $conn->prepare("DELETE FROM therapist_availability WHERE id = ?");
        $del_stmt->bind_param("i", $slot_id);
        $del_stmt->execute();
        $_SESSION['availability_success'] = 'Slot deleted.';
    } else {
        $_SESSION['availability_error'] = 'Cannot delete this slot.';
    }
    header('Location: ../pages/dashboard/therapist.php#availability');
    exit();
}

// Admin: Update slot status
if ($user_type === 'admin' && isset($_POST['update_slot_status']) && isset($_POST['slot_id'])) {
    $slot_id = intval($_POST['slot_id']);
    $new_status = intval($_POST['new_status']);
    $stmt = $conn->prepare("UPDATE therapist_availability SET is_booked = ?, appointment_id = NULL WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $slot_id);
    if ($stmt->execute()) {
        $_SESSION['availability_success'] = 'Slot status updated.';
    } else {
        $_SESSION['availability_error'] = 'Failed to update slot status.';
    }
    header('Location: ../pages/dashboard/admin.php#manage-slots');
    exit();
}

// Default redirect based on user type
if ($user_type === 'admin') {
    header('Location: ../pages/dashboard/admin.php#manage-slots');
} else {
    header('Location: ../pages/dashboard/therapist.php#availability');
}
exit(); 