<?php
// Determine the base path for assets
$current_file = $_SERVER['PHP_SELF'];
$is_in_pages = strpos($current_file, '/pages/') !== false;
$base_path = $is_in_pages ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>GreenLife Wellness</title>
    <meta name="description" content="Transform your life with holistic wellness services. Book appointments with certified therapists and wellness experts.">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo $base_path; ?>assets/images/logo.png">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/main.css?v=<?php echo time(); ?>">
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>?v=<?php echo time(); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito&family=Quicksand&display=swap" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <!-- Logo -->
                <div class="navbar-brand">
                    <a href="<?php echo $base_path; ?>index.php" class="logo">
                        <img src="<?php echo $base_path; ?>assets/images/logo.png" alt="GreenLife Wellness Logo" class="logo-image">
                        <span class="logo-name">GreenLife</span>
                        <span class="logo-wellness">Wellness</span>
                    </a>
                </div>
            
                <!-- Desktop Navigation -->
                <div class="navbar-menu">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="<?php echo $base_path; ?>index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                                <span>Home</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_path; ?>pages/services.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : ''; ?>">
                                <span>Services</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_path; ?>pages/about.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">
                                <span>About</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_path; ?>pages/blog.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'blog.php' ? 'active' : ''; ?>">
                                <span>Blog</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_path; ?>pages/contact.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">
                                <span>Contact</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- User Actions -->
                <div class="navbar-actions">
                    <?php
                    session_start();
                    if (isset($_SESSION['user_id'])) {
                        $user_name = $_SESSION['name'] ?? $_SESSION['username'] ?? 'User';
                        $user_role = $_SESSION['user_type'] ?? 'client';
                        $profile_image = $_SESSION['profile_pic'] ?? null;
                    ?>
                        <div class="user-menu">
                            <div class="user-avatar">
                                <?php if ($profile_image): ?>
                                    <img src="<?php echo $base_path; ?>assets/images/profiles/<?php echo $profile_image; ?>" alt="<?php echo htmlspecialchars($user_name); ?>">
                    <?php else: ?>
                                    <i class="fas fa-user"></i>
                    <?php endif; ?>
                            </div>
                            <div class="user-dropdown">
                                <div class="user-info">
                                    <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                                    <span class="user-role"><?php echo ucfirst($user_role); ?></span>
                                </div>
                                <ul class="dropdown-menu">
                                    <li><a href="<?php echo $base_path; ?>pages/dashboard/<?php echo $user_role; ?>.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                                    <?php if ($user_role === 'client'): ?>
                                    <li><a href="<?php echo $base_path; ?>pages/appointments.php"><i class="fas fa-calendar-alt"></i> Appointments</a></li>
                                    <?php endif; ?>
                                    <?php if ($user_role !== 'admin'): ?>
                                    <li><a href="<?php echo $base_path; ?>pages/dashboard/<?php echo $user_role; ?>.php#profile"><i class="fas fa-user-edit"></i> Profile</a></li>
                                    <?php endif; ?>
                                    <li><a href="<?php echo $base_path; ?>includes/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="auth-buttons">
                            <a href="<?php echo $base_path; ?>pages/login.php" class="btn-login">
                                <span>Login</span>
                            </a>
                            <a href="<?php echo $base_path; ?>pages/register.php" class="btn-register">
                                <span>Register</span>
                            </a>
                        </div>
                    <?php } ?>
                </div>

                <!-- Mobile Menu Toggle -->
                <div class="mobile-menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </nav>
        </div>
    </header>
    
    <!-- Mobile Navigation -->
    <div class="mobile-nav">
        <div class="mobile-nav-header">
            <div class="navbar-brand">
                <a href="<?php echo $base_path; ?>index.php" class="logo">
                    <img src="<?php echo $base_path; ?>assets/images/logo.png" alt="GreenLife Wellness Logo" class="logo-image">
                    <span class="logo-name">GreenLife</span>
                    <span class="logo-wellness">Wellness</span>
                </a>
            </div>
            <button class="mobile-nav-close">
                <span style="font-size:2rem;line-height:1;">&times;</span>
            </button>
        </div>
        <ul class="mobile-nav-list">
            <li><a href="<?php echo $base_path; ?>index.php">Home</a></li>
            <li><a href="<?php echo $base_path; ?>pages/services.php">Services</a></li>
            <li><a href="<?php echo $base_path; ?>pages/about.php">About</a></li>
            <li><a href="<?php echo $base_path; ?>pages/blog.php">Blog</a></li>
            <li><a href="<?php echo $base_path; ?>pages/contact.php">Contact</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="<?php echo $base_path; ?>pages/dashboard/<?php echo $_SESSION['user_type'] ?? 'client'; ?>.php">Dashboard</a></li>
                <?php if (($_SESSION['user_type'] ?? 'client') === 'client'): ?>
                <li><a href="<?php echo $base_path; ?>pages/appointments.php">Appointments</a></li>
                <?php endif; ?>
                <?php if (($_SESSION['user_type'] ?? 'client') !== 'admin'): ?>
                <li><a href="<?php echo $base_path; ?>pages/dashboard/<?php echo $_SESSION['user_type'] ?? 'client'; ?>.php#profile">Profile</a></li>
                <?php endif; ?>
                <li><a href="<?php echo $base_path; ?>includes/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="<?php echo $base_path; ?>pages/login.php">Login</a></li>
                <li><a href="<?php echo $base_path; ?>pages/register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Main Content Wrapper -->
    <main class="main-content"> 