<?php include '../config.php'; ?>
<?php include 'includes/header.php'; ?>

<?php
if (!isset($_GET['id'])) {
    echo "<script>window.location='products.php';</script>";
    exit;
}

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM products WHERE id=$id");
$product = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $image_url = $product['image_url']; // Keep old image by default

    // Image Upload
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $target_dir = "../assets/img/products/";
        if (!is_dir($target_dir))
            mkdir($target_dir, 0777, true);

        $filename = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file_path = $target_dir . $filename;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file_path)) {
            $image_url = "assets/img/products/" . $filename;
        }
    }

    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, category=?, image_url=? WHERE id=?");
    $stmt->bind_param("sssssi", $name, $description, $price, $category, $image_url, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Product updated successfully!'); window.location='products.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
}
?>

<div class="page-header">
    <h1 class="page-title">Edit Product</h1>
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
                    <input type="text" name="name" class="form-control" value="<?php echo $product['name']; ?>"
                        required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control"
                        rows="4"><?php echo $product['description']; ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="form-label">Price (Rp)</label>
                        <input type="number" name="price" class="form-control" value="<?php echo $product['price']; ?>"
                            required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-control" required>
                            <option value="fresh" <?php if ($product['category'] == 'fresh')
                                echo 'selected'; ?>>Fresh
                            </option>
                            <option value="fruity" <?php if ($product['category'] == 'fruity')
                                echo 'selected'; ?>>Fruity
                            </option>
                            <option value="floral" <?php if ($product['category'] == 'floral')
                                echo 'selected'; ?>>Floral
                            </option>
                            <option value="gourmand" <?php if ($product['category'] == 'gourmand')
                                echo 'selected'; ?>>
                                Gourmand</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Current Image</label>
                    <div class="mb-3">
                        <?php if ($product['image_url']): ?>
                            <img src="../<?php echo $product['image_url']; ?>" width="120"
                                style="border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                        <?php else: ?>
                            <p class="text-muted">No image uploaded</p>
                        <?php endif; ?>
                    </div>
                    <label class="form-label">Change Image</label>
                    <input type="file" name="image" class="form-control">
                    <small class="text-muted mt-2 d-block">Leave empty to keep current image</small>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn-primary-admin">
                        <ion-icon name="sync-outline"></ion-icon> Update Product
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