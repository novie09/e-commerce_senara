<?php
include 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Enforce login for simpler logic
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_items = [];
// Fetch cart items
$stmt = $conn->prepare("
    SELECT ci.id as cart_id, ci.quantity, p.id as product_id, p.name, p.price, p.image_url 
    FROM cart_items ci 
    JOIN products p ON ci.product_id = p.id 
    WHERE ci.user_id = ?
    ORDER BY ci.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Senara</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap"
        rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <main class="container cart-page">
        <div class="flex items-center gap-4 mb-4">
            <a href="index.php" class="text-muted" style="text-decoration: none; color: #666;"><ion-icon
                    name="arrow-back-outline"></ion-icon> Back</a>
        </div>

        <h1 class="page-title text-center"
            style="font-size: 2.5rem; margin-bottom: 20px; font-family: 'Playfair Display', serif;">Your Cart</h1>

        <?php if (empty($cart_items)): ?>
            <div class="text-center py-5">
                <ion-icon name="cart-outline" style="font-size: 4rem; color: #ccc; margin-bottom: 20px;"></ion-icon>
                <p class="text-muted">Your cart is currently empty.</p>
                <a href="products.php" class="btn-primary mt-3" style="display: inline-block; margin-top: 20px;">Start
                    Shopping</a>
            </div>
        <?php else: ?>
            <form action="checkout.php" method="POST" id="cartForm">
                <div class="cart-list" style="max-width: 900px; margin: 0 auto;">
                    <div class="mb-3 text-muted" style="margin-bottom: 20px;">Total: <span id="itemCount">0</span> items
                        selected</div>

                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item flex" data-price="<?php echo $item['price']; ?>"
                            data-id="<?php echo $item['cart_id']; ?>">
                            <input type="checkbox" name="selected_items[]" value="<?php echo $item['cart_id']; ?>"
                                class="cart-checkbox">

                            <img src="<?php echo !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : 'assets/img/placeholder.jpg'; ?>"
                                alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-img">

                            <div class="cart-details">
                                <h3 class="cart-title"><?php echo htmlspecialchars($item['name']); ?></h3>
                                <div class="cart-price">Rp. <?php echo number_format($item['price'], 2, ',', '.'); ?></div>
                            </div>

                            <div class="qty-control">
                                <button type="button" class="qty-btn minus" onclick="updateQty(this, -1)">-</button>
                                <input type="number" name="qty[<?php echo $item['cart_id']; ?>]"
                                    value="<?php echo $item['quantity']; ?>" class="qty-input" readonly>
                                <button type="button" class="qty-btn plus" onclick="updateQty(this, 1)">+</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Fixed Bottom Bar -->
                <div class="cart-bottom-bar">
                    <div class="total-section text-right" style="text-align: right;">
                        <span class="text-muted" style="display: block; font-size: 0.9rem;">Total</span>
                        <span class="total-price" id="grandTotal">Rp. 0</span>
                    </div>
                    <button type="submit" class="btn-checkout" id="checkoutBtn" disabled
                        style="opacity: 0.5; cursor: not-allowed;">Checkout</button>
                </div>
            </form>
        <?php endif; ?>

    </main>

    <script>
        // Formatting helper
        const formatter = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 2
        });

        function calculateTotal() {
            let total = 0;
            let count = 0;
            const checkboxes = document.querySelectorAll('.cart-checkbox:checked');

            checkboxes.forEach(cb => {
                const itemRow = cb.closest('.cart-item');
                const price = parseFloat(itemRow.getAttribute('data-price'));
                const qty = parseInt(itemRow.querySelector('.qty-input').value);
                total += price * qty;
                count++;
            });

            document.getElementById('grandTotal').innerText = formatter.format(total).replace('IDR', 'Rp.');
            document.getElementById('itemCount').innerText = count;

            const btn = document.getElementById('checkoutBtn');
            if (count > 0) {
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.style.cursor = 'pointer';
            } else {
                btn.disabled = true;
                btn.style.opacity = '0.5';
                btn.style.cursor = 'not-allowed';
            }
        }

        function updateQty(btn, change) {
            const row = btn.closest('.cart-item');
            const input = row.querySelector('.qty-input');
            const cartId = row.getAttribute('data-id');
            let newQty = parseInt(input.value) + change;

            if (newQty < 1) return; // Minimum 1

            input.value = newQty;
            calculateTotal(); // Update UI immediately

            // Send AJAX update
            fetch('update_cart_ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `cart_id=${cartId}&quantity=${newQty}`
            }).catch(err => console.error('Failed to update cart', err));
        }

        // Listen for checkbox changes
        document.querySelectorAll('.cart-checkbox').forEach(cb => {
            cb.addEventListener('change', calculateTotal);
        });
    </script>
</body>

</html>