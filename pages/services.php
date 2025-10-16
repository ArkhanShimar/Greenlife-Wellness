<?php
$page_title = "Our Services";
$additional_css = ["../assets/css/services.css"];
include '../includes/db.php';
include '../includes/header.php';

// Fetch services from database with category
$services = [];
$res = $conn->query("SELECT service_id, name, description, duration_minutes, price, category, image_path FROM services ORDER BY category, name");
while ($row = $res->fetch_assoc()) $services[] = $row;

// Define category names and icons based on actual categories
$category_names = [
    'nutrition' => 'Nutrition & Diet',
    'yoga' => 'Yoga & Meditation',
    'beauty' => 'Beauty & Spa',
    'therapy' => 'Therapy & Counseling',
    'wellness program' => 'Wellness Programs'
];

$category_icons = [
    'nutrition' => 'fas fa-apple-alt',
    'yoga' => 'fas fa-om',
    'beauty' => 'fas fa-spa',
    'therapy' => 'fas fa-brain',
    'wellness program' => 'fas fa-heart'
];

// Group services by category
$services_by_category = [];
foreach ($services as $service) {
    $category = strtolower(trim($service['category']));
    if (!isset($services_by_category[$category])) {
        $services_by_category[$category] = [];
    }
    $services_by_category[$category][] = $service;
}
?>

<!-- Hero Section -->
<section class="services-hero">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">Our Wellness Services</h1>
            <p class="hero-description">
                Discover our comprehensive range of wellness services designed to nurture your mind, body, and soul. 
                Each service is tailored to help you achieve optimal health and well-being.
            </p>
        </div>
    </div>
</section>



<!-- Category Selector -->
<section class="category-selector">
    <div class="container">
        <div class="category-nav">
            <?php foreach ($category_names as $category_key => $category_name): ?>
                <button class="category-btn" data-category="<?php echo $category_key; ?>">
                    <i class="<?php echo $category_icons[$category_key]; ?>"></i>
                    <span><?php echo $category_name; ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Services by Category -->
<div class="services-container">
    <?php foreach ($category_names as $category_key => $category_name): ?>
        <section class="service-category" id="category-<?php echo $category_key; ?>">
            <div class="container">
                <div class="category-header">
                    <h2><?php echo $category_name; ?></h2>
                    <p>Professional <?php echo strtolower($category_name); ?> services to support your wellness journey</p>
                </div>
                
                <div class="services-list">
                    <?php if (isset($services_by_category[$category_key]) && !empty($services_by_category[$category_key])): ?>
                        <?php foreach ($services_by_category[$category_key] as $service): ?>
                            <div class="service-item">
                                <div class="service-image">
                                    <?php if (!empty($service['image_path'])): ?>
                                        <img src="../assets/images/services/<?php echo htmlspecialchars($service['image_path']); ?>" alt="<?php echo htmlspecialchars($service['name']); ?>">
                                    <?php else: ?>
                                        <div class="service-icon">
                                            <i class="<?php echo $category_icons[$category_key]; ?>"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="service-content">
                                    <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                                    <p><?php echo htmlspecialchars($service['description']); ?></p>
                                    <div class="service-details">
                                        <div class="service-info">
                                            <span class="duration">
                                                <i class="fas fa-clock"></i>
                                                <?php echo htmlspecialchars($service['duration_minutes']); ?> minutes
                                            </span>
                                            <span class="price">
                                                <i class="fas fa-tag"></i>
                                                LKR <?php echo number_format($service['price'], 2); ?>
                                            </span>
                                        </div>
                                        <?php if (isset($_SESSION['user_id'])): ?>
                                            <a href="appointments.php?service=<?php echo $service['service_id']; ?>" class="btn btn-primary">
                                                <i class="fas fa-calendar-plus"></i>
                                                Book Now
                                            </a>
                                        <?php else: 
                                            // Create the redirect URL for the login page
                                            $redirect_url = urlencode("../pages/appointments.php?service=" . $service['service_id']);
                                        ?>
                                            <a href="login.php?redirect_url=<?php echo $redirect_url; ?>" class="btn btn-primary">
                                                <i class="fas fa-sign-in-alt"></i>
                                                Login to Book
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-services">
                            <i class="<?php echo $category_icons[$category_key]; ?>"></i>
                            <h3>No services available in this category</h3>
                            <p>Check back soon for new <?php echo strtolower($category_name); ?> services.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endforeach; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryBtns = document.querySelectorAll('.category-btn');
    const categorySelector = document.querySelector('.category-selector');
    const mainHeader = document.querySelector('.header');

    if (mainHeader && categorySelector) {
        // Set the sticky top position for the category selector to sit right below the main header
        categorySelector.style.top = `${mainHeader.offsetHeight}px`;

        // Add active class to the first category button by default
        if (categoryBtns.length > 0) {
            categoryBtns[0].classList.add('active');
        }

        categoryBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();

                const targetCategory = this.getAttribute('data-category');
                const targetElement = document.getElementById(`category-${targetCategory}`);

                if (targetElement) {
                    // Update active styling on the clicked button
                    categoryBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Calculate the total height of all sticky elements to offset the scroll
                    const headerHeight = mainHeader.offsetHeight;
                    const categoryNavHeight = categorySelector.offsetHeight;
                    const totalOffset = headerHeight + categoryNavHeight;

                    const elementPosition = targetElement.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - totalOffset;

                    // Execute the smooth scroll
                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>