    </main>

    <?php
    // Determine the base path for assets (same logic as header)
    $current_file = $_SERVER['PHP_SELF'];
    $is_in_pages = strpos($current_file, '/pages/') !== false;
    $base_path = $is_in_pages ? '../' : '';
    ?>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <!-- Company Info -->
                <div class="footer-section">
                    <div class="footer-brand">
                        <a href="<?php echo $base_path; ?>index.php" class="logo">
                            <img src="<?php echo $base_path; ?>assets/images/logo.png" alt="GreenLife Wellness Logo" class="logo-image">
                            <span class="logo-wellness">GreenLife</span>
                            <span class="logo-wellness">Wellness</span>
                        </a>
                    </div>
                    <p class="footer-description">
                        Transform your life with holistic wellness services. We provide personalized care through certified therapists and wellness experts to help you achieve optimal health and well-being.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link" aria-label="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="footer-section">
                    <h3 class="footer-title">Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo $base_path; ?>index.php">Home</a></li>
                        <li><a href="<?php echo $base_path; ?>pages/services.php">Services</a></li>
                        <li><a href="<?php echo $base_path; ?>pages/about.php">About Us</a></li>
                        <li><a href="<?php echo $base_path; ?>pages/contact.php">Contact</a></li>
                        <li><a href="<?php echo isset($_SESSION['user_id']) ? $base_path . 'pages/appointments.php' : $base_path . 'pages/login.php'; ?>">Book Appointment</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div class="footer-section">
                    <h3 class="footer-title">Contact Info</h3>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <p>123 Wellness Street</p>
                                <p>Colombo 03, Sri Lanka</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <p>+94 11 234 5678</p>
                                <p>Mon-Fri: 8AM-8PM</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <p>info@greenlifewellness.lk</p>
                                <p>support@greenlifewellness.lk</p>
                            </div>
                        </div>
                </div>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; <?php echo date('Y'); ?> GreenLife Wellness. All rights reserved.</p>
                    <div class="footer-bottom-links">
                        <a href="<?php echo $base_path; ?>pages/privacy.php">Privacy Policy</a>
                        <a href="<?php echo $base_path; ?>pages/terms.php">Terms of Service</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top Button -->
    <button class="back-to-top" id="backToTop" aria-label="Back to top">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- JavaScript Files -->
    <script src="<?php echo $base_path; ?>assets/js/main.js"></script>
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Custom Scripts -->
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileToggle = document.querySelector('.mobile-menu-toggle');
            const mobileNav = document.querySelector('.mobile-nav');
            const mobileClose = document.querySelector('.mobile-nav-close');
            const mobileNavLinks = document.querySelectorAll('.mobile-nav-list a');

            if (mobileToggle) {
                mobileToggle.addEventListener('click', function() {
                    mobileNav.classList.add('active');
                    document.body.style.overflow = 'hidden';
                });
            }

            if (mobileClose) {
                mobileClose.addEventListener('click', function() {
                    mobileNav.classList.remove('active');
                    document.body.style.overflow = '';
                });
            }

            // Close mobile nav when clicking on links
            mobileNavLinks.forEach(link => {
                link.addEventListener('click', function() {
                    mobileNav.classList.remove('active');
                    document.body.style.overflow = '';
                });
            });

            // Back to top button
            const backToTopBtn = document.getElementById('backToTop');
            if (backToTopBtn) {
                window.addEventListener('scroll', function() {
                    if (window.pageYOffset > 300) {
                        backToTopBtn.classList.add('show');
                    } else {
                        backToTopBtn.classList.remove('show');
                    }
                });

                backToTopBtn.addEventListener('click', function() {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            }

            // User dropdown toggle
            const userMenu = document.querySelector('.user-menu');
            if (userMenu) {
                userMenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                    this.classList.toggle('active');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function() {
                    userMenu.classList.remove('active');
                });
            }

            // Hero video handling
            const heroVideo = document.getElementById('heroVideo');
            if (heroVideo) {
                // Show video when it starts playing
                heroVideo.addEventListener('loadeddata', function() {
                    this.classList.add('loaded');
                });

                // Handle video loading errors
                heroVideo.addEventListener('error', function() {
                    console.log('Video failed to load, using fallback');
                    this.style.display = 'none';
                    const fallback = this.querySelector('.video-fallback');
                    if (fallback) {
                        fallback.style.display = 'flex';
                    }
                });

                // Ensure video plays on mobile
                heroVideo.addEventListener('canplay', function() {
                    this.play().catch(function(error) {
                        console.log('Video autoplay failed:', error);
                    });
                });

                // Fallback for browsers that don't support autoplay
                setTimeout(function() {
                    if (heroVideo.paused && heroVideo.readyState >= 2) {
                        heroVideo.play().catch(function(error) {
                            console.log('Video autoplay failed after timeout:', error);
                        });
                    }
                }, 1000);
            }
        });
    </script>
</body>
</html>