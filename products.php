<?php
include 'config.php';

// Categories for Tabs
$categories = [
    'fresh' => 'assets/img/fresh.jpg',
    'fruity' => 'assets/img/fruity.jpg',
    'floral' => 'assets/img/floral.jpg',
    'gourmand' => 'assets/img/gourmand.jpg'
];

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$is_search = !empty($search);

if ($is_search) {
    $pageTitleDisplay = 'Search Results for "' . htmlspecialchars($search) . '"';
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? ORDER BY created_at DESC");
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("s", $searchTerm);
} else {
    $category = isset($_GET['category']) ? $_GET['category'] : 'fresh';
    // Validate category
    if (!array_key_exists($category, $categories)) {
        $category = 'fresh';
    }
    $pageTitleDisplay = ucfirst($category);
    $stmt = $conn->prepare("SELECT * FROM products WHERE category = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $category);
}

$stmt->execute();
$result = $stmt->get_result();

include 'includes/header.php';
?>

<main class="products-page">
    <div class="container">
        <!-- Dynamic Title -->
        <h1 class="page-title text-capitalize">
            <?php echo $pageTitleDisplay; ?>
        </h1>

        <!-- Category Tabs -->
        <?php if (!$is_search): ?>
            <div class="category-tabs flex gap-4">
                <?php foreach ($categories as $slug => $img): ?>
                    <a href="products.php?category=<?php echo $slug; ?>"
                        class="cat-tab <?php echo ($slug === $category) ? 'active' : ''; ?>">
                        <img src="<?php echo $img; ?>" alt="<?php echo ucfirst($slug); ?>">
                        <span><?php echo ucfirst($slug); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Product Grid -->
        <div class="product-grid grid">
            <?php
            // Logic moved to top
            
            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
                    ?>
                    <div class="product-card">
                        <a href="product_detail.php?id=<?php echo $row['id']; ?>"
                            style="text-decoration: none; color: inherit;">
                            <div class="product-img">
                                <img src="<?php echo !empty($row['image_url']) ? htmlspecialchars($row['image_url']) : 'assets/img/placeholder.jpg'; ?>"
                                    alt="<?php echo htmlspecialchars($row['name']); ?>">
                            </div>
                            <div class="product-details">
                                <h3 class="text-capitalize"><?php echo htmlspecialchars($row['name']); ?></h3>
                                <p class="price">Rp. <?php echo number_format($row['price'], 2, ',', '.'); ?></p>
                            </div>
                        </a>
                    </div>
                    <?php
                endwhile;
            else:
                ?>
                <div class="no-products text-center" style="grid-column: 1 / -1; padding: 40px;">
                    <ion-icon name="cube-outline" style="font-size: 3rem; color: #ccc;"></ion-icon>
                    <p class="text-muted mt-3">No products found in this category yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>