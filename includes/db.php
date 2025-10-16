<?php
// Database configuration for XAMPP (default credentials)
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Default XAMPP username
define('DB_PASS', ''); // Default XAMPP password (empty)
define('DB_NAME', 'greenlife_wellness');

// Error reporting (for development)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Create connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set charset
    $conn->set_charset("utf8");

} catch (Exception $e) {
    // Log error and show user-friendly message
    error_log("Database error: " . $e->getMessage());
    die("We're experiencing technical difficulties. Please try again later.");
}
?>