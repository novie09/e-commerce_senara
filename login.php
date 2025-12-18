<?php
include 'config.php';
$pageTitle = "Login";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, name, password, role FROM users WHERE email = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Password correct
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: admin/index.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No account found with that email.";
        }
        $stmt->close();
    } else {
        $error = "Database error.";
    }
}
?>
<?php include 'includes/header.php'; ?>

<main class="container" style="padding-top: 80px; padding-bottom: 80px; max-width: 500px;">
    <h1 class="page-title text-center" style="margin-bottom: 30px;">Login</h1>

    <?php if (isset($error)): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST"
        style="background: #f9f9f9; padding: 40px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; color: var(--secondary-color);">Email Address</label>
            <input type="email" name="email" placeholder="Enter your email" required
                style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
        </div>
        <div style="margin-bottom: 30px;">
            <label style="display: block; margin-bottom: 8px; color: var(--secondary-color);">Password</label>
            <input type="password" name="password" placeholder="Enter your password" required
                style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
        </div>

        <button type="submit" class="btn-primary"
            style="width: 100%; background-color: var(--accent-color); color: white; border: none; cursor: pointer;">Sign
            In</button>

        <p class="text-center" style="margin-top: 20px; font-size: 0.9rem;">
            Don't have an account? <a href="signup.php" style="color: var(--accent-color); font-weight: 500;">Sign
                up</a>
        </p>
    </form>
</main>

<?php include 'includes/footer.php'; ?>