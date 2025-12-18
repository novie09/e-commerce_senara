<?php
include 'config.php';
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']); // Basic check, normally would check real auth
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senara - Aromatherapy Candles</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap"
        rel="stylesheet">
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <main>
        <!-- Hero Section -->
        <section class="hero-container">
            <div class="hero-banner">
                <img src="assets/img/hero_banner.jpg" alt="Aromatherapy Candles">
            </div>
        </section>

        <!-- Shop by Scent -->
        <section class="shop-by-scent container">
            <h2 class="section-title text-center">Shop by Scent</h2>
            <div class="scent-grid grid">
                <a href="products.php?category=fresh" class="scent-card">
                    <img src="assets/img/fresh.jpg" alt="Fresh">
                    <div class="scent-info flex justify-between items-center">
                        <h3>Fresh</h3>
                        <ion-icon name="chevron-forward-outline"></ion-icon>
                    </div>
                </a>
                <a href="products.php?category=fruity" class="scent-card">
                    <img src="assets/img/fruity.jpg" alt="Fruity">
                    <div class="scent-info flex justify-between items-center">
                        <h3>Fruity</h3>
                        <ion-icon name="chevron-forward-outline"></ion-icon>
                    </div>
                </a>
                <a href="products.php?category=floral" class="scent-card">
                    <img src="assets/img/floral.jpg" alt="Floral">
                    <div class="scent-info flex justify-between items-center">
                        <h3>Floral</h3>
                        <ion-icon name="chevron-forward-outline"></ion-icon>
                    </div>
                </a>
                <a href="products.php?category=gourmand" class="scent-card">
                    <img src="assets/img/gourmand.jpg" alt="Gourmand">
                    <div class="scent-info flex justify-between items-center">
                        <h3>Gourmand</h3>
                        <ion-icon name="chevron-forward-outline"></ion-icon>
                    </div>
                </a>
            </div>
        </section>

        <!-- Senara is a Serenity (Dynamic) -->
        <?php
        // Fetch Serenity Content
        $serenity_title = "SENARA IS A SERENITY";
        $serenity_text = "For creating a peaceful space, the atmosphere must feel calm and soothing. Soft scents and warm candlelight bring harmony into your home — where elegance meets tranquility.";
        $serenity_img = "assets/img/Brand_Introduction.jpg";

        $stmt = $conn->prepare("SELECT title, content, image_url FROM site_contents WHERE page_slug = 'home-serenity'");
        if ($stmt) {
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows > 0) {
                $row = $res->fetch_assoc();
                $serenity_title = $row['title'];
                $serenity_text = $row['content']; // Expecting pure text or HTML without the <h2> wrapped? Based on DB seed it is text.
                if (!empty($row['image_url']) && file_exists($row['image_url'])) {
                    $serenity_img = $row['image_url'];
                }
            }
        }
        ?>
        <section class="serenity-section container flex items-center">
            <div class="serenity-image">
                <img src="<?php echo htmlspecialchars($serenity_img); ?>"
                    alt="<?php echo htmlspecialchars($serenity_title); ?>">
            </div>
            <div class="serenity-content">
                <h2><?php echo htmlspecialchars($serenity_title); ?></h2>
                <div style="margin-bottom: 20px; line-height: 1.6;">
                    <?php echo nl2br($serenity_text); // Or echo $serenity_text if we decide to store HTML ?>
                </div>

                <div class="features grid">
                    <div class="feature-item">
                        <h4>Handcrafted with Love</h4>
                        <p>Each candle poured by hand, made with patience and care.</p>
                    </div>
                    <div class="feature-item">
                        <h4>Nature's Embrace</h4>
                        <p>Pure, natural ingredients for a clean, safe burn.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Candle Care Button -->
        <section class="container" style="margin-bottom: 10px;">
            <a href="candle_care.php" class="candle-care-btn">
                Candle Care
            </a>
        </section>

        <!-- Rewards Section -->
        <section class="rewards-section container">
            <div class="rewards-wrapper grid">
                <div class="rewards-content">
                    <div class="rewards-header">
                        <h2 class="brand-title">Senara</h2>
                        <h3 class="reward-subtitle">Reward</h3>
                    </div>

                    <div class="rewards-body">
                        <p class="main-cta">Sign up today to unlock exclusive perks and benefits</p>
                        <a href="<?php echo $isLoggedIn ? 'profile.php' : 'login.php'; ?>" class="btn-primary">Join
                            Now</a>
                    </div>
                </div>
                <div class="rewards-icons grid">
                    <div class="reward-item">
                        <div class="icon-circle">
                            <span class="percent">%</span>
                        </div>
                        <p>10% off full price<br>5% off sell</p>
                    </div>
                    <div class="reward-item">
                        <div class="icon-circle">
                            <ion-icon name="cube-outline"></ion-icon>
                        </div>
                        <p>50% off standard<br>delivery</p>
                    </div>
                    <div class="reward-item">
                        <div class="icon-circle">
                            <ion-icon name="ticket-outline"></ion-icon>
                        </div>
                        <p>Renewal voucher</p>
                    </div>
                    <div class="reward-item">
                        <div class="icon-circle">
                            <ion-icon name="return-down-back-outline"></ion-icon>
                        </div>
                        <p>Extended return period</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>