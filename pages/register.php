<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard/client.php");
    exit();
}

// Set page title
$page_title = "Register | GreenLife Wellness";

// Get any errors from session
$errors = $_SESSION['register_errors'] ?? [];
$input_data = $_SESSION['register_data'] ?? [];

// Ensure all expected keys exist to avoid undefined index errors
$input_data = array_merge([
    'firstname' => '',
    'lastname' => '',
    'username' => '',
    'email' => '',
    'phone' => '',
    'dob' => '',
    'address' => ''
], is_array($input_data) ? $input_data : []);

// Clear session data
unset($_SESSION['register_errors']);
unset($_SESSION['register_data']);
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
            <h1>Create Your Account</h1>
            <p>Join GreenLife Wellness to start your journey to a healthier you.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <strong>Please fix the following errors:</strong>
                <ul style="list-style-position: inside; padding-left: 10px; margin-top: 5px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form id="registerForm" action="../includes/register_process.php" method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="firstname">First Name</label>
                    <input type="text" id="firstname" name="firstname" placeholder="e.g., John" value="<?php echo htmlspecialchars($input_data['firstname']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="lastname">Last Name</label>
                    <input type="text" id="lastname" name="lastname" placeholder="e.g., Doe" value="<?php echo htmlspecialchars($input_data['lastname']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Choose a unique username" value="<?php echo htmlspecialchars($input_data['username']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" value="<?php echo htmlspecialchars($input_data['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="profile_pic">Profile Picture</label>
                <input type="file" id="profile_pic" name="profile_pic" accept="image/*" class="file-input">
                <small class="file-help">Upload a profile picture (JPG, PNG, GIF - Max 2MB)</small>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="(Optional)" value="<?php echo htmlspecialchars($input_data['phone']); ?>">
                </div>
                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($input_data['dob']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" placeholder="Your street address" value="<?php echo htmlspecialchars($input_data['address']); ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a strong password" required>
                </div>
                <div class="form-group">
                    <label for="password-repeat">Confirm Password</label>
                    <input type="password" id="password-repeat" name="password-repeat" placeholder="Confirm your password" required>
                </div>
            </div>
            
            <input type="hidden" name="user_type" value="client">

            <div class="form-submit">
                <button type="submit" name="register-submit" class="btn btn-primary">Create Account</button>
            </div>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="login.php">Login here</a></p>
            <p><a href="../index.php">&larr; Go back to Home</a></p>
        </div>
    </div>
    <script>
        // Simple client-side password match validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const passwordRepeat = document.getElementById('password-repeat').value;
            
            if (password !== passwordRepeat) {
                e.preventDefault();
                // Find existing alert box or create one
                let alertBox = document.querySelector('.alert-error');
                if (!alertBox) {
                    alertBox = document.createElement('div');
                    alertBox.className = 'alert alert-error';
                    this.prepend(alertBox);
                }
                alertBox.innerHTML = 'Passwords do not match. Please try again.';
                window.scrollTo(0, 0); // Scroll to top to make sure user sees the error
                return false;
            }
        });
    </script>
</body>
</html>