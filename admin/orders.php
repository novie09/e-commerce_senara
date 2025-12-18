<?php
include '../config.php';
include 'includes/header.php';

// Fetch all orders
$orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
?>

<div class="page-header">
    <h1 class="page-title">Manage Orders</h1>
</div>

<div class="row">
    <div class="col-12">
        <div class="admin-card">
            <?php if ($orders->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Shipping Method</th>
                                <th>Pickup Note</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($ord = $orders->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold">#<?php echo str_pad($ord['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo date('d M Y, H:i', strtotime($ord['created_at'])); ?></td>
                                    <td>
                                        <div class="fw-medium"><?php echo htmlspecialchars($ord['recipient_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($ord['phone_number']); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($ord['shipping_method'] == 'Pickup at Store'): ?>
                                            <span class="badge bg-warning text-dark">
                                                <ion-icon name="storefront-outline" style="vertical-align: bottom;"></ion-icon>
                                                Pickup
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-info text-dark">
                                                <ion-icon name="bicycle-outline" style="vertical-align: bottom;"></ion-icon>
                                                Delivery
                                            </span>
                                        <?php endif; ?>
                                        <div class="small text-muted mt-1">
                                            <?php echo htmlspecialchars($ord['shipping_method']); ?></div>
                                    </td>
                                    <td>
                                        <?php if (!empty($ord['pickup_note'])): ?>
                                            <div class="alert alert-warning py-1 px-2 mb-0 small"
                                                style="display:inline-block; max-width: 200px;">
                                                <ion-icon name="time-outline" style="vertical-align: -2px;"></ion-icon>
                                                <?php echo htmlspecialchars($ord['pickup_note']); ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold">
                                        Rp. <?php echo number_format($ord['grand_total'], 2, ',', '.'); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary text-uppercase"><?php echo $ord['status']; ?></span>
                                    </td>
                                    <td>
                                        <a href="view_order.php?id=<?php echo $ord['id']; ?>" class="btn-action btn-edit">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <ion-icon name="cart-outline" style="font-size: 3rem; color: #ddd;"></ion-icon>
                    <p class="text-muted mt-3">No orders found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>

</html>