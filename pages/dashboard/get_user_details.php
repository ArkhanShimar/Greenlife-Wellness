<?php
session_start();
include '../../includes/db.php';

// Security check: only admins can access this
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');
$userId = $_GET['id'] ?? 0;
$userType = $_GET['type'] ?? '';

if ($userId === 0 || !in_array($userType, ['client', 'therapist'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

if ($userType === 'client') {
    $stmt = $conn->prepare("SELECT user_id, username, email, first_name, last_name, date_of_birth, phone, address, status, profile_pic FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
} else { // therapist
    $stmt = $conn->prepare("SELECT therapist_id as user_id, name, username, email, qualification, speciality, experience, profile_pic FROM therapists WHERE therapist_id = ?");
    $stmt->bind_param("i", $userId);
}

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    
    if($data) {
        if (!empty($data['profile_pic'])) {
            $data['profile_pic_url'] = '../../assets/images/profiles/' . $data['profile_pic'];
        }
        echo json_encode($data);
    } else {
        echo json_encode(['error' => 'User not found']);
    }
} else {
    echo json_encode(['error' => 'Database query failed']);
}
?> 