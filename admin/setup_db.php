<?php
include '../config.php';

function run_sql_file($conn, $file)
{
    if (!file_exists($file))
        return false;
    $sql = file_get_contents($file);
    if ($conn->multi_query($sql)) {
        do {
            // consume all results
            if ($result = $conn->store_result())
                $result->free();
        } while ($conn->more_results() && $conn->next_result());
        return true;
    } else {
        echo "Error executing $file: " . $conn->error . "<br>";
        return false;
    }
}

echo "<h1>Setting up Database...</h1>";

// Run Orders Update
if (run_sql_file($conn, 'update_orders.sql')) {
    echo "Orders table updated.<br>";
}

// Run Payments Update
if (run_sql_file($conn, 'update_payments.sql')) {
    echo "Banks table updated.<br>";
}

// Run Wallet Update
if (run_sql_file($conn, 'update_wallet.sql')) {
    echo "E-Wallets added.<br>";
}

echo "<h3>Done! You can delete this file now.</h3>";
echo "<a href='index.php'>Go to Dashboard</a>";
?>