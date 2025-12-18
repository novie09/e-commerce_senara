<?php
// Auth Check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$pageTitle = "My Addresses";
include 'config.php';

$user_id = $_SESSION['user_id'];
$msg = "";

// Handle Add Address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_address'])) {
    $recipient = $_POST['recipient_name'];
    $phone = $_POST['phone_number'];
    $address = $_POST['address_line'];
    $city = $_POST['city'];
    $province = $_POST['province'];
    $postal = $_POST['postal_code'];

    // Check if first address, make primary
    $check = $conn->query("SELECT id FROM user_addresses WHERE user_id = $user_id");
    $is_primary = ($check->num_rows == 0) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO user_addresses (user_id, recipient_name, phone_number, address_line, city, province, postal_code, is_primary) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssi", $user_id, $recipient, $phone, $address, $city, $province, $postal, $is_primary);

    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success'>Address added successfully!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Error adding address.</div>";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $del_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $del_id, $user_id);
    $stmt->execute();
    header("Location: addresses.php");
    exit;
}

// Handle Set Primary
if (isset($_GET['action']) && $_GET['action'] == 'set_primary' && isset($_GET['id'])) {
    $p_id = $_GET['id'];

    // Reset all to 0
    $conn->query("UPDATE user_addresses SET is_primary = 0 WHERE user_id = $user_id");

    // Set selected to 1
    $stmt = $conn->prepare("UPDATE user_addresses SET is_primary = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $p_id, $user_id);
    $stmt->execute();

    header("Location: addresses.php");
    exit;
}

// Fetch Addresses
$stmt = $conn->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_primary DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$addresses = $stmt->get_result();
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
                // Fetch user email for display
                $u_res = $conn->query("SELECT email FROM users WHERE id = $user_id");
                $u_data = $u_res->fetch_assoc();
                ?>
                <p><?php echo htmlspecialchars($u_data['email']); ?></p>
            </div>

            <nav class="profile-nav">
                <ul>
                    <li><a href="profile.php"><ion-icon name="person-circle-outline"></ion-icon> Personal Info</a></li>
                    <li><a href="my_orders.php"><ion-icon name="bag-handle-outline"></ion-icon> My Orders</a></li>
                    <li><a href="addresses.php" class="active"><ion-icon name="location-outline"></ion-icon>
                            Addresses</a></li>
                    <li><a href="logout.php" class="logout-link"
                            onclick="return confirm('Apakah anda yakin ingin keluar?');"><ion-icon
                                name="log-out-outline"></ion-icon> Log
                            Out</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Content Area -->
        <section class="profile-content">
            <div class="content-header flex justify-between items-center mb-4">
                <h2 class="profile-section-title">My Addresses</h2>
            </div>

            <?php echo $msg; ?>

            <div class="row">
                <!-- Address List -->
                <div class="col-md-7 mb-4">
                    <?php if ($addresses->num_rows > 0): ?>
                        <div class="address-list">
                            <?php while ($addr = $addresses->fetch_assoc()): ?>
                                <div class="address-card">
                                    <?php if ($addr['is_primary']): ?>
                                        <div class="primary-badge">Primary</div>
                                    <?php endif; ?>

                                    <h5><?php echo htmlspecialchars($addr['recipient_name']); ?></h5>
                                    <p style="margin-bottom: 5px; color: #666; font-weight: 500; font-size: 0.9rem;">
                                        <?php echo htmlspecialchars($addr['phone_number']); ?>
                                    </p>
                                    <p class="address-details">
                                        <?php echo htmlspecialchars($addr['address_line']); ?><br>
                                        <?php echo htmlspecialchars($addr['city']); ?>,
                                        <?php echo htmlspecialchars($addr['province']); ?>
                                        <?php echo htmlspecialchars($addr['postal_code']); ?>
                                    </p>

                                    <div class="mt-3 d-flex align-items-center justify-content-between">
                                        <div>
                                            <?php if (!$addr['is_primary']): ?>
                                                <a href="addresses.php?action=set_primary&id=<?php echo $addr['id']; ?>"
                                                    class="btn-sm btn-black-custom"
                                                    style="padding: 6px 12px; display: inline-block; width: auto; font-size: 0.8rem; text-decoration: none; margin-right: 10px;">Select</a>
                                            <?php endif; ?>
                                        </div>
                                        <a href="addresses.php?delete=<?php echo $addr['id']; ?>" class="text-danger small"
                                            style="text-decoration: none; font-weight: 500;"
                                            onclick="return confirm('Are you sure you want to remove this address?');">
                                            <ion-icon name="trash-outline"
                                                style="vertical-align: middle; margin-right: 4px;"></ion-icon>Remove
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state text-center py-5"
                            style="background: #f9f9f9; border-radius: 12px; border: 1px dashed #ddd;">
                            <ion-icon name="map-outline" style="font-size: 40px; color: #ccc;"></ion-icon>
                            <p class="text-muted mt-2">No addresses saved yet.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Add New Form -->
                <div class="col-md-5">
                    <div class="profile-form-container">
                        <h4
                            style="margin-bottom: 25px; font-size: 1.1rem; font-family: 'Inter', sans-serif; font-weight: 600;">
                            Add New Address</h4>
                        <form method="POST">
                            <div class="form-group-custom">
                                <label class="form-label-custom">Recipient Name</label>
                                <input type="text" name="recipient_name" class="form-control-custom" required
                                    placeholder="e.g. John Doe">
                            </div>
                            <div class="form-group-custom">
                                <label class="form-label-custom">Phone Number</label>
                                <input type="text" name="phone_number" class="form-control-custom" required
                                    placeholder="e.g. 08123456789">
                            </div>
                            <div class="form-group-custom">
                                <label class="form-label-custom">Address Line</label>
                                <textarea name="address_line" class="form-control-custom" rows="3" required
                                    placeholder="Street name, house number..."></textarea>
                            </div>
                            <div class="row">
                                <div class="col-6 form-group-custom">
                                    <label class="form-label-custom">City</label>
                                    <input type="text" name="city" class="form-control-custom" required>
                                </div>
                                <div class="col-6 form-group-custom">
                                    <label class="form-label-custom">Province</label>
                                    <input type="text" name="province" class="form-control-custom" required>
                                </div>
                            </div>
                            <div class="form-group-custom">
                                <label class="form-label-custom">Postal Code</label>
                                <input type="text" name="postal_code" class="form-control-custom" required>
                            </div>
                            <button type="submit" name="add_address" class="btn-black-custom">Save Address</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php include 'includes/footer.php'; ?>