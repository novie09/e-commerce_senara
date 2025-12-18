<?php
// If $page_slug is not set by a parent file, try to get it from URL
if (!isset($page_slug) && isset($_GET['slug'])) {
    $page_slug = $_GET['slug'];
}

// Default connection if not included already
if (!isset($conn)) {
    include 'config.php';
}

$page_title = "Page Not Found";
$page_content = "The page you are looking for does not exist.";

if (isset($page_slug)) {
    $stmt = $conn->prepare("SELECT title, content, image_url FROM site_contents WHERE page_slug = ?");
    $stmt->bind_param("s", $page_slug);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $page_title = $row['title'];
        $page_content = $row['content']; // Assumes HTML content stored in DB
        $page_image = $row['image_url'];
    } else {
        // Page not found in DB or content empty
        $page_content = "";
    }
}
?>

<?php include 'includes/header.php'; ?>

<main class="container" style="padding-top: 60px; padding-bottom: 80px; min-height: 60vh;">
    <div class="page-content" style="max-width: 800px; margin: 0 auto;">
        <h1 class="text-center mb-5" style="font-family: 'Playfair Display', serif; font-size: 2.5rem;">
            <?php echo htmlspecialchars($page_title); ?>
        </h1>

        <?php if (!empty($page_image) && file_exists($page_image)): ?>
            <div class="page-hero-image" style="margin-bottom: 40px;">
                <img src="<?php echo htmlspecialchars($page_image); ?>" alt="<?php echo htmlspecialchars($page_title); ?>"
                    style="width: 100%; height: 400px; object-fit: cover; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
            </div>
        <?php endif; ?>

        <?php if (!empty($page_content)): ?>
            <div class="content-body" style="line-height: 1.8; color: #555;">
                <?php
                // Check if content contains HTML tags
                if ($page_content != strip_tags($page_content)) {
                    echo $page_content; // Output raw HTML
                } else {
                    echo nl2br($page_content); // Output plain text with breaks
                }
                ?>
            </div>
        <?php else: ?>
            <div class="text-center text-muted py-5">
                <p>Content is coming soon.</p>
                <a href="index.php" class="btn btn-outline-dark mt-3">Back to Home</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>