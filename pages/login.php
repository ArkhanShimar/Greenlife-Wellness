<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard/client.php"); // Redirect to a default dashboard
    exit();
}

// Set page title
$page_title = "Login | GreenLife Wellness";

// No header is included on auth pages for a cleaner look
// include '../includes/header.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>

<body class="auth-page">

    <div class="auth-container">
        <div class="auth-header">
            <a href="../index.php" class="logo-text">GreenLife Wellness</a>
            <h1>Welcome Back!</h1>
            <p>Login to access your GreenLife Wellness dashboard.</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?php
                $error = $_GET['error'];
                if ($error == 'emptyfields') {
                    echo 'Please fill in all fields.';
                } elseif ($error == 'wrongpassword') {
                    echo 'Incorrect password. Please try again.';
                } elseif ($error == 'nouser') {
                    echo 'No account found with that username or email.';
                } elseif ($error == 'login_failed') {
                    echo 'Login failed. Please check your credentials.';
                } else {
                    echo 'An unknown error occurred. Please try again.';
                }
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['registration']) && $_GET['registration'] == 'success'): ?>
            <div class="alert alert-success">
                Registration successful! You can now log in.
            </div>
        <?php endif; ?>

        <form id="loginForm" action="../includes/login_process.php" method="POST">
            
            <div class="form-group">
                <label for="login-username">Username or Email</label>
                <input type="text" id="login-username" name="username" placeholder="e.g., yourname or you@example.com" required>
            </div>

            <div class="form-group">
                <label for="login-password">Password</label>
                <input type="password" id="login-password" name="password" placeholder="Enter your password" required>
            </div>
            
            <input type="hidden" name="role" value="client"> <!-- Default role, can be expanded later -->

            <!-- Hidden field for the redirect URL -->
            <?php if (isset($_GET['redirect_url'])): ?>
                <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($_GET['redirect_url']); ?>">
            <?php endif; ?>

            <div class="form-submit">
                <button type="submit" name="login-submit" class="btn btn-primary">Sign In</button>
            </div>
        </form>

        <div class="auth-footer">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
            <p><a href="../index.php">&larr; Go back to Home</a></p>
        </div>
    </div>

</body>
</html>