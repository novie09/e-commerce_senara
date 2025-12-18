<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'config.php';

$pageTitle = "My Orders";
$user_id = $_SESSION['user_id'];

// Status Mapping
$status_map = [
    'all' => 'All',
    'pending' => 'Not Yet Paid',
    'processing' => 'Under Packaging',
    'shipped' => 'Sent',
    'completed' => 'Finish',
    'cancelled' => 'Cancelled'
];

$current_status = $_GET['status'] ?? 'all';

// Query Orders
$sql = "SELECT * FROM orders WHERE user_id = ?";
$types = "i";
$params = [$user_id];

if ($current_status !== 'all' && array_key_exists($current_status, $status_map)) {
    $sql .= " AND status = ?";
    $types .= "s";
    $params[] = $current_status;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$orders_result = $stmt->get_result();
?>

<?php include 'includes/header.php'; ?>

<style>
    /* ORDER CARD */
    .order-card {
        border: 1px solid #efefef;
        border-radius: 8px;
        transition: all .2s ease;
    }

    .order-card:hover {
        box-shadow: 0 6px 18px rgba(0, 0, 0, .06);
        transform: translateY(-2px);
    }

    /* BUTTONS */
    .btn-cancel-custom {
        background: #c0392b;
        /* Red Background */
        color: #ffffff;
        /* White Text */
        border: 1px solid #c0392b;
        border-radius: 50px;
        font-size: .85rem;
        padding: 10px 35px;
        /* Wider Padding */
        transition: all 0.3s ease;
    }

    .btn-cancel-custom:hover {
        background: #a93226;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(192, 57, 43, 0.2);
    }

    .btn-detail-custom {
        background: #e6d3c3;
        color: #5a3e2b;
        border: 1px solid #dcc6b3;
        border-radius: 50px;
        font-size: .85rem;
        padding: 10px 35px;
        /* Wider Padding */
        transition: all 0.3s ease;
    }

    .btn-detail-custom:hover {
        background: #dcc6b3;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(230, 211, 195, 0.4);
    }
</style>

<main class="container profile-page" style="padding-top:40px; padding-bottom:80px; min-height:80vh;">

    <!-- Tabs -->
    <div class="order-tabs-wrapper sticky-top bg-white"
        style="top:70px; z-index:90; margin-bottom:30px; border-bottom:1px solid #efefef;">
        <div class="order-tabs">
            <?php foreach ($status_map as $key => $label): ?>
                <a href="my_orders.php?status=<?= $key; ?>"
                    class="order-tab <?= ($current_status === $key) ? 'active' : ''; ?>">
                    <?= $label; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Order List -->
    <?php if ($orders_result->num_rows > 0): ?>
        <?php while ($order = $orders_result->fetch_assoc()): ?>

            <?php
            $oid = (int) $order['id'];
            $items_res = $conn->query("
                SELECT oi.*, p.image_url
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = $oid
            ");
            ?>

            <div class="card order-card mb-4">

                <!-- HEADER -->
                <div class="card-header bg-white d-flex align-items-center">
                    <ion-icon name="storefront" class="me-2"></ion-icon>
                    <strong>Senara Official Store</strong>
                </div>

                <!-- BODY -->
                <div class="card-body">
                    <?php while ($item = $items_res->fetch_assoc()): ?>
                        <div class="d-flex py-3 border-bottom">
                            <img src="<?= htmlspecialchars($item['image_url']); ?>" class="rounded me-3"
                                style="width:80px;height:80px;object-fit:cover;border:1px solid #eee;">

                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">
                                    <?= htmlspecialchars($item['product_name']); ?>
                                </h6>
                                <div class="text-muted small mb-1">Variant: Default</div>

                                <div class="d-flex justify-content-between">
                                    <span class="text-muted small">x<?= $item['quantity']; ?></span>
                                    <span class="fw-medium">
                                        Rp <?= number_format($item['price'], 0, ',', '.'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- FOOTER -->
                <div class="card-footer bg-white border-0">

                    <!-- Date + Total -->
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <?= date('d M Y, H:i', strtotime($order['created_at'])); ?>
                        </small>

                        <div class="text-end">
                            <small class="text-muted">Total Order</small>
                            <div class="fw-bold text-danger fs-5">
                                Rp <?= number_format($order['grand_total'], 2, ',', '.'); ?>
                            </div>
                        </div>
                    </div>

                    <!-- ACTIONS -->
                    <!-- ACTIONS: CANCEL + DETAIL SEJAJAR -->
                    <div class="d-flex justify-content-end align-items-center gap-3 mt-3">

                        <?php if ($order['status'] === 'pending'): ?>
                            <button class="btn btn-cancel-custom" onclick="openCancelModal(<?= $order['id']; ?>)">
                                Cancel Order
                            </button>
                        <?php endif; ?>

                        <a href="order_detail.php?id=<?= $order['id']; ?>" class="btn btn-detail-custom fw-semibold">
                            Order Detail
                        </a>

                    </div>


                </div>

            </div>
            </div>

        <?php endwhile; ?>
    <?php else: ?>
        <div class="text-center py-5">
            <ion-icon name="bag-handle-outline" style="font-size:4rem;color:#eee;"></ion-icon>
            <h5 class="text-muted mt-3">No orders yet</h5>
            <a href="products.php" class="btn btn-dark px-5 mt-2">Go Shopping</a>
        </div>
    <?php endif; ?>

</main>

</main>

<!-- CANCEL MODAL -->
<div id="cancelModal" class="cancel-modal-overlay">
    <div class="cancel-modal-box">
        <h4 class="mb-4 fw-bold">Cancel Order</h4>
        <p class="text-muted mb-4">Please select a reason for cancellation:</p>

        <form id="cancelForm" action="cancel_order.php" method="POST">
            <input type="hidden" name="order_id" id="modalOrderId">

            <div class="reason-options">
                <label class="reason-item">
                    <input type="radio" name="cancel_reason" value="Berubah pikiran setelah melakukan pemesanan"
                        required>
                    <span>Berubah pikiran setelah melakukan pemesanan</span>
                </label>
                <label class="reason-item">
                    <input type="radio" name="cancel_reason" value="Salah memilih ukuran / varian produk">
                    <span>Salah memilih ukuran / varian produk</span>
                </label>
                <label class="reason-item">
                    <input type="radio" name="cancel_reason" value="Ingin mengubah metode pembayaran">
                    <span>Ingin mengubah metode pembayaran</span>
                </label>
                <label class="reason-item">
                    <input type="radio" name="cancel_reason" value="Saya ingin mempertimbangkan ulang pesanan ini">
                    <span>Saya ingin mempertimbangkan ulang pesanan ini</span>
                </label>
                <label class="reason-item">
                    <input type="radio" name="cancel_reason" value="Alasan lainnya">
                    <span>Alasan lainnya</span>
                </label>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                <button type="button" class="btn btn-detail-custom" onclick="closeCancelModal()">Close</button>
                <button type="submit" class="btn btn-cancel-custom">Confirm Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
    .cancel-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .cancel-modal-overlay.active {
        display: flex;
        opacity: 1;
    }

    .cancel-modal-box {
        background: white;
        width: 90%;
        max-width: 500px;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        transform: scale(0.9);
        transition: transform 0.3s ease;
    }

    .cancel-modal-overlay.active .cancel-modal-box {
        transform: scale(1);
    }

    .reason-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        border: 1px solid #eee;
        border-radius: 8px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .reason-item:hover {
        background: #f9f9f9;
        border-color: #ddd;
    }

    .reason-item input[type="radio"] {
        accent-color: #c0392b;
        transform: scale(1.2);
    }
</style>

<script>
    function openCancelModal(orderId) {
        document.getElementById('modalOrderId').value = orderId;
        const modal = document.getElementById('cancelModal');
        modal.classList.add('active');
    }

    function closeCancelModal() {
        const modal = document.getElementById('cancelModal');
        modal.classList.remove('active');
    }

    // Close on outside click
    document.getElementById('cancelModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeCancelModal();
        }
    });
</script>

<?php include 'includes/footer.php'; ?>