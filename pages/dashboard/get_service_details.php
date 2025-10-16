<?php
// Check if user is logged in and is an admin
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

include '../../includes/db.php';

if (isset($_GET['id'])) {
    $service_id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT service_id, name, description, category, duration_minutes, price, image_path FROM services WHERE service_id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $service = $result->fetch_assoc();
        
        // Add the full URL for the image if it exists
        if (!empty($service['image_path'])) {
            $service['image_path_url'] = '../../assets/images/services/' . $service['image_path'];
        }
        
        header('Content-Type: application/json');
        echo json_encode($service);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Service not found']);
    }
    
    $stmt->close();
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Service ID not provided']);
}

$conn->close();
?> 