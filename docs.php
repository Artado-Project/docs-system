<?php
require_once 'db.php';

$slug = $_GET['slug'] ?? null;
$cat_slug = $_GET['cat'] ?? null;

if (!$slug && $cat_slug) {
    // If category requested without slug, redirect to first page of that category
    $stmt = $pdo->prepare("SELECT p.slug FROM pages p JOIN categories c ON p.category_id = c.id WHERE c.slug = ? ORDER BY p.display_order ASC LIMIT 1");
    $stmt->execute([$cat_slug]);
    $first_page = $stmt->fetch();
    if ($first_page) {
        header("Location: docs.php?slug=" . $first_page['slug']);
        exit;
    }
}

if (!$slug) {
    // Fallback to home if nothing found
    header("Location: index.php");
    exit;
}

// Fetch the page
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM pages p JOIN categories c ON p.category_id = c.id WHERE p.slug = ?");
$stmt->execute([$slug]);
$page = $stmt->fetch();

if (!$page) {
    $page_title = '404 - Not Found';
    include 'header.php';
    echo "<h1>Page Not Found</h1><p>The documentation page you are looking for does not exist.</p>";
    include 'footer.php';
    exit;
}

$page_title = $page['title'];
$current_slug = $page['slug'];
include 'header.php';
?>

<article>
    <header class="doc-header">
        <div class="doc-meta"><?php echo htmlspecialchars($page['category_name']); ?></div>
        <h1><?php echo htmlspecialchars($page['title']); ?></h1>
    </header>

    <div class="content-body">
        <?php echo $page['content']; ?>
    </div>

    <footer style="margin-top: 4rem; padding-top: 2rem; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between;">
        <div class="doc-meta">Last updated: <?php echo date('F j, Y', strtotime($page['updated_at'])); ?></div>
        <a href="#" style="font-size: 0.9rem;"><i class="fas fa-edit"></i> Edit this page</a>
    </footer>
</article>

<?php include 'footer.php'; ?>
