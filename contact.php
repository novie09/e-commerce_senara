<?php
include 'config.php';
$pageTitle = "Contact Us";

$message_sent = false;
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    // Optional: Get user_id if logged in
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;

    $stmt = $conn->prepare("INSERT INTO messages (user_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $name, $email, $subject, $message);

    if ($stmt->execute()) {
        $message_sent = true;
    } else {
        $error = "Something went wrong. Please try again.";
    }
}
?>
<?php include 'includes/header.php'; ?>

<main class="container" style="padding-top: 60px; padding-bottom: 80px;">
    <h1 class="page-title text-start mb-5" style="max-width: 800px; margin: 0 auto;">Contact Us</h1>

    <div style="max-width: 800px; margin: 0 auto;">
        <?php if ($message_sent): ?>
            <div class="alert alert-success"
                style="background: #d4edda; color: #155724; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <h4 style="margin-top: 0;">Thanks for submitting!</h4>
                <p class="mb-0">We have received your message and will get back to you shortly.</p>
            </div>
            <!-- Show form again tailored for next submission or just hide it? User mockup shows alert but implies staying on page. -->
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"
                style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="row" style="display: flex; gap: 20px; margin-bottom: 20px;">
                <div style="flex: 1;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Email*</label>
                    <input type="email" name="email" required
                        style="width: 100%; padding: 12px; border: 1px solid #000; border-radius: 0; outline: none;">
                </div>
                <div style="flex: 1;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Name</label>
                    <input type="text" name="name"
                        style="width: 100%; padding: 12px; border: 1px solid #000; border-radius: 0; outline: none;">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Subject*</label>
                <input type="text" name="subject" required
                    style="width: 100%; padding: 12px; border: 1px solid #000; border-radius: 0; outline: none;">
            </div>

            <div style="margin-bottom: 30px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Add a message here</label>
                <textarea name="message" required rows="6"
                    style="width: 100%; padding: 12px; border: 1px solid #000; border-radius: 0; outline: none; resize: vertical;"></textarea>
            </div>

            <div style="text-align: right; display: flex; justify-content: flex-end; align-items: center; gap: 20px;">
                <?php if ($message_sent): ?>
                    <span style="color: green;">Thanks for submitting!</span>
                <?php endif; ?>
                <button type="submit"
                    style="background-color: #A68A7C; color: white; border: none; padding: 12px 40px; border-radius: 25px; cursor: pointer; font-size: 1rem; font-weight: 500;">
                    Submit
                </button>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>