<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'send_message':
            sendMessage($conn, $user_id);
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

// Send a message to admin
function sendMessage($conn, $user_id) {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (!$subject || !$message) {
        echo json_encode(['success' => false, 'error' => 'Subject and message are required']);
        exit();
    }
    
    // Get user info
    $user_stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE user_id = ?");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user_info = $user_result->fetch_assoc();
    $user_stmt->close();
    
    if (!$user_info) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit();
    }
    
    // Insert message into database
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, subject, message, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("iss", $user_id, $subject, $message);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send message']);
    }
    
    $stmt->close();
    exit();
}
?> 