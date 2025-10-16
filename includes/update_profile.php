<?php
session_start();
include 'db.php';

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'client') {
    $_SESSION['profile_error'] = 'Unauthorized access';
    header('Location: ../pages/dashboard/client.php#profile');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $errors = [];
    
    // Get form data
    $first_name = sanitizeInput($_POST['first_name'] ?? '');
    $last_name = sanitizeInput($_POST['last_name'] ?? '');
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $dob = sanitizeInput($_POST['dob'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    
    // Validate required fields
    if (empty($first_name)) $errors[] = 'First name is required';
    if (empty($last_name)) $errors[] = 'Last name is required';
    if (empty($username)) $errors[] = 'Username is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (empty($dob)) $errors[] = 'Date of birth is required';
    
    // Validate email format
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    // Check if username/email already exists (excluding current user)
    if (empty($errors)) {
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
        $check_stmt->bind_param("ssi", $username, $email, $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = 'Username or email already exists';
        }
        $check_stmt->close();
    }
    
    // Handle profile picture upload
    $profile_pic = null;
    if (isset($_FILES['new_profile_pic']) && $_FILES['new_profile_pic']['error'] === UPLOAD_ERR_OK) {
        $upload_result = handleProfilePicUpload($_FILES['new_profile_pic']);
        if (isset($upload_result['error'])) {
            $errors[] = $upload_result['error'];
        } else {
            $profile_pic = $upload_result['filename'];
        }
    }
    
    // If no errors, update profile
    if (empty($errors)) {
        try {
            $conn->begin_transaction();
            
            // Build update query
            $update_fields = [];
            $update_values = [];
            $update_types = '';
            
            $fields = [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'username' => $username,
                'email' => $email,
                'phone' => $phone,
                'date_of_birth' => $dob,
                'address' => $address
            ];
            
            foreach ($fields as $field => $value) {
                $update_fields[] = "$field = ?";
                $update_values[] = $value;
                $update_types .= 's';
            }
            
            // Add profile picture if uploaded
            if ($profile_pic) {
                $update_fields[] = "profile_pic = ?";
                $update_values[] = $profile_pic;
                $update_types .= 's';
            }
            
            $update_values[] = $user_id;
            $update_types .= 'i';
            
            $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($update_types, ...$update_values);
            
            if ($stmt->execute()) {
                $conn->commit();
                $_SESSION['profile_success'] = 'Profile updated successfully!';
                
                // Update session data
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                
            } else {
                throw new Exception('Failed to update profile');
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['profile_error'] = 'Error updating profile: ' . $e->getMessage();
        }
    } else {
        $_SESSION['profile_error'] = implode(', ', $errors);
    }
} else {
    $_SESSION['profile_error'] = 'Invalid request method';
}

header('Location: ../pages/dashboard/client.php#profile');
exit();

// Function to handle profile picture upload
function handleProfilePicUpload($file) {
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    // Check file type
    if (!in_array($file['type'], $allowed_types)) {
        return ['error' => 'Invalid file type. Please upload JPG, PNG, or GIF images only.'];
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        return ['error' => 'File size too large. Maximum size is 2MB.'];
    }
    
    // Create upload directory if it doesn't exist
    $upload_dir = '../assets/images/profiles/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . uniqid() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['filename' => $filename];
    } else {
        return ['error' => 'Failed to upload file. Please try again.'];
    }
}

// Function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?> 