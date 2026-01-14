<?php
require_once 'db.php';
$page_title = 'Welcome';
$hide_sidebar = true; // Home page looks better without sidebar in this style
include 'header.php';
?>

<div class="hero">
    <h1>GNOME Developer Documentation</h1>
    <p>Everything you need to build great apps for the GNOME platform.</p>
</div>

<div class="grid">
    <?php
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY display_order ASC");
    $categories = $stmt->fetchAll();
    foreach ($categories as $cat):
    ?>
    <div class="card" onclick="location.href='docs.php?cat=<?php echo $cat['slug']; ?>'">
        <i class="fas fa-<?php echo $cat['icon']; ?>"></i>
        <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
        <p><?php echo htmlspecialchars($cat['description']); ?></p>
    </div>
    <?php endforeach; ?>
</div>

<div style="margin-top: 5rem; text-align: center; color: var(--text-muted);">
    <p>Powered by PHP & MySQL. Inspired by GNOME.</p>
</div>

<?php include 'footer.php'; ?>
