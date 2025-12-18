<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Basic Admin Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect or handle unauthorized access
}

// Get the current page name for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senara Admin Dashboard</title>

    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap"
        rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <!-- Admin CSS -->
    <link rel="stylesheet" href="assets/css/admin.css">
</head>

<body>

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <a href="index.php" class="brand-logo">
            Senara<span style="color: var(--accent-color);">.</span>
        </a>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="index.php" class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                    <ion-icon name="grid-outline"></ion-icon>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="products.php"
                    class="nav-link <?php echo ($current_page == 'products.php' || $current_page == 'add_product.php' || $current_page == 'edit_product.php') ? 'active' : ''; ?>">
                    <ion-icon name="cube-outline"></ion-icon>
                    <span>Products</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="orders.php"
                    class="nav-link <?php echo ($current_page == 'orders.php' || $current_page == 'view_order.php') ? 'active' : ''; ?>">
                    <ion-icon name="cart-outline"></ion-icon>
                    <span>Orders</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="banks.php" class="nav-link <?php echo ($current_page == 'banks.php') ? 'active' : ''; ?>">
                    <ion-icon name="wallet-outline"></ion-icon>
                    <span>Payments</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="users.php" class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
                    <ion-icon name="people-outline"></ion-icon>
                    <span>Users</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="pages.php"
                    class="nav-link <?php echo ($current_page == 'pages.php' || $current_page == 'edit_page.php') ? 'active' : ''; ?>">
                    <ion-icon name="document-text-outline"></ion-icon>
                    <span>Pages</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="messages.php" class="nav-link <?php echo $current_page == 'messages.php' ? 'active' : ''; ?>">
                    <ion-icon name="mail-outline"></ion-icon>
                    <span>Messages</span>
                </a>
            </li>
        </ul>

        <div class="admin-footer">
            <a href="../index.php" target="_blank" class="nav-link">
                <ion-icon name="eye-outline"></ion-icon>
                <span>View Site</span>
            </a>
            <a href="../logout.php" class="logout-btn" onclick="return confirm('Apakah anda yakin ingin keluar?');">
                <ion-icon name="log-out-outline"></ion-icon> Logout
            </a>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="main-content">
        <!-- Top Header Mobile Toggle could go here -->