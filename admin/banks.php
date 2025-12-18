<?php
include '../config.php';
include 'includes/header.php';

// Handle Add Bank
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['bank_name'];
    $category = $_POST['category'] ?? 'bank';

    $target_dir = "../assets/img/banks/";
    if (!file_exists($target_dir))
        mkdir($target_dir, 0777, true);

    if (isset($_POST['add_bank'])) {
        $hero_image = '';
        if (isset($_FILES['bank_logo']) && $_FILES['bank_logo']['error'] == 0) {
            $filename = time() . '_' . basename($_FILES['bank_logo']['name']);
            if (move_uploaded_file($_FILES['bank_logo']['tmp_name'], $target_dir . $filename)) {
                $hero_image = "assets/img/banks/" . $filename;
            }
        }
        // Check if category column exists, assuming migration run
        // For backwards compatibility or errors, we assume category works
        $stmt = $conn->prepare("INSERT INTO banks (bank_name, category, logo_url) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $category, $hero_image);
        $stmt->execute();
        echo "<script>window.location.href='banks.php';</script>";

    } elseif (isset($_POST['update_bank'])) {
        $id = $_POST['bank_id'];
        $hero_image = $_POST['old_logo'];

        if (isset($_FILES['bank_logo']) && $_FILES['bank_logo']['error'] == 0) {
            $filename = time() . '_' . basename($_FILES['bank_logo']['name']);
            if (move_uploaded_file($_FILES['bank_logo']['tmp_name'], $target_dir . $filename)) {
                $hero_image = "assets/img/banks/" . $filename;
            }
        }
        $stmt = $conn->prepare("UPDATE banks SET bank_name=?, category=?, logo_url=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $category, $hero_image, $id);
        $stmt->execute();
        echo "<script>window.location.href='banks.php';</script>";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM banks WHERE id=$id");
    echo "<script>window.location.href='banks.php';</script>";
}

// Fetch for Edit
$edit_bank = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $edit_bank = $conn->query("SELECT * FROM banks WHERE id=$id")->fetch_assoc();
}

$banks = $conn->query("SELECT * FROM banks ORDER BY category ASC, bank_name ASC");
?>

<div class="page-header">
    <h1 class="page-title">Payment Methods</h1>
</div>

<div class="row">
    <!-- Add/Edit Form -->
    <div class="col-md-4">
        <div class="admin-card">
            <h5><?php echo $edit_bank ? 'Edit Method' : 'Add New Method'; ?></h5>
            <form method="POST" enctype="multipart/form-data" class="mt-3">
                <?php if ($edit_bank): ?>
                    <input type="hidden" name="bank_id" value="<?php echo $edit_bank['id']; ?>">
                    <input type="hidden" name="old_logo" value="<?php echo $edit_bank['logo_url']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label>Provider Name</label>
                    <input type="text" name="bank_name" class="form-control" required
                        value="<?php echo $edit_bank ? htmlspecialchars($edit_bank['bank_name']) : ''; ?>"
                        placeholder="e.g. Bank BSI or GoPay">
                </div>

                <div class="mb-3">
                    <label>Category</label>
                    <select name="category" class="form-select">
                        <option value="bank" <?php echo ($edit_bank && $edit_bank['category'] == 'bank') ? 'selected' : ''; ?>>Bank Transfer</option>
                        <option value="ewallet" <?php echo ($edit_bank && $edit_bank['category'] == 'ewallet') ? 'selected' : ''; ?>>E-Wallet</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Logo</label>
                    <?php if ($edit_bank && $edit_bank['logo_url']): ?>
                        <div class="mb-2">
                            <img src="../<?php echo htmlspecialchars($edit_bank['logo_url']); ?>"
                                style="height: 40px; border: 1px solid #eee; padding: 2px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="bank_logo" class="form-control" <?php echo $edit_bank ? '' : 'required'; ?>>
                    <small class="text-muted">Recommended size: 100x50px</small>
                </div>

                <?php if ($edit_bank): ?>
                    <button type="submit" name="update_bank" class="btn-primary-admin w-100 mb-2">Update Method</button>
                    <a href="banks.php" class="btn btn-light w-100">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="add_bank" class="btn-primary-admin w-100">Add Method</button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- List -->
    <div class="col-md-8">
        <div class="admin-card">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Logo</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($b = $banks->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($b['logo_url']): ?>
                                        <img src="../<?php echo htmlspecialchars($b['logo_url']); ?>"
                                            style="height: 30px; object-fit: contain;">
                                    <?php else: ?>
                                        <div style="width:50px; height:30px; background:#eee;"></div>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold"><?php echo htmlspecialchars($b['bank_name']); ?></td>
                                <td>
                                    <?php if ($b['category'] == 'ewallet'): ?>
                                        <span class="badge bg-info text-dark">E-Wallet</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Bank</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="banks.php?edit=<?php echo $b['id']; ?>" class="text-primary me-2">
                                        <ion-icon name="create-outline"></ion-icon>
                                    </a>
                                    <a href="banks.php?delete=<?php echo $b['id']; ?>" class="text-danger"
                                        onclick="return confirm('Delete?');">
                                        <ion-icon name="trash-outline"></ion-icon>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>

</html>