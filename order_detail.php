<?php
// order_detail.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: my_orders.php");
    exit;
}

include 'config.php';
$user_id = $_SESSION['user_id'];
$order_id = $_GET['id'];

// Fetch Order Details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: my_orders.php");
    exit;
}

// Fetch Order Items
$items_res = $conn->query("
    SELECT oi.*, p.image_url 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = $order_id
");

// Determine Current Step
$steps = ['pending', 'processing', 'shipped', 'completed'];
$status = $order['status'];
$current_step_index = ($status === 'cancelled') ? -1 : array_search($status, $steps);

// Calculate Bar Width
// 0 -> 0%, 1 -> 33%, 2 -> 66%, 3 -> 100%
$progress_width = 0;
if ($current_step_index > 0) {
    $progress_width = min(100, $current_step_index * 33.33);
}
?>
<?php include 'includes/header.php'; ?>

<main class="container profile-page" style="padding-top: 40px; padding-bottom: 80px; min-height: 80vh;">

    <!-- Top Header -->
    <div class="mb-4">
        <a href="my_orders.php" class="btn-back-pill">
            <ion-icon name="arrow-back-outline"></ion-icon> Back to My Orders
        </a>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-0">Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h1>
        </div>
        <div>
            <span class="status-badge <?php echo $status; ?>">
                <?php echo ucfirst($status); ?>
            </span>
        </div>
    </div>

    <!-- Stepper Section -->
    <?php if ($status !== 'cancelled'): ?>
        <div class="card-premium mb-5">
            <div class="card-body p-4 p-md-5">
                <div class="timeline-split-container">
                    <?php
                    // Define Timeline Items
                    $timeline_data = [
                        'pending' => [
                            'title' => 'Order Placed',
                            'desc' => 'Order has been created and is waiting for payment.',
                            'icon' => 'receipt-outline'
                        ],
                        'processing' => [
                            'title' => 'Under Packaging',
                            'desc' => 'The sender has arranged the shipment. Waiting for the order to be handed over to the shipping service.',
                            'icon' => 'cube-outline'
                        ],
                        'shipped' => [
                            'title' => 'Shipped',
                            'desc' => 'The order has been submitted to the shipping service for processing.',
                            'icon' => 'car-outline'
                        ],
                        'completed' => [
                            'title' => 'Completed',
                            'desc' => 'Package has been delivered to your address.',
                            'icon' => 'checkmark-circle-outline'
                        ]
                    ];

                    // Render Steps Reverse (Newest Top? Or Oldest Top?) 
                    // Screenshot suggests Newest Top usually, but user asked for "like screenshot 2" which often shows flow top->bottom.
                    // Let's stick to standard flow (Order placed top) for logical reading, unless "Vertical Split" means newest first.
                    // Checking screenshot 2 description: "Time | Icon | Info". 
                    // We will render in logical order: Placed -> Processing -> Shipped -> Completed.
                
                    $i = 0;
                    foreach ($timeline_data as $key => $data):
                        $active = ($i <= $current_step_index);
                        $is_current = ($i === $current_step_index);
                        $completed = ($i < $current_step_index);

                        $row_class = '';
                        if ($completed)
                            $row_class = 'completed';
                        elseif ($is_current)
                            $row_class = 'active';

                        // Mock dates for timeline
                        $date_display = ($active) ? date('d-m-Y H:i', strtotime($order['created_at'])) : '';
                        ?>
                        <div class="timeline-row <?php echo $row_class; ?>">
                            <!-- 1. Time -->
                            <div class="timeline-time">
                                <?php echo $date_display; ?>
                            </div>

                            <!-- 2. Line/Icon -->
                            <div class="timeline-line-col">
                                <div class="timeline-icon-circle">
                                    <ion-icon name="<?php echo $data['icon']; ?>"></ion-icon>
                                </div>
                            </div>

                            <!-- 3. Info -->
                            <div class="timeline-info">
                                <div class="timeline-title"><?php echo $data['title']; ?></div>
                                <div class="timeline-desc"><?php echo $data['desc']; ?></div>
                            </div>
                        </div>
                        <?php $i++; endforeach; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger mb-4">
            <ion-icon name="alert-circle-outline" style="vertical-align:middle; font-size:1.2rem;"></ion-icon>
            This order has been cancelled.
        </div>
    <?php endif; ?>

    <!-- Main Content Grid -->
    <div class="row">
        <!-- Left Column (8) -->
        <div class="col-lg-8">



            <!-- Shipping Address -->
            <div class="card-premium">
                <div class="card-header d-flex align-items-center gap-2">
                    <ion-icon name="location-outline" style="font-size:1.2rem;"></ion-icon>
                    <h6 class="mb-0 fw-bold">Shipping Address</h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($order['recipient_name']); ?></h6>
                    <p class="text-muted mb-1"><?php echo htmlspecialchars($order['phone_number']); ?></p>
                    <p class="text-secondary mb-0" style="line-height:1.5;">
                        <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                    </p>
                </div>
            </div>

            <!-- Product List -->
            <div class="card-premium">
                <div class="card-header d-flex align-items-center gap-2">
                    <ion-icon name="bag-handle-outline" style="font-size:1.2rem;"></ion-icon>
                    <h6 class="mb-0 fw-bold">Product Ordered</h6>
                </div>
                <div class="card-body pt-0">
                    <?php
                    $items_res->data_seek(0);
                    while ($item = $items_res->fetch_assoc()):
                        ?>
                        <div class="order-product-item">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" class="rounded border"
                                style="width: 80px; height: 80px; object-fit: cover;">

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between mb-1">
                                    <h6 class="fw-bold mb-0 text-dark">
                                        <?php echo htmlspecialchars($item['product_name']); ?>
                                    </h6>
                                    <!-- Price -->
                                    <div class="fw-bold">
                                        Rp <?php echo number_format($item['price'], 0, ',', '.'); ?>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted small">Variant: Default</div>
                                    <div class="text-muted small">x<?php echo $item['quantity']; ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

        </div>

        <!-- Right Column (4) -->
        <div class="col-lg-4">

            <!-- Order Summary -->
            <div class="card-premium">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">Order Summary</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Subtotal</span>
                        <span>Rp <?php echo number_format($order['subtotal'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Shipping</span>
                        <span>Rp <?php echo number_format($order['shipping_cost'], 0, ',', '.'); ?></span>
                    </div>
                    <?php if ($order['discount_amount'] > 0): ?>
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Discount</span>
                            <span class="text-success">- Rp
                                <?php echo number_format($order['discount_amount'], 0, ',', '.'); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-3 small">
                        <span class="text-muted">Tax</span>
                        <span>Rp <?php echo number_format($order['tax_amount'], 0, ',', '.'); ?></span>
                    </div>

                    <hr class="my-3" style="opacity:0.1">

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark">Total</span>
                        <span class="fw-bold text-danger fs-5">
                            Rp <?php echo number_format($order['grand_total'], 0, ',', '.'); ?>
                        </span>
                    </div>
                </div>
            </div>



            <!-- Actions -->
            <div class="d-grid gap-2">
                <?php if ($status === 'completed'): ?>
                    <a href="products.php" class="btn btn-primary-custom py-2 w-100">Buy Again</a>
                <?php elseif ($status === 'pending'): ?>
                    <a href="payment.php?id=<?php echo $order_id; ?>" class="btn btn-primary-custom py-2">Pay Now</a>
                <?php else: ?>
                    <a href="help.php" class="btn btn-outline-secondary py-2">Need Help?</a>
                <?php endif; ?>
            </div>

        </div>
    </div>

</main>

<?php include 'includes/footer.php'; ?>