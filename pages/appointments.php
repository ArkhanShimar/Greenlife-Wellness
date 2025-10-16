<?php 
session_start();
include '../includes/db.php';

// Fetch services
$services = [];
$res = $conn->query("SELECT service_id, name, description, price, duration_minutes, image_path FROM services ORDER BY name");
while ($row = $res->fetch_assoc()) $services[] = $row;

// Fetch therapists
$therapists = [];
$res = $conn->query("SELECT therapist_id, name, speciality, qualification, profile_pic FROM therapists ORDER BY name");
while ($row = $res->fetch_assoc()) $therapists[] = $row;

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book_appointment') {
    $client_name = trim($_POST['client_name']);
    $client_email = trim($_POST['client_email']);
    $client_phone = trim($_POST['client_phone']);
    $service_id = intval($_POST['service_id']);
    $therapist_id = intval($_POST['therapist_id']);
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $availability_slot_id = intval($_POST['slot_id']);

    // Start transaction
    $conn->begin_transaction();

    try {
        // 1. Lock the availability slot and double-check it's not booked
        $lock_stmt = $conn->prepare("SELECT id FROM therapist_availability WHERE id = ? AND is_booked = 0 FOR UPDATE");
        $lock_stmt->bind_param("i", $availability_slot_id);
        $lock_stmt->execute();
        $result = $lock_stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("This time slot has just been booked by someone else. Please select another time.");
        }
        $lock_stmt->close();

        // 2. Insert the new appointment
        $insert_stmt = $conn->prepare("INSERT INTO appointments (client_name, client_email, client_phone, service_id, therapist_id, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $insert_stmt->bind_param("sssisss", $client_name, $client_email, $client_phone, $service_id, $therapist_id, $appointment_date, $appointment_time);
        $insert_stmt->execute();
        $new_appointment_id = $conn->insert_id;
        $insert_stmt->close();

        // 3. Update the availability slot to mark it as booked
        $update_stmt = $conn->prepare("UPDATE therapist_availability SET is_booked = 1, appointment_id = ? WHERE id = ?");
        $update_stmt->bind_param("ii", $new_appointment_id, $availability_slot_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        // If all queries were successful, commit the transaction
        $conn->commit();

        $_SESSION['appointment_success'] = "Appointment booked successfully!";
        header("Location: dashboard/client.php#appointments");
        exit();

    } catch (Exception $e) {
        // If any query fails, roll back the transaction
        $conn->rollback();
        $message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - GreenLife Wellness</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/appointments.css">
</head>
<body>
    <div class="appointment-container">
        <!-- Progress Bar -->
        <div class="progress-container">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <div class="progress-steps">
                <div class="step active" data-step="1">
                    <i class="fas fa-spa"></i>
                    <span>Service</span>
                </div>
                <div class="step" data-step="2">
                    <i class="fas fa-user-md"></i>
                    <span>Therapist</span>
                </div>
                <div class="step" data-step="3">
                    <i class="fas fa-calendar"></i>
                    <span>Date & Time</span>
                </div>
                <div class="step" data-step="4">
                    <i class="fas fa-user"></i>
                    <span>Details</span>
                </div>
            </div>
        </div>

        <!-- Multi-step Form -->
        <form id="appointmentForm" method="POST" class="appointment-form">
            <input type="hidden" name="action" value="book_appointment">
            
            <!-- Step 1: Service Selection -->
            <div class="form-step active" id="step1">
                <div class="step-header">
                    <h2>Select Your Service</h2>
                    <p>Choose the wellness service that best suits your needs</p>
                </div>
                
                <div class="services-grid">
                    <?php foreach ($services as $service): ?>
                        <div class="service-card" data-service-id="<?php echo $service['service_id']; ?>">
                            <div class="service-image">
                                <?php if (!empty($service['image_path'])): ?>
                                    <img src="../assets/images/services/<?php echo htmlspecialchars($service['image_path']); ?>" alt="<?php echo htmlspecialchars($service['name']); ?>">
                                <?php else: ?>
                                    <div class="service-icon">
                                        <i class="fas fa-spa"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                            <p><?php echo htmlspecialchars($service['description']); ?></p>
                            <div class="service-details">
                                <span class="service-duration"><i class="fas fa-clock"></i> <?php echo htmlspecialchars($service['duration_minutes']); ?> mins</span>
                                <span class="service-price">LKR <?php echo number_format($service['price'], 2); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <input type="hidden" name="service_id" id="selectedService" required>
                
                <div class="step-actions">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='dashboard/client.php'">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </button>
                    <button type="button" class="btn btn-primary" onclick="nextStep()" id="nextBtn1" disabled>
                        Next <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Step 2: Therapist Selection -->
            <div class="form-step" id="step2">
                <div class="step-header">
                    <h2>Select Your Therapist</h2>
                    <p>Choose from our experienced wellness professionals</p>
                </div>
                <div class="therapists-grid">
                    <?php foreach ($therapists as $therapist): ?>
                        <div class="therapist-card" data-therapist-id="<?php echo $therapist['therapist_id']; ?>">
                            <div class="therapist-avatar">
                                <?php if (!empty($therapist['profile_pic'])): ?>
                                    <img src="../assets/images/profiles/<?php echo htmlspecialchars($therapist['profile_pic']); ?>" alt="<?php echo htmlspecialchars($therapist['name']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-user-md"></i>
                                <?php endif; ?>
                            </div>
                            <h3><?php echo htmlspecialchars($therapist['name']); ?></h3>
                            <p class="therapist-speciality"><?php echo htmlspecialchars($therapist['speciality']); ?></p>
                            <p class="therapist-qualification"><?php echo htmlspecialchars($therapist['qualification']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="therapist_id" id="selectedTherapist" required>
                <div class="step-actions">
                    <button type="button" class="btn btn-secondary" onclick="prevStep()">
                        <i class="fas fa-arrow-left"></i> Previous
                    </button>
                    <button type="button" class="btn btn-primary" onclick="nextStep()" id="nextBtn2" disabled>
                        Next <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Step 3: Therapist's Available Date & Time -->
            <div class="form-step" id="step3">
                <div class="step-header">
                    <h2>Select Date & Time</h2>
                    <p>Choose a date from the calendar, then select an available time.</p>
                </div>
                <div class="availability-picker">
                    <div id="calendar-container">
                        <!-- Calendar will be generated by JS here -->
                        <div class="loading-slots" style="display:none;">Loading available dates...</div>
                    </div>
                    <div id="time-slots-container">
                        <div class="time-slots-list-container">
                             <h4 id="time-slots-header" style="display:none;">Available Times on <span id="selected-date-display"></span></h4>
                             <div class="time-slots-list"></div>
                        </div>
                        <p class="time-slot-instruction">Please select a date to see available times.</p>
                    </div>
                </div>
                <input type="hidden" name="appointment_date" id="selectedDate" required>
                <input type="hidden" name="appointment_time" id="selectedTime" required>
                <input type="hidden" name="slot_id" id="selectedSlotId" required>
                <div class="step-actions">
                    <button type="button" class="btn btn-secondary" onclick="prevStep()">
                        <i class="fas fa-arrow-left"></i> Previous
                    </button>
                    <button type="button" class="btn btn-primary" onclick="nextStep()" id="nextBtn3" disabled>
                        Next <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Step 4: Client Details -->
            <div class="form-step" id="step4">
                <div class="step-header">
                    <h2>Your Details</h2>
                    <p>Please provide your contact information</p>
                </div>
                
                <div class="client-details">
                    <div class="form-group">
                        <label for="client_name">Full Name</label>
                        <input type="text" id="client_name" name="client_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="client_email">Email Address</label>
                        <input type="email" id="client_email" name="client_email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="client_phone">Phone Number</label>
                        <input type="tel" id="client_phone" name="client_phone" required>
                    </div>
                </div>
                
                <div class="appointment-summary">
                    <h3>Appointment Summary</h3>
                    <div class="summary-item">
                        <span class="label">Service:</span>
                        <span class="value" id="summaryService"></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Date:</span>
                        <span class="value" id="summaryDate"></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Time:</span>
                        <span class="value" id="summaryTime"></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Therapist:</span>
                        <span class="value" id="summaryTherapist"></span>
                    </div>
                </div>
                
                <div class="step-actions">
                    <button type="button" class="btn btn-secondary" onclick="prevStep()">
                        <i class="fas fa-arrow-left"></i> Previous
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Book Appointment
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script src="../assets/js/appointments.js"></script>
</body>
</html>