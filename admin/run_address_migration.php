<?php
include '../config.php';

$sql = file_get_contents('update_user_address.sql');

if ($conn->multi_query($sql)) {
    echo "Address table created and seeded successfully.";
} else {
    echo "Error: " . $conn->error;
}
?>