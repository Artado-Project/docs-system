<?php
require_once 'includes/db.php';
require_once 'includes/lang.php';
require_once 'includes/Parsedown.php';
require_once 'includes/toc.php';

$slug = $_GET['slug'] ?? null;
$cat_slug = $_GET['cat'] ?? null;

if (!$slug && $cat_slug) {
    // Check if category exists
    $stmt = $pdo->prepare("SELECT id, name, slug FROM categories WHERE slug = ?");
    $stmt->execute([$cat_slug]);
    $category = $stmt->fetch();
    
    if (!$category) {
        // Category doesn't exist, show 404
        $page_title = '404 - Category Not Found';
        include 'includes/header.php';
        echo "<div class='hero'><h1>404</h1><p>The category you are looking for does not exist.</p><a href='index.php' class='btn btn-primary' style='margin-top:2rem;'>Back Home</a></div>";
        include 'includes/footer.php';
        exit;
    }
    
    // If category requested without slug, redirect to first page of that category
    $stmt = $pdo->prepare("SELECT p.slug FROM pages p JOIN categories c ON p.category_id = c.id WHERE c.slug = ? ORDER BY p.display_order ASC LIMIT 1");
    $stmt->execute([$cat_slug]);
    $first_page = $stmt->fetch();
    if ($first_page) {
        header("Location: docs.php?slug=" . $first_page['slug']);
        exit;
    } else {
        // If no pages in category, show category page with message
        $page_title = htmlspecialchars($category['name']);
        include 'includes/header.php';
        echo "<div class='hero'><h1>" . htmlspecialchars($category['name']) . "</h1><p style='color: var(--text-muted);'>This category doesn't have any pages yet.</p><a href='index.php' class='btn btn-primary' style='margin-top:2rem;'>Back Home</a></div>";
        include 'includes/footer.php';
        exit;
    }
}

if (!$slug) {
        header("Location: index.php");
        exit;
}

// Fetch the page
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug FROM pages p JOIN categories c ON p.category_id = c.id WHERE p.slug = ?");
$stmt->execute([$slug]);
$page = $stmt->fetch();

if (!$page) {
    $page_title = '404 - Not Found';
    include 'includes/header.php';
        echo "<div class='hero'><h1>404</h1><p>The documentation page you are looking for does not exist.</p><a href='index.php' class='btn btn-primary' style='margin-top:2rem;'>Back Home</a></div>";
    include 'includes/footer.php';
    exit;
}

$page_content_raw = $page['content'];
$page_content_html = renderMarkdown($page_content_raw);
$toc_data = TOCGenerator::generate($page_content_html);
$page_content = $toc_data['html'];
$toc_list = TOCGenerator::renderList($toc_data['toc']);
$page_description = mb_substr(strip_tags($page_content), 0, 160) . '...';

$page_title = $page['title'];
$current_slug = $page['slug'];
include 'includes/header.php';
?>

<div class="breadcrumb" style="margin-bottom: 2rem; font-size: 0.9rem; display: flex; gap: 0.5rem; align-items: center; color: var(--text-muted);">
    <a href="index.php"><?php echo t('home', 'Home'); ?></a>
    <i class="fas fa-chevron-right" style="font-size: 0.7rem; opacity: 0.5;"></i>
    <a href="docs.php?cat=<?php echo $page['category_slug']; ?>"><?php echo htmlspecialchars($page['category_name']); ?></a>
    <i class="fas fa-chevron-right" style="font-size: 0.7rem; opacity: 0.5;"></i>
    <span style="color: var(--text-main); font-weight: 500;"><?php echo htmlspecialchars($page['title']); ?></span>
</div>

<article>
    <header class="doc-header">
        <h1><?php echo htmlspecialchars($page['title']); ?></h1>
        <div class="doc-meta">
            <span><i class="far fa-folder"></i> <?php echo htmlspecialchars($page['category_name']); ?></span>
            <span><i class="far fa-clock"></i> <?php echo t('updated', 'Updated'); ?> <?php echo date('M j, Y', strtotime($page['updated_at'])); ?></span>
        </div>
    </header>

    <div class="doc-container">
        <div class="content-body">
            <?php echo $page_content; ?>
        </div>
        
        <?php if (!empty($toc_data['toc'])): ?>
        <aside class="toc-sidebar">
            <?php echo $toc_list; ?>
        </aside>
        <?php endif; ?>
    </div>

    <footer style="margin-top: 6rem; padding-top: 2rem; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; gap: 1rem; align-items: center;">
            <div style="width: 40px; height: 40px; background: var(--bg-alt); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                <img src="<?php echo htmlspecialchars($site_settings['logo_url'] ?? 'assets/img/logo.svg'); ?>" alt="Artado" style="width: 28px; height: 28px;">
            </div>
            <div>
                <div style="font-weight: 600; font-size: 0.95rem;"><?php echo htmlspecialchars($site_settings['site_title'] ?? 'Artado Documentation'); ?></div>
                <div style="font-size: 0.85rem; color: var(--text-muted);"><?php echo htmlspecialchars($site_settings['footer_text'] ?? 'Community Powered'); ?></div>
            </div>
        </div>
        <a href="admin.php" class="btn btn-outline" style="font-size: 0.85rem;"><i class="fas fa-edit"></i> <?php echo t('edit_page', 'Edit this page'); ?></a>
    </footer>
</article>

<?php include 'includes/footer.php'; ?>
