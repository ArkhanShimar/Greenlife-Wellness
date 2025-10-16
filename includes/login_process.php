<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debugging - log that we reached this page
error_log('Login POST: ' . print_r($_POST, true));

// Include database connection
require_once '../includes/db.php';
require_once '../includes/auth_functions.php';

// Debugging - check if files were included
error_log("DB connection included: " . (isset($conn) ? 'Yes' : 'No'));
error_log("Auth functions included: " . (function_exists('loginUser') ? 'Yes' : 'No'));

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST data received: " . print_r($_POST, true));
    
    // Sanitize inputs - use general sanitization instead of email-specific
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? ''; // Don't sanitize passwords
    
    // Simple validation
    $errors = [];
    if (empty($username)) {
        $errors['username'] = 'Username or email is required';
    }
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }

    if (empty($errors)) {
        error_log("Attempting to login user: " . $username);
        
        if (loginUser($conn, $username, $password)) {
            error_log("Login successful for: " . $username);
            
            // Handle redirection
            if (isset($_POST['redirect_url']) && !empty($_POST['redirect_url'])) {
                // Sanitize the redirect URL to prevent header injection vulnerabilities
                $redirect_url = filter_var(urldecode($_POST['redirect_url']), FILTER_SANITIZE_URL);
                if (filter_var($redirect_url, FILTER_VALIDATE_URL) || (strpos($redirect_url, '../') === 0)) {
                    header('Location: ' . $redirect_url);
                    exit();
                }
            }

            // Redirect based on user type if no valid redirect_url is provided
            switch ($_SESSION['user_type']) {
                case 'admin':
                    header('Location: ../pages/dashboard/admin.php');
                    exit();
                case 'therapist':
                    header('Location: ../pages/dashboard/therapist.php');
                    exit();
                default:
                    header('Location: ../pages/dashboard/client.php');
                    exit();
            }
        } else {
            error_log("Login failed for: " . $username);
            $errors['login'] = 'Invalid username/email or password';
        }
    }
    
    // Store errors in session
    $_SESSION['login_errors'] = $errors;
    $_SESSION['login_username'] = $username;
    
    // Debugging
    error_log("Errors encountered: " . print_r($errors, true));

    error_log('login_process: $_SESSION after login: ' . print_r($_SESSION, true));
}

// If we get here, redirect back to login
error_log("Redirecting back to login page");
header('Location: ../pages/login.php?error=login_failed');
exit();
?>