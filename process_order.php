<?php
include 'config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['cart_ids'])) {
    echo json_encode(['success' => false, 'message' => 'No items selected']);
    exit;
}

// 1. Fetch Cart Items & Calc Totals (Securely)
$placeholders = implode(',', array_fill(0, count($data['cart_ids']), '?'));
$types = str_repeat('i', count($data['cart_ids']));
$params = array_merge($data['cart_ids'], [$user_id]);

$stmt = $conn->prepare("
    SELECT ci.id as cart_id, ci.quantity, ci.product_id, p.name, p.price 
    FROM cart_items ci 
    JOIN products p ON ci.product_id = p.id 
    WHERE ci.id IN ($placeholders) AND ci.user_id = ?
");

$stmt->bind_param($types . 'i', ...$params);
$stmt->execute();
$items_result = $stmt->get_result();

$order_items = [];
$subtotal = 0;

while ($row = $items_result->fetch_assoc()) {
    $order_items[] = $row;
    $subtotal += ($row['price'] * $row['quantity']);
}

if (empty($order_items)) {
    echo json_encode(['success' => false, 'message' => 'Cart items not found']);
    exit;
}

// 2. Fetch User Address (Primary)
$addr_stmt = $conn->prepare("SELECT * FROM user_addresses WHERE user_id = ? AND is_primary = 1 LIMIT 1");
$addr_stmt->bind_param("i", $user_id);
$addr_stmt->execute();
$addr_res = $addr_stmt->get_result();
$addr = $addr_res->fetch_assoc();

if (!$addr) {
    // Fallback or Error? Ideally force address. Using session defaults for now if guest-like.
    $recipient_name = $_SESSION['name'] ?? 'Guest';
    $phone_number = $data['phone'] ?? '-';
    $address_full = $data['address'] ?? 'No address provided';
} else {
    $recipient_name = $addr['recipient_name'];
    $phone_number = $addr['phone_number'];
    $address_full = $addr['address_line'] . ", " . $addr['city'] . ", " . $addr['province'];
}

// 3. Final Calculations
$shipping_method = $data['shipping_method'] ?? 'Reguler';
$shipping_cost = floatval($data['shipping_cost'] ?? 0);
$pickup_note = ($shipping_method === 'Pickup at Store') ? ($data['pickup_note'] ?? '') : null;
$payment_method = $data['payment_method'] ?? 'Bank Transfer';

$discount = $subtotal * 0.10; // 10% Member Discount
$tax = 16000; // Fixed from checkout.php
$grand_total = ($subtotal - $discount) + $shipping_cost + $tax;
if ($grand_total < 0)
    $grand_total = 0;

// 4. Insert into Orders
$conn->begin_transaction();

try {
    $ins_order = $conn->prepare("INSERT INTO orders (user_id, recipient_name, phone_number, shipping_address, shipping_method, shipping_cost, pickup_note, payment_method, subtotal, discount_amount, tax_amount, grand_total, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $ins_order->bind_param("issssdssssdd", $user_id, $recipient_name, $phone_number, $address_full, $shipping_method, $shipping_cost, $pickup_note, $payment_method, $subtotal, $discount, $tax, $grand_total);

    if (!$ins_order->execute()) {
        throw new Exception("Order insert failed: " . $ins_order->error);
    }

    $order_id = $conn->insert_id;

    // 5. Insert Order Items
    $ins_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price, total) VALUES (?, ?, ?, ?, ?, ?)");

    foreach ($order_items as $item) {
        $item_total = $item['price'] * $item['quantity'];
        $ins_item->bind_param("iisidd", $order_id, $item['product_id'], $item['name'], $item['quantity'], $item['price'], $item_total);
        $ins_item->execute();
    }

    // 6. Delete from Cart
    $del_cart = $conn->prepare("DELETE FROM cart_items WHERE id IN ($placeholders) AND user_id = ?");
    $del_cart->bind_param($types . 'i', ...$params);
    $del_cart->execute();

    $conn->commit();
    echo json_encode(['success' => true, 'order_id' => $order_id]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>