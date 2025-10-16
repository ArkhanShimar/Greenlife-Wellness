<?php
// Authentication functions for GreenLife Wellness Center

/**
 * Register a new user
 * @param mysqli $conn Database connection
 * @param string $username Username
 * @param string $email Email address
 * @param string $password Password
 * @param string $first_name First name
 * @param string $last_name Last name
 * @param string $dob Date of birth
 * @param string $phone Phone number
 * @param string $address Address
 * @param string $profile_pic Profile picture filename
 * @param string $user_type User type (client/therapist/admin)
 * @return int|bool User ID if successful, false otherwise
 */
function registerUser($conn, $username, $email, $password, $first_name, $last_name, $dob, $phone, $address, $profile_pic, $user_type)
{
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Begin transaction
        $conn->begin_transaction();

        // Insert into users table
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, date_of_birth, phone, address, profile_pic, user_type) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssss", $username, $email, $hashed_password, $first_name, $last_name, $dob, $phone, $address, $profile_pic, $user_type);

        if (!$stmt->execute()) {
            throw new Exception("User registration failed");
        }

        $user_id = $stmt->insert_id;
        $stmt->close();

        // If user is a therapist, add to therapists table
        if ($user_type === 'therapist') {
            $stmt = $conn->prepare("INSERT INTO therapists (therapist_id, profile_pic) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $profile_pic);

            if (!$stmt->execute()) {
                throw new Exception("Therapist registration failed");
            }
            $stmt->close();
        }

        // Commit transaction
        $conn->commit();
        return $user_id;

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        error_log("Registration error: " . $e->getMessage());
        return false;
    }
}

/**
 * Login user
 * @param mysqli $conn Database connection
 * @param string $username Username or email
 * @param string $password Password
 * @return bool True if login successful, false otherwise
 */
function loginUser($conn, $username, $password)
{
    // First, try users table (admin/client)
    $stmt = $conn->prepare("SELECT user_id, username, email, password, user_type, status 
                       FROM users 
                       WHERE (username = ? OR email = ?) 
                       AND (status = 'active' OR user_type = 'admin')");
    $stmt->bind_param("ss", $username, $username);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['logged_in'] = true;
                $stmt->close();
                return true;
            }
        }
    }
    $stmt->close(); // Close the first statement here

    // If not found, try therapists table
    $stmt = $conn->prepare("SELECT therapist_id, username, email, password, name FROM therapists WHERE (username = ? OR email = ?)");
    $stmt->bind_param("ss", $username, $username);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $therapist = $result->fetch_assoc();
            if ($password === $therapist['password']) {
                // Set session variables
                $_SESSION['user_id'] = $therapist['therapist_id'];
                $_SESSION['username'] = $therapist['username'];
                $_SESSION['email'] = $therapist['email'];
                $_SESSION['user_type'] = 'therapist';
                $_SESSION['name'] = $therapist['name'];
                $_SESSION['logged_in'] = true;
                $stmt->close();
                return true;
            }
        }
    }
    // Only close the second statement if it was successfully prepared and executed
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }

    return false;
}

/**
 * Check if username or email already exists
 * @param mysqli $conn Database connection
 * @param string $username Username
 * @param string $email Email
 * @return bool True if exists, false otherwise
 */
function userExists($conn, $username, $email)
{
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();
    $count = $stmt->num_rows;
    $stmt->close();
    return $count > 0;
}

/**
 * Logout user
 */
function logoutUser()
{
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    if (session_id() !== "" || isset($_COOKIE[session_name()])) {
        session_destroy();
    }

    // Delete the session cookie
    setcookie(session_name(), '', time() - 3600, '/');

    // Redirect to login page
    header("Location: ../pages/login.php");
    exit();
}

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn()
{
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Redirect if user is not logged in
 * @param string $redirect_url URL to redirect to
 */
function requireLogin($redirect_url = '../pages/login.php')
{
    if (!isLoggedIn()) {
        $_SESSION['login_redirect'] = $_SERVER['REQUEST_URI'];
        header("Location: $redirect_url");
        exit();
    }
}

/**
 * Redirect if user doesn't have required role
 * @param string|array $required_role Required role(s)
 * @param string $redirect_url URL to redirect to
 */
function requireRole($required_role, $redirect_url = '../pages/dashboard/')
{
    if (!isLoggedIn()) {
        requireLogin();
    }

    if (is_array($required_role)) {
        if (!in_array($_SESSION['user_type'], $required_role)) {
            header("Location: $redirect_url");
            exit();
        }
    } else {
        if ($_SESSION['user_type'] !== $required_role) {
            header("Location: $redirect_url");
            exit();
        }
    }
}

function getUserType()
{
    return isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
}

// Function to sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

?>