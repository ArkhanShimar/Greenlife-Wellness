<?php 
// Check if user is logged in and is a client
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'client') {
    header("Location: ../login.php");
    exit();
}

if (isset($_SESSION['show_registration_success'])) {
    echo "<script>alert('Successfully registered!');</script>";
    unset($_SESSION['show_registration_success']);
}

include '../../includes/db.php';

// Get client data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();

// Get upcoming appointments
$client_email = $client['email'];
$appt_stmt = $conn->prepare("
    SELECT a.*, s.name as service_name, t.name as therapist_name
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    JOIN therapists t ON a.therapist_id = t.therapist_id
    WHERE a.client_email = ? AND a.status IN ('pending', 'confirmed') AND a.appointment_date >= CURDATE()
    ORDER BY a.appointment_date, a.appointment_time
");
$appt_stmt->bind_param("s", $client_email);
$appt_stmt->execute();
$appointments = $appt_stmt->get_result();

// Get past appointments for history
$history_stmt = $conn->prepare("
    SELECT a.*, s.name as service_name, t.name as therapist_name
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    JOIN therapists t ON a.therapist_id = t.therapist_id
    WHERE a.client_email = ? AND (a.status IN ('completed', 'cancelled') OR a.appointment_date < CURDATE())
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$history_stmt->bind_param("s", $client_email);
$history_stmt->execute();
$history_appointments = $history_stmt->get_result();

// Set unread count to 0 for now (will be implemented later)
$unread_count = 0;

// Get client's sent messages
$sent_messages_stmt = $conn->prepare("
    SELECT * FROM messages 
    WHERE sender_id = ? 
    ORDER BY created_at DESC
");
$sent_messages_stmt->bind_param("i", $user_id);
$sent_messages_stmt->execute();
$sent_messages = $sent_messages_stmt->get_result();

// Get unread replies count
$unread_replies_stmt = $conn->prepare("
    SELECT COUNT(*) as count FROM messages 
    WHERE sender_id = ? AND admin_reply IS NOT NULL AND is_read = 0
");
$unread_replies_stmt->bind_param("i", $user_id);
$unread_replies_stmt->execute();
$unread_result = $unread_replies_stmt->get_result();
    $unread_count = $unread_result->fetch_assoc()['count'];

// Mark all messages as read when this page is loaded
$mark_read_stmt = $conn->prepare("
    UPDATE messages 
    SET is_read = 1 
    WHERE sender_id = ? AND is_read = 0
");
$mark_read_stmt->bind_param("i", $user_id);
$mark_read_stmt->execute();
$mark_read_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - GreenLife Wellness</title>
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
            <li><a href="#appointments" class="nav-link" onclick="showSection(event, 'appointments')">My Appointments</a></li>
            <li><a href="../appointments.php" class="nav-link">Book Appointment</a></li>
            <li><a href="#messages" class="nav-link" onclick="showSection(event, 'messages')">Messages</a></li>
        </ul>
    </nav>
    <div class="header-right">
        <div class="dropdown">
            <div class="user-profile dropdown-toggle">
                <?php if (!empty($client['profile_pic'])): ?>
                    <img src="../../assets/images/profiles/<?php echo htmlspecialchars($client['profile_pic']); ?>" alt="Profile Picture" class="profile-pic">
                <?php else: ?>
                    <div class="profile-pic-placeholder"><i class="fas fa-user"></i></div>
                <?php endif; ?>
                <span><?php echo htmlspecialchars($client['first_name']); ?></span>
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
                <h1>Welcome, <?php echo htmlspecialchars($client['first_name']); ?>!</h1>
                <p>Your wellness journey starts here.</p>
            </div>
        </div>
        <!-- Dashboard Section -->
        <section id="dashboard" class="content-section active">
            <div class="dashboard-cards">
                <div class="card" onclick="showSection('appointments')" style="cursor: pointer;">
                    <h3>Upcoming Appointments</h3>
                    <div class="card-value"><?php echo $appointments->num_rows; ?></div>
                    <p><?php if ($appointments->num_rows > 0) {
                        $appt = $appointments->fetch_assoc();
                        echo 'Next: ' . date('F j, g:i A', strtotime($appt['appointment_date'] . ' ' . $appt['appointment_time']));
                        $appointments->data_seek(0); // Reset pointer
                    } else {
                        echo 'No upcoming';
                    } ?></p>
                </div>
                <div class="card" onclick="showSection('profile')" style="cursor: pointer;">
                    <h3>Profile</h3>
                    <div class="card-value">✔️</div>
                    <p><?php echo htmlspecialchars($client['email']); ?></p>
        </div>
                <div class="card" onclick="showSection('messages')" style="cursor: pointer;">
                    <h3>Messages</h3>
                    <div class="card-value"><?php echo $unread_count; ?></div>
                    <p>Unread</p>
                </div>
            </div>
            <div class="wellness-showcase">
                <div class="showcase-header">
                    <h3>Welcome to GreenLife Wellness</h3>
                    <p>Your journey to holistic wellness starts here</p>
                </div>
                
                <div class="services-highlight">
                    <div class="service-item">
                        <div class="service-icon">🌿</div>
                        <h4>Holistic Wellness</h4>
                        <p>Comprehensive care for mind, body, and spirit</p>
                    </div>
                    <div class="service-item">
                        <div class="service-icon">✨</div>
                        <h4>Expert Care</h4>
                        <p>Professional therapists dedicated to your well-being</p>
                    </div>
                </div>
                
                <div class="cta-section">
                    <div class="cta-content">
                        <h4>Ready to Transform Your Wellness?</h4>
                        <p>Book your first session today and experience the difference. Our signature services include therapeutic massage, aromatherapy, and personalized wellness programs designed just for you.</p>
                        <button class="btn btn-primary" onclick="showSection('appointments')">
                            <i class="fas fa-calendar-plus"></i>
                            Book Now
                        </button>
                    </div>
                    <div class="cta-stats">
                        <div class="stat">
                            <span class="stat-number">500+</span>
                            <span class="stat-label">Happy Clients</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number">10+</span>
                            <span class="stat-label">Varieties of Services</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number">15+</span>
                            <span class="stat-label">Years Experience</span>
        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Appointments Section -->
        <section id="appointments" class="content-section">
            <div class="appointments-container">
                <div class="appointments-header">
                    <h2>My Appointments</h2>
                </div>
                
                <?php if (isset($_SESSION['appointment_success'])): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $_SESSION['appointment_success']; ?>
                    </div>
                    <?php unset($_SESSION['appointment_success']); ?>
                <?php endif; ?>
                
                <div class="appointments-list">
                <?php if ($appointments->num_rows === 0): ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar"></i>
                            <h3>No appointments yet</h3>
                            <p>You haven't booked any appointments yet. Use the "Book Appointment" link in the header to schedule your first wellness session!</p>
                        </div>
                <?php else: ?>
                            <?php while($appt = $appointments->fetch_assoc()): ?>
                            <div class="appointment-card">
                                <div class="appointment-info">
                                    <h4><?php echo htmlspecialchars($appt['service_name']); ?></h4>
                                    <p>With <?php echo htmlspecialchars($appt['therapist_name']); ?></p>
                                    <p><?php echo date('F j, Y', strtotime($appt['appointment_date'])); ?> at <?php echo date('g:i A', strtotime($appt['appointment_time'])); ?></p>
                                    <span class="status-badge status-<?php echo $appt['status']; ?>"><?php echo ucfirst($appt['status']); ?></span>
                                    </div>
                                        <?php if ($appt['status'] === 'pending' || $appt['status'] === 'confirmed'): ?>
                                    <?php
                                    // Check if appointment is at least 4 days away
                                    $appointment_date = new DateTime($appt['appointment_date']);
                                    $current_date = new DateTime();
                                    $diff = $current_date->diff($appointment_date);
                                    $can_cancel = $diff->days >= 4;
                                    ?>
                                    <div class="appointment-actions">
                                        <?php if ($can_cancel): ?>
                                            <button class="btn btn-danger" onclick="cancelAppointment(<?php echo $appt['appointment_id']; ?>)">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        <?php else: ?>
                                            <span class="cancel-disabled">Cannot cancel (less than 4 days)</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>

                <div class="appointments-history">
                    <h3 class="section-title"><i class="fas fa-history"></i> Appointment History</h3>
                    <?php if ($history_appointments->num_rows === 0): ?>
                        <div class="empty-state">
                            <p>Your past appointments will appear here.</p>
                        </div>
                    <?php else: ?>
                        <?php while($appt = $history_appointments->fetch_assoc()): ?>
                            <div class="appointment-card-history">
                                <div class="appointment-info">
                                    <h4><?php echo htmlspecialchars($appt['service_name']); ?></h4>
                                    <p>With <?php echo htmlspecialchars($appt['therapist_name']); ?></p>
                                    <p><?php echo date('F j, Y', strtotime($appt['appointment_date'])); ?> at <?php echo date('g:i A', strtotime($appt['appointment_time'])); ?></p>
                                </div>
                                <span class="status-badge status-<?php echo $appt['status']; ?>"><?php echo ucfirst($appt['status']); ?></span>
                        </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
        </div>
        </section>
        <!-- Profile Section -->
        <section id="profile" class="content-section">
            <div class="profile-container">
                <div class="profile-header">
                    <h2>My Profile</h2>
                    <button id="editProfileBtn" class="btn btn-secondary" onclick="toggleProfileEdit()">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>
                </div>
                
                <?php if (isset($_SESSION['profile_success'])): ?>
                    <div class="success-message">
                        <?php echo $_SESSION['profile_success']; ?>
                    </div>
                    <?php unset($_SESSION['profile_success']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['profile_error'])): ?>
                    <div class="error-message">
                        <?php echo $_SESSION['profile_error']; ?>
                    </div>
                    <?php unset($_SESSION['profile_error']); ?>
                <?php endif; ?>
                
                <form id="profileForm" method="post" action="../../includes/update_profile.php" enctype="multipart/form-data">
                    <div class="profile-picture-section">
                        <div class="profile-pic-container">
                            <?php if (!empty($client['profile_pic'])): ?>
                                <img src="../../assets/images/profiles/<?php echo htmlspecialchars($client['profile_pic']); ?>" alt="Profile Picture" class="profile-pic" id="profilePic">
                            <?php else: ?>
                                <img src="../../assets/images/default-avatar.jpg" alt="Profile Picture" class="profile-pic" id="profilePic">
                            <?php endif; ?>
                        </div>
                        <div class="profile-pic-upload" id="profilePicUpload" style="display: none;">
                            <label for="newProfilePic">Change Profile Picture</label>
                            <input type="file" id="newProfilePic" name="new_profile_pic" accept="image/*">
                            <small>JPG, PNG, GIF - Max 2MB</small>
                        </div>
                    </div>
                    
                    <div class="profile-details">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="profileFirstName">First Name</label>
                                <input type="text" id="profileFirstName" name="first_name" value="<?php echo htmlspecialchars($client['first_name']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="profileLastName">Last Name</label>
                                <input type="text" id="profileLastName" name="last_name" value="<?php echo htmlspecialchars($client['last_name']); ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="profileUsername">Username</label>
                                <input type="text" id="profileUsername" name="username" value="<?php echo htmlspecialchars($client['username']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="profileEmail">Email</label>
                                <input type="email" id="profileEmail" name="email" value="<?php echo htmlspecialchars($client['email']); ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="profilePhone">Phone</label>
                                <input type="tel" id="profilePhone" name="phone" value="<?php echo htmlspecialchars($client['phone'] ?? ''); ?>" readonly>
                </div>
                            <div class="form-group">
                                <label for="profileDob">Date of Birth</label>
                                <input type="date" id="profileDob" name="dob" value="<?php echo htmlspecialchars($client['date_of_birth']); ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="profileAddress">Address</label>
                            <textarea id="profileAddress" name="address" rows="3" readonly><?php echo htmlspecialchars($client['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="profile-actions" id="profileActions" style="display: none;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="cancelProfileEdit()">
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
        
        <!-- Messages Section -->
        <section id="messages" class="content-section">
            <div class="messages-container">
                <div class="messages-header">
                    <h2><i class="fas fa-comments"></i> Messages</h2>
                    <button class="btn btn-primary" onclick="toggleMessageForm()">
                        <i class="fas fa-plus"></i> New Message
                    </button>
                </div>
                <!-- New Message Form (Hidden by default) -->
                <div id="newMessageForm" class="message-form-container" style="display: none;">
                    <div class="form-header">
                        <h3><i class="fas fa-paper-plane"></i> Send New Message</h3>
                        <button class="close-form" onclick="toggleMessageForm()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="success-message">
                            <?php echo $_SESSION['success']; ?>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="error-message">
                            <?php echo $_SESSION['error']; ?>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    <form id="messageForm" action="../../includes/messages.php" method="POST" class="message-form">
                        <input type="hidden" name="action" value="send_message">
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <select id="subject" name="subject" required>
                                <option value="">Select a subject</option>
                                <option value="General Inquiry">General Inquiry</option>
                                <option value="Appointment Booking">Appointment Booking</option>
                                <option value="Wellness Programs">Wellness Programs</option>
                                <option value="Feedback">Feedback</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="message">Your Message</label>
                            <textarea id="message" name="message" rows="4" required placeholder="Type your message here..."></textarea>
                        </div>
                        <div class="form-submit">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                                Send Message
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="toggleMessageForm()">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
                <!-- Chat Messages -->
                <div class="chat-messages">
                    <?php if ($sent_messages->num_rows === 0): ?>
                        <div class="empty-chat">
                            <div class="empty-chat-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <h3>No messages yet</h3>
                            <p>Start a conversation with our wellness team!</p>
                            <button class="btn btn-primary" onclick="toggleMessageForm()">
                                <i class="fas fa-plus"></i> Send First Message
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="messages-timeline">
                            <?php while($msg = $sent_messages->fetch_assoc()): ?>
                                <div class="message-thread" data-message-id="<?php echo $msg['message_id']; ?>">
                                    <!-- Client Message -->
                                    <div class="message-bubble client-message">
                                        <div class="message-header">
                                            <div class="message-sender">
                                                <div class="sender-avatar">
                                                    <?php if (!empty($client['profile_pic'])): ?>
                                                        <img src="../../assets/images/profiles/<?php echo htmlspecialchars($client['profile_pic']); ?>" alt="You">
                                                    <?php else: ?>
                                                        <i class="fas fa-user"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="sender-info">
                                                    <span class="sender-name">You</span>
                                                    <span class="message-time"><?php echo date('M j, g:i A', strtotime($msg['created_at'])); ?></span>
                                                </div>
                                            </div>
                                            <div class="message-status">
                                                <span class="status-badge status-<?php echo $msg['status']; ?>"><?php echo ucfirst($msg['status']); ?></span>
                                                <?php if ($msg['is_read']): ?>
                                                    <span class="read-indicator"><i class="fas fa-check-double"></i></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="message-content">
                                            <div class="message-subject"><?php echo htmlspecialchars($msg['subject']); ?></div>
                                            <div class="message-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                                        </div>
                                    </div>
                                    <?php if ($msg['admin_reply']): ?>
                                        <!-- Admin Reply -->
                                        <div class="message-bubble admin-message">
                                            <div class="message-header">
                                                <div class="message-sender">
                                                    <div class="sender-avatar admin-avatar">
                                                        <i class="fas fa-user-tie"></i>
                                                    </div>
                                                    <div class="sender-info">
                                                        <span class="sender-name"><?php echo htmlspecialchars($msg['replied_by_name'] ?? 'GreenLife Team'); ?></span>
                                                        <span class="message-time"><?php echo date('M j, g:i A', strtotime($msg['replied_at'])); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="message-content">
                                                <div class="message-text"><?php echo nl2br(htmlspecialchars($msg['admin_reply'])); ?></div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <!-- Waiting for Reply -->
                                        <div class="message-status-indicator">
                                            <div class="status-dot"></div>
                                            <span>Waiting for reply...</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
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

// Check for hash in URL and show corresponding section
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash.substring(1);
    if (hash && document.getElementById(hash)) {
        showSection(event, hash);
    }
});

// Toggle message form visibility
function toggleMessageForm() {
    const form = document.getElementById('newMessageForm');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth' });
    } else {
        form.style.display = 'none';
    }
}

// Toggle profile edit mode
function toggleProfileEdit() {
    const inputs = document.querySelectorAll('#profileForm input, #profileForm textarea');
    const editBtn = document.getElementById('editProfileBtn');
    const actions = document.getElementById('profileActions');
    const picUpload = document.getElementById('profilePicUpload');
    
    // Toggle readonly state
    inputs.forEach(input => {
        if (input.type !== 'file' && input.type !== 'hidden') {
            input.readOnly = !input.readOnly;
        }
    });
    
    // Toggle button text and functionality
    if (editBtn.innerHTML.includes('Edit')) {
        editBtn.innerHTML = '<i class="fas fa-times"></i> Cancel Edit';
        editBtn.className = 'btn btn-secondary';
        actions.style.display = 'flex';
        picUpload.style.display = 'block';
    } else {
        editBtn.innerHTML = '<i class="fas fa-edit"></i> Edit Profile';
        editBtn.className = 'btn btn-secondary';
        actions.style.display = 'none';
        picUpload.style.display = 'none';
        
        // Reset form to original values
        resetProfileForm();
    }
}

// Cancel profile edit
function cancelProfileEdit() {
    const editBtn = document.getElementById('editProfileBtn');
    const actions = document.getElementById('profileActions');
    const picUpload = document.getElementById('profilePicUpload');
    
    editBtn.innerHTML = '<i class="fas fa-edit"></i> Edit Profile';
    actions.style.display = 'none';
    picUpload.style.display = 'none';
    
    // Reset form to original values
    resetProfileForm();
}

// Reset form to original values
function resetProfileForm() {
    const inputs = document.querySelectorAll('#profileForm input, #profileForm textarea');
    inputs.forEach(input => {
        if (input.type !== 'file' && input.type !== 'hidden') {
            input.readOnly = true;
        }
    });
    
    // Clear file input
    const fileInput = document.getElementById('newProfilePic');
    if (fileInput) {
        fileInput.value = '';
    }
}

// Cancel appointment function
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
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            if (menu !== dropdownMenu) {
                menu.classList.remove('show');
            }
        });
        dropdownMenu.classList.toggle('show');
    });
});

window.addEventListener('click', function(e) {
    if (!e.target.matches('.dropdown-toggle')) {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            if (menu.classList.contains('show') && !menu.parentElement.contains(e.target)) {
                 menu.classList.remove('show');
            }
        });
    }
});

// Message form submission
document.getElementById('messageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Disable submit button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    
    fetch('../../includes/messages.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Message sent successfully!');
            this.reset();
            toggleMessageForm();
            location.reload(); // Refresh to show new message
        } else {
            alert('Error: ' + (data.error || 'Failed to send message'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error sending message. Please try again.');
    })
    .finally(() => {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});
</script>
</body>
</html>