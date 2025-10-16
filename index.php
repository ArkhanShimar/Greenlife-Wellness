<?php
// session_start();
$page_title = "Home";
$additional_css = ["assets/css/index.css"];
include 'includes/header.php';
?>

<!-- Hero Section -->
    <section class="hero">
    <div class="hero-background">
        <video autoplay muted loop playsinline class="hero-video" id="heroVideo">
            <source src="assets/videos/hero-video.mp4" type="video/mp4">
            <!-- Fallback for browsers that don't support video -->
            <div class="video-fallback">
                <img src="assets/images/hero-bg.jpg" alt="Wellness Background" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
        </video>
        <div class="hero-overlay"></div>
    </div>
    
        <div class="container">
            <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                Rejuvenate Your Soul
                </h1>
                <p class="hero-description">
                    Discover personalized wellness services designed to nurture your mind, body, and soul. 
                    Connect with certified therapists and wellness experts for a journey to optimal health.
                </p>
                <div class="hero-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="pages/appointments.php" class="btn-hero">
                            <i class="fas fa-calendar-plus"></i>
                            Book Appointment
                        </a>
                    <?php else: ?>
                        <a href="pages/login.php" class="btn-hero">
                            <i class="fas fa-calendar-plus"></i>
                            Book Appointment
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="hero-scroll">
        <a href="#why-choose" class="scroll-arrow-link" aria-label="Scroll down">
            <span>Scroll to explore</span>
            <i class="fas fa-chevron-down"></i>
        </a>
    </div>
    </section>

<!-- Why Choose Section -->
<section class="why-choose" id="why-choose">
        <div class="container">
    <div class="section-header">
      <h2 class="section-title">Why Choose GreenLife Wellness?</h2>
      <p class="section-description">Experience holistic care, expert guidance, and a nurturing environment for your wellness journey.</p>
    </div>
    <div class="why-features">
      <div class="why-feature">
        <span class="why-icon">🌱</span>
        <h3>Certified Experts</h3>
        <p>Our team consists of licensed and certified wellness professionals with years of experience.</p>
                    </div>
      <div class="why-feature">
        <span class="why-icon">🤝</span>
        <h3>Personalized Care</h3>
        <p>Every treatment and session is tailored to your unique needs and goals.</p>
                </div>
      <div class="why-feature">
        <span class="why-icon">🛡️</span>
        <h3>Safe & Supportive</h3>
        <p>We provide a safe, welcoming environment and ongoing support for your well-being.</p>
                    </div>
      <div class="why-feature">
        <span class="why-icon">✨</span>
        <h3>Holistic Approach</h3>
        <p>We blend ancient wisdom with modern science for truly holistic wellness.</p>
                </div>
      <div class="why-feature">
        <span class="why-icon">⏰</span>
        <h3>Flexible Scheduling</h3>
        <p>Book appointments at your convenience with our flexible scheduling and extended hours.</p>
                    </div>
      <div class="why-feature">
        <span class="why-icon">🌿</span>
        <h3>Natural Methods</h3>
        <p>We embrace natural, evidence-based therapies that work in harmony with your body.</p>
                </div>
            </div>
        </div>
    </section>

<!-- Services Preview Section -->
<section class="services-preview">
        <div class="container">
        <div class="section-header">
            <h2 class="section-title">Holistic Wellness Services</h2>
            <p class="section-description">
                We offer personalized therapies blending ancient wisdom with modern science.<br>
                From Ayurvedic detox to therapeutic yoga, our experts tailor each experience to your unique needs.
            </p>
        </div>
        <div class="services-list-simple">
            <div class="service-simple">Ayurvedic Therapies</div>
            <div class="service-simple">Yoga &amp; Meditation</div>
            <div class="service-simple">Deep Tissue Massage</div>
            <div class="service-simple">Nutrition Counseling</div>
        </div>
        <div class="services-cta" style="margin-top:2rem;">
            <a href="pages/services.php" class="btn-services">Explore All Services</a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
    <div class="section-header">
            <h2 class="section-title">What Our Clients Say</h2>
        <p class="section-description">
            Real stories from people who have transformed their lives with our wellness services
        </p>
    </div>
            
    <div class="testimonials-grid">
        <div class="testimonial-card">
                    <div class="testimonial-content">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                            </div>
                <p class="testimonial-text">
                    "GreenLife Wellness has completely transformed my approach to health. The personalized care and expert guidance have helped me achieve a balanced lifestyle I never thought possible."
                </p>
                        </div>
            <div class="testimonial-author">
                <div class="author-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="author-info">
                    <h4 class="author-name">Sarah Johnson</h4>
                    <p class="author-title">Wellness Client</p>
                        </div>
                    </div>
                </div>
                
        <div class="testimonial-card">
                    <div class="testimonial-content">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                            </div>
                <p class="testimonial-text">
                    "The therapeutic massage sessions have been incredible for managing my stress and chronic pain. The therapists are truly skilled and caring professionals."
                </p>
                        </div>
            <div class="testimonial-author">
                <div class="author-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="author-info">
                    <h4 class="author-name">Michael Chen</h4>
                    <p class="author-title">Massage Client</p>
                        </div>
                    </div>
                </div>
                
        <div class="testimonial-card">
                    <div class="testimonial-content">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                            </div>
                <p class="testimonial-text">
                    "The nutrition coaching program helped me develop a healthy relationship with food and achieve my fitness goals sustainably. Highly recommended!"
                </p>
                        </div>
            <div class="testimonial-author">
                <div class="author-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="author-info">
                    <h4 class="author-name">Emily Rodriguez</h4>
                    <p class="author-title">Nutrition Client</p>
                    </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
            <h2 class="cta-title">Ready to Start Your Wellness Journey?</h2>
            <p class="cta-description">
                Take the first step towards a healthier, more balanced life. Book your appointment today and experience the transformative power of holistic wellness.
            </p>
            <div class="cta-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="pages/appointments.php" class="btn btn-primary btn-lg">
                        Book Your Session
                    </a>
                <?php else: ?>
                    <a href="pages/login.php" class="btn btn-primary btn-lg">
                        Book Your Session
                    </a>
                <?php endif; ?>
            </div>
            </div>
        </div>
    </section>

<?php include 'includes/footer.php'; ?> 