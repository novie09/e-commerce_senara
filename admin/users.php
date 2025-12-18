<?php include '../config.php'; ?>
<?php include 'includes/header.php'; ?>

<div class="page-header">
    <h1 class="page-title">Registered Users</h1>
    <!-- <div class="page-actions"></div> -->
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined At</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM users ORDER BY id DESC");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>#" . $row['id'] . "</td>";
                        echo "<td><div style='font-weight:600;'>" . $row['name'] . "</div></td>";
                        echo "<td>" . $row['email'] . "</td>";
                        $role_badge = $row['role'] == 'admin' ? 'background:#ffe2e5;color:#f64e60;' : 'background:#e1f0ff;color:#3699ff;';
                        echo "<td><span class='badge' style='" . $role_badge . "'>" . ucfirst($row['role']) . "</span></td>";
                        echo "<td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center py-4 text-muted'>No users found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>

</html>