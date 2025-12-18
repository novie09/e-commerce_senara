<?php include '../config.php'; ?>
<?php include 'includes/header.php'; ?>

<div class="page-header">
    <h1 class="page-title">User Messages</h1>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>User</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM messages ORDER BY created_at DESC");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>#" . $row['id'] . "</td>";
                        echo "<td>" . date('M d, Y H:i', strtotime($row['created_at'])) . "</td>";
                        echo "<td>
                                <div style='font-weight:600;'>" . htmlspecialchars($row['name']) . "</div>
                                <div class='text-muted' style='font-size:0.85rem;'>" . htmlspecialchars($row['email']) . "</div>
                              </td>";
                        echo "<td>" . htmlspecialchars($row['subject']) . "</td>";
                        echo "<td><div style='max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>" . htmlspecialchars($row['message']) . "</div></td>";
                        echo "<td>
                            <button class='btn-action btn-view' onclick='alert(\"" . htmlspecialchars(addslashes($row['message'])) . "\")' title='Read Full Message'><ion-icon name='eye-outline'></ion-icon></button>
                            <a href='delete_message.php?id=" . $row['id'] . "' class='btn-action btn-delete' onclick='return confirm(\"Delete this message?\")' title='Delete'><ion-icon name='trash-outline'></ion-icon></a>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center py-4 text-muted'>No messages received yet.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>

</html>