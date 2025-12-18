<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $order_id = intval($_POST['order_id']);
    $user_id = $_SESSION['user_id'];

    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['payment_proof'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];

        if (!in_array($file['type'], $allowed_types)) {
            header("Location: payment.php?id=$order_id&error=invalid_type");
            exit;
        }

        if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
            header("Location: payment.php?id=$order_id&error=too_large");
            exit;
        }

        // Create upload dir if not exists
        $upload_dir = 'assets/uploads/payments/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'proof_' . $order_id . '_' . time() . '.' . $ext;
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            // Update Database
            $stmt = $conn->prepare("UPDATE orders SET payment_proof = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sii", $filename, $order_id, $user_id);

            if ($stmt->execute()) {
                header("Location: payment.php?id=$order_id&success=proof_uploaded");
            } else {
                header("Location: payment.php?id=$order_id&error=db_error");
            }
            $stmt->close();
        } else {
            header("Location: payment.php?id=$order_id&error=upload_failed");
        }
    } else {
        header("Location: payment.php?id=$order_id&error=no_file");
    }
} else {
    header("Location: index.php");
}
?>