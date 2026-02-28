<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__FILE__) . '/db.php';
require_once dirname(__FILE__) . '/lang.php';

try {
    $site_settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Exception $e) {
    $site_settings = [];
}

// Check maintenance mode
$maintenance_mode = ($site_settings['maintenance_mode'] ?? '0') == '1';
// Check if current page is admin or login (works with both .php and clean URLs)
$current_script = basename($_SERVER['PHP_SELF']);
$current_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_prefix = '';

$is_admin_page = in_array($current_script, ['admin.php', 'login.php', 'logout.php', 'setup.php']) || 
                 strpos($current_uri, '/admin') !== false || 
                 strpos($current_uri, '/login') !== false ||
                 strpos($current_uri, '/logout') !== false;
$is_logged_in = isset($_SESSION['user_id']);

if ($maintenance_mode && !$is_admin_page && !$is_logged_in) {
    $maintenance_message = $site_settings['maintenance_message'] ?? 'Site is under maintenance. Please check back later.';
    http_response_code(503);
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo t('maintenance_mode', 'Maintenance Mode'); ?> - <?php echo htmlspecialchars($site_settings['site_title'] ?? 'Artado Docs'); ?></title>
        <link rel="icon" href="<?php echo htmlspecialchars($site_settings['favicon_url'] ?? 'assets/img/favicon.svg'); ?>" type="image/svg+xml">
        <link rel="stylesheet" href="assets/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body data-theme="dark">
        <div style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 2rem; text-align: center;">
            <div>
                <div style="width: 100px; height: 100px; background: rgba(74, 95, 199, 0.1); border-radius: 24px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem;">
                    <img src="assets/img/logo.svg" alt="Artado" style="width: 70px; height: 70px;">
                </div>
                <h1 style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--text-main);"><?php echo t('maintenance_mode', 'Maintenance Mode'); ?></h1>
                <p style="color: var(--text-muted); font-size: 1.1rem; max-width: 500px; margin: 0 auto;"><?php echo htmlspecialchars($maintenance_message); ?></p>
                <?php if (!$is_logged_in): ?>
                <a href="login.php" class="btn btn-primary" style="margin-top: 2rem;">
                    <i class="fas fa-sign-in-alt"></i> <?php echo t('admin_login', 'Admin Login'); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Global data needed by all pages
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY display_order ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    // If tables don't exist, redirect to setup
    if (strpos($e->getMessage(), "doesn't exist") !== false && basename($_SERVER['PHP_SELF']) != 'setup.php') {
        header('Location: setup.php');
        exit;
    }
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="<?php echo htmlspecialchars($page_description ?? $site_settings['hero_desc'] ?? 'Artado Documentation'); ?>">
    <meta name="author" content="Artado Ecosystem">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
    <meta property="og:title" content="<?php echo $page_title ?? 'Documentation'; ?> - <?php echo htmlspecialchars($site_settings['site_title'] ?? 'Artado Docs'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description ?? $site_settings['hero_desc'] ?? 'Artado Documentation'); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($site_settings['favicon_url'] ?? 'assets/img/logo.svg'); ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="<?php echo $page_title ?? 'Documentation'; ?> - <?php echo htmlspecialchars($site_settings['site_title'] ?? 'Artado Docs'); ?>">
    <meta property="twitter:description" content="<?php echo htmlspecialchars($page_description ?? $site_settings['hero_desc'] ?? 'Artado Documentation'); ?>">

    <title><?php echo $page_title ?? 'Documentation'; ?> - <?php echo htmlspecialchars($site_settings['site_title'] ?? 'Artado Docs'); ?></title>
    <link rel="icon" href="<?php echo $path_prefix . htmlspecialchars($site_settings['favicon_url'] ?? 'assets/img/favicon.svg'); ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?php echo $path_prefix; ?>assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
</head>
<body data-theme="dark">
    <div class="app-container">
        <?php if (!isset($hide_sidebar) || !$hide_sidebar): ?>
        <button class="mobile-menu-toggle" onclick="toggleMobileMenu()" title="Menü">
            <i class="fas fa-bars"></i>
        </button>
        <?php if (isset($toc_data) && !empty($toc_data['toc'])): ?>
        <?php endif; ?>
        <div class="mobile-sidebar-overlay" onclick="toggleMobileMenu()"></div>
        <div class="mobile-toc-overlay" onclick="toggleMobileTOC()"></div>
        <aside class="sidebar">
            <div class="sidebar-logo" onclick="location.href='<?php echo $path_prefix; ?>index.php'" style="cursor:pointer;">
                <img src="<?php echo $path_prefix . htmlspecialchars($site_settings['logo_url'] ?? 'assets/img/logo.svg'); ?>" alt="Artado" style="width: 32px; height: 32px;">
                <span><?php echo htmlspecialchars($site_settings['site_title'] ?? 'Artado Docs'); ?></span>
            </div>
            
            <div class="search-box">
                <form action="<?php echo $path_prefix; ?>search.php" method="GET">
                    <input type="text" name="q" placeholder="<?php echo t('search_docs', 'Search docs...'); ?>" value="<?php echo $_GET['q'] ?? ''; ?>">
                </form>
            </div>

            <nav style="flex: 1; overflow-y: auto; overflow-x: hidden; min-height: 0; padding-right: 0.5rem; margin-right: -0.5rem; margin-bottom: 1rem;">
                <div class="nav-group">
                    <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> <?php echo t('home', 'Home'); ?>
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="admin.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user-shield"></i> <?php echo t('dashboard', 'Yönetim Paneli'); ?>
                    </a>
                    <a href="logout.php" class="nav-link" style="color:var(--danger);">
                        <i class="fas fa-sign-out-alt"></i> <?php echo t('logout', 'Çıkış'); ?>
                    </a>
                    <?php endif; ?>
                </div>

                <?php
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
                        <i class="fas fa-file-alt" style="font-size: 0.8rem; opacity: 0.7;"></i>
                        <?php echo htmlspecialchars($p['title']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </nav>

            <div style="margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--border); display: flex; flex-direction: column; gap: 0.75rem; flex-shrink: 0; background: var(--bg-sidebar);">
                <div class="language-switcher" style="display: flex; gap: 0.5rem;">
                    <button onclick="setLang('en')" class="lang-btn" style="flex: 1; padding: 0.6rem; text-align: center; border-radius: var(--radius-sm); border: 1px solid var(--border); text-decoration: none; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); transition: var(--transition); background: var(--bg-base); cursor: pointer;">
                        EN
                    </button>
                    <button onclick="setLang('tr')" class="lang-btn" style="flex: 1; padding: 0.6rem; text-align: center; border-radius: var(--radius-sm); border: 1px solid var(--border); text-decoration: none; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); transition: var(--transition); background: var(--bg-base); cursor: pointer;">
                        TR
                    </button>
                </div>
                <button id="theme-toggle" style="width: 100%; padding: 0.6rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-base); color: var(--text-main); cursor: pointer; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: var(--transition);">
                    <i class="fas fa-moon"></i> <span>Dark Mode</span>
                </button>
            </div>
        </aside>
        <?php endif; ?>
        
        <main class="main-content <?php echo (isset($hide_sidebar) && $hide_sidebar) ? 'full-width' : ''; ?>">
