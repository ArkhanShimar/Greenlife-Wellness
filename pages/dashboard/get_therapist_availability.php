<?php
header('Content-Type: application/json');
include '../../includes/db.php';

date_default_timezone_set('Asia/Colombo'); // Adjust as needed

$therapist_id = isset($_GET['therapist_id']) ? intval($_GET['therapist_id']) : (isset($_POST['therapist_id']) ? intval($_POST['therapist_id']) : 0);

if (!$therapist_id) {
    echo json_encode(['success' => false, 'error' => 'No therapist_id provided']);
    exit;
}

$now_date = date('Y-m-d');
$now_time = date('H:i:s');

$stmt = $conn->prepare("SELECT id, available_date, start_time, end_time FROM therapist_availability WHERE therapist_id = ? AND is_booked = 0 AND (available_date > ? OR (available_date = ? AND end_time > ?)) ORDER BY available_date, start_time");
$stmt->bind_param('isss', $therapist_id, $now_date, $now_date, $now_time);
$stmt->execute();
$result = $stmt->get_result();
$slots = [];
while ($row = $result->fetch_assoc()) {
    $slots[] = $row;
}
echo json_encode(['success' => true, 'slots' => $slots]);
exit; 