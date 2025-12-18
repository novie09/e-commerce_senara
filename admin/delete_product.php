<?php
include '../config.php';

// Check if ID is set
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Optional: Delete image file if exists
    // $sql_img = "SELECT image_url FROM products WHERE id = $id";
    // ... unlink logic ...

    $sql = "DELETE FROM products WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Product deleted successfully'); window.location='products.php';</script>";
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    header("Location: products.php");
}
?>
