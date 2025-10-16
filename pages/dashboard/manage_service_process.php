<?php
// Check if user is logged in and is an admin
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

include '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['serviceId'] ?? null;
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $duration_minutes = intval($_POST['duration_minutes']);
    $price = floatval($_POST['price']);
    
    // Validate inputs
    if (empty($name) || empty($description) || empty($category) || $duration_minutes <= 0 || $price <= 0) {
        echo json_encode(['success' => false, 'message' => 'All fields are required and must be valid']);
        exit();
    }
    
    // Handle file upload
    $image_path = null;
    if (isset($_FILES['image_path']) && $_FILES['image_path']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../assets/images/services/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_info = pathinfo($_FILES['image_path']['name']);
        $file_extension = strtolower($file_info['extension']);
        
        // Validate file type
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($file_extension, $allowed_extensions)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed']);
            exit();
        }
        
        // Generate unique filename
        $image_path = 'service_' . time() . '_' . uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $image_path;
        
        if (!move_uploaded_file($_FILES['image_path']['tmp_name'], $upload_path)) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
            exit();
        }
    }
    
    try {
        if ($service_id) {
            // Update existing service
            if ($image_path) {
                // Get old image to delete
                $stmt = $conn->prepare("SELECT image_path FROM services WHERE service_id = ?");
                $stmt->bind_param("i", $service_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows === 1) {
                    $old_service = $result->fetch_assoc();
                    if (!empty($old_service['image_path'])) {
                        $old_image_path = '../../assets/images/services/' . $old_service['image_path'];
                        if (file_exists($old_image_path)) {
                            unlink($old_image_path);
                        }
                    }
                }
                $stmt->close();
                
                // Update with new image
                $stmt = $conn->prepare("UPDATE services SET name = ?, description = ?, category = ?, duration_minutes = ?, price = ?, image_path = ? WHERE service_id = ?");
                $stmt->bind_param("sssidsi", $name, $description, $category, $duration_minutes, $price, $image_path, $service_id);
            } else {
                // Update without changing image
                $stmt = $conn->prepare("UPDATE services SET name = ?, description = ?, category = ?, duration_minutes = ?, price = ? WHERE service_id = ?");
                $stmt->bind_param("sssidi", $name, $description, $category, $duration_minutes, $price, $service_id);
            }
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Service updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update service']);
            }
        } else {
            // Create new service
            $stmt = $conn->prepare("INSERT INTO services (name, description, category, duration_minutes, price, image_path) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssids", $name, $description, $category, $duration_minutes, $price, $image_path);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Service created successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create service']);
            }
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['serviceId'])) {
        $service_id = intval($_POST['serviceId']);
        $stmt = $conn->prepare("DELETE FROM services WHERE service_id = ?");
        $stmt->bind_param("i", $service_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Service deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete service.']);
        }
        $stmt->close();
        exit();
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?> 