<?php 
// Check if user is logged in and is a therapist
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'therapist') {
    header("Location: ../login.php");
    exit();
}

include '../../includes/db.php';

// Get therapist data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM therapists WHERE therapist_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$therapist = $result->fetch_assoc();

// Get upcoming appointments
$upcoming_stmt = $conn->prepare("
    SELECT a.*, s.name as service_name, u.first_name as client_first, u.last_name as client_last
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    JOIN users u ON a.client_email = u.email
    WHERE a.therapist_id = ? AND a.appointment_date >= ? AND a.status IN ('confirmed')
    ORDER BY a.appointment_date, a.appointment_time
");
$today = date('Y-m-d');
$upcoming_stmt->bind_param("is", $user_id, $today);
$upcoming_stmt->execute();
$upcoming_appointments = $upcoming_stmt->get_result();

// Get pending appointments
$pending_stmt = $conn->prepare("
    SELECT a.*, s.name as service_name, u.first_name as client_first, u.last_name as client_last
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    JOIN users u ON a.client_email = u.email
    WHERE a.therapist_id = ? AND a.status = 'pending'
    ORDER BY a.appointment_date, a.appointment_time
");
$pending_stmt->bind_param("i", $user_id);
$pending_stmt->execute();
$pending_appointments = $pending_stmt->get_result();

// Get completed appointments
$completed_stmt = $conn->prepare("
    SELECT a.*, s.name as service_name, a.client_name
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    WHERE a.therapist_id = ? AND a.status = 'completed'
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$completed_stmt->bind_param("i", $therapist['therapist_id']);
$completed_stmt->execute();
$completed_appointments = $completed_stmt->get_result();

// Get unread messages count
$unread_count = 0;
$unread_stmt = $conn->prepare("SELECT COUNT(*) as count FROM messages WHERE admin_reply IS NULL AND status = 'pending'");
if ($unread_stmt) {
    $unread_stmt->execute();
    $unread_result = $unread_stmt->get_result();
    $unread_count = $unread_result->fetch_assoc()['count'];
    $unread_stmt->close();
}

// Get all messages for therapist
$messages_stmt = $conn->prepare("
    SELECT * FROM messages 
    ORDER BY created_at DESC
");
$messages_stmt->execute();
$messages = $messages_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Therapist Dashboard - GreenLife Wellness</title>
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
            <li><a href="#manage-blog" class="nav-link" onclick="showSection(event, 'manage-blog')">Manage Blog</a></li>
            <li><a href="#messages" class="nav-link" onclick="showSection(event, 'messages')">Messages</a></li>
            <li><a href="#availability" class="nav-link" onclick="showSection(event, 'availability')">Availability</a></li>
            </ul>
        </nav>
    <div class="header-right">
        <div class="dropdown">
            <div class="user-profile dropdown-toggle">
                <?php if (!empty($therapist['profile_pic'])): ?>
                    <img src="../../assets/images/profiles/<?php echo htmlspecialchars($therapist['profile_pic']); ?>" alt="Profile Picture" class="profile-pic">
                <?php else: ?>
                    <div class="profile-pic-placeholder"><i class="fas fa-user"></i></div>
                <?php endif; ?>
                <span><?php echo htmlspecialchars($therapist['name']); ?></span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="dropdown-menu">
                <a href="../../index.php">Home</a>
                <a href="#profile" onclick="showSection(event, 'profile')">My Profile</a>
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
                <h1>Therapist Portal</h1>
                <p>Here's what's happening at GreenLife Wellness today.</p>
            </div>
        </div>
        <!-- Dashboard Section -->
        <section id="dashboard" class="content-section active">
            <div class="dashboard-cards">
                <div class="card" onclick="showSection('appointments')" style="cursor: pointer;">
                    <h3>Upcoming Appointments</h3>
                    <div class="card-value"><?php echo $upcoming_appointments->num_rows; ?></div>
                </div>
                <div class="card" onclick="showSection('appointments')" style="cursor: pointer;">
                    <h3>Pending Approvals</h3>
                    <div class="card-value"><?php echo $pending_appointments->num_rows; ?></div>
                </div>
                <div class="card" onclick="showSection('profile')" style="cursor: pointer;">
                    <h3>My Profile</h3>
                    <p>View and manage your profile</p>
                </div>
            </div>
            
            <div class="wellness-showcase">
                <div class="showcase-header">
                    <h3>Welcome Back, <?php echo htmlspecialchars($therapist['name']); ?>!</h3>
                    <p>Ready to make a difference in someone's wellness journey today?</p>
                </div>
                
                <div class="services-highlight">
                    <div class="service-item">
                        <div class="service-icon">💪</div>
                        <h4>Your Expertise</h4>
                        <p>Share your skills and help clients achieve wellness</p>
                    </div>
                    <div class="service-item">
                        <div class="service-icon">🤝</div>
                        <h4>Client Care</h4>
                        <p>Provide exceptional care and therapeutic treatments</p>
                    </div>
                </div>
                
                <div class="cta-section">
                    <div class="cta-content">
                        <h4>Your Schedule</h4>
                        <p>You have <?php echo $upcoming_appointments->num_rows; ?> upcoming and <?php echo $pending_appointments->num_rows; ?> pending appointments. Your expertise makes a real difference in the lives of your clients.</p>
                        <button class="btn btn-primary" onclick="showSection('appointments')">
                            <i class="fas fa-calendar-check"></i>
                            View Schedule
                        </button>
                    </div>
                    <div class="cta-stats">
                        <div class="stat">
                            <span class="stat-number"><?php echo $upcoming_appointments->num_rows; ?></span>
                            <span class="stat-label">Upcoming</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number"><?php echo $pending_appointments->num_rows; ?></span>
                            <span class="stat-label">Pending</span>
                        </div>
                    </div>
                </div>
        </div>
        </section>
        <!-- Appointments Section -->
        <section id="appointments" class="content-section">
            <div class="appointments-list">
                <h2>Upcoming Appointments</h2>
                <?php if ($upcoming_appointments->num_rows === 0): ?>
                    <div class="empty-state">No upcoming appointments.</div>
                <?php else: ?>
                            <?php while($appt = $upcoming_appointments->fetch_assoc()): ?>
                        <div class="appointment-card">
                            <div class="appointment-info">
                                <h4><?php echo htmlspecialchars($appt['service_name']); ?></h4>
                                <p>With <?php echo htmlspecialchars($appt['client_first'] . ' ' . $appt['client_last']); ?></p>
                                <p><?php echo date('F j, Y', strtotime($appt['appointment_date'])); ?> at <?php echo date('g:i A', strtotime($appt['appointment_time'])); ?></p>
                                <span class="status-badge status-confirmed">Confirmed</span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                <h2>Pending Approvals</h2>
                <?php if ($pending_appointments->num_rows === 0): ?>
                    <div class="empty-state">No pending appointments.</div>
                <?php else: ?>
                            <?php while($appt = $pending_appointments->fetch_assoc()): ?>
                        <div class="appointment-card">
                            <div class="appointment-info">
                                <h4><?php echo htmlspecialchars($appt['service_name']); ?></h4>
                                <p>With <?php echo htmlspecialchars($appt['client_first'] . ' ' . $appt['client_last']); ?></p>
                                <p><?php echo date('F j, Y', strtotime($appt['appointment_date'])); ?> at <?php echo date('g:i A', strtotime($appt['appointment_time'])); ?></p>
                                <span class="status-badge status-pending">Pending</span>
                                    </div>
                            <div class="appointment-actions">
                                <button class="btn btn-success" onclick="approveAppointment(<?php echo $appt['appointment_id']; ?>)">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            </div>
                        </div>
                        <?php endwhile; ?>
                <?php endif; ?>

                <h2>Completed Appointments</h2>
                <?php if ($completed_appointments->num_rows === 0): ?>
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <h4>No completed appointments</h4>
                        <p>Your past appointments will appear here.</p>
                    </div>
                <?php else: ?>
                    <?php while($appt = $completed_appointments->fetch_assoc()): ?>
                        <div class="appointment-card">
                            <div class="appointment-info">
                                <h4><?php echo htmlspecialchars($appt['service_name']); ?></h4>
                                <p>With <?php echo htmlspecialchars($appt['client_name']); ?></p>
                                <p><?php echo date('F j, Y', strtotime($appt['appointment_date'])); ?> at <?php echo date('g:i A', strtotime($appt['appointment_time'])); ?></p>
                                <span class="status-badge status-completed">Completed</span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </section>
        <!-- Availability Section -->
        <section id="availability" class="content-section">
            <h2>Manage Your Availability</h2>
            <?php if (isset($_SESSION['availability_success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['availability_success']; unset($_SESSION['availability_success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['availability_error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['availability_error']; unset($_SESSION['availability_error']); ?></div>
            <?php endif; ?>
            <form action="../../includes/manage_availability.php" method="POST" class="availability-form">
                <label for="available_date">Date:</label>
                <input type="date" name="available_date" id="available_date" required>
                <label for="start_time">Start Time:</label>
                <input type="time" name="start_time" id="start_time" required>
                <label for="end_time">End Time:</label>
                <input type="time" name="end_time" id="end_time" required>
                <button type="submit" name="add_availability" class="btn btn-primary">Add Slot</button>
            </form>
            <h3>Your Upcoming Availability</h3>
            <?php
            $availability_stmt = $conn->prepare("SELECT * FROM therapist_availability WHERE therapist_id = ? AND available_date >= ? ORDER BY available_date, start_time");
            $availability_stmt->bind_param("is", $user_id, $today);
            $availability_stmt->execute();
            $availability_result = $availability_stmt->get_result();
            if ($availability_result->num_rows === 0): ?>
                <div>No availability slots set.</div>
            <?php else:
                // Group slots by date
                $slots_by_date = [];
                while($slot = $availability_result->fetch_assoc()) {
                    $slots_by_date[$slot['available_date']][] = $slot;
                }
                foreach ($slots_by_date as $date => $slots): ?>
                    <h4 style="margin-top:2em;margin-bottom:0.5em;">
                        <?php echo date('l, F j, Y', strtotime($date)); ?>
                    </h4>
                    <table class="availability-table" style="margin-bottom:1.5em;">
                <thead>
                    <tr>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                        <?php foreach ($slots as $slot): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(substr($slot['start_time'], 0, 5)); ?></td>
                        <td><?php echo htmlspecialchars(substr($slot['end_time'], 0, 5)); ?></td>
                        <td><?php echo $slot['is_booked'] ? 'Booked' : 'Available'; ?></td>
                        <td>
                            <?php if (!$slot['is_booked']): ?>
                            <form action="../../includes/manage_availability.php" method="POST" style="display:inline;">
                                <input type="hidden" name="slot_id" value="<?php echo $slot['id']; ?>">
                                <button type="submit" name="delete_availability" class="btn btn-danger btn-sm" onclick="return confirm('Delete this slot?');">Delete</button>
                            </form>
                            <?php else: ?>
                                --
                            <?php endif; ?>
                        </td>
                    </tr>
                        <?php endforeach; ?>
                </tbody>
            </table>
                <?php endforeach;
            endif; ?>
        </section>
        <!-- Profile Section -->
        <section id="profile" class="content-section">
            <div class="profile-container">
                <div class="profile-header">
                </div>
                
                <div class="profile-picture-section">
                    <div class="profile-pic-container">
                        <?php if (!empty($therapist['profile_pic'])): ?>
                            <img src="../../assets/images/profiles/<?php echo htmlspecialchars($therapist['profile_pic']); ?>" alt="Profile Picture" class="profile-pic">
                        <?php else: ?>
                            <img src="../../assets/images/default-avatar.jpg" alt="Profile Picture" class="profile-pic">
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="profile-details">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="profileName">Full Name:</label>
                            <input type="text" id="profileName" value="<?php echo htmlspecialchars($therapist['name']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="profileEmail">Email:</label>
                            <input type="email" id="profileEmail" value="<?php echo htmlspecialchars($therapist['email']); ?>" readonly>
                    </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="profileQualification">Qualification:</label>
                            <input type="text" id="profileQualification" value="<?php echo htmlspecialchars($therapist['qualification'] ?? ''); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="profileSpeciality">Speciality:</label>
                            <input type="text" id="profileSpeciality" value="<?php echo htmlspecialchars($therapist['speciality'] ?? ''); ?>" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="profileExperience">Experience:</label>
                        <input type="text" id="profileExperience" value="<?php echo htmlspecialchars($therapist['experience'] ?? ''); ?>" readonly>
                    </div>
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
                                                        echo htmlspecialchars($msg['replied_by_name']) . ' (' . ucfirst($msg['replied_by_type'] ?? 'therapist') . ')';
                                                    } else {
                                                        // Fallback for existing data
                                                        echo htmlspecialchars($therapist['name']) . ' (Therapist)';
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
                                        <button type="button" class="btn-action edit-blog"
                                            data-id="<?php echo $blog['id']; ?>"
                                            data-title="<?php echo htmlspecialchars($blog['title'], ENT_QUOTES); ?>"
                                            data-category="<?php echo htmlspecialchars($blog['category'], ENT_QUOTES); ?>"
                                            data-author="<?php echo htmlspecialchars($blog['author'], ENT_QUOTES); ?>"
                                            data-status="<?php echo htmlspecialchars($blog['status'], ENT_QUOTES); ?>"
                                            data-excerpt="<?php echo htmlspecialchars($blog['excerpt'], ENT_QUOTES); ?>"
                                            data-image="<?php echo htmlspecialchars($blog['image_path'], ENT_QUOTES); ?>"
                                            data-content='<?php echo htmlspecialchars($blog['content'], ENT_QUOTES); ?>'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn-action delete-blog" data-blog-id="<?php echo $blog['id']; ?>"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
        </div>
    </div>
        </section>
    </main>
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
<!-- Blog Preview Modal -->
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
        body: 'action=admin_reply&message_id=' + messageId + '&reply=' + encodeURIComponent(replyText) + '&update=1'
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

// Approve appointment function
function approveAppointment(appointmentId) {
    if (confirm('Are you sure you want to approve this appointment?')) {
        fetch('../../includes/appointment_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=approve&appointment_id=' + appointmentId
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
            alert('Error approving appointment');
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

// Blog Delete Logic (Therapist)
function deleteBlog(blogId, btn) {
    if (confirm('Are you sure you want to delete this blog post?')) {
        fetch('therapist.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=delete&blogId=' + encodeURIComponent(blogId)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Remove the row from the table
                const row = btn.closest('tr');
                if (row) row.remove();
            } else {
                alert(data.message || 'Delete failed');
            }
        });
    }
}
document.querySelectorAll('.delete-blog').forEach(btn => {
    btn.onclick = function() {
        deleteBlog(this.dataset.blogId, this);
    };
});
</script>
</body>
</html>