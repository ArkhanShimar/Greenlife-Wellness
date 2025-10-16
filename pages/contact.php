<?php include '../includes/header.php'; ?>
<head>
    <link rel="stylesheet" href="../assets/css/contact.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<main class="contact-page">
    <!-- Get in Touch Banner -->
    <section class="contact-banner">
        <div class="container">
            <h1>Get in Touch</h1>
            <p>Have questions or need support? We'd love to hear from you. Here's how you can reach us.</p>
        </div>
    </section>

    <!-- Contact Boxes Section -->
    <section class="contact-boxes">
        <div class="container">
            <!-- Box 1: Need Assistance? -->
            <div class="contact-box">
                <div class="contact-box-icon">
                    <i class="fas fa-phone-alt"></i>
                    </div>
                <h2>Need Assistance?</h2>
                <p class="contact-description">
                    Have questions about our services, appointments, or wellness programs? Our team is ready to help.
                </p>
                <div class="contact-info-item">
                    <strong>Phone:</strong>
                    <a href="tel:+94112345678">+94 11 234 5678</a>
                </div>
                <div class="contact-info-item">
                    <strong>Email:</strong>
                    <a href="mailto:info@greenlifewellness.lk">info@greenlifewellness.lk</a>
                </div>
                </div>
                
            <!-- Box 2: Send a Real-Time Message -->
            <div class="contact-box">
                <div class="contact-box-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h2>Send a Real-Time Message</h2>
                <p class="contact-description">
                    For existing clients, the best way to get personalized support is by sending a message through your dashboard.
                </p>
                <?php if(isset($_SESSION['user_id'])): 
                    $dashboard_link = ($_SESSION['user_type'] == 'therapist') ? 'therapist.php' : 'client.php';
                ?>
                    <a href="dashboard/<?php echo $dashboard_link; ?>#messages" class="btn-primary">Go to Dashboard</a>
                <?php else: ?>
                    <a href="login.php" class="btn-primary">Login to Send Message</a>
                    <p class="login-prompt">
                        Don't have an account? <a href="register.php">Register here</a>.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Center Details Section -->
    <section class="center-details">
        <div class="container">
            <!-- Opening Hours -->
            <div class="detail-card">
                <div class="detail-icon"><i class="fas fa-clock"></i></div>
                <h3>Opening Hours</h3>
                <p><strong>Monday - Friday:</strong> 8:00 AM - 8:00 PM</p>
                <p><strong>Saturday:</strong> 9:00 AM - 5:00 PM</p>
                <p><strong>Sunday:</strong> Closed</p>
                    </div>

            <!-- Founder Info -->
            <div class="detail-card founder-card">
                <div class="founder-photo">
                    <img src="../assets/images/founder.avif" alt="Dr. Anil Perera, Founder">
                </div>
                <h3>Our Founder</h3>
                <p><strong>Dr. Anil Perera</strong></p>
                <p>Guiding our vision with decades of experience in holistic health.</p>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
             <div class="address-card">
                <div class="detail-icon"><i class="fas fa-map-marker-alt"></i></div>
                <h3>Our Address</h3>
                <p>123 Wellness Road, Colombo 05, Sri Lanka</p>
                <a href="https://maps.app.goo.gl/your-google-maps-link" target="_blank" class="btn-secondary">Get Directions</a>
                    </div>
            <div class="map-embed">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d31687.29298317005!2d79.845346!3d6.902244!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae259631a27e41f%3A0x193836c995166468!2sColombo!5e0!3m2!1sen!2slk!4v1620000000000!5m2!1sen!2slk" 
                    width="100%" 
                    height="100%" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy"
                    title="GreenLife Wellness Location">
                </iframe>
                <p class="map-notice"><strong>Note:</strong> Please replace the `src` in the iframe above with your actual Google Maps embed link.</p>
            </div>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>