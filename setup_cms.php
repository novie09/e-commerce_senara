<?php
include 'config.php';

// Create site_contents table
$sql = "CREATE TABLE IF NOT EXISTS site_contents (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_slug VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(100) NOT NULL,
    content TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table site_contents created successfully.<br>";

    // Default Content
    $defaults = [
        ['rewards', 'Senara Reward', 'Sign up today to unlock exclusive perks and benefits'],
        ['candle_care', 'Candle Care', 'Proper candle care is essential for a clean, safe burn.'],
        ['help', 'Help & Support', 'How can we assist you today?'],
        ['contact', 'Contact Us', 'Get in touch with us for any inquiries.'],
        ['privacy', 'Privacy Policy', 'Your privacy is important to us.']
    ];

    foreach ($defaults as $page) {
        $slug = $page[0];
        $title = $page[1];
        $content = $page[2];

        // Insert if not exists
        $check = $conn->query("SELECT id FROM site_contents WHERE page_slug = '$slug'");
        if ($check->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO site_contents (page_slug, title, content) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $slug, $title, $content);
            $stmt->execute();
            echo "Inserted default for $slug.<br>";
        }
    }
} else {
    echo "Error creating table: " . $conn->error;
}
?>