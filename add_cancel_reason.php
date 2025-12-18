<?php
include 'config.php';

$sql = "ALTER TABLE orders ADD COLUMN cancel_reason VARCHAR(255) DEFAULT NULL AFTER status";

if ($conn->query($sql) === TRUE) {
    echo "Table orders updated successfully";
} else {
    echo "Error updating table: " . $conn->error;
}
?>