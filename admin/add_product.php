<?php include '../config.php'; ?>
<?php include 'includes/header.php'; ?>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];

    // Image Upload (Simple)
    $target_dir = "../assets/img/products/";
    if (!is_dir($target_dir))
        mkdir($target_dir, 0777, true);

    $target_file = "";
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $filename = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file_path = $target_dir . $filename;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file_path)) {
            $target_file = "assets/img/products/" . $filename;
        }
    }

    $stmt = $conn->prepare("INSERT INTO products (name, description, price, category, image_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $description, $price, $category, $target_file);

    if ($stmt->execute()) {
        echo "<script>alert('Product added successfully!'); window.location='products.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
}
?>

<div class="page-header">
    <h1 class="page-title">Add Product</h1>
    <div class="page-actions">
        <a href="products.php" class="btn-action btn-view" title="Back to List"><ion-icon
                name="arrow-back-outline"></ion-icon></a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="admin-card">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Lavender Bliss">
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"
                        placeholder="Enter product details..."></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="form-label">Price (Rp)</label>
                        <input type="number" name="price" class="form-control" required placeholder="0">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-control" required>
                            <option value="fresh">Fresh</option>
                            <option value="fruity">Fruity</option>
                            <option value="floral">Floral</option>
                            <option value="gourmand">Gourmand</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Product Image</label>
                    <input type="file" name="image" class="form-control">
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn-primary-admin">
                        <ion-icon name="save-outline"></ion-icon> Save Product
                    </button>
                    <a href="products.php" class="btn btn-secondary ms-2"
                        style="padding: 12px 24px; border-radius: 8px;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>

</html>