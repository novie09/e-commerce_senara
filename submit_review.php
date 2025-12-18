<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $order_id = intval($_POST['order_id']);
    $rating = intval($_POST['rating']);
    $user_id = $_SESSION['user_id'];

    if ($rating < 1 || $rating > 5) {
        header("Location: order_detail.php?id=$order_id&error=invalid_rating");
        exit;
    }

    // Update Database
    $stmt = $conn->prepare("UPDATE orders SET rating = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iii", $rating, $order_id, $user_id);

    if ($stmt->execute()) {
        header("Location: order_detail.php?id=$order_id&success=review_submitted");
    } else {
        header("Location: order_detail.php?id=$order_id&error=db_error");
    }
    $stmt->close();
} else {
    header("Location: index.php");
}
?>