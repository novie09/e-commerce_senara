<?php include '../config.php'; ?>
<?php include 'includes/header.php'; ?>

<div class="page-header">
    <h1 class="page-title">Pages & Content</h1>
    <a href="#" class="btn-primary-admin" style="display:none;">Add New</a> <!-- Fixed list for now -->
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Page Slug</th>
                    <th>Title</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM site_contents ORDER BY id ASC");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td><span style='background: #eff2f5; padding: 4px 8px; border-radius: 4px; font-family: monospace;'>" . $row['page_slug'] . "</span></td>";
                        echo "<td style='font-weight: 500;'>" . $row['title'] . "</td>";
                        echo "<td>" . ($row['updated_at'] ? date('M j, Y H:i', strtotime($row['updated_at'])) : 'Draft') . "</td>";
                        echo "<td>
                            <a href='edit_page.php?id=" . $row['id'] . "' class='btn-action btn-edit'>
                                <ion-icon name='create-outline' style='vertical-align: text-bottom;'></ion-icon> Edit Content
                            </a>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center'>No pages found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>

</html>