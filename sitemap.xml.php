<?php
header("Content-Type: application/xml; charset=utf-8");
require_once 'includes/db.php';

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// Base URL (assuming localhost/docs for now, should be dynamic if possible)
$base_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/";

// Homepage
echo '<url><loc>' . $base_url . 'index.php</loc><priority>1.0</priority></url>';

// Categories
$cats = $pdo->query("SELECT slug FROM categories")->fetchAll();
foreach ($cats as $cat) {
    echo '<url><loc>' . $base_url . 'docs.php?cat=' . $cat['slug'] . '</loc><priority>0.8</priority></url>';
}

// Pages
$pages = $pdo->query("SELECT slug, updated_at FROM pages")->fetchAll();
foreach ($pages as $p) {
    echo '<url>';
    echo '<loc>' . $base_url . 'docs.php?slug=' . $p['slug'] . '</loc>';
    echo '<lastmod>' . date('Y-m-d', strtotime($p['updated_at'])) . '</lastmod>';
    echo '<priority>0.9</priority>';
    echo '</url>';
}

echo '</urlset>';
?>
