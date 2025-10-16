<?php include '../includes/header.php'; ?>
<?php include '../includes/db.php';

// Fetch therapists from database
$therapists = [];
$stmt = $conn->prepare("SELECT therapist_id, name, speciality, qualification, profile_pic, experience FROM therapists ORDER BY name");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $therapists[] = $row;
    }
    $stmt->close();
}
?>
<head>
    <link rel="stylesheet" href="../assets/css/about.css">
    <!-- Google Fonts (Quicksand) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;700&display=swap" rel="stylesheet">
</head>

<main class="about-page">
    <section class="home" id="home">
        <div class="home-content">
            <h3>Heal. Restore. Thrive.</h3>
            <h1>The holistic way</h1>
            <h3><span class="multiple-text"></span></h3>
            <p>
                In a world that moves too fast, we invite you to slow down
                - to breathe deeply, move mindfully, and reconnect with the
                rhythm of nature. Here, ancient wisdom meets the present moment,
                guiding you toward harmony of body, clarity of mind, and peace
                of soul. Through the sacred practices of Ayurveda and Yoga, we help
                you awaken the healer within, cleanse what no longer serves you, and
                rediscover the stillness that has always been yours. This is more than
                wellness - it's a return to your true self.
            </p>
            <a href="register.php" class="btn">Join Us</a>
        </div>

        <div class="home-img">
            <img src="../assets/images/about.png" alt="Home Image" />
        </div>
    </section>

    <!-- Our Vision & Mission Section -->
    <section class="vision-mission-section">
        <div class="container">
            <div class="vision-mission-item">
                <h2 class="section-title text-center">Our Vision</h2>
                <p>To be the leading wellness destination in Sri Lanka, recognized for excellence in holistic healthcare, innovative treatment approaches, and unwavering commitment to client well-being. We envision a community where everyone has access to comprehensive wellness care that promotes lasting health and vitality.</p>
            </div>

            <div class="vision-mission-item">
                <h2 class="section-title text-center">Our Mission</h2>
                <p>To provide holistic, personalized wellness care that addresses the whole person - body, mind, and spirit. We are committed to empowering individuals to achieve optimal health through natural, evidence-based therapies and compassionate care.</p>
            </div>
        </div>
    </section>

    <!-- Our Team -->
    <section class="our-team">
        <div class="container">
            <h2 class="section-title text-center">Meet Our Team</h2>
            <p class="section-subtitle">Experienced professionals dedicated to your wellness</p>
            
            <div class="therapist-slider-container">
                <div class="therapist-slider" id="therapistSlider">
                    <?php if (!empty($therapists)): ?>
                        <?php foreach ($therapists as $index => $therapist): ?>
                            <div class="therapist-slide <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                                <div class="therapist-card">
                                    <div class="therapist-image">
                                        <?php if (!empty($therapist['profile_pic'])): ?>
                                            <img src="../assets/images/profiles/<?php echo htmlspecialchars($therapist['profile_pic']); ?>" alt="<?php echo htmlspecialchars($therapist['name']); ?>">
                                        <?php else: ?>
                                            <div class="default-avatar">
                                                <i class="fas fa-user-md"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="therapist-info">
                                        <h3><?php echo htmlspecialchars($therapist['name']); ?></h3>
                                        <p class="therapist-speciality"><?php echo htmlspecialchars($therapist['speciality']); ?></p>
                                        <p class="therapist-qualification"><?php echo htmlspecialchars($therapist['qualification']); ?></p>
                                        <?php if (!empty($therapist['experience'])): ?>
                                            <p class="therapist-experience"><?php echo htmlspecialchars($therapist['experience']); ?> years of experience</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="therapist-slide active">
                            <div class="therapist-card">
                                <div class="therapist-info">
                                    <h3>Our Expert Team</h3>
                                    <p>Our team of certified therapists and wellness experts are dedicated to providing you with the highest quality care and personalized treatment plans.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (is_array($therapists) && count($therapists) > 1): ?>
                    <button class="slider-nav prev" id="prevBtn">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="slider-nav next" id="nextBtn">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    
                    <div class="slider-dots">
                        <?php for ($i = 0; $i < count($therapists); $i++): ?>
                            <span class="dot <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>"></span>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Facility Tour -->
    <section class="facility-tour">
        <div class="container">
            <h2 class="section-title">Experience Our Facility</h2>
            <p class="section-subtitle">A serene environment for healing and relaxation</p>
            
            <div class="facility-features-list">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="feature-text">
                        <h3>Welcoming Reception</h3>
                        <p>Your journey to wellness begins in our warm and inviting reception area, designed to make you feel at home.</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-spa"></i>
                    </div>
                    <div class="feature-text">
                        <h3>Serene Treatment Rooms</h3>
                        <p>Private, calm spaces featuring natural lighting and decor to enhance your healing and relaxation during treatments.</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-praying-hands"></i>
                    </div>
                    <div class="feature-text">
                        <h3>Peaceful Yoga Studio</h3>
                        <p>A dedicated, peaceful environment for practice and meditation, equipped with premium mats and props.</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-seedling"></i>
                    </div>
                    <div class="feature-text">
                        <h3>Tranquil Garden Space</h3>
                        <p>A natural outdoor sanctuary for relaxation, meditation, and connecting with nature.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Typed.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/typed.js/2.1.0/typed.umd.js"></script>

<script>
// Therapist Slider Functionality
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('therapistSlider');
    const slides = document.querySelectorAll('.therapist-slide');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const dots = document.querySelectorAll('.dot');
    
    let currentIndex = 0;
    const totalSlides = slides.length;
    
    // Only initialize slider if there are multiple slides
    if (totalSlides <= 1) {
        return;
    }
    
    function showSlide(index) {
        // Hide all slides
        slides.forEach(slide => {
            slide.classList.remove('active');
        });
        
        // Remove active class from all dots
        dots.forEach(dot => {
            dot.classList.remove('active');
        });
        
        // Show current slide
        if (slides[index]) {
            slides[index].classList.add('active');
        }
        
        // Activate current dot
        if (dots[index]) {
            dots[index].classList.add('active');
        }
        
        currentIndex = index;
    }
    
    function nextSlide() {
        const nextIndex = (currentIndex + 1) % totalSlides;
        showSlide(nextIndex);
    }
    
    function prevSlide() {
        const prevIndex = (currentIndex - 1 + totalSlides) % totalSlides;
        showSlide(prevIndex);
    }
    
    // Event listeners
    if (nextBtn) {
        nextBtn.addEventListener('click', nextSlide);
    }
    
    if (prevBtn) {
        prevBtn.addEventListener('click', prevSlide);
    }
    
    // Dot navigation
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
        });
    });
    
    // Auto-slide every 5 seconds
    if (totalSlides > 1) {
        setInterval(nextSlide, 5000);
    }

    // Typed.js initialization
    const typed = new Typed('.multiple-text', {
        strings: ['Ayurvedic Fitness', 'Holistic Healing', 'Stress Relief Sessions', 'Herbal Nutrition Plans', 'Ayurvedic Therapies', 'Power Yoga'],
        typeSpeed: 60,
        backSpeed: 60,
        backDelay: 1000,
        loop: true,
    });
});
</script>

<?php include '../includes/footer.php'; ?>