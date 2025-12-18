<?php
$servername = "sqlXXX.infinityfree.com";
$username = "if0_40703494";
$password = "senaramylove";
$dbname = "if0_40703494_senara_db";


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>