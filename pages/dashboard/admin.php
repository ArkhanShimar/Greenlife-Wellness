<?php 
// Check if user is logged in and is an admin
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../../includes/db.php';

// Get admin data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Get stats for dashboard
$clients_stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE user_type = 'client'");
$clients_stmt->execute();
$clients = $clients_stmt->get_result()->fetch_assoc();

$therapists_stmt = $conn->prepare("SELECT COUNT(*) as count FROM therapists");
$therapists_stmt->execute();
$therapists = $therapists_stmt->get_result()->fetch_assoc();

$appointments_stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE appointment_date >= CURDATE()");
$appointments_stmt->execute();
$appointments = $appointments_stmt->get_result()->fetch_assoc();

$revenue_stmt = $conn->prepare("SELECT SUM(s.price) as total FROM appointments a JOIN services s ON a.service_id = s.service_id WHERE a.status = 'completed' AND MONTH(a.appointment_date) = MONTH(CURDATE())");
$revenue_stmt->execute();
$revenue = $revenue_stmt->get_result()->fetch_assoc();

// Get unread messages count
$unread_count = 0;
$unread_stmt = $conn->prepare("SELECT COUNT(*) as count FROM messages WHERE admin_reply IS NULL AND status = 'pending'");
if ($unread_stmt) {
    $unread_stmt->execute();
    $unread_result = $unread_stmt->get_result();
    $unread_count = $unread_result->fetch_assoc()['count'];
    $unread_stmt->close();
}

// Get all messages for admin
$messages_stmt = $conn->prepare("
    SELECT * FROM messages 
    ORDER BY created_at DESC
");
$messages_stmt->execute();
$messages = $messages_stmt->get_result();

// Get appointments for admin
$pending_appointments_stmt = $conn->prepare("
    SELECT a.*, s.name as service_name, t.name as therapist_name, t.speciality
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    JOIN therapists t ON a.therapist_id = t.therapist_id
    WHERE a.status = 'pending'
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
");
$pending_appointments_stmt->execute();
$pending_appointments = $pending_appointments_stmt->get_result();

$confirmed_appointments_stmt = $conn->prepare("
    SELECT a.*, s.name as service_name, t.name as therapist_name, t.speciality
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    JOIN therapists t ON a.therapist_id = t.therapist_id
    WHERE a.status = 'confirmed'
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
");
$confirmed_appointments_stmt->execute();
$confirmed_appointments = $confirmed_appointments_stmt->get_result();

// Get completed appointments
$completed_appointments_stmt = $conn->prepare("
    SELECT a.*, s.name as service_name, t.name as therapist_name, t.speciality
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    JOIN therapists t ON a.therapist_id = t.therapist_id
    WHERE a.status = 'completed'
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$completed_appointments_stmt->execute();
$completed_appointments = $completed_appointments_stmt->get_result();

// Get all clients
$clients_list_stmt = $conn->prepare("SELECT user_id, username, email, first_name, last_name, status, registration_date FROM users WHERE user_type = 'client' ORDER BY registration_date DESC");
$clients_list_stmt->execute();
$clients_list = $clients_list_stmt->get_result();

// Get all therapists
$therapists_list_stmt = $conn->prepare("SELECT therapist_id, name, email, speciality, qualification, experience FROM therapists ORDER BY created_at DESC");
$therapists_list_stmt->execute();
$therapists_list = $therapists_list_stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['blogId'])) {
    session_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
    include '../../includes/db.php';
    $blog_id = intval($_POST['blogId']);
    $stmt = $conn->prepare("DELETE FROM blogs WHERE id = ?");
    $stmt->bind_param("i", $blog_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Blog deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete blog.']);
    }
    $stmt->close();
    $conn->close();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GreenLife Wellness</title>
    <link rel="stylesheet" href="../../assets/css/dashboard-modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<header class="dashboard-header">
    <div class="header-left">
        <div class="header-logo">
            <a href="../../index.php">
                <img src="../../assets/images/logo.png" alt="GreenLife Logo">
                <h2>GreenLife</h2>
            </a>
        </div>
    </div>
    <nav class="header-nav">
            <ul>
            <li><a href="#dashboard" class="nav-link active" onclick="showSection(event, 'dashboard')">Dashboard</a></li>
            <li><a href="#appointments" class="nav-link" onclick="showSection(event, 'appointments')">Appointments</a></li>
            <li class="dropdown">
                <a class="nav-link dropdown-toggle">Manage <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i></a>
                <div class="dropdown-menu">
                    <a href="#manage-users" onclick="showSection(event, 'manage-users')">Manage Users</a>
                    <a href="#manage-services" onclick="showSection(event, 'manage-services')">Manage Services</a>
                    <a href="#manage-blog" onclick="showSection(event, 'manage-blog')">Manage Blog</a>
                    <a href="#manage-slots" onclick="showSection(event, 'manage-slots')">Manage Time Slots</a>
                </div>
            </li>
            <li><a href="#messages" class="nav-link" onclick="showSection(event, 'messages')">Messages</a></li>
            </ul>
        </nav>
    <div class="header-right">
        <div class="dropdown">
            <div class="user-profile dropdown-toggle">
                <?php if (!empty($admin['profile_pic'])): ?>
                    <img src="../../assets/images/profiles/<?php echo htmlspecialchars($admin['profile_pic']); ?>" alt="Profile Picture" class="profile-pic">
                <?php else: ?>
                    <div class="profile-pic-placeholder"><i class="fas fa-user"></i></div>
                <?php endif; ?>
                <span><?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="dropdown-menu">
                <a href="../../index.php">Home</a>
                <a href="../../includes/logout.php">Logout</a>
            </div>
        </div>
        <button class="mobile-nav-toggle"><i class="fas fa-bars"></i></button>
    </div>
</header>
<div class="dashboard-container">
    <!-- Main Content -->
    <main class="main-content">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-message">
                <h1>Admin Portal</h1>
                <p>Here's what's happening at GreenLife Wellness today.</p>
            </div>
        </div>
        <!-- Dashboard Section -->
        <section id="dashboard" class="content-section active">
            <div class="dashboard-cards">
                <div class="card" onclick="showSection(event, 'appointments')" style="cursor: pointer;">
                    <h3>Pending Appointments</h3>
                    <div class="card-value"><?php echo $pending_appointments->num_rows; ?></div>
                </div>
                <div class="card" onclick="showSection(event, 'messages')" style="cursor: pointer;">
                    <h3>Messages</h3>
                    <div class="card-value"><?php echo $unread_count; ?></div>
                </div>
                <div class="card">
                    <h3>Monthly Revenue</h3>
                    <div class="card-value">LKR <?php echo number_format($revenue['total'] ?? 0, 2); ?></div>
                </div>
            </div>
            
            <div class="wellness-showcase">
                <div class="showcase-header">
                    <h3>GreenLife Wellness Center</h3>
                    <p>Leading the way in holistic wellness and therapeutic care</p>
                </div>
                
                <div class="services-highlight">
                    <div class="service-item">
                        <div class="service-icon">🏆</div>
                        <h4>Premium Services</h4>
                        <p>Top-tier wellness treatments and therapies</p>
                    </div>
                    <div class="service-item">
                        <div class="service-icon">👥</div>
                        <h4>Growing Community</h4>
                        <p>Building a healthy, connected community</p>
                    </div>
                </div>
                
                <div class="cta-section">
                    <div class="cta-content">
                        <h4>Center Overview</h4>
                        <p>GreenLife Wellness is dedicated to providing exceptional wellness services. Our center features state-of-the-art facilities, expert therapists, and a comprehensive range of therapeutic treatments designed to enhance your well-being.</p>
                        <button class="btn btn-primary" onclick="showSection(event, 'manage-services')">
                            <i class="fas fa-cog"></i>
                            Manage Services
                        </button>
                            </div>
                    <div class="cta-stats">
                        <div class="stat">
                            <span class="stat-number"><?php echo $clients['count']; ?>+</span>
                            <span class="stat-label">Active Clients</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number">10+</span>
                            <span class="stat-label">Varieties of Services</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number"><?php echo $therapists['count']; ?></span>
                            <span class="stat-label">Expert Therapists</span>
                        </div>
                    </div>
                </div>
                    </div>
        </section>
        <!-- Manage Users Section -->
        <section id="manage-users" class="content-section">
            <div class="toolbar">
                <button class="btn btn-primary" id="addTherapistBtn">
                    <i class="fas fa-plus"></i> Add New Therapist
                </button>
            </div>

            <!-- Clients List -->
            <div class="user-list-container">
                <h3>Clients</h3>
                <div class="table-responsive">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Registered On</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($client = $clients_list->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $client['user_id']; ?></td>
                                    <td><?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($client['email']); ?></td>
                                    <td><?php echo date('F j, Y', strtotime($client['registration_date'])); ?></td>
                                    <td><span class="status-badge status-<?php echo strtolower(htmlspecialchars($client['status'])); ?>"><?php echo htmlspecialchars($client['status']); ?></span></td>
                                    <td>
                                        <button class="btn-action edit" data-user-id="<?php echo $client['user_id']; ?>" data-user-type="client"><i class="fas fa-edit"></i></button>
                                        <button class="btn-action delete-user" data-user-id="<?php echo $client['user_id']; ?>" data-user-type="client"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Therapists List -->
            <div class="user-list-container">
                <h3>Therapists</h3>
                <div class="table-responsive">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Speciality</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($therapist = $therapists_list->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $therapist['therapist_id']; ?></td>
                                    <td><?php echo htmlspecialchars($therapist['name']); ?></td>
                                    <td><?php echo htmlspecialchars($therapist['email']); ?></td>
                                    <td><?php echo htmlspecialchars($therapist['speciality']); ?></td>
                                    <td>
                                        <button class="btn-action edit" data-user-id="<?php echo $therapist['therapist_id']; ?>" data-user-type="therapist"><i class="fas fa-edit"></i></button>
                                        <button class="btn-action delete-user" data-user-id="<?php echo $therapist['therapist_id']; ?>" data-user-type="therapist"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        <!-- Manage Services Section -->
        <section id="manage-services" class="content-section">
            <div class="toolbar">
                <button class="btn btn-primary" id="addServiceBtn">
                    <i class="fas fa-plus"></i> Add New Service
                </button>
            </div>

            <!-- Services List -->
            <div class="user-list-container">
                <h3>Services</h3>
                <div class="table-responsive">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Duration</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Get all services
                            $services_list_stmt = $conn->prepare("SELECT service_id, name, category, duration_minutes, price FROM services ORDER BY category, name");
                            $services_list_stmt->execute();
                            $services_list = $services_list_stmt->get_result();
                            ?>
                            <?php while($service = $services_list->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $service['service_id']; ?></td>
                                    <td><?php echo htmlspecialchars($service['name']); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($service['category'])); ?></td>
                                    <td><?php echo htmlspecialchars($service['duration_minutes']); ?> mins</td>
                                    <td>LKR <?php echo number_format($service['price'], 2); ?></td>
                                    <td>
                                        <button class="btn-action edit" data-service-id="<?php echo $service['service_id']; ?>"><i class="fas fa-edit"></i></button>
                                        <button class="btn-action delete-service" data-service-id="<?php echo $service['service_id']; ?>"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        
        <!-- Appointments Section -->
        <section id="appointments" class="content-section">
            <div class="appointments-container">
                <div class="appointments-header">
                    <div class="appointment-stats">
                        <span class="stat-item">
                            <i class="fas fa-clock"></i>
                            Pending: <?php echo $pending_appointments->num_rows; ?>
                        </span>
                        <span class="stat-item">
                            <i class="fas fa-check-circle"></i>
                            Confirmed: <?php echo $confirmed_appointments->num_rows; ?>
                        </span>
                    </div>
                </div>
                
                <!-- Pending Appointments -->
                <div class="appointments-section">
                    <h3><i class="fas fa-clock"></i> Pending Appointments</h3>
                    <?php if ($pending_appointments->num_rows === 0): ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-check"></i>
                            <h4>No pending appointments</h4>
                            <p>All appointments have been processed.</p>
                        </div>
                    <?php else: ?>
                        <div class="appointments-grid">
                            <?php while($appt = $pending_appointments->fetch_assoc()): ?>
                                <div class="appointment-card pending">
                                    <div class="appointment-header">
                                        <h4><?php echo htmlspecialchars($appt['service_name']); ?></h4>
                                        <span class="status-badge status-pending">Pending</span>
                                    </div>
                                    <div class="appointment-details">
                                        <div class="detail-item">
                                            <span class="label">Client:</span>
                                            <span class="value"><?php echo htmlspecialchars($appt['client_name']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="label">Therapist:</span>
                                            <span class="value"><?php echo htmlspecialchars($appt['therapist_name']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="label">Date:</span>
                                            <span class="value"><?php echo date('F j, Y', strtotime($appt['appointment_date'])); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="label">Time:</span>
                                            <span class="value"><?php echo date('g:i A', strtotime($appt['appointment_time'])); ?></span>
        </div>
                                        <div class="detail-item">
                                            <span class="label">Contact:</span>
                                            <span class="value"><?php echo htmlspecialchars($appt['client_email']); ?></span>
            </div>
                    </div>
                                    <div class="appointment-actions">
                                        <button class="btn btn-success" onclick="confirmAppointment(<?php echo $appt['appointment_id']; ?>)">
                                            <i class="fas fa-check"></i> Confirm
                                        </button>
                                        <button class="btn btn-danger" onclick="cancelAppointment(<?php echo $appt['appointment_id']; ?>)">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Confirmed Appointments -->
                <div class="appointments-section">
                    <h3><i class="fas fa-check-circle"></i> Confirmed Appointments</h3>
                    <?php if ($confirmed_appointments->num_rows === 0): ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-check"></i>
                            <h4>No confirmed appointments</h4>
                            <p>Confirmed appointments will appear here.</p>
                        </div>
                    <?php else: ?>
                        <div class="appointments-grid">
                            <?php while($appt = $confirmed_appointments->fetch_assoc()): ?>
                                <div class="appointment-card confirmed">
                                    <div class="appointment-header">
                                        <h4><?php echo htmlspecialchars($appt['service_name']); ?></h4>
                                        <span class="status-badge status-confirmed">Confirmed</span>
                                    </div>
                                    <div class="appointment-details">
                                        <div class="detail-item">
                                            <span class="label">Client:</span>
                                            <span class="value"><?php echo htmlspecialchars($appt['client_name']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="label">Therapist:</span>
                                            <span class="value"><?php echo htmlspecialchars($appt['therapist_name']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="label">Date:</span>
                                            <span class="value"><?php echo date('F j, Y', strtotime($appt['appointment_date'])); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="label">Time:</span>
                                            <span class="value"><?php echo date('g:i A', strtotime($appt['appointment_time'])); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="label">Contact:</span>
                                            <span class="value"><?php echo htmlspecialchars($appt['client_email']); ?></span>
                                        </div>
                                    </div>
                                    <div class="appointment-actions">
                                        <button class="btn btn-primary" onclick="markCompleted(<?php echo $appt['appointment_id']; ?>)">
                                            <i class="fas fa-check-double"></i> Mark Completed
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Completed Appointments -->
                <div class="appointments-section completed-appointments-section">
                    <h3><i class="fas fa-history"></i> Completed Appointments</h3>
                    <?php if ($completed_appointments->num_rows === 0): ?>
                        <div class="empty-state">
                            <i class="fas fa-check-double"></i>
                            <h4>No completed appointments</h4>
                            <p>Completed appointments will appear here.</p>
                        </div>
                    <?php else: ?>
                        <div class="appointments-grid">
                            <?php while($appt = $completed_appointments->fetch_assoc()): ?>
                                <div class="appointment-card completed">
                                    <div class="appointment-header">
                                        <h4><?php echo htmlspecialchars($appt['service_name']); ?></h4>
                                        <span class="status-badge status-completed">Completed</span>
                                    </div>
                                    <div class="appointment-details">
                                        <div class="detail-item">
                                            <span class="label">Client:</span>
                                            <span class="value"><?php echo htmlspecialchars($appt['client_name']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="label">Therapist:</span>
                                            <span class="value"><?php echo htmlspecialchars($appt['therapist_name']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="label">Date:</span>
                                            <span class="value"><?php echo date('F j, Y', strtotime($appt['appointment_date'])); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="label">Time:</span>
                                            <span class="value"><?php echo date('g:i A', strtotime($appt['appointment_time'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <!-- Messages Section -->
        <section id="messages" class="content-section">
            <div class="messages-container">
                <div class="messages-header">
                    <div class="message-stats">
                        <span class="stat-item">
                            <i class="fas fa-inbox"></i>
                            Total: <?php echo $messages->num_rows; ?>
                        </span>
                        <span class="stat-item">
                            <i class="fas fa-clock"></i>
                            Pending: <?php echo $unread_count; ?>
                        </span>
                    </div>
                </div>
                
                <!-- Messages List -->
                <div class="messages-list">
                    <?php if ($messages->num_rows === 0): ?>
                        <div class="empty-state">
                            <i class="fas fa-comments"></i>
                            <h3>No messages yet</h3>
                            <p>Client messages will appear here when they send inquiries.</p>
                        </div>
                    <?php else: ?>
                        <div class="messages-grid">
                            <?php while($msg = $messages->fetch_assoc()): ?>
                                <div class="message-card <?php echo ($msg['admin_reply'] === NULL) ? 'unread' : ''; ?>">
                                    <div class="message-header">
                                        <div class="message-info">
                                            <h4><?php echo htmlspecialchars($msg['subject']); ?></h4>
                                            <span class="message-date"><?php echo date('M j, Y g:i A', strtotime($msg['created_at'])); ?></span>
                                            <span class="message-status status-<?php echo $msg['status']; ?>"><?php echo ucfirst($msg['status']); ?></span>
                                        </div>
                                        <div class="message-sender">
                                            <span class="sender-name"><?php echo htmlspecialchars($msg['sender_name']); ?></span>
                                            <span class="sender-email"><?php echo htmlspecialchars($msg['sender_email']); ?></span>
                    </div>
                </div>
                                    
                                    <div class="message-content">
                                        <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                    </div>
                                    
                                    <?php if ($msg['admin_reply']): ?>
                                        <div class="reply-section">
                                            <h5><i class="fas fa-reply"></i> Your Reply</h5>
                                            <div class="reply-sender">
                                                <div class="sender-label">Replied by:</div>
                                                <div class="sender-name">
                                                    <?php 
                                                    if (!empty($msg['replied_by_name'])) {
                                                        echo htmlspecialchars($msg['replied_by_name']) . ' (' . ucfirst($msg['replied_by_type'] ?? 'admin') . ')';
                                                    } else {
                                                        // Fallback for existing data
                                                        echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) . ' (Admin)';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="reply-content">
                                                <p><?php echo nl2br(htmlspecialchars($msg['admin_reply'])); ?></p>
                                                <span class="reply-date"><?php echo date('M j, Y g:i A', strtotime($msg['replied_at'])); ?></span>
                                            </div>
                                            <button class="btn btn-sm btn-edit-reply" onclick="editReply(<?php echo $msg['message_id']; ?>)">
                                                <i class="fas fa-edit"></i> Edit Reply
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <div class="reply-form-section">
                                            <h5><i class="fas fa-reply"></i> Reply to Client</h5>
                                            <form class="reply-form" onsubmit="submitReply(event, <?php echo $msg['message_id']; ?>)">
                                                <div class="form-group">
                                                    <textarea name="reply" rows="4" required placeholder="Type your reply here..."></textarea>
                                                </div>
                                                <div class="form-submit">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-paper-plane"></i>
                                                        Send Reply
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                    </div>
                            <?php endwhile; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <!-- Manage Blog Section -->
        <section id="manage-blog" class="content-section">
            <div class="toolbar">
                <button class="btn btn-primary" id="addBlogBtn">
                    <i class="fas fa-plus"></i> Add New Blog
                </button>
            </div>
            <div class="user-list-container">
                <h3>Blog Posts</h3>
                <div class="table-responsive">
                    <table class="user-table" id="blogTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Author</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $blogs = $conn->query("SELECT * FROM blogs ORDER BY created_at DESC");
                            while($blog = $blogs->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $blog['id']; ?></td>
                                    <td><?php echo htmlspecialchars($blog['title']); ?></td>
                                    <td><?php echo htmlspecialchars($blog['category']); ?></td>
                                    <td><?php echo htmlspecialchars($blog['author']); ?></td>
                                    <td><span class="status-badge status-<?php echo htmlspecialchars($blog['status']); ?>"><?php echo htmlspecialchars($blog['status']); ?></span></td>
                                    <td><?php echo date('M j, Y', strtotime($blog['created_at'])); ?></td>
                                    <td>
                                        <button class="btn-action edit-blog" 
                                            data-id="<?php echo $blog['id']; ?>"
                                            data-title="<?php echo htmlspecialchars($blog['title'], ENT_QUOTES); ?>"
                                            data-category="<?php echo htmlspecialchars($blog['category'], ENT_QUOTES); ?>"
                                            data-author="<?php echo htmlspecialchars($blog['author'], ENT_QUOTES); ?>"
                                            data-status="<?php echo htmlspecialchars($blog['status'], ENT_QUOTES); ?>"
                                            data-excerpt="<?php echo htmlspecialchars($blog['excerpt'], ENT_QUOTES); ?>"
                                            data-image="<?php echo htmlspecialchars($blog['image_path'], ENT_QUOTES); ?>"
                                            data-content='<?php echo htmlspecialchars($blog['content'], ENT_QUOTES); ?>'
                                        ><i class="fas fa-edit"></i></button>
                                        <button class="btn-action delete-blog" data-blog-id="<?php echo $blog['id']; ?>"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        <!-- Manage Time Slots Section -->
        <section id="manage-slots" class="content-section">
            <h2>Manage Therapist Time Slots</h2>
            <?php
            $slots_stmt = $conn->query("SELECT ta.*, t.name as therapist_name FROM therapist_availability ta JOIN therapists t ON ta.therapist_id = t.therapist_id ORDER BY ta.available_date ASC, ta.start_time ASC");
            if ($slots_stmt->num_rows === 0): ?>
                <div>No availability slots found.</div>
            <?php else:
                // Group slots by date
                $slots_by_date = [];
                while($slot = $slots_stmt->fetch_assoc()) {
                    $slots_by_date[$slot['available_date']][] = $slot;
                }
                foreach ($slots_by_date as $date => $slots): ?>
                    <h4 style="margin-top:2em;margin-bottom:0.5em;">
                        <?php echo date('l, F j, Y', strtotime($date)); ?>
                    </h4>
                    <table class="availability-table" style="margin-bottom:1.5em;">
                <thead>
                    <tr>
                        <th>Therapist</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                        <?php foreach ($slots as $slot): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($slot['therapist_name']); ?></td>
                        <td><?php echo htmlspecialchars(substr($slot['start_time'], 0, 5)); ?></td>
                        <td><?php echo htmlspecialchars(substr($slot['end_time'], 0, 5)); ?></td>
                        <td><?php echo $slot['is_booked'] ? 'Booked' : 'Available'; ?></td>
                        <td>
                            <form action="../../includes/manage_availability.php" method="POST" class="inline-status-form" style="display:inline;">
                                <input type="hidden" name="slot_id" value="<?php echo $slot['id']; ?>">
                                <select name="new_status" class="slot-status-dropdown">
                                    <option value="0" <?php if(!$slot['is_booked']) echo 'selected'; ?>>Available</option>
                                    <option value="1" <?php if($slot['is_booked']) echo 'selected'; ?>>Booked</option>
                                </select>
                                <button type="submit" name="update_slot_status" class="btn btn-primary btn-sm">Update</button>
                            </form>
                        </td>
                    </tr>
                        <?php endforeach; ?>
                </tbody>
            </table>
                <?php endforeach;
            endif; ?>
        </section>
    </main>
</div>

<!-- User Form Modal -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2 id="modalTitle">Add New Therapist</h2>
        <form id="userForm" enctype="multipart/form-data">
            <input type="hidden" id="userId" name="userId">
            <input type="hidden" id="userType" name="userType">

            <!-- Shared Fields -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
                <small>Leave blank to keep current password.</small>
            </div>

            <!-- Client-specific fields -->
            <div class="form-group user-field client-field">
                <label for="firstName">First Name</label>
                <input type="text" id="firstName" name="first_name">
            </div>
            <div class="form-group user-field client-field">
                <label for="lastName">Last Name</label>
                <input type="text" id="lastName" name="last_name">
            </div>
            <div class="form-group user-field client-field">
                <label for="username_client">Username</label>
                <input type="text" id="username_client" name="username">
            </div>
             <div class="form-group user-field client-field">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone">
            </div>
            <div class="form-group user-field client-field">
                <label for="dob">Date of Birth</label>
                <input type="date" id="dob" name="date_of_birth">
            </div>
            <div class="form-group user-field client-field">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="3"></textarea>
            </div>
            <div class="form-group user-field client-field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                    <option value="pending">Pending</option>
                </select>
            </div>

            <!-- Therapist-specific fields -->
            <div class="form-group user-field therapist-field">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name">
            </div>
            <div class="form-group user-field therapist-field">
                <label for="username_therapist">Username</label>
                <input type="text" id="username_therapist" name="username_therapist">
            </div>
            <div class="form-group user-field therapist-field">
                <label for="qualification">Qualification</label>
                <input type="text" id="qualification" name="qualification">
            </div>
            <div class="form-group user-field therapist-field">
                <label for="speciality">Speciality</label>
                <input type="text" id="speciality" name="speciality">
            </div>
            <div class="form-group user-field therapist-field">
                <label for="experience">Experience (Years)</label>
                <input type="number" id="experience" name="experience">
            </div>
            
            <!-- Profile Picture Upload -->
            <div class="form-group">
                <label for="profilePic">Profile Picture</label>
                <input type="file" id="profilePic" name="profile_pic">
                <div id="currentPic"></div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Service Form Modal -->
<div id="serviceModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2 id="serviceModalTitle">Add New Service</h2>
        <form id="serviceForm" enctype="multipart/form-data">
            <input type="hidden" id="serviceId" name="serviceId">

            <div class="form-group">
                <label for="serviceName">Service Name</label>
                <input type="text" id="serviceName" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="serviceDescription">Description</label>
                <textarea id="serviceDescription" name="description" rows="4" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="serviceCategory">Category</label>
                <select id="serviceCategory" name="category" required>
                    <option value="">Select Category</option>
                    <option value="nutrition">Nutrition & Diet</option>
                    <option value="yoga">Yoga & Meditation</option>
                    <option value="beauty">Beauty & Spa</option>
                    <option value="therapy">Therapy & Counseling</option>
                    <option value="wellness program">Wellness Programs</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="serviceDuration">Duration (minutes)</label>
                <input type="number" id="serviceDuration" name="duration_minutes" min="15" step="15" required>
            </div>
            
            <div class="form-group">
                <label for="servicePrice">Price (LKR)</label>
                <input type="number" id="servicePrice" name="price" min="0" step="0.01" required>
            </div>
            
            <!-- Service Image Upload -->
            <div class="form-group">
                <label for="serviceImage">Service Image</label>
                <input type="file" id="serviceImage" name="image_path" accept="image/*">
                <div id="currentServiceImage"></div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Blog Form Modal -->
<div id="blogModalForm" class="modal">
    <div class="modal-content">
        <span class="close" id="closeBlogModal">&times;</span>
        <form id="blogForm" enctype="multipart/form-data">
            <input type="hidden" id="blogId" name="blogId">
            <div class="form-group">
                <label for="blogTitle">Title</label>
                <input type="text" id="blogTitle" name="title" required>
            </div>
            <div class="form-group">
                <label for="blogCategory">Category</label>
                <input type="text" id="blogCategory" name="category" required>
            </div>
            <div class="form-group">
                <label for="blogAuthor">Author</label>
                <input type="text" id="blogAuthor" name="author" required>
            </div>
            <div class="form-group">
                <label for="blogStatus">Status</label>
                <select id="blogStatus" name="status">
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                </select>
            </div>
            <div class="form-group">
                <label for="blogExcerpt">Excerpt</label>
                <textarea id="blogExcerpt" name="excerpt" rows="2" required></textarea>
            </div>
            <div class="form-group">
                <label for="blogImage">Image Path</label>
                <input type="text" id="blogImage" name="image_path">
            </div>
            <div class="form-group">
                <label for="blogContent">Content (HTML allowed)</label>
                <textarea id="blogContent" name="content" rows="8" required></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="previewBlogBtn" style="margin-right:10px;">Preview</button>
                <button type="submit" class="btn btn-primary">Save Blog</button>
            </div>
        </form>
    </div>
</div>

<!-- Blog Preview Modal (matches public blog modal) -->
<div id="blogPreviewModal" class="modal" style="display:none;z-index:11000;">
    <div class="modal-content">
        <span class="close" id="closeBlogPreview">&times;</span>
        <div class="blog-modal-body">
            <img id="previewModalImage" src="" alt="" class="blog-modal-image" style="display:none;">
            <span id="previewModalCategory" class="blog-category"></span>
            <h2 id="previewModalTitle" class="blog-title"></h2>
            <div class="blog-meta">
                <span id="previewModalAuthor" class="blog-author"></span>
                <span id="previewModalDate" class="blog-date"></span>
            </div>
            <div id="previewModalContent" class="blog-post-content"></div>
        </div>
    </div>
</div>
<script>
// Sidebar navigation logic
function showSection(event, section) {
    event.preventDefault();
    document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
    document.getElementById(section).classList.add('active');
    document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
    document.querySelector('.nav-link[href="#' + section + '"]').classList.add('active');
    document.getElementById('pageTitle').textContent = section.charAt(0).toUpperCase() + section.slice(1);
}

// Submit reply to client message
function submitReply(event, messageId) {
    event.preventDefault();
    const form = event.target;
    const replyText = form.querySelector('textarea[name="reply"]').value;
    
    if (!replyText.trim()) {
        alert('Please enter a reply message');
        return;
    }
    
    fetch('../../includes/messages.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=admin_reply&message_id=' + messageId + '&reply=' + encodeURIComponent(replyText)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the page to show the updated message
            location.reload();
        } else {
            alert('Error sending reply: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error sending reply');
    });
}

// Edit existing reply
function editReply(messageId) {
    const replySection = document.querySelector(`[onclick="editReply(${messageId})"]`).closest('.reply-section');
    const replyContent = replySection.querySelector('.reply-content p').textContent;
    
    // Create edit form
    const editForm = document.createElement('div');
    editForm.className = 'reply-form-section';
    editForm.innerHTML = `
        <h5><i class="fas fa-edit"></i> Edit Reply</h5>
        <form class="reply-form" onsubmit="updateReply(event, ${messageId})">
            <div class="form-group">
                <textarea name="reply" rows="4" required>${replyContent}</textarea>
    </div>
            <div class="form-submit">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Update Reply
                </button>
                <button type="button" class="btn btn-secondary" onclick="cancelEdit(${messageId})">
                    Cancel
                </button>
</div>
        </form>
    `;
    
    // Replace reply section with edit form
    replySection.parentNode.replaceChild(editForm, replySection);
}

// Update existing reply
function updateReply(event, messageId) {
    event.preventDefault();
    const form = event.target;
    const replyText = form.querySelector('textarea[name="reply"]').value;
    
    if (!replyText.trim()) {
        alert('Please enter a reply message');
        return;
    }
    
    fetch('../../includes/messages.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=admin_reply&message_id=' + messageId + '&reply=' + encodeURIComponent(replyText)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating reply: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating reply');
    });
}

// Cancel edit
function cancelEdit(messageId) {
    location.reload();
}

// Admin appointment management functions
function confirmAppointment(appointmentId) {
    if (confirm('Are you sure you want to confirm this appointment?')) {
        fetch('../../includes/appointment_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=confirm&appointment_id=' + appointmentId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error confirming appointment');
        });
    }
}

function cancelAppointment(appointmentId) {
    if (confirm('Are you sure you want to cancel this appointment?')) {
        fetch('../../includes/appointment_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=cancel&appointment_id=' + appointmentId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error canceling appointment');
        });
    }
}

function markCompleted(appointmentId) {
    if (confirm('Are you sure you want to mark this appointment as completed?')) {
        fetch('../../includes/appointment_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=complete&appointment_id=' + appointmentId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error marking appointment as completed');
        });
    }
}

// Blog Modal Form Logic
const blogModal = document.getElementById('blogModalForm');
const closeBlogModal = document.getElementById('closeBlogModal');
const addBlogBtn = document.getElementById('addBlogBtn');
const blogForm = document.getElementById('blogForm');
const editBlogBtns = document.querySelectorAll('.edit-blog');

addBlogBtn.onclick = function() {
    blogForm.reset();
    document.getElementById('blogId').value = '';
    blogModal.style.display = 'block';
};
editBlogBtns.forEach(btn => {
    btn.onclick = function() {
        document.getElementById('blogId').value = this.dataset.id;
        document.getElementById('blogTitle').value = this.dataset.title;
        document.getElementById('blogCategory').value = this.dataset.category;
        document.getElementById('blogAuthor').value = this.dataset.author;
        document.getElementById('blogStatus').value = this.dataset.status;
        document.getElementById('blogExcerpt').value = this.dataset.excerpt;
        document.getElementById('blogImage').value = this.dataset.image;
        document.getElementById('blogContent').value = this.dataset.content;
        blogModal.style.display = 'block';
    };
});
closeBlogModal.onclick = function() {
    blogModal.style.display = 'none';
};
window.onclick = function(event) {
    if (event.target == blogModal) {
        blogModal.style.display = 'none';
    }
};

// Blog Preview Logic
const previewBlogBtn = document.getElementById('previewBlogBtn');
const blogPreviewModal = document.getElementById('blogPreviewModal');
const closeBlogPreview = document.getElementById('closeBlogPreview');

previewBlogBtn.onclick = function() {
    // Get form values
    const title = document.getElementById('blogTitle').value;
    const category = document.getElementById('blogCategory').value;
    const author = document.getElementById('blogAuthor').value;
    const image = document.getElementById('blogImage').value;
    const content = document.getElementById('blogContent').value;
    const date = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });

    document.getElementById('previewModalTitle').textContent = title;
    document.getElementById('previewModalCategory').textContent = category;
    document.getElementById('previewModalAuthor').textContent = author;
    document.getElementById('previewModalDate').textContent = date;
    if (image) {
        document.getElementById('previewModalImage').src = image;
        document.getElementById('previewModalImage').style.display = 'block';
    } else {
        document.getElementById('previewModalImage').style.display = 'none';
    }
    document.getElementById('previewModalContent').innerHTML = content;
    blogPreviewModal.style.display = 'block';
    document.body.style.overflow = 'hidden';
};
closeBlogPreview.onclick = function() {
    blogPreviewModal.style.display = 'none';
    document.body.style.overflow = '';
};
window.addEventListener('click', function(event) {
    if (event.target == blogPreviewModal) {
        blogPreviewModal.style.display = 'none';
        document.body.style.overflow = '';
    }
});

// Mobile Nav Toggle
const mobileNavToggle = document.querySelector('.mobile-nav-toggle');
const headerNav = document.querySelector('.header-nav');

mobileNavToggle.addEventListener('click', function() {
    headerNav.classList.toggle('active');
});

// Dropdown Menu Logic
document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
    toggle.addEventListener('click', function(e) {
        e.preventDefault();
        const dropdownMenu = this.nextElementSibling;
        // Close other dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            if (menu !== dropdownMenu) {
                menu.classList.remove('show');
            }
        });
        dropdownMenu.classList.toggle('show');
    });
});

// Close dropdowns when clicking outside
window.addEventListener('click', function(e) {
    if (!e.target.matches('.dropdown-toggle')) {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            if (menu.classList.contains('show') && !menu.parentElement.contains(e.target)) {
                 menu.classList.remove('show');
            }
        });
    }
});

function handleStatusChange(select) {
    var form = select.closest('form');
    var apptInput = form.querySelector('.slot-appt-id-input');
    if (select.value == '1') {
        apptInput.style.display = 'inline-block';
    } else {
        apptInput.style.display = 'none';
        apptInput.value = '';
    }
}

// Delete User
function deleteUser(userId, userType, btn) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch('manage_user_process.php', {
            method: 'POST',
            body: new URLSearchParams({ action: 'delete', userId, userType })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const row = btn.closest('tr');
                if (row) row.remove();
            } else alert(data.message || 'Delete failed');
        });
    }
}
document.querySelectorAll('.delete-user').forEach(btn => {
    btn.onclick = function() {
        deleteUser(this.dataset.userId, this.dataset.userType, this);
    };
});
// Delete Service
function deleteService(serviceId, btn) {
    if (confirm('Are you sure you want to delete this service?')) {
        fetch('manage_service_process.php', {
            method: 'POST',
            body: new URLSearchParams({ action: 'delete', serviceId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const row = btn.closest('tr');
                if (row) row.remove();
            } else alert(data.message || 'Delete failed');
        });
    }
}
document.querySelectorAll('.delete-service').forEach(btn => {
    btn.onclick = function() {
        deleteService(this.dataset.serviceId, this);
    };
});
// Delete Blog
function deleteBlog(blogId) {
    if (confirm('Are you sure you want to delete this blog post?')) {
        fetch('manage_blog_process.php', {
            method: 'POST',
            body: new URLSearchParams({ action: 'delete', blogId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Delete failed');
        });
    }
}
document.querySelectorAll('.delete-blog').forEach(btn => {
    btn.onclick = function() {
        deleteBlog(this.dataset.blogId);
    };
});
</script>
<script src="../../assets/js/dashboard.js"></script>
</body>
</html>