<?php
include '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: pages.php");
    exit;
}

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM site_contents WHERE id=$id");
$page = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $image_url = $page['image_url']; // Keep existing by default

    // Handle Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../assets/uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $filename = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = "assets/uploads/" . $filename;
        }
    }

    $stmt = $conn->prepare("UPDATE site_contents SET title=?, content=?, image_url=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("sssi", $title, $content, $image_url, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Content updated successfully!'); window.location='pages.php';</script>";
    } else {
        $error = "Error updating page: " . $conn->error;
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="page-header">
    <h1 class="page-title">Edit Content</h1>
    <a href="pages.php" class="btn-action">
        <ion-icon name="arrow-back-outline"></ion-icon> Back
    </a>
</div>

<div class="admin-card" style="max-width: 900px;">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"
            style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label class="form-label">Page/Section Title</label>
            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($page['title']); ?>"
                required>
        </div>

        <div class="form-group">
            <label class="form-label">Content Text</label>
            <textarea name="content" class="form-control"
                rows="10"><?php echo htmlspecialchars($page['content']); ?></textarea>
            <small class="text-muted">You can use HTML or simple text here.</small>
        </div>

        <div class="form-group">
            <label class="form-label">Section Image (Optional)</label>
            <?php if (!empty($page['image_url'])): ?>
                <div style="margin-bottom: 10px;">
                    <img src="../<?php echo $page['image_url']; ?>" alt="Current Image"
                        style="max-height: 200px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,00,0,0.1);">
                </div>
            <?php endif; ?>
            <input type="file" name="image" class="form-control" accept="image/*">
            <small class="text-muted">Upload a new image to replace the current one (e.g., for "Senara is Serenity"
                section).</small>
        </div>

        <button type="submit" class="btn-primary-admin">Update Content</button>
        <a href="pages.php" class="btn-action btn-delete" style="text-decoration: none; margin-left: 10px;">Cancel</a>
    </form>
</div>

</body>

</html>