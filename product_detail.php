<?php
include 'config.php';
include 'includes/header.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    }
}

if (!$product) {
    echo "<div class='container py-5 text-center'><h1>Product not found</h1><a href='products.php' class='btn-primary'>Back to Products</a></div>";
    include 'includes/footer.php';
    exit;
}
?>

<main class="container" style="padding-top: 60px; padding-bottom: 100px;">
    <div class="product-detail-layout"
        style="display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: start;">
        <!-- Product Image -->
        <div class="detail-image">
            <img src="<?php echo !empty($product['image_url']) ? htmlspecialchars($product['image_url']) : 'assets/img/placeholder.jpg'; ?>"
                alt="<?php echo htmlspecialchars($product['name']); ?>"
                style="width: 100%; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
        </div>

        <!-- Product Info -->
        <div class="detail-info">
            <h1 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; margin-bottom: 20px;">
                <?php echo htmlspecialchars($product['name']); ?>
            </h1>
            <p class="price" style="font-size: 1.5rem; color: #333; font-weight: 500; margin-bottom: 30px;">
                Rp. <?php echo number_format($product['price'], 2, ',', '.'); ?>
            </p>

            <div class="description" style="color: #666; line-height: 1.8; margin-bottom: 40px; white-space: pre-line;">
                <?php echo htmlspecialchars($product['description']); ?>
            </div>

            <form action="add_to_cart.php" method="POST" class="add-cart-form">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

                <div class="flex items-center gap-4 mb-4">
                    <label style="font-weight: 500;">Quantity:</label>
                    <div class="qty-control">
                        <input type="number" name="quantity" value="1" min="1" class="qty-input" style="width: 50px;">
                    </div>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; text-align: center; padding: 15px;">
                    Add to Cart
                </button>
            </form>

            <div class="mt-4">
                <a href="products.php?category=<?php echo htmlspecialchars($product['category']); ?>" class="text-muted"
                    style="font-size: 0.9rem;">
                    <ion-icon name="arrow-back-outline" style="vertical-align: middle;"></ion-icon> Continue Shopping
                </a>
            </div>
        </div>
    </div>
    <!-- Toast Container -->
    <div id="toast" class="toast-notification">
        <ion-icon name="checkmark-circle"></ion-icon>
        <span>Product successfully added to cart!</span>
    </div>

    <script>
        // Check for 'added' param
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('added') === 'success') {
            const toast = document.getElementById('toast');
            // Slight delay for animation
            setTimeout(() => {
                toast.classList.add('show');
            }, 100);

            // Hide after 3 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                // Clean URL
                const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + "?id=" + urlParams.get('id');
                window.history.replaceState({ path: newUrl }, '', newUrl);
            }, 3000);
        }
    </script>
</main>

<?php include 'includes/footer.php'; ?>