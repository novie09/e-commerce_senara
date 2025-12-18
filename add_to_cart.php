<?php
include 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check authentication
    if (!isset($_SESSION['user_id'])) {
        // Store current URL to redirect back after login? For now just redirect to login.
        header("Location: login.php?redirect=product_detail.php?id=" . $_POST['product_id']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    if ($quantity < 1)
        $quantity = 1;

    // Check if product exists in cart for this user
    $checkparams = [$user_id, $product_id];
    $check_stmt = $conn->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
    $check_stmt->bind_param("ii", $user_id, $product_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Update quantity
        $row = $result->fetch_assoc();
        $new_qty = $row['quantity'] + $quantity;
        $update_stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        $update_stmt->bind_param("ii", $new_qty, $row['id']);
        $update_stmt->execute();
    } else {
        // Insert new item
        $insert_stmt = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("iii", $user_id, $product_id, $quantity);
        $insert_stmt->execute();
    }

    // Redirect back to product detail with success flag
    header("Location: product_detail.php?id=" . $product_id . "&added=success");
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>