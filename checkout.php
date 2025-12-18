<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || empty($_POST['selected_items'])) {
    header("Location: cart.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$selected_ids = $_POST['selected_items'];
// Securely build imploded string
$placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
$types = str_repeat('i', count($selected_ids));

$stmt = $conn->prepare("
    SELECT ci.id as cart_id, ci.quantity, p.name, p.price, p.image_url 
    FROM cart_items ci 
    JOIN products p ON ci.product_id = p.id 
    WHERE ci.id IN ($placeholders) AND ci.user_id = ?
");

// Bind selected IDs + user_id
$params = array_merge($selected_ids, [$user_id]);
$stmt->bind_param($types . 'i', ...$params);
$stmt->execute();
$result = $stmt->get_result();

$checkout_items = [];
$subtotal = 0;
while ($row = $result->fetch_assoc()) {
    $checkout_items[] = $row;
    $subtotal += ($row['price'] * $row['quantity']);
}

// Dynamic Calculation
$shipping_cost = 0;
$tax = 16000;

// Member Discount 10%
$discount = $subtotal * 0.10;

$total = ($subtotal - $discount) + $shipping_cost + $tax;
if ($total < 0)
    $total = 0;

// Fetch User Addresses
$addresses = [];
$primary_address = null;

$addr_stmt = $conn->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_primary DESC");
$addr_stmt->bind_param("i", $user_id);
$addr_stmt->execute();
$addr_result = $addr_stmt->get_result();

while ($addr = $addr_result->fetch_assoc()) {
    $addresses[] = $addr;
    if ($addr['is_primary'] || !$primary_address) {
        $primary_address = $addr;
    }
}

// Fallback if no address found (use session name)
if (!$primary_address) {
    $primary_address = [
        'recipient_name' => $_SESSION['name'] ?? 'Guest',
        'phone_number' => '-',
        'address_line' => 'Please add an address',
    ];
}

// Fetch Banks & E-Wallets
$banks_res = $conn->query("SELECT * FROM banks WHERE is_active = 1");
$banks = [];
$ewallets = [];
while ($b = $banks_res->fetch_assoc()) {
    if (isset($b['category']) && $b['category'] == 'ewallet') {
        $ewallets[] = $b;
    } else {
        $banks[] = $b;
    }
}

// Calculate ETA (2-3 days from now)
$eta_start = date('d', strtotime('+2 days'));
$eta_end = date('d M', strtotime('+3 days'));
// If months differ (e.g., 30 Jan - 1 Feb), full format would be needed, but for simplicity:
// Let's do a more robust check or just stick to simple "Tiba [Day]-[Date]"
// Actually, let's use full date logic if months differ
$start_ts = strtotime('+2 days');
$end_ts = strtotime('+3 days');
if (date('m', $start_ts) === date('m', $end_ts)) {
    $eta_string = "Tiba " . date('d', $start_ts) . "-" . date('d M', $end_ts);
} else {
    $eta_string = "Tiba " . date('d M', $start_ts) . "-" . date('d M', $end_ts);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Senara</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap"
        rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <style>
        /* Inlined Modal Styles for Reliability */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            /* Changed from flex+visibility hidden */
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .shipping-tabs {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .ship-tab {
            flex: 1;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            gap: 10px;
            cursor: pointer;
            align-items: center;
            background: white;
            transition: all 0.2s;
        }

        .ship-tab.active {
            border: 2px solid #FA591D;
            background: #fff;
        }

        /* Icon Colors */
        /* Icon Colors */
        .ship-tab img {
            transition: filter 0.2s;
            filter: grayscale(100%) opacity(0.8);
        }

        .ship-tab.active img {
            /* Filter to approximate #FA591D */
            filter: brightness(0) saturate(100%) invert(56%) sepia(67%) saturate(2255%) hue-rotate(346deg) brightness(98%) contrast(98%);
        }

        /* Text Colors */
        .tab-text {
            text-align: left;
        }

        .tab-title {
            font-weight: 600;
            font-size: 0.95rem;
            color: #333;
            transition: color 0.2s;
        }

        /* Target title inside active tab */
        .ship-tab.active .tab-title {
            color: #FA591D;
        }

        .ship-tab small {
            color: #888;
            font-weight: 500;
            font-size: 0.8rem;
            transition: color 0.2s;
        }

        .ship-tab.active small {
            color: #FA591D;
        }

        .section-label {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .delivery-options-list {
            background: #f9f9f9;
            border-radius: 8px;
            overflow: hidden;
        }

        .delivery-option {
            display: flex;
            gap: 20px;
            padding: 20px;
            background: #f9f9f9;
            margin: 0;
            cursor: pointer;
            align-items: center;
            border-bottom: 1px solid #eee;
        }

        .delivery-option:last-child {
            border-bottom: none;
        }

        .delivery-option:hover {
            background: #f0f0f0;
        }

        .delivery-option.disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background: #f4f4f4;
        }

        /* Radio Customization */
        .delivery-option input[type="radio"] {
            accent-color: #333;
            transform: scale(1.2);
        }

        .opt-name {
            font-weight: 600;
            font-size: 1rem;
            color: #333;
        }

        .opt-price {
            font-weight: 500;
            color: #333;
        }

        .opt-error {
            color: #888;
            font-size: 0.85rem;
            margin-top: 2px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-cancel {
            background: white;
            border: 1px solid #ccc;
            padding: 12px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            color: #666;
        }

        .btn-confirm {
            background: #B08D81;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn-confirm:hover {
            background: #9a7b70;
        }
    </style>
</head>

<body class="checkout-page">

    <div class="container" style="max-width: 1000px; padding-top: 40px;">
        <!-- Header -->
        <div class="checkout-header">
            <h1 style="font-family: 'Playfair Display', serif; font-size: 2.5rem;">Senara</h1>
        </div>

        <!-- Address Card -->
        <div class="checkout-card">
            <div class="shipping-header">
                <ion-icon name="location"></ion-icon> Shipping Address
            </div>

            <div class="address-content">
                <div style="font-weight: 600; color: #333;">
                    <?php echo htmlspecialchars($primary_address['recipient_name']); ?><br>
                    <span style="font-weight: 400; color: #555;">(+62)
                        <?php echo htmlspecialchars($primary_address['phone_number']); ?></span>
                </div>
                <div>
                    <?php echo htmlspecialchars($primary_address['address_line']); ?>, <br>
                    <?php echo htmlspecialchars($primary_address['city']); ?>,
                    <?php echo htmlspecialchars($primary_address['province']); ?>
                </div>
                <!-- <div style="text-align: right;">
                    <a href="#" class="change-btn" id="openAddressModal">Ubah</a>
                </div> -->
            </div>
        </div>

        <!-- Product Ordered -->
        <div class="checkout-card">
            <h3 style="text-transform: lowercase;">product ordered</h3>

            <div class="product-table-header">
                <div>Product Code</div>
                <div style="text-align: right;">Price</div>
                <div style="text-align: center;">Quantity</div>
                <div style="text-align: right;">Total</div>
            </div>

            <?php foreach ($checkout_items as $item): ?>
                <div class="product-table-row">
                    <div class="product-info">
                        <img
                            src="<?php echo !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : 'assets/img/placeholder.jpg'; ?>">
                        <span style="font-weight: 500; color: #333;"><?php echo htmlspecialchars($item['name']); ?></span>
                    </div>
                    <div style="text-align: right;">Rp. <?php echo number_format($item['price'], 2, ',', '.'); ?></div>
                    <div style="text-align: center;">
                        <span class="qty-pill">
                            <ion-icon name="remove-circle-outline" style="color: #999; cursor: pointer;"
                                onclick="updateCheckoutQty(<?php echo $item['cart_id']; ?>, -1, <?php echo $item['price']; ?>)"></ion-icon>
                            <span id="qty-<?php echo $item['cart_id']; ?>"><?php echo $item['quantity']; ?></span>
                            <ion-icon name="add-circle-outline" style="color: #999; cursor: pointer;"
                                onclick="updateCheckoutQty(<?php echo $item['cart_id']; ?>, 1, <?php echo $item['price']; ?>)"></ion-icon>
                        </span>
                    </div>
                    <div style="text-align: right; font-weight: 600;">Rp.
                        <span
                            id="total-<?php echo $item['cart_id']; ?>"><?php echo number_format($item['price'] * $item['quantity'], 2, ',', '.'); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Discount Row -->
            <div class="discount-row">
                <div style="flex: 1; display: flex; align-items: center; justify-content: flex-end; gap: 20px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="discount-tag">%</span> Member Discount
                    </div>
                    <div style="font-weight: 600; margin-left: auto;">-Rp.
                        <span id="summary-discount-value"><?php echo number_format($discount, 2, ',', '.'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Delivery Row -->
            <div class="discount-row" style="color: #333; border-top: 1px dashed #eee; margin-top: 15px;">
                <div style="flex: 1; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div style="font-weight: 500;">Delivery Options: <span id="shipping-name">Reguler</span></div>
                        <span class="text-muted" style="font-size: 0.85rem;"
                            id="shipping-eta"><?php echo $eta_string; ?></span>
                        <a href="#" class="change-btn" id="openShippingModal" style="margin-left: 15px;">Ubah</a>
                    </div>
                    <div style="font-weight: 600;">Rp. <span id="shipping-cost">0</span></div>
                </div>
            </div>

            <!-- Pickup Note Field (Hidden by default) -->
            <div id="pickup-note-container"
                style="display: none; margin-top: 15px; border-top: 1px dashed #eee; padding-top: 15px;">
                <label style="font-weight: 500; font-size: 0.95rem; display: block; margin-bottom: 8px;">Pickup Time
                    Note:</label>
                <input type="text" id="pickup_note" name="pickup_note" placeholder="Example: I'll pick up at 2 PM"
                    class="form-control"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
            </div>
        </div>

        <!-- Payment Method -->
        <div class="checkout-card">
            <h3>Payment Method</h3>
            <div class="payment-tabs">
                <button class="tab-btn" id="pm-cod" onclick="switchPayment('COD')">COD</button>
                <button class="tab-btn active" id="pm-bank" onclick="switchPayment('Bank Transfer')">Bank
                    Transfer</button>
                <button class="tab-btn" id="pm-ewallet" onclick="switchPayment('E-Wallet')">E-Wallet</button>
            </div>

            <!-- Content: COD -->
            <div id="content-cod" style="display: none; padding: 20px 0;">
                <div style="padding: 15px; background: #f9f9f9; border-radius: 8px; border: 1px solid #eee;">
                    <h5 style="margin-top: 0;">Cash On Delivery</h5>
                    <p style="margin-bottom: 0; color: #666; font-size: 0.9rem;">Pay with cash when your package
                        arrives.</p>
                </div>
            </div>

            <!-- Content: Bank Transfer -->
            <div id="content-bank" class="bank-list-modern">
                <?php foreach ($banks as $b): ?>
                    <label class="bank-row">
                        <input type="radio" name="bank_choice" value="<?php echo htmlspecialchars($b['bank_name']); ?>"
                            onchange="selectBank(this)">
                        <?php if (!empty($b['logo_url'])): ?>
                            <div class="bank-logo-img"
                                style="width: 60px; height: 35px; display: flex; align-items: center; justify-content: center; background: white; border-radius: 4px; border: 1px solid #eee; padding: 2px;">
                                <img src="<?php echo htmlspecialchars($b['logo_url']); ?>"
                                    style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            </div>
                        <?php else: ?>
                            <div class="bank-logo-box" style="background: #ccc; color: white;">?</div>
                        <?php endif; ?>
                        <div style="font-weight: 500; margin-left: 10px;"><?php echo htmlspecialchars($b['bank_name']); ?>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <!-- Content: E-Wallet -->
            <div id="content-ewallet" class="bank-list-modern" style="display: none;">
                <?php foreach ($ewallets as $ew): ?>
                    <label class="bank-row">
                        <input type="radio" name="bank_choice" value="<?php echo htmlspecialchars($ew['bank_name']); ?>"
                            onchange="selectBank(this)">
                        <?php if (!empty($ew['logo_url'])): ?>
                            <div class="bank-logo-img"
                                style="width: 60px; height: 35px; display: flex; align-items: center; justify-content: center; background: white; border-radius: 4px; border: 1px solid #eee; padding: 2px;">
                                <img src="<?php echo htmlspecialchars($ew['logo_url']); ?>"
                                    style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            </div>
                        <?php else: ?>
                            <div class="bank-logo-box" style="background: #6c5ce7; color: white;">E</div>
                        <?php endif; ?>
                        <div style="font-weight: 500; margin-left: 10px;"><?php echo htmlspecialchars($ew['bank_name']); ?>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Summary -->
        <div class="checkout-card" style="background: #fcfcfc;">
            <div class="summary-container">
                <div class="summary-line">
                    <span>Product subtotal</span>
                    <span>Rp. <span
                            id="summary-subtotal"><?php echo number_format($subtotal, 2, ',', '.'); ?></span></span>
                </div>
                <div class="summary-line">
                    <span>Original price</span>
                    <span>Rp. <span
                            id="summary-original"><?php echo number_format($subtotal, 2, ',', '.'); ?></span></span>
                </div>
                <!-- ... -->
                <div class="summary-line total">
                    <span>Total Payment:</span>
                    <span style="font-size: 1.4rem; white-space: nowrap;">Rp. <span
                            id="summary-grand-total"><?php echo number_format($total, 2, ',', '.'); ?></span></span>
                </div>

                <div style="width: 100%; display: flex; justify-content: flex-end;">
                    <div style="width: 100%; display: flex; justify-content: flex-end;">
                        <button class="pay-btn-blue" id="payBtn">Pay</button>
                    </div>
                </div>
            </div>
        </div>

    </div>




    </div>

    <!-- Shipping Modal (Moved to root) -->
    <div class="modal-overlay" id="shippingModal">
        <div class="modal-box shipping-modal-box">
            <h2 class="modal-header" style="font-size: 1.5rem; margin-bottom: 25px;">Shipping Options</h2>

            <div class="shipping-tabs">
                <button class="ship-tab active" id="tab-delivery">
                    <img src="assets/img/icon-delivery.png" style="width: 40px; height: 40px; object-fit: contain;"
                        onerror="this.src='https://cdn-icons-png.flaticon.com/512/713/713311.png'">
                    <div class="tab-text">
                        <div class="tab-title">Deliver to Your Address</div>
                        <small>From Rp. 0</small>
                    </div>
                </button>
                <button class="ship-tab" id="tab-pickup">
                    <img src="assets/img/icon-pickup.png" style="width: 40px; height: 40px; object-fit: contain;"
                        onerror="this.src='https://cdn-icons-png.flaticon.com/512/925/925748.png'">
                    <div class="tab-text">
                        <div class="tab-title">Pick Up at The Store</div>
                    </div>
                </button>
            </div>

            <div id="content-delivery">
                <div class="section-label">Select Delivery Service</div>
                <div class="delivery-options-list">
                    <label class="delivery-option">
                        <div style="width: 25%; font-weight: 600;">Reguler</div>
                        <div style="width: 25%; font-weight: 600;">Rp. 0</div>
                        <input type="radio" name="delivery_option" value="regular" data-cost="0" data-name="Reguler"
                            data-eta="<?php echo $eta_string; ?>" checked style="margin-left: auto;">
                    </label>
                    <label class="delivery-option disabled">
                        <div style="display: flex; flex-direction: column; width: 100%;">
                            <div style="font-weight: 600;">Same Day</div>
                            <div class="opt-error">exceeding the delivery distance limit</div>
                        </div>
                        <input type="radio" name="delivery_option" value="sameday" disabled style="margin-left: auto;">
                    </label>
                </div>
            </div>

            <div id="content-pickup" style="display:none;">
                <div class="section-label">Select Pickup Service</div>
                <div class="delivery-options-list">
                    <label class="delivery-option">
                        <div style="width: 40%; font-weight: 600;">Pickup at Store</div>
                        <div style="width: 25%; font-weight: 600;">Rp. 0</div>
                        <input type="radio" name="delivery_option" value="pickup" data-cost="0"
                            data-name="Pickup at Store" data-eta="Siap dalam 2 jam" style="margin-left: auto;">
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn-cancel" id="closeShippingModal">Later</button>
                <button class="btn-confirm" id="confirmShippingModal">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        // Checkout Quantity Logic
        const fixedTax = <?php echo $tax; ?>;
        const fixedDiscount = <?php echo $discount; ?>;
        let shipping = <?php echo $shipping_cost; ?>;

        function updateCheckoutQty(cartId, change, unitPrice) {
            const qtySpan = document.getElementById('qty-' + cartId);
            let currentQty = parseInt(qtySpan.innerText);
            let newQty = currentQty + change;

            if (newQty < 1) return; // Prevent 0

            // Optimistic UI Update
            qtySpan.innerText = newQty;

            // Update Item Total Text
            const itemTotalSpan = document.getElementById('total-' + cartId);
            let itemTotal = newQty * unitPrice;
            itemTotalSpan.innerText = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(itemTotal);

            // Recalculate Grand Total by summing all visible items
            recalculateGrandTotal();

            // Send AJAX to update DB
            const formData = new FormData();
            formData.append('cart_id', cartId);
            formData.append('quantity', newQty);

            fetch('update_cart_ajax.php', {
                method: 'POST',
                body: formData
            }).then(response => {
                // Optional: handle failure
                console.log('Qty updated');
            });
        }

        function recalculateGrandTotal() {
            let subtotal = 0;
            // Find all item total spans
            const itemTotals = document.querySelectorAll('[id^="total-"]');

            itemTotals.forEach(span => {
                // Remove dots, replace comma with dot to parse float
                // ID format: 1.000.000,00 -> 1000000.00
                let raw = span.innerText.replace(/\./g, '').replace(',', '.');
                subtotal += parseFloat(raw);
            });

            // Update Subtotal UI
            const fmt = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 });

            if (document.getElementById('summary-subtotal'))
                document.getElementById('summary-subtotal').innerText = fmt.format(subtotal);

            if (document.getElementById('summary-original'))
                document.getElementById('summary-original').innerText = fmt.format(subtotal);

            // Calculate Grand Total
            let grandTotal = (subtotal - fixedDiscount) + shipping + fixedTax;
            if (grandTotal < 0) grandTotal = 0;

            if (document.getElementById('summary-grand-total'))
                document.getElementById('summary-grand-total').innerText = fmt.format(grandTotal);
        }

        // --- Shipping Modal Logic ---
        let tempShippingCost = 0;
        let tempShippingName = 'Reguler';
        let tempShippingEta = '<?php echo $eta_string; ?>';

        const shippingModal = document.getElementById('shippingModal');
        const openShippingBtn = document.getElementById('openShippingModal');
        const closeShippingBtn = document.getElementById('closeShippingModal');
        const confirmShippingBtn = document.getElementById('confirmShippingModal');

        // Open Modal
        openShippingBtn.addEventListener('click', (e) => {
            e.preventDefault();
            console.log('Opening shipping modal');
            shippingModal.classList.add('active');
        });

        // Close Modal
        closeShippingBtn.addEventListener('click', () => {
            shippingModal.classList.remove('active');
        });

        // Close on outside click
        shippingModal.addEventListener('click', (e) => {
            if (e.target === shippingModal) {
                shippingModal.classList.remove('active');
            }
        });

        // Switch Tabs
        window.switchShippingTab = function (tabName) {
            const tabDelivery = document.getElementById('tab-delivery');
            const tabPickup = document.getElementById('tab-pickup');
            const contentDelivery = document.getElementById('content-delivery');
            const contentPickup = document.getElementById('content-pickup');

            if (tabName === 'delivery') {
                tabDelivery.classList.add('active');
                tabPickup.classList.remove('active');
                contentDelivery.style.display = 'block';
                contentPickup.style.display = 'none';
            } else {
                tabDelivery.classList.remove('active');
                tabPickup.classList.add('active');
                contentDelivery.style.display = 'none';
                contentPickup.style.display = 'block';
            }
        };

        // Add Event Listeners for Tabs
        document.getElementById('tab-delivery').addEventListener('click', () => switchShippingTab('delivery'));
        document.getElementById('tab-pickup').addEventListener('click', () => switchShippingTab('pickup'));

        // Update Temp Selection from Radio Inputs
        const shippingRadios = document.querySelectorAll('input[name="delivery_option"]');
        shippingRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.checked) {
                    tempShippingCost = parseFloat(this.getAttribute('data-cost'));
                    tempShippingName = this.getAttribute('data-name');
                    tempShippingEta = this.getAttribute('data-eta');
                }
            });
        });

        // --- Payment Tabs Logic ---
        let selectedBank = '';
        window.switchPayment = function (method) {
            const btnCod = document.getElementById('pm-cod');
            const btnBank = document.getElementById('pm-bank');
            const btnEwallet = document.getElementById('pm-ewallet');

            const contentCod = document.getElementById('content-cod');
            const contentBank = document.getElementById('content-bank');
            const contentEwallet = document.getElementById('content-ewallet');

            // Reset All
            btnCod.classList.remove('active');
            btnBank.classList.remove('active');
            btnEwallet.classList.remove('active');
            contentCod.style.display = 'none';
            contentBank.style.display = 'none';
            contentEwallet.style.display = 'none';

            if (method === 'COD') {
                btnCod.classList.add('active');
                contentCod.style.display = 'block';
            } else if (method === 'Bank Transfer') {
                btnBank.classList.add('active');
                contentBank.style.display = 'grid'; // Grid layout for banks
            } else if (method === 'E-Wallet') {
                btnEwallet.classList.add('active');
                contentEwallet.style.display = 'grid'; // Grid layout for e-wallets
            }
        };

        window.selectBank = function (el) {
            selectedBank = el.value;
        };

        // --- Payment Logic ---
        const payBtn = document.getElementById('payBtn');
        const cartIds = <?php echo json_encode($selected_ids); ?>;

        payBtn.addEventListener('click', () => {
            // Basic UI Loading State
            payBtn.innerText = 'Processing...';
            payBtn.disabled = true;

            const pickupNote = document.getElementById('pickup_note').value;

            // Determine Payment Method
            let paymentMethod = '';
            const isCOD = document.getElementById('pm-cod').classList.contains('active');
            const isEwallet = document.getElementById('pm-ewallet').classList.contains('active');

            if (isCOD) {
                paymentMethod = 'COD';
            } else {
                if (!selectedBank) {
                    alert('Please select a payment provider.');
                    payBtn.innerText = 'Pay';
                    payBtn.disabled = false;
                    return;
                }
                if (isEwallet) {
                    paymentMethod = 'E-Wallet - ' + selectedBank;
                } else {
                    paymentMethod = 'Bank Transfer - ' + selectedBank;
                }
            }

            // Current Shipping

            // Current Shipping (Global variable 'shipping' has cost, we need name)
            // Using DOM as source of truth for name since we updated it on confirm
            const shippingMethod = document.getElementById('shipping-name').innerText;

            const payload = {
                cart_ids: cartIds,
                shipping_method: shippingMethod,
                shipping_cost: shipping,
                pickup_note: pickupNote,
                payment_method: paymentMethod,
                phone: '<?php echo $primary_address['phone_number']; ?>', // Fallback
                address: '<?php echo $primary_address['address_line']; ?>' // Fallback
            };

            fetch('process_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // alert('Order placed successfully! Order ID: ' + data.order_id);
                        window.location.href = 'payment.php?id=' + data.order_id;
                    } else {
                        alert('Failed to place order: ' + data.message);
                        payBtn.innerText = 'Pay';
                        payBtn.disabled = false;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('An error occurred.');
                    payBtn.innerText = 'Pay';
                    payBtn.disabled = false;
                });
        });

        // Confirm Selection
        confirmShippingBtn.addEventListener('click', () => {
            // Update Global Shipping Variable
            shipping = tempShippingCost;

            // Update UI
            document.getElementById('shipping-name').innerText = tempShippingName;
            document.getElementById('shipping-eta').innerText = tempShippingEta;

            // Toggle Pickup Note Field
            const pickupNoteContainer = document.getElementById('pickup-note-container');
            if (tempShippingName === 'Pickup at Store') {
                pickupNoteContainer.style.display = 'block';
            } else {
                pickupNoteContainer.style.display = 'none';
                document.getElementById('pickup_note').value = ''; // Clear value if not pickup
            }

            // Format Cost
            const fmt = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 });
            document.getElementById('shipping-cost').innerText = fmt.format(shipping);

            // Recalculate Total
            recalculateGrandTotal();

            // Close Modal
            shippingModal.classList.remove('active');
        });
    </script>

</body>

</html>