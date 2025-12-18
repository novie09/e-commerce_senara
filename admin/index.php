<?php include '../config.php'; ?>
<?php include 'includes/header.php'; ?>

<?php
// Get Stats
$product_count = $conn->query("SELECT count(*) as count FROM products")->fetch_assoc()['count'];
$user_count = $conn->query("SELECT count(*) as count FROM users")->fetch_assoc()['count'];
?>

<div class="page-header">
    <h1 class="page-title">Dashboard Overview</h1>
    <div class="page-actions">
        <a href="../index.php" target="_blank" class="btn-primary-admin"
            style="background:var(--secondary-bg);color:var(--text-muted)">
            <ion-icon name="globe-outline"></ion-icon> View Website
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-lg-4">
        <div class="admin-card stat-card">
            <div class="stat-wrapper">
                <div>
                    <div class="stat-value"><?php echo $product_count; ?></div>
                    <div class="stat-label">Total Products</div>
                </div>
                <div class="stat-icon bg-light-primary">
                    <ion-icon name="cube-outline"></ion-icon>
                </div>
            </div>
            <div class="mt-4">
                <a href="products.php" class="btn-action btn-edit"
                    style="width:auto; padding: 8px 16px; font-size: 0.85rem;">
                    Manage Products
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4">
        <div class="admin-card stat-card">
            <div class="stat-wrapper">
                <div>
                    <div class="stat-value"><?php echo $user_count; ?></div>
                    <div class="stat-label">Registered Users</div>
                </div>
                <div class="stat-icon bg-light-success">
                    <ion-icon name="people-outline"></ion-icon>
                </div>
            </div>
            <div class="mt-4">
                <a href="users.php" class="btn-action btn-edit"
                    style="width:auto; padding: 8px 16px; font-size: 0.85rem;">
                    View Users
                </a>
            </div>
        </div>
    </div>
</div>

</body>

</html>