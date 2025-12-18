<?php
include 'config.php';
$pageTitle = "Sign Up";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if email exists
        $check = $conn->query("SELECT id FROM users WHERE email='$email'");
        if ($check->num_rows > 0) {
            $error = "Email already registered!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                echo "<script>alert('Registration successful! Please login.'); window.location='login.php';</script>";
                exit;
            } else {
                $error = "Error registering: " . $conn->error;
            }
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<main class="container" style="padding-top: 80px; padding-bottom: 80px; max-width: 500px;">
    <h1 class="page-title text-center" style="margin-bottom: 30px;">Create Account</h1>

    <?php if (isset($error)): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST"
        style="background: #f9f9f9; padding: 40px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; color: var(--secondary-color);">Full Name</label>
            <input type="text" name="name" placeholder="Enter your name" required
                style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
        </div>
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; color: var(--secondary-color);">Email Address</label>
            <input type="email" name="email" placeholder="Enter your email" required
                style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
        </div>
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; color: var(--secondary-color);">Password</label>
            <input type="password" name="password" placeholder="Create password" required
                style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
        </div>
        <div style="margin-bottom: 30px;">
            <label style="display: block; margin-bottom: 8px; color: var(--secondary-color);">Confirm Password</label>
            <input type="password" name="confirm_password" placeholder="Confirm password" required
                style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
        </div>

        <button type="submit" class="btn-primary"
            style="width: 100%; background-color: var(--accent-color); color: white; border: none; cursor: pointer;">Sign
            Up</button>

        <p class="text-center" style="margin-top: 20px; font-size: 0.9rem;">
            Already have an account? <a href="login.php" style="color: var(--accent-color); font-weight: 500;">Login</a>
        </p>
    </form>
</main>

<?php include 'includes/footer.php'; ?>