<?php include '../config.php'; ?>
<?php include 'includes/header.php'; ?>

<div class="page-header">
    <h1 class="page-title">Products</h1>
    <div class="page-actions">
        <a href="add_product.php" class="btn-primary-admin">
            <ion-icon name="add-circle-outline"></ion-icon> Add Product
        </a>
    </div>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM products ORDER BY id DESC");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>#" . $row['id'] . "</td>";
                        echo "<td><img src='../" . ($row['image_url'] ? $row['image_url'] : 'assets/img/placeholder.jpg') . "' class='table-img'></td>";
                        echo "<td><div style='font-weight:600;'>" . $row['name'] . "</div></td>";
                        echo "<td><span class='badge badge-success'>" . ucfirst($row['category']) . "</span></td>";
                        echo "<td>Rp " . number_format($row['price'], 0, ',', '.') . "</td>";
                        echo "<td class='text-end'>
                            <a href='edit_product.php?id=" . $row['id'] . "' class='btn-action btn-edit' title='Edit'><ion-icon name='create-outline'></ion-icon></a>
                            <a href='delete_product.php?id=" . $row['id'] . "' class='btn-action btn-delete' onclick='return confirm(\"Are you sure you want to delete this product?\")' title='Delete'><ion-icon name='trash-outline'></ion-icon></a>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center py-4 text-muted'>No products found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>

</html>