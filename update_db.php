<?php
include 'config.php';

// 1. Update Enum Column
$sql_alter = "ALTER TABLE orders MODIFY COLUMN status ENUM('pending','processing','shipped','completed','cancelled') DEFAULT 'pending'";
if ($conn->query($sql_alter) === TRUE) {
    echo "Table schema updated successfully.<br>";
} else {
    echo "Error updating schema: " . $conn->error . "<br>";
}

// 2. Update Sample Order to 'processing' to test UI
$sql_update = "UPDATE orders SET status = 'processing' WHERE id = 1";
if ($conn->query($sql_update) === TRUE) {
    echo "Order #1 status updated to 'processing'.<br>";
} else {
    echo "Error updating order: " . $conn->error . "<br>";
}

// 3. Update another if exists
$sql_update2 = "UPDATE orders SET status = 'shipped' WHERE id = 2";
$conn->query($sql_update2);

echo "Database update complete. Please refresh your order detail page.";
?>