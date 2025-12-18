<?php
include 'config.php';

// Add payment_proof column
$sql = "ALTER TABLE orders ADD COLUMN payment_proof VARCHAR(255) DEFAULT NULL AFTER status";

if ($conn->query($sql) === TRUE) {
    echo "Table orders updated successfully with payment_proof column.";
} else {
    echo "Error updating table: " . $conn->error;
}
?>