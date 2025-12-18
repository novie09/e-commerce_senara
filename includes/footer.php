<!-- Value Props Bar -->
<div class="value-props">
    <div class="container flex justify-between">
        <div class="prop-item flex items-center gap-2">
            <ion-icon name="heart-outline"></ion-icon>
            <span>New and Glow-worthy</span>
        </div>
        <div class="prop-item flex items-center gap-2">
            <ion-icon name="car-outline"></ion-icon>
            <span>Free delivery on orders over Rp. 1.000.000,00</span>
        </div>
        <div class="prop-item flex items-center gap-2">
            <ion-icon name="ribbon-outline"></ion-icon>
            <span>Join Senara reward & save 10%</span>
        </div>
    </div>
</div>

<footer>
    <div class="container grid footer-grid">
        <div class="footer-brand">
            <div class="footer-logo">
                <img src="assets/img/logo.png" alt="Senara Logo" style="width: 150px; opacity: 0.8;">
                <!-- Using Brand_Introduction as placeholder or part of design, if pure logo not available -->
                <!-- Or just text if preferred -->
                <!-- <h2 class="logo">Senara</h2> -->
            </div>
        </div>

        <div class="footer-links">
            <h4>About</h4>
            <ul>
                <li><a href="about.php">Senara</a></li>
            </ul>
        </div>

        <div class="footer-links">
            <h4>Explore</h4>
            <ul>
                <li><a href="rewards.php">Senara Reward</a></li>
                <li><a href="candle_care.php">Candles Care</a></li>
                <li><a href="<?php echo isset($_SESSION['user_id']) ? 'profile.php' : 'login.php'; ?>">My Account</a>
                </li>
            </ul>
        </div>

        <div class="footer-links">
            <h4>Help & Support</h4>
            <ul>
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="help.php">Help Center</a></li>
                <li><a href="privacy.php">Privacy Policy</a></li>
            </ul>
        </div>
    </div>

    <div class="container text-center social-icons">
        <a href="#"><ion-icon name="logo-instagram"></ion-icon></a>
        <a href="#"><ion-icon name="logo-tiktok"></ion-icon></a>
    </div>
</footer>
</body>

</html>