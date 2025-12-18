<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senara - Aromatherapy Candles</title>
    <link href="assets/css/style.css?v=<?= time(); ?>" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Italiana&family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=Inter:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>

<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container flex justify-between" style="justify-content: flex-end; gap: 20px;">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span style="font-size: 0.9rem; color: #666;">Hi,
                    <b><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></b></span>
                <!-- Logout Removed -->
            <?php else: ?>
                <a href="login.php" class="help-link">Login</a>
                <a href="signup.php" class="help-link">Sign Up</a>
            <?php endif; ?>
            <a href="help.php" class="help-link">Help</a>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header">
        <div class="container header-grid">
            <!-- Left: Menu Toggle -->
            <div class="header-left">
                <button id="menu-toggle" class="menu-btn">
                    <ion-icon name="menu-outline" style="font-size: 28px;"></ion-icon>
                </button>
            </div>

            <!-- Center: Logo -->
            <div class="header-center">
                <a href="index.php" class="logo">Senara</a>
            </div>

            <!-- Right: Search & Icons -->
            <div class="header-right flex items-center gap-4">
                <form action="products.php" method="GET" class="search-bar">
                    <button type="submit"
                        style="background:none; border:none; padding:0; display:flex; align-items:center;">
                        <ion-icon name="search-outline"></ion-icon>
                    </button>
                    <input type="text" name="search" placeholder="Search products..."
                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </form>
                <div class="nav-icons flex gap-4">
                    <a href="<?php echo isset($_SESSION['user_id']) ? 'profile.php' : 'login.php'; ?>"><ion-icon
                            name="person-outline" style="font-size: 24px;"></ion-icon></a>
                    <a href="cart.php"><ion-icon name="cart-outline" style="font-size: 24px;"></ion-icon></a>
                </div>
            </div>
        </div>


        <!-- Mega Menu Overlay -->
        <div id="mega-menu" class="mega-menu">
            <div class="container menu-grid">
                <!-- Menu Links -->
                <div class="menu-links">
                    <a href="about.php" class="menu-item">
                        <span>About Senara</span>
                        <ion-icon name="arrow-forward-circle-outline"></ion-icon>
                    </a>
                    <a href="contact.php" class="menu-item">
                        <span>Contact Us</span>
                        <ion-icon name="arrow-forward-circle-outline"></ion-icon>
                    </a>
                    <a href="help.php" class="menu-item">
                        <span>Help Center</span>
                        <ion-icon name="arrow-forward-circle-outline"></ion-icon>
                    </a>
                    <a href="privacy.php" class="menu-item">
                        <span>Privacy Policy</span>
                        <ion-icon name="arrow-forward-circle-outline"></ion-icon>
                    </a>
                    <a href="rewards.php" class="menu-item">
                        <span>Senara Rewards</span>
                        <ion-icon name="arrow-forward-circle-outline"></ion-icon>
                    </a>
                </div>

                <!-- Menu Images -->
                <div class="menu-images">
                    <div class="menu-img-wrapper">
                        <img src="assets/img/nav2.jpg" alt="Colorful Candles">
                    </div>
                    <div class="menu-img-wrapper">
                        <img src="assets/img/nav1.jpg" alt="Cozy Atmosphere">
                        <!-- Using hero_banner temporarily as it might be cozy, otherwise finding best match -->
                    </div>
                </div>
            </div>
        </div>
    </header>

    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const megaMenu = document.getElementById('mega-menu');
        const menuIcon = menuToggle.querySelector('ion-icon');

        menuToggle.addEventListener('click', () => {
            megaMenu.classList.toggle('active');

            // Toggle Icon
            if (megaMenu.classList.contains('active')) {
                menuIcon.setAttribute('name', 'close-outline');
            } else {
                menuIcon.setAttribute('name', 'menu-outline');
            }
        });
    </script>
</body>

</html>