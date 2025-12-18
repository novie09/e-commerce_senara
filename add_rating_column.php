<?php
include 'config.php';

// Add rating column
$sql = "ALTER TABLE orders ADD COLUMN rating INT DEFAULT NULL AFTER payment_proof";

if ($conn->query($sql) === TRUE) {
    echo "Table orders updated successfully with rating column.";
} else {
    echo "Error updating table: " . $conn->error;
}
?>