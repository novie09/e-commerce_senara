<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    // Optional: password update logic could go here later

    if (empty($name) || empty($email)) {
        header("Location: profile.php?edit=1&error=empty_fields");
        exit;
    }

    // Update query
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $email, $user_id);

    if ($stmt->execute()) {
        // Update session
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        header("Location: profile.php?success=profile_updated");
    } else {
        header("Location: profile.php?edit=1&error=db_error");
    }
    $stmt->close();
} else {
    header("Location: profile.php");
}
?>