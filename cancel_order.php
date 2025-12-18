<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $order_id = intval($_POST['order_id']);
    $cancel_reason = $_POST['cancel_reason'];
    $user_id = $_SESSION['user_id'];

    if (empty($cancel_reason)) {
        // Fallback
        header("Location: my_orders.php?error=reason_required");
        exit;
    }

    // Update Order Status and Reason
    // Use prepared statement to prevent SQL injection and ensure user owns the order
    $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled', cancel_reason = ? WHERE id = ? AND user_id = ? AND status = 'pending'");
    $stmt->bind_param("sii", $cancel_reason, $order_id, $user_id);

    if ($stmt->execute()) {
        header("Location: my_orders.php?status=cancelled&msg=order_cancelled");
    } else {
        header("Location: my_orders.php?error=update_failed");
    }

    $stmt->close();
} else {
    header("Location: my_orders.php");
}
?>