<?php
include '../config.php';
include 'includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit;
}

$order_id = $_GET['id'];

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    $success_msg = "Order status updated successfully!";
}

// Fetch Order
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "<div class='container mt-5'><h3>Order not found</h3></div>";
    exit;
}

// Fetch Items
$stmt_items = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items = $stmt_items->get_result();
?>

<div class="d-flex align-items-center gap-3">
    <a href="orders.php" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center"
        style="width: 40px; height: 40px;">
        <ion-icon name="arrow-back-outline"></ion-icon>
    </a>
    <h1 class="page-title mb-0">Order #<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></h1>
    <span class="badge bg-secondary text-uppercase ms-2"><?php echo $order['status']; ?></span>
</div>
</div>

<div class="row mt-4">
    <!-- Order Details -->
    <div class="col-md-8">

        <?php if ($order['status'] === 'cancelled' && !empty($order['cancel_reason'])): ?>
            <div class="alert alert-danger mb-4">
                <strong>Cancellation Reason:</strong> <?php echo htmlspecialchars($order['cancel_reason']); ?>
            </div>
        <?php endif; ?>

        <div class="admin-card mb-4">
            <h5 class="mb-3">Items Ordered</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $items->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <!-- Placeholder or actual image if stored (we stored name snapshot) -->
                                        <!-- Since we didn't store image url in order_items, we might need to join or just query products if still exists. 
                                             For now, simple bullet list or name is fine. -->
                                        <div>
                                            <div class="fw-medium"><?php echo htmlspecialchars($item['product_name']); ?>
                                            </div>
                                            <div class="text-muted small">Rp.
                                                <?php echo number_format($item['price'], 2, ',', '.'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center"><?php echo $item['quantity']; ?></td>
                                <td class="text-end fw-bold">Rp.
                                    <?php echo number_format($item['price'] * $item['quantity'], 2, ',', '.'); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot style="border-top: 2px solid #eee;">
                        <tr>
                            <td colspan="2" class="text-end">Subtotal</td>
                            <td class="text-end">Rp. <?php echo number_format($order['subtotal'], 2, ',', '.'); ?></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-end">Discount (10%)</td>
                            <td class="text-end text-success">- Rp.
                                <?php echo number_format($order['discount_amount'], 2, ',', '.'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-end">Tax (Included)</td>
                            <td class="text-end">Rp. <?php echo number_format($order['tax_amount'], 2, ',', '.'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-end">Shipping</td>
                            <td class="text-end">Rp. <?php echo number_format($order['shipping_cost'], 2, ',', '.'); ?>
                            </td>
                        </tr>
                        <tr class="fs-5 fw-bold">
                            <td colspan="2" class="text-end">Grand Total</td>
                            <td class="text-end text-primary">Rp.
                                <?php echo number_format($order['grand_total'], 2, ',', '.'); ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-md-4">
        <!-- Status Update -->
        <div class="admin-card mb-4">
            <h5 class="mb-3">Update Status</h5>
            <form method="POST">
                <select name="status" class="form-select mb-3">
                    <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending
                    </option>
                    <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>
                        Processing</option>
                    <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped
                    </option>
                    <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed
                    </option>
                    <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled
                    </option>
                </select>
                <button type="submit" name="update_status" class="btn-primary-admin w-100">Update Status</button>
            </form>
        </div>

        <!-- Customer Info -->
        <div class="admin-card mb-4">
            <h5 class="mb-3">Customer Details</h5>
            <div class="mb-3">
                <label class="text-muted small">Name</label>
                <div class="fw-medium"><?php echo htmlspecialchars($order['recipient_name']); ?></div>
            </div>
            <div class="mb-3">
                <label class="text-muted small">Phone</label>
                <div><?php echo htmlspecialchars($order['phone_number']); ?></div>
            </div>
            <div class="mb-3">
                <label class="text-muted small">Shipping Address</label>
                <div>
                    <?php echo htmlspecialchars($order['shipping_address']); ?>
                </div>
            </div>
        </div>

        <!-- Shipping & Payment -->
        <div class="admin-card">
            <h5 class="mb-3">Shipping & Payment</h5>
            <div class="mb-3">
                <label class="text-muted small">Shipping Method</label>
                <div class="d-flex align-items-center gap-2">
                    <?php if ($order['shipping_method'] == 'Pickup at Store'): ?>
                        <ion-icon name="storefront-outline" class="text-warning"></ion-icon>
                    <?php else: ?>
                        <ion-icon name="bicycle-outline" class="text-info"></ion-icon>
                    <?php endif; ?>
                    <span class="fw-medium"><?php echo htmlspecialchars($order['shipping_method']); ?></span>
                </div>
                <?php if (!empty($order['pickup_note'])): ?>
                    <div class="mt-2 p-2 bg-light border rounded small text-secondary">
                        <ion-icon name="time-outline" style="vertical-align: middle;"></ion-icon>
                        Note: <?php echo htmlspecialchars($order['pickup_note']); ?>
                    </div>
                <?php endif; ?>
            </div>

            <hr>

            <div class="mb-3">
                <label class="text-muted small">Payment Method</label>
                <div class="fw-bold fs-5 text-primary">
                    <?php echo htmlspecialchars($order['payment_method']); ?>
                </div>
            </div>

            <div class="mb-3">
                <label class="text-muted small">Order Date</label>
                <div><?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?></div>
            </div>

            <?php if (!empty($order['payment_proof'])): ?>
                <hr>
                <div class="mb-3">
                    <label class="text-muted small d-block mb-2">Payment Proof</label>
                    <a href="../assets/uploads/payments/<?php echo htmlspecialchars($order['payment_proof']); ?>"
                        target="_blank">
                        <img src="../assets/uploads/payments/<?php echo htmlspecialchars($order['payment_proof']); ?>"
                            class="img-fluid rounded border" style="max-height: 200px; width: 100%; object-fit: cover;">
                    </a>
                    <div class="d-grid mt-2">
                        <a href="../assets/uploads/payments/<?php echo htmlspecialchars($order['payment_proof']); ?>"
                            target="_blank" class="btn btn-sm btn-outline-secondary">View Full Size</a>
                    </div>
                </div>
            <?php endif; ?>




        </div>
    </div>
</div>

</body>

</html>