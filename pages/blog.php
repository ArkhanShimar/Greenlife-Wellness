<?php
require_once '../includes/db.php';

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 6;
$offset = ($page - 1) * $per_page;

// Build the SQL query
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(title LIKE ? OR content LIKE ? OR excerpt LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($category)) {
    $where_conditions[] = "category = ?";
    $params[] = $category;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM blogs $where_clause";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$count_stmt->execute();
$total_result = $count_stmt->get_result();
$total_blogs = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_blogs / $per_page);

// Get blogs with pagination
$sql = "SELECT * FROM blogs $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get all categories for filter buttons
$categories_sql = "SELECT DISTINCT category FROM blogs ORDER BY category";
$categories_result = $conn->query($categories_sql);
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row['category'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - GreenLife Wellness</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/blog.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="blog-container">
        <div class="blog-header">
            <h1>Wellness Blog</h1>
            <p>Discover expert tips, insights, and guidance for your wellness journey. Explore our collection of health and wellness articles.</p>
        </div>

        <!-- Search and Filter Section -->
        <div class="blog-filters">
            <form method="GET" action="" class="search-section">
                <div class="search-box">
                    <input type="text" name="search" placeholder="Search articles..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <button type="submit" class="read-more">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="?<?php echo !empty($category) ? 'category=' . urlencode($category) : ''; ?>" class="clear-search-btn" style="margin-left:10px;display:inline-block;vertical-align:middle;">Clear Search</a>
                <?php endif; ?>
            </form>

            <div class="category-filters">
                <a href="?<?php echo !empty($search) ? 'search=' . urlencode($search) . '&' : ''; ?>" 
                   class="category-btn <?php echo empty($category) ? 'active' : ''; ?>">
                    All Categories
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a href="?<?php echo !empty($search) ? 'search=' . urlencode($search) . '&' : ''; ?>category=<?php echo urlencode($cat); ?>" 
                       class="category-btn <?php echo $category === $cat ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat); ?>
                    </a>
                <?php endforeach; ?>
                </div>
        </div>

        <!-- Blog Grid -->
        <div class="blog-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($blog = $result->fetch_assoc()): ?>
                    <article class="blog-card">
                        <?php if (!empty($blog['image_path'])): ?>
                            <img src="../<?php echo htmlspecialchars($blog['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($blog['title']); ?>" 
                                 class="blog-image">
                        <?php endif; ?>
                        
                        <div class="blog-content">
                            <span class="blog-category"><?php echo htmlspecialchars($blog['category']); ?></span>
                            <h2 class="blog-title"><?php echo htmlspecialchars($blog['title']); ?></h2>
                            <p class="blog-excerpt"><?php echo htmlspecialchars($blog['excerpt']); ?></p>
                            
                            <div class="blog-meta">
                                <span class="blog-author"><?php echo htmlspecialchars($blog['author']); ?></span>
                                <span class="blog-date"><?php echo date('M j, Y', strtotime($blog['created_at'])); ?></span>
                </div>
                            
                            <button class="read-more" 
                                data-title="<?php echo htmlspecialchars($blog['title'], ENT_QUOTES); ?>"
                                data-category="<?php echo htmlspecialchars($blog['category'], ENT_QUOTES); ?>"
                                data-author="<?php echo htmlspecialchars($blog['author'], ENT_QUOTES); ?>"
                                data-date="<?php echo date('F j, Y', strtotime($blog['created_at'])); ?>"
                                data-image="<?php echo !empty($blog['image_path']) ? '../' . htmlspecialchars($blog['image_path'], ENT_QUOTES) : ''; ?>"
                                data-content='<?php echo htmlspecialchars($blog['content'], ENT_QUOTES); ?>'>
                        Read More
                    </button>
                </div>
            </article>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-results">
                    <h3>No articles found</h3>
                    <p>
                        <?php if (!empty($search) || !empty($category)): ?>
                            No articles match your search criteria. Try adjusting your search terms or browse all categories.
                        <?php else: ?>
                            No articles are available at the moment. Please check back later.
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
                    </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                       class="pagination-btn">Previous</a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                       class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                       class="pagination-btn">Next</a>
                <?php endif; ?>
                </div>
        <?php endif; ?>
    </main>

    <!-- Blog Modal Popup -->
    <div id="blogModal" class="blog-modal" style="display:none;">
        <div class="blog-modal-content">
            <span class="blog-modal-close">&times;</span>
            <div class="blog-modal-body">
                <img id="modalImage" src="" alt="" class="blog-modal-image" style="display:none;">
                <span id="modalCategory" class="blog-category"></span>
                <h2 id="modalTitle" class="blog-title"></h2>
                <div class="blog-meta">
                    <span id="modalAuthor" class="blog-author"></span>
                    <span id="modalDate" class="blog-date"></span>
                </div>
                <div id="modalContent" class="blog-post-content"></div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

<script>
        // Add smooth scrolling and enhance user experience
document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll to top when clicking pagination
            const paginationLinks = document.querySelectorAll('.pagination-btn');
            paginationLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!this.classList.contains('active')) {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
    });
});

            // Auto-submit search on Enter key
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        this.closest('form').submit();
                    }
                });
            }

            // Add loading state for form submissions
            const searchForm = document.querySelector('.search-section');
            if (searchForm) {
                searchForm.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.textContent = 'Searching...';
                        submitBtn.disabled = true;
                    }
                });
            }

            // Blog Modal Logic
            const modal = document.getElementById('blogModal');
            const modalClose = document.querySelector('.blog-modal-close');
            const modalTitle = document.getElementById('modalTitle');
            const modalCategory = document.getElementById('modalCategory');
            const modalAuthor = document.getElementById('modalAuthor');
            const modalDate = document.getElementById('modalDate');
            const modalImage = document.getElementById('modalImage');
            const modalContent = document.getElementById('modalContent');

            document.querySelectorAll('.read-more').forEach(btn => {
                btn.addEventListener('click', function() {
                    modalTitle.textContent = this.dataset.title;
                    modalCategory.textContent = this.dataset.category;
                    modalAuthor.textContent = this.dataset.author;
                    modalDate.textContent = this.dataset.date;
                    if (this.dataset.image) {
                        modalImage.src = this.dataset.image;
                        modalImage.style.display = 'block';
    } else {
                        modalImage.style.display = 'none';
                    }
                    modalContent.innerHTML = this.dataset.content;
                    modal.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                });
            });

            modalClose.onclick = function() {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            };
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                }
            };
        });
</script>
</body>
</html> 