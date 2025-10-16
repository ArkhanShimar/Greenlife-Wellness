<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection and functions
require_once '../includes/db.php';
require_once '../includes/auth_functions.php';

// Initialize variables
$errors = [];
$input_data = [
    'firstname' => '',
    'lastname' => '',
    'username' => '',
    'email' => '',
    'phone' => '',
    'dob' => '',
    'address' => ''
];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input data
    $input_data['firstname'] = sanitizeInput($_POST['firstname']);
    $input_data['lastname'] = sanitizeInput($_POST['lastname']);
    $input_data['username'] = sanitizeInput($_POST['username']);
    $input_data['email'] = sanitizeInput($_POST['email']);
    $input_data['phone'] = sanitizeInput($_POST['phone'] ?? '');
    $input_data['dob'] = sanitizeInput($_POST['dob'] ?? '');
    $input_data['address'] = sanitizeInput($_POST['address'] ?? '');
    $password = trim($_POST['password']);
    $password_repeat = trim($_POST['password-repeat']);
    $user_type = sanitizeInput($_POST['user_type'] ?? 'client');

    // Handle profile picture upload
    $profile_pic = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $upload_result = handleProfilePicUpload($_FILES['profile_pic']);
        if (isset($upload_result['error'])) {
            $errors['profile_pic'] = $upload_result['error'];
        } else {
            $profile_pic = $upload_result['filename'];
        }
    }

    // Validate inputs
    if (empty($input_data['firstname'])) {
        $errors['firstname'] = 'First name is required';
    }
    if (empty($input_data['lastname'])) {
        $errors['lastname'] = 'Last name is required';
    }
    if (empty($input_data['username'])) {
        $errors['username'] = 'Username is required';
    } elseif (strlen($input_data['username']) < 4) {
        $errors['username'] = 'Username must be at least 4 characters';
    }
    if (empty($input_data['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($input_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    if (empty($input_data['dob'])) {
        $errors['dob'] = 'Date of birth is required';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $input_data['dob'])) {
        $errors['dob'] = 'Invalid date format';
    }
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    }
    if ($password !== $password_repeat) {
        $errors['password-repeat'] = 'Passwords do not match';
    }
    if (empty($user_type) || !in_array($user_type, ['client', 'therapist'])) {
        $errors['user-type'] = 'Invalid user type';
    }

    // Check if username/email already exists
    if (empty($errors['username']) && empty($errors['email'])) {
        if (userExists($conn, $input_data['username'], $input_data['email'])) {
            $errors['username'] = 'Username or email already exists';
        }
    }

    // If no errors, attempt registration
    if (empty($errors)) {
        $user_id = registerUser(
            $conn,
            $input_data['username'],
            $input_data['email'],
            $password,
            $input_data['firstname'],
            $input_data['lastname'],
            $input_data['dob'],
            $input_data['phone'],
            $input_data['address'],
            $profile_pic,
            $user_type
        );

        if ($user_id) {
            // Registration successful - log user in and redirect
            loginUser($conn, $input_data['username'], $password);
            if ($user_type === 'client') {
                $_SESSION['show_registration_success'] = true;
            }
            $_SESSION['register_success'] = true;
            
            // Redirect based on user type
            switch ($user_type) {
                case 'therapist':
                    header('Location: ../pages/dashboard/therapist.php');
                    exit();
                case 'client':
                default:
                    header('Location: ../pages/dashboard/client.php');
                    exit();
            }
        } else {
            $errors['database'] = 'Registration failed. Please try again.';
        }
    }
}

// If registration failed or form not submitted, redirect back to register page with errors
$_SESSION['register_errors'] = $errors;
$_SESSION['register_data'] = $input_data;
header('Location: ../pages/register.php');
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
?>