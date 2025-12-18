<?php
include 'config.php';
session_start();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'] ?? 0;

// Fetch Order
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "Order not found or access denied.";
    exit;
}

// Generate VA Logic
$payment_method = $order['payment_method'];

// Default VA prefix
$va_number = "8800" . $order['phone_number'];
$bank_name = "Bank Transfer";

if (strpos($payment_method, 'COD') !== false) {
    $is_cod = true;
} else {
    $is_cod = false;
    $parts = explode(' - ', $payment_method);
    if (count($parts) > 1) {
        $bank_name = $parts[1];
    }

    // Simulate VA
    if ($bank_name == 'BCA')
        $va_number = "3901" . $order['phone_number'];
    elseif ($bank_name == 'BRI')
        $va_number = "8881" . $order['phone_number'];
    elseif ($bank_name == 'Mandiri')
        $va_number = "89022" . $order['phone_number'];
    elseif ($bank_name == 'BNI')
        $va_number = "988" . $order['phone_number'];
    elseif ($bank_name == 'Gopay')
        $va_number = "70001" . $order['phone_number'];
    elseif ($bank_name == 'OVO')
        $va_number = "39358" . $order['phone_number'];
    else
        $va_number = "8800" . $order['phone_number'];
}

// Target Time for JS Countdown (Created + 24 Hours)
$target_time_iso = date('Y-m-d H:i:s', strtotime($order['created_at'] . ' +1 day'));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Senara</title>
    <!-- Use standard CSS but override body for centering -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap"
        rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <style>
        body {
            background-color: #f9f9f9;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 20px;
        }

        .payment-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            /* Stronger shadow */
            max-width: 480px;
            width: 100%;
            overflow: hidden;
            border: 1px solid #eee;
        }

        .payment-header {
            padding: 25px;
            text-align: center;
            border-bottom: 1px solid #f5f5f5;
        }

        .payment-body {
            padding: 30px;
        }

        .va-box {
            background: #f8f9fa;
            border: 1px dashed #ccc;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-top: 20px;
        }

        .va-number {
            font-size: 1.6rem;
            font-weight: 700;
            color: #333;
            letter-spacing: 1px;
            margin: 10px 0;
            font-family: monospace;
        }

        .copy-btn {
            background: #fff;
            border: 1px solid #ddd;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
        }

        .copy-btn:hover {
            background: #f0f0f0;
            border-color: #ccc;
        }

        .countdown-box {
            background-color: #fff5f5;
            border: 1px dashed #ffcccc;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            margin-bottom: 25px;
        }
    </style>
</head>

<body>

    <div class="payment-card">
        <div class="payment-header">
            <h5 class="mb-0 fw-bold" style="font-family: 'Playfair Display', serif;">Payment Instructions</h5>
        </div>
        <div class="payment-body">

            <!-- Amount Section -->
            <div class="text-center mb-4">
                <div class="text-muted small mb-1">Total Payment</div>
                <div class="h2 fw-bold text-danger mb-2">Rp.
                    <?php echo number_format($order['grand_total'], 0, ',', '.'); ?>
                </div>
                <div class="badge bg-light text-dark border px-3 py-2 rounded-pill">Order
                    #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></div>
            </div>

            <?php if ($is_cod): ?>
                <div class="alert alert-info text-center">
                    <ion-icon name="cash-outline" size="large"></ion-icon>
                    <h6 class="mt-2 fw-bold">Cash On Delivery</h6>
                    <p class="mb-0 small">Please prepare the exact amount of cash for the courier upon delivery.</p>
                </div>
            <?php else: ?>

                <!-- Payment Method & Timer -->
                <div class="mb-3 text-center">
                    <div class="mb-3">
                        <span class="text-muted small d-block">Payment Method</span>
                        <span class="fw-bold fs-5"><?php echo htmlspecialchars($bank_name); ?></span>
                    </div>

                    <!-- Countdown -->
                    <div class="countdown-box">
                        <div class="text-danger small fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 1px;">PAY
                            BEFORE</div>
                        <div id="countdown" class="h3 fw-bold text-danger mb-0" style="font-family: monospace;">-- : -- : --
                        </div>
                    </div>
                </div>

                <!-- Virtual Account -->
                <div class="va-box">
                    <div class="text-muted small">Virtual Account Number</div>
                    <div class="va-number" id="vaNumber"><?php echo $va_number; ?></div>
                    <button class="copy-btn" onclick="copyVA()">
                        <ion-icon name="copy-outline"></ion-icon> Copy Number
                    </button>
                </div>

                <div class="small text-muted text-center mt-3 mb-4">
                    Complete your payment via ATM, Internet Banking, or Mobile Banking before the timer expires.
                </div>

                <!-- Proof Upload Section -->
                <div class="mt-5 mb-5 pt-4 border-top">
                    <?php if (!empty($order['payment_proof'])): ?>
                        <div class="alert alert-success text-center py-4 border-0"
                            style="background-color: #d1e7dd; color: #0f5132;">
                            <ion-icon name="checkmark-circle" style="font-size: 3rem; color: #198754;"></ion-icon>
                            <h6 class="fw-bold mt-2 mb-1" style="font-size: 1.1rem;">Payment Proof Uploaded</h6>
                            <p class="small mb-0">Please wait for admin verification.</p>
                        </div>
                    <?php else: ?>
                        <div class="text-center mb-4">
                            <h5 class="fw-bold mb-2"
                                style="font-family: 'Playfair Display', serif; font-size: 1.4rem; color: #2c3e50;">Upload
                                Payment Proof</h5>
                            <p class="text-muted small">Please upload your transaction receipt here.</p>
                        </div>

                        <form action="upload_proof.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="order_id" value="<?= $order_id; ?>">

                            <!-- Custom File Upload -->
                            <div class="mb-4 text-center">
                                <input type="file" name="payment_proof" id="fileInput" style="display:none;" accept="image/*"
                                    required onchange="updateFileName()">
                                <label for="fileInput" class="upload-zone d-block mx-auto p-4 rounded-3"
                                    style="border: 2px dashed #bdc3c7; cursor: pointer; transition: all 0.2s; background: #f8f9fa; max-width: 100%;">
                                    <ion-icon name="cloud-upload-outline" style="font-size: 2.5rem; color: #95a5a6;"></ion-icon>
                                    <div class="fw-bold mt-2 text-dark" id="fileNameDisplay" style="font-size: 1rem;">Click to
                                        Choose File</div>
                                    <div class="small text-muted mt-1">Format: JPG, PNG (Max 5MB)</div>
                                </label>
                            </div>

                            <!-- Upload Button (Green) -->
                            <button type="submit" class="btn-upload-green">
                                <ion-icon name="arrow-up-circle" style="font-size: 1.2rem;"></ion-icon>
                                Upload Proof
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <script>
                    function updateFileName() {
                        const fileInput = document.getElementById('fileInput');
                        const fileNameDisplay = document.getElementById('fileNameDisplay');
                        const uploadZone = document.querySelector('.upload-zone');

                        if (fileInput.files.length > 0) {
                            fileNameDisplay.innerText = fileInput.files[0].name;
                            fileNameDisplay.style.color = '#27ae60';
                            uploadZone.style.borderColor = '#27ae60';
                            uploadZone.style.backgroundColor = '#eafaf1';
                        } else {
                            fileNameDisplay.innerText = "Click to Choose File";
                            fileNameDisplay.style.color = '#333';
                            uploadZone.style.borderColor = '#bdc3c7';
                            uploadZone.style.backgroundColor = '#f8f9fa';
                        }
                    }
                </script>



            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="d-grid gap-2" style="margin-top: 30px;">
                <a href="my_orders.php" class="btn btn-primary-custom w-100 py-3 text-center rounded-pill">Check Payment
                    Status</a>
                <a href="index.php" class="btn btn-transparent w-100 py-2 text-center text-muted"
                    style="text-decoration: none;">Shop More</a>
            </div>

        </div>
    </div>

    <script>
        function copyVA() {
            const vaText = document.getElementById('vaNumber').innerText;
            navigator.clipboard.writeText(vaText).then(() => {
                alert('Virtual Account Number copied!');
            });
        }

        // Countdown Timer Logic
        const targetDateStr = "<?php echo $target_time_iso; ?>";
        // Parse ISO date string carefully for cross-browser support
        const targetDate = new Date(targetDateStr.replace(' ', 'T'));

        const countdownElem = document.getElementById('countdown');

        function updateCountdown() {
            if (!countdownElem) return;

            const now = new Date().getTime();
            const distance = targetDate.getTime() - now;

            if (distance < 0) {
                countdownElem.innerHTML = "EXPIRED";
                countdownElem.classList.remove('text-danger');
                countdownElem.classList.add('text-muted');
                return;
            }

            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            countdownElem.innerHTML =
                String(hours).padStart(2, '0') + " : " +
                String(minutes).padStart(2, '0') + " : " +
                String(seconds).padStart(2, '0');
        }

        setInterval(updateCountdown, 1000);
        updateCountdown(); 
    </script>
</body>

</html>