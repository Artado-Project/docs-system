<?php
require_once 'includes/db.php';
require_once 'includes/lang.php';

// Redirect admin parameters to admin page
if (isset($_GET['tab']) || isset($_GET['msg']) || isset($_GET['edit_cat']) || isset($_GET['edit_page']) || isset($_GET['delete_cat']) || isset($_GET['delete_page']) || isset($_GET['delete_user'])) {
    header('Location: admin.php?' . http_build_query($_GET));
    exit;
}

$page_title = t('hero_title', 'The Future of');
$hide_sidebar = false; 
include 'includes/header.php';
?>

<div class="hero-section">
    <div class="hero-content-wrapper">
        <div class="hero-logo-box">
            <img src="assets/img/logo.svg" alt="Artado">
        </div>
        <div class="hero-text-content">
            <h1><?php echo htmlspecialchars($site_settings['hero_title'] ?? t('hero_title', 'Artado Development')); ?></h1>
            <p><?php echo htmlspecialchars($site_settings['hero_desc'] ?? t('site_hero_desc', 'Premium documentation and resources for building next-generation applications.')); ?></p>
        </div>
    </div>
    
    <div class="hero-actions">
        <a href="docs.php?slug=getting-started" class="btn btn-primary">
            <i class="fas fa-rocket"></i> <?php echo t('get_started', 'Başlayın'); ?>
        </a>
        <a href="#explore" class="btn btn-outline">
            <?php echo t('explore_categories', 'Kategorileri Keşfet'); ?> <i class="fas fa-chevron-down"></i>
        </a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?php echo count($categories); ?></div>
        <div class="stat-label"><?php echo t('modules', 'Modüller'); ?></div>
    </div>
    <div class="stat-card">
        <?php
        $pageCount = $pdo->query("SELECT COUNT(*) FROM pages")->fetchColumn();
        ?>
        <div class="stat-value"><?php echo $pageCount; ?></div>
        <div class="stat-label"><?php echo t('guides', 'Kılavuzlar'); ?></div>
    </div>
</div>

<div id="explore" class="explore-section">
    <h2 class="section-title"><?php echo t('explore_ecosystem', 'Ekosistemi Keşfet'); ?></h2>
    <div class="grid">
        <?php
        foreach ($categories as $cat):
        ?>
        <div class="card" onclick="location.href='docs.php?cat=<?php echo $cat['slug']; ?>'">
            <div class="card-icon">
                <i class="fas fa-<?php echo $cat['icon']; ?>"></i>
            </div>
            <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
            <p><?php echo htmlspecialchars($cat['description']); ?></p>
            <div class="card-footer">
                <?php echo t('learn_more', 'Daha fazla bilgi'); ?> <i class="fas fa-arrow-right"></i>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

























<footer style="margin-top: 6rem; padding: 4rem 0 2rem; border-top: 1px solid var(--border);">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 2rem;">
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 4rem; margin-bottom: 3rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 12px; font-weight: 800; font-size: 1.2rem; color: var(--text-main); margin-bottom: 1.5rem;">
                    <img src="assets/img/logo.svg" alt="Artado" style="width: 32px; height: 32px;"> Artado Docs
                </div>
                <p style="font-size: 0.95rem; line-height: 1.7; color: var(--text-muted); max-width: 400px;">
                    <?php echo t('footer_desc', 'The official documentation platform for the Artado developer ecosystem. Build beautiful, accessible, and high-performance apps.'); ?>
                </p>
            </div>
            <div>
                <h4 style="margin-bottom: 1.25rem; font-size: 1rem; font-weight: 700; color: var(--text-main);"><?php echo t('resources', 'Resources'); ?></h4>
                <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 0.75rem;">
                    <li><a href="#" style="font-size: 0.9rem; color: var(--text-muted); transition: var(--transition);"><?php echo t('hig', 'Human Interface Guidelines'); ?></a></li>
                    <li><a href="#" style="font-size: 0.9rem; color: var(--text-muted); transition: var(--transition);"><?php echo t('api_docs', 'API Documentation'); ?></a></li>
                    <li><a href="#" style="font-size: 0.9rem; color: var(--text-muted); transition: var(--transition);"><?php echo t('beginner_tutorials', 'Beginner Tutorials'); ?></a></li>
                </ul>
            </div>
            <div>
                <h4 style="margin-bottom: 1.25rem; font-size: 1rem; font-weight: 700; color: var(--text-main);"><?php echo t('support', 'Support'); ?></h4>
                <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 0.75rem;">
                    <li><a href="#" style="font-size: 0.9rem; color: var(--text-muted); transition: var(--transition);"><?php echo t('forum', 'Forum'); ?></a></li>
                    <li><a href="#" style="font-size: 0.9rem; color: var(--text-muted); transition: var(--transition);"><?php echo t('issue_tracker', 'Issue Tracker'); ?></a></li>
                    <li><a href="login.php" style="font-size: 0.9rem; color: var(--text-muted); transition: var(--transition);"><?php echo t('admin', 'Admin'); ?></a></li>
                </ul>
            </div>
        </div>

    </div>
</footer> 

<?php include 'includes/footer.php'; ?>
