<?php
// Auth Check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$pageTitle = "My Profile";
?>
<?php include 'includes/header.php'; ?>

<main class="container profile-page" style="padding-top: 60px; padding-bottom: 80px;">
    <div class="profile-layout grid">
        <!-- Sidebar -->
        <aside class="profile-sidebar">
            <div class="user-summary text-center">
                <div class="avatar-placeholder">
                    <span><?php echo strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)); ?></span>
                </div>
                <h3><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></h3>
                <?php
                include 'config.php';
                $user_id = $_SESSION['user_id'];
                // Fetch user data
                $result = $conn->query("SELECT * FROM users WHERE id = $user_id");
                if ($result && $result->num_rows > 0) {
                    $user_data = $result->fetch_assoc();
                } else {
                    $user_data = ['name' => 'User', 'email' => 'Unknown', 'created_at' => date('Y-m-d')];
                }
                ?>
                <p><?php echo htmlspecialchars($user_data['email']); ?></p>
            </div>

            <nav class="profile-nav">
                <ul>
                    <li><a href="#" class="active"><ion-icon name="person-circle-outline"></ion-icon> Personal Info</a>
                    </li>
                    <li><a href="my_orders.php"><ion-icon name="bag-handle-outline"></ion-icon> My Orders</a></li>
                    <li><a href="addresses.php"><ion-icon name="location-outline"></ion-icon> Addresses</a></li>
                    <li><a href="logout.php" class="logout-link"
                            onclick="return confirm('Apakah anda yakin ingin keluar?');"><ion-icon
                                name="log-out-outline"></ion-icon> Log
                            Out</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Content Area -->
        <section class="profile-content">
            <?php
            $is_edit = isset($_GET['edit']);
            ?>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success mb-4">Profile updated successfully!</div>
            <?php endif; ?>

            <div class="content-header flex justify-between items-center">
                <h2><?php echo $is_edit ? 'Edit Profile' : 'Personal Information'; ?></h2>
                <?php if ($is_edit): ?>
                    <a href="profile.php" class="btn btn-outline-secondary">Cancel</a>
                <?php else: ?>
                    <a href="profile.php?edit=1" class="btn-secondary"
                        style="text-decoration:none; display:inline-block; padding: 8px 16px; border-radius: 20px;">Edit
                        Profile</a>
                <?php endif; ?>
            </div>

            <?php if ($is_edit): ?>
                <form action="update_profile.php" method="POST" class="info-grid grid">
                    <div class="info-group">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control"
                            value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
                    </div>
                    <div class="info-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control"
                            value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                    </div>
                    <div class="info-group">
                        <button type="submit" class="btn btn-primary-custom px-4 py-2 mt-2">Save Changes</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="info-grid grid">
                    <div class="info-group">
                        <label>Full Name</label>
                        <div class="info-value"><?php echo htmlspecialchars($user_data['name']); ?></div>
                    </div>
                    <div class="info-group">
                        <label>Email Address</label>
                        <div class="info-value"><?php echo htmlspecialchars($user_data['email']); ?></div>
                    </div>
                    <div class="info-group">
                        <label>Member Since</label>
                        <div class="info-value"><?php echo date('F j, Y', strtotime($user_data['created_at'])); ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php include 'includes/footer.php'; ?>