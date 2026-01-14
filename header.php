<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Developer Documentation'; ?> - GNOME Docs</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body data-theme="light">
    <div class="app-container">
        <?php if (!isset($hide_sidebar) || !$hide_sidebar): ?>
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fab fa-gnome"></i>
                <span>GNOME Docs</span>
            </div>
            
            <div class="search-box">
                <form action="search.php" method="GET">
                    <input type="text" name="q" placeholder="Search documentation..." value="<?php echo $_GET['q'] ?? ''; ?>">
                </form>
            </div>

            <nav>
                <?php
                // Fetch groups for sidebar
                $stmt = $pdo->query("SELECT * FROM categories ORDER BY display_order ASC");
                $categories = $stmt->fetchAll();

                foreach ($categories as $cat):
                ?>
                <div class="nav-group">
                    <div class="nav-group-title"><?php echo htmlspecialchars($cat['name']); ?></div>
                    <?php
                    $stmt_pages = $pdo->prepare("SELECT title, slug FROM pages WHERE category_id = ? ORDER BY display_order ASC");
                    $stmt_pages->execute([$cat['id']]);
                    $pages = $stmt_pages->fetchAll();
                    foreach ($pages as $p):
                        $active = (isset($current_slug) && $current_slug == $p['slug']) ? 'active' : '';
                    ?>
                    <a href="docs.php?slug=<?php echo $p['slug']; ?>" class="nav-link <?php echo $active; ?>">
                        <?php echo htmlspecialchars($p['title']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </nav>

            <div style="margin-top: auto; padding-top: 2rem;">
                <button id="theme-toggle" style="background:none; border:none; color:var(--text-muted); cursor:pointer;">
                    <i class="fas fa-moon"></i> Dark Mode
                </button>
            </div>
        </aside>
        <?php endif; ?>
        
        <main class="main-content <?php echo (isset($hide_sidebar) && $hide_sidebar) ? 'full-width' : ''; ?>">
