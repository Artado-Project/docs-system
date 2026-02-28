<?php 
require_once 'includes/db.php';
require_once 'includes/lang.php';
require_once 'includes/utils.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Handle GET deletions (must be before header.php)
if (isset($_GET['delete_user']) && $_GET['delete_user'] != $_SESSION['user_id']) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_GET['delete_user']]);
    header("Location: admin.php?tab=users&msg=" . urlencode(t('user_deleted', 'User deleted.')));
    exit;
}
if (isset($_GET['delete_cat'])) {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$_GET['delete_cat']]);
    header("Location: admin.php?tab=content&msg=" . urlencode(t('category_deleted', 'Category deleted successfully!')));
    exit;
}
if (isset($_GET['delete_page'])) {
    $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
    $stmt->execute([$_GET['delete_page']]);
    header("Location: admin.php?tab=content&msg=" . urlencode(t('page_deleted', 'Page deleted successfully!')));
    exit;
}

// Handle POST requests (must be before header.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add Category
    if (isset($_POST['add_category'])) {
        $name = $_POST['name'];
        $slug = !empty($_POST['slug']) ? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['slug']))) : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $slug_check = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
        $slug_check->execute([$slug]);
        if ($slug_check->fetch()) {
            $slug = $slug . '-' . time();
        }
        $desc = $_POST['description'];
        $icon = $_POST['icon'] ?: 'book';
        $display_order = intval($_POST['display_order'] ?? 0);
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, icon, display_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $desc, $icon, $display_order]);
            header("Location: admin.php?tab=content&msg=" . urlencode(t('category_created', 'Category created!')));
            exit;
        } catch (PDOException $e) { 
            $error = t('error_adding_category', 'Error adding category.') . ' ' . $e->getMessage(); 
        }
    }
    // Edit Category
    elseif (isset($_POST['edit_category'])) {
        $id = $_POST['cat_id'];
        $name = $_POST['name'];
        $slug = !empty($_POST['slug']) ? createSlug($_POST['slug']) : createSlug($name);
        $slug_check = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
        $slug_check->execute([$slug, $id]);
        if ($slug_check->fetch()) {
            $slug = $slug . '-' . $id;
        }
        $desc = $_POST['description'];
        $icon = $_POST['icon'] ?: 'book';
        $display_order = intval($_POST['display_order'] ?? 0);
        try {
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, icon = ?, display_order = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $desc, $icon, $display_order, $id]);
            header("Location: admin.php?tab=content&msg=" . urlencode(t('category_updated', 'Category updated!')));
            exit;
        } catch (PDOException $e) {
            $error = t('error_adding_category', 'Error updating category.') . ' ' . $e->getMessage();
        }
    }
    // Add Page
    elseif (isset($_POST['add_page'])) {
        $cat_id = $_POST['category_id'];
        $title = $_POST['title'];
        $slug = !empty($_POST['slug']) ? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['slug']))) : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug_check = $pdo->prepare("SELECT id FROM pages WHERE slug = ?");
        $slug_check->execute([$slug]);
        if ($slug_check->fetch()) {
            $slug = $slug . '-' . time();
        }
        $content = $_POST['content'];
        $display_order = intval($_POST['display_order'] ?? 0);
        try {
            $stmt = $pdo->prepare("INSERT INTO pages (category_id, title, slug, content, display_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$cat_id, $title, $slug, $content, $display_order]);
            header("Location: admin.php?tab=content&msg=" . urlencode(t('page_created', 'Page created!')));
            exit;
        } catch (PDOException $e) { 
            $error = t('error_adding_page', 'Error adding page.') . ' ' . $e->getMessage(); 
        }
    }
    // Edit Page
    elseif (isset($_POST['edit_page'])) {
        $id = $_POST['page_id'];
        $cat_id = $_POST['category_id'];
        $title = $_POST['title'];
        $slug = !empty($_POST['slug']) ? createSlug($_POST['slug']) : createSlug($title);
        $slug_check = $pdo->prepare("SELECT id FROM pages WHERE slug = ? AND id != ?");
        $slug_check->execute([$slug, $id]);
        if ($slug_check->fetch()) {
            $slug = $slug . '-' . $id;
        }
        $content = $_POST['content'];
        $display_order = intval($_POST['display_order'] ?? 0);
        try {
            $stmt = $pdo->prepare("UPDATE pages SET category_id = ?, title = ?, slug = ?, content = ?, display_order = ? WHERE id = ?");
            $stmt->execute([$cat_id, $title, $slug, $content, $display_order, $id]);
            header("Location: admin.php?tab=content&msg=" . urlencode(t('page_updated', 'Page updated!')));
            exit;
        } catch (PDOException $e) {
            $error = t('error_adding_page', 'Error updating page.') . ' ' . $e->getMessage();
        }
    }
    // Update Settings
    elseif (isset($_POST['update_settings'])) {
        $settings = $_POST['settings'];
        
        // Checkbox handling: if not in POST, it means it was unchecked
        if (!isset($settings['maintenance_mode'])) {
            $settings['maintenance_mode'] = '0';
        }
        
        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);
        }
        header("Location: admin.php?tab=settings&msg=" . urlencode(t('settings_updated', 'Settings updated successfully!')));
        exit;
    }
    // Change Own Password
    elseif (isset($_POST['change_own_password'])) {
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if ($user && password_verify($current_pass, $user['password'])) {
            $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$new_hash, $_SESSION['user_id']]);
            header("Location: admin.php?tab=settings&msg=" . urlencode(t('password_changed', 'Password changed successfully!')));
            exit;
        } else {
            $error = t('current_password_incorrect', 'Current password is incorrect.');
        }
    }
    // Add User
    elseif (isset($_POST['add_user'])) {
        $user = $_POST['username'];
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'] ?? 'admin';
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$user, $pass, $role]);
            header("Location: admin.php?tab=users&msg=" . urlencode(t('user_added', 'User added!')));
            exit;
        } catch (PDOException $e) {
            $error = t('username_exists', 'Username already exists.');
        }
    }
    // Change User Password
    elseif (isset($_POST['change_password'])) {
        $user_id = $_POST['user_id'];
        $new_pass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$new_pass, $user_id]);
        header("Location: admin.php?tab=users&msg=" . urlencode(t('password_changed', 'Password changed successfully!')));
        exit;
    }
}

// Get message from URL if redirect happened
if (isset($_GET['msg'])) {
    $message = urldecode($_GET['msg']);
}


$page_title = t('admin_dashboard', 'Yönetim Paneli');
include 'includes/header.php';

// Fetch Specific Data for Editing
$editing_cat = null;
if (isset($_GET['edit_cat'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$_GET['edit_cat']]);
    $editing_cat = $stmt->fetch();
}

$editing_page = null;
if (isset($_GET['edit_page'])) {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->execute([$_GET['edit_page']]);
    $editing_page = $stmt->fetch();
}

// Fetch Everything
$cats = $pdo->query("SELECT * FROM categories ORDER BY display_order ASC")->fetchAll();
$pages = $pdo->query("SELECT p.*, c.name as category_name FROM pages p JOIN categories c ON p.category_id = c.id ORDER BY p.display_order ASC, p.created_at DESC")->fetchAll();
$all_users = $pdo->query("SELECT id, username, role, created_at FROM users")->fetchAll();
$site_settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);

// Get statistics
$stats = [
    'categories' => count($cats),
    'pages' => count($pages),
    'users' => count($all_users),
    'recent_pages' => array_slice($pages, 0, 5)
];

// Handle own password change
if (isset($_POST['change_own_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($current_pass, $user['password'])) {
        $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$new_hash, $_SESSION['user_id']]);
        $message = t('password_changed', 'Password changed successfully!');
    } else {
        $error = t('current_password_incorrect', 'Current password is incorrect.');
    }
}

$active_tab = $_GET['tab'] ?? 'dashboard';
?>

<!-- Select2 & SimpleMDE -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">

<?php 
// Convert hex to rgb for inline styles if needed, but we have --primary-rgb
?>
<div class="admin-header" style="background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.1) 0%, rgba(var(--primary-rgb), 0.05) 100%); padding: 2.5rem; border-radius: var(--radius-lg); margin-bottom: 3rem; border: 1px solid var(--border);">
    <div>
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
            <div style="width: 50px; height: 50px; background: rgba(var(--primary-rgb), 0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-cog" style="font-size: 1.5rem; color: var(--primary);"></i>
            </div>
            <div>
                <h1 style="margin:0; font-size: 2rem; background: linear-gradient(135deg, var(--text-main) 0%, var(--primary) 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;"><?php echo t('management_console', 'Management Console'); ?></h1>
                <p style="margin: 0.25rem 0 0; color: var(--text-muted); font-size: 0.9rem;"><?php echo $_SESSION['username']; ?> - <?php echo t('dashboard', 'Dashboard'); ?></p>
            </div>
        </div>
        <div style="margin-top: 2rem; text-align: center;">
            <a href="index.php" style="font-size: 0.9rem; color: var(--text-muted);"><i class="fas fa-arrow-left"></i> <?php echo t('back_to_homepage', 'Ana Sayfaya Dön'); ?></a>
        </div>
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <a href="?tab=dashboard" class="btn <?php echo $active_tab == 'dashboard' ? 'btn-primary' : 'btn-outline'; ?>" style="padding: 0.75rem 1.5rem;">
                <i class="fas fa-chart-line"></i> <?php echo t('dashboard', 'Dashboard'); ?>
            </a>
            <a href="?tab=content" class="btn <?php echo $active_tab == 'content' ? 'btn-primary' : 'btn-outline'; ?>" style="padding: 0.75rem 1.5rem;">
                <i class="fas fa-file-alt"></i> <?php echo t('content', 'Content'); ?>
            </a>
            <a href="?tab=users" class="btn <?php echo $active_tab == 'users' ? 'btn-primary' : 'btn-outline'; ?>" style="padding: 0.75rem 1.5rem;">
                <i class="fas fa-users"></i> <?php echo t('users', 'Users'); ?>
            </a>
            <a href="?tab=settings" class="btn <?php echo $active_tab == 'settings' ? 'btn-primary' : 'btn-outline'; ?>" style="padding: 0.75rem 1.5rem;">
                <i class="fas fa-cog"></i> <?php echo t('settings', 'Settings'); ?>
            </a>
        </div>
    </div>
    <div style="text-align: right;">
        <span class="badge badge-primary"><?php echo $_SESSION['username']; ?></span>
        <a href="logout.php" class="btn btn-outline" style="color:var(--danger); border-color:rgba(224,27,36,0.2); margin-left: 0.5rem;"><i class="fas fa-sign-out-alt"></i></a>
    </div>
</div>

<?php if ($message): ?>
    <div style="background: rgba(46, 194, 126, 0.1); color: var(--success); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 2rem; border-left: 4px solid var(--success);">
        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div style="background: rgba(224, 27, 36, 0.1); color: var(--danger); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 2rem; border-left: 4px solid var(--danger);">
        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<?php if ($active_tab == 'dashboard'): ?>
    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="admin-card" style="padding: 2rem; text-align: center;">
            <div style="width: 60px; height: 60px; background: rgba(74, 95, 199, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <i class="fas fa-folder" style="font-size: 1.5rem; color: var(--primary);"></i>
            </div>
            <div style="font-size: 2.5rem; font-weight: 800; color: var(--primary); margin-bottom: 0.5rem;"><?php echo $stats['categories']; ?></div>
            <div style="color: var(--text-muted); font-weight: 600;"><?php echo t('categories', 'Categories'); ?></div>
        </div>
        <div class="admin-card" style="padding: 2rem; text-align: center;">
            <div style="width: 60px; height: 60px; background: rgba(46, 194, 126, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <i class="fas fa-file-alt" style="font-size: 1.5rem; color: var(--success);"></i>
            </div>
            <div style="font-size: 2.5rem; font-weight: 800; color: var(--success); margin-bottom: 0.5rem;"><?php echo $stats['pages']; ?></div>
            <div style="color: var(--text-muted); font-weight: 600;"><?php echo t('pages', 'Pages'); ?></div>
        </div>
        <div class="admin-card" style="padding: 2rem; text-align: center;">
            <div style="width: 60px; height: 60px; background: rgba(246, 163, 46, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <i class="fas fa-users" style="font-size: 1.5rem; color: var(--warning);"></i>
            </div>
            <div style="font-size: 2.5rem; font-weight: 800; color: var(--warning); margin-bottom: 0.5rem;"><?php echo $stats['users']; ?></div>
            <div style="color: var(--text-muted); font-weight: 600;"><?php echo t('users', 'Users'); ?></div>
        </div>
    </div>

    <div class="grid" style="grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
        <div class="admin-card">
            <div style="padding: 1.5rem; border-bottom: 1px solid var(--border);">
                <h3 style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-clock" style="color: var(--primary);"></i> <?php echo t('recent_pages', 'Recent Pages'); ?>
                </h3>
            </div>
            <div style="padding: 1rem;">
                <?php if (count($stats['recent_pages']) > 0): ?>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <?php foreach ($stats['recent_pages'] as $page): ?>
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; background: var(--bg-base); border-radius: var(--radius-sm);">
                                <div>
                                    <div style="font-weight: 600; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($page['title']); ?></div>
                                    <div style="font-size: 0.85rem; color: var(--text-muted);">
                                        <span class="badge badge-primary" style="font-size: 0.75rem;"><?php echo htmlspecialchars($page['category_name']); ?></span>
                                        <span style="margin-left: 0.5rem;"><?php echo date('M j, Y', strtotime($page['updated_at'])); ?></span>
                                    </div>
                                </div>
                                <a href="docs.php?slug=<?php echo $page['slug']; ?>" class="btn btn-outline" style="padding: 0.4rem;" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-muted); text-align: center; padding: 2rem;"><?php echo t('no_pages', 'No pages yet.'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="admin-card">
            <div style="padding: 1.5rem; border-bottom: 1px solid var(--border);">
                <h3 style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-bolt" style="color: var(--primary);"></i> <?php echo t('quick_actions', 'Quick Actions'); ?>
                </h3>
            </div>
            <div style="padding: 1rem; display: flex; flex-direction: column; gap: 0.5rem;">
                <a href="?tab=content" class="btn btn-outline" style="justify-content: flex-start;">
                    <i class="fas fa-plus"></i> <?php echo t('add_page', 'Create Page'); ?>
                </a>
                <a href="?tab=content" class="btn btn-outline" style="justify-content: flex-start;">
                    <i class="fas fa-folder-plus"></i> <?php echo t('add_category', 'Add Category'); ?>
                </a>
                <a href="?tab=users" class="btn btn-outline" style="justify-content: flex-start;">
                    <i class="fas fa-user-plus"></i> <?php echo t('add_user', 'Add User'); ?>
                </a>
                <a href="index.php" class="btn btn-outline" style="justify-content: flex-start;" target="_blank">
                    <i class="fas fa-home"></i> <?php echo t('view_site', 'View Site'); ?>
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($active_tab == 'content'): ?>
    <div class="grid" style="grid-template-columns: 1fr 1fr; margin-bottom: 4rem;">
        <section class="admin-card" style="padding: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-<?php echo $editing_cat ? 'edit' : 'folder-plus'; ?>" style="color: var(--primary);"></i> 
                <?php echo $editing_cat ? t('edit_category', 'Edit Category') : t('add_category', 'Add Category'); ?>
            </h2>
            <form method="POST">
                <input type="hidden" name="<?php echo $editing_cat ? 'edit_category' : 'add_category'; ?>" value="1">
                <?php if ($editing_cat): ?><input type="hidden" name="cat_id" value="<?php echo $editing_cat['id']; ?>"><?php endif; ?>
                <div class="form-group"><label><?php echo t('name', 'Name'); ?></label><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($editing_cat['name'] ?? ''); ?>" required></div>
                <div class="form-group">
                    <label><?php echo t('slug', 'Slug'); ?> <small style="color: var(--text-muted);">(<?php echo t('optional', 'optional'); ?>)</small></label>
                    <input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars($editing_cat['slug'] ?? ''); ?>" placeholder="<?php echo t('auto_generated', 'Auto-generated from name'); ?>">
                    <small style="color: var(--text-muted); font-size: 0.85rem;"><?php echo t('slug_hint', 'Leave empty to auto-generate from name'); ?></small>
                </div>
                <div class="form-group"><label><?php echo t('icon', 'Icon (FA)'); ?></label><input type="text" name="icon" class="form-control" value="<?php echo htmlspecialchars($editing_cat['icon'] ?? ''); ?>" placeholder="rocket"></div>
                <div class="form-group"><label><?php echo t('description', 'Description'); ?></label><textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($editing_cat['description'] ?? ''); ?></textarea></div>
                <div class="form-group"><label><?php echo t('display_order', 'Display Order'); ?></label><input type="number" name="display_order" class="form-control" value="<?php echo $editing_cat['display_order'] ?? 0; ?>" min="0"></div>
                <button type="submit" class="btn btn-primary" style="width:100%;">
                    <i class="fas fa-<?php echo $editing_cat ? 'save' : 'plus'; ?>"></i> <?php echo $editing_cat ? t('update', 'Update') : t('create', 'Create'); ?>
                </button>
                <?php if ($editing_cat): ?><a href="index.php?tab=content" class="btn btn-outline" style="width:100%; margin-top:0.5rem;"><i class="fas fa-times"></i> <?php echo t('cancel', 'Cancel'); ?></a><?php endif; ?>
            </form>
        </section>

        <section class="admin-card" style="padding: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-<?php echo $editing_page ? 'edit' : 'file-medical'; ?>" style="color: var(--primary);"></i> 
                <?php echo $editing_page ? t('edit_page', 'Edit Page') : t('add_page', 'Create Page'); ?>
            </h2>
            <form method="POST" id="pageForm">
                <input type="hidden" name="<?php echo $editing_page ? 'edit_page' : 'add_page'; ?>" value="1">
                <?php if ($editing_page): ?><input type="hidden" name="page_id" value="<?php echo $editing_page['id']; ?>"><?php endif; ?>
                <div class="form-group">
                    <label><?php echo t('category', 'Category'); ?></label>
                    <select name="category_id" class="form-control" required>
                        <?php foreach ($cats as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo ($editing_page && $editing_page['category_id'] == $c['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label><?php echo t('title', 'Başlık'); ?></label><input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($editing_page['title'] ?? ''); ?>" required></div>
                <div class="form-group">
                    <label><?php echo t('slug', 'Slug'); ?> <small style="color: var(--text-muted);">(<?php echo t('optional', 'isteğe bağlı'); ?>)</small></label>
                    <input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars($editing_page['slug'] ?? ''); ?>" placeholder="<?php echo t('auto_generated', 'Başlıktan otomatik oluşturulur'); ?>">
                    <small style="color: var(--text-muted); font-size: 0.85rem;"><?php echo t('slug_hint', 'Boş bırakırsanız başlıktan otomatik oluşturulur'); ?></small>
                </div>
                <div class="form-group"><label><?php echo t('display_order', 'Display Order'); ?></label><input type="number" name="display_order" class="form-control" value="<?php echo $editing_page['display_order'] ?? 0; ?>" min="0"></div>
                <div class="form-group">
                    <label><?php echo t('content', 'Content'); ?> (Markdown)</label>
                    <textarea name="content" id="markdown-editor" class="form-control" rows="15"><?php echo htmlspecialchars($editing_page['content'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">
                    <i class="fas fa-<?php echo $editing_page ? 'save' : 'paper-plane'; ?>"></i> <?php echo $editing_page ? t('update', 'Update') : t('publish', 'Publish'); ?>
                </button>
                <?php if ($editing_page): ?><a href="admin.php?tab=content" class="btn btn-outline" style="width:100%; margin-top:0.5rem;"><i class="fas fa-times"></i> <?php echo t('cancel', 'Cancel'); ?></a><?php endif; ?>
            </form>
        </section>
    </div>

    <div class="admin-card">
        <table class="admin-table">
            <thead><tr><th><?php echo t('title', 'Title'); ?></th><th><?php echo t('category', 'Category'); ?></th><th><?php echo t('actions', 'Actions'); ?></th></tr></thead>
            <tbody>
                <?php foreach ($pages as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['title']); ?></td>
                    <td><span class="badge badge-primary"><?php echo htmlspecialchars($p['category_name']); ?></span></td>
                    <td>
                        <a href="docs.php?slug=<?php echo $p['slug']; ?>" class="btn btn-outline" style="padding:0.4rem;" title="<?php echo t('view', 'View'); ?>" target="_blank"><i class="fas fa-eye"></i></a>
                        <a href="?tab=content&edit_page=<?php echo $p['id']; ?>" class="btn btn-outline" style="padding:0.4rem; color:var(--primary);" title="<?php echo t('edit', 'Edit'); ?>"><i class="fas fa-edit"></i></a>
                        <a href="?delete_page=<?php echo $p['id']; ?>" class="btn btn-outline" style="padding:0.4rem; color:var(--danger);" onclick="return confirm('<?php echo t('delete_confirm', 'Delete?'); ?>')" title="<?php echo t('delete', 'Delete'); ?>"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="admin-card" style="margin-top: 2rem;">
        <div style="padding:1.5rem; border-bottom:1px solid var(--border);">
            <h3 style="margin:0; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-folder" style="color: var(--primary);"></i> <?php echo t('managed_categories', 'Managed Categories'); ?>
            </h3>
        </div>
        <table class="admin-table">
            <thead><tr><th><?php echo t('icon', 'Icon'); ?></th><th><?php echo t('name', 'Name'); ?></th><th><?php echo t('slug', 'Slug'); ?></th><th><?php echo t('actions', 'Actions'); ?></th></tr></thead>
            <tbody>
                <?php foreach ($cats as $c): ?>
                <tr>
                    <td><i class="fas fa-<?php echo $c['icon']; ?>"></i></td>
                    <td><?php echo htmlspecialchars($c['name']); ?></td>
                    <td><code><?php echo $c['slug']; ?></code></td>
                    <td>
                        <a href="?tab=content&edit_cat=<?php echo $c['id']; ?>" class="btn btn-outline" style="padding:0.4rem; color:var(--primary);" title="<?php echo t('edit', 'Edit'); ?>"><i class="fas fa-edit"></i></a>
                        <a href="?delete_cat=<?php echo $c['id']; ?>" class="btn btn-outline" style="padding:0.4rem; color:var(--danger);" onclick="return confirm('<?php echo t('delete_cat_confirm', 'Delete category and all its pages?'); ?>')" title="<?php echo t('delete', 'Delete'); ?>"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php if ($active_tab == 'users'): ?>
    <div class="grid" style="grid-template-columns: 1fr 2fr;">
        <section class="admin-card" style="padding: 2rem;">
            <h2 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-user-plus" style="color: var(--primary);"></i> <?php echo t('add_user', 'Add User'); ?>
            </h2>
            <form method="POST">
                <input type="hidden" name="add_user" value="1">
                <div class="form-group"><label><?php echo t('username', 'Username'); ?></label><input type="text" name="username" class="form-control" required></div>
                <div class="form-group"><label><?php echo t('password', 'Password'); ?></label><input type="password" name="password" class="form-control" required></div>
                <div class="form-group">
                    <label><?php echo t('role', 'Role'); ?></label>
                    <select name="role" class="form-control">
                        <option value="admin">Admin</option>
                        <option value="editor">Editor</option>
                        <option value="viewer">Viewer</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">
                    <i class="fas fa-plus"></i> <?php echo t('add_user', 'Add User'); ?>
                </button>
            </form>
        </section>
        <section class="admin-card">
            <table class="admin-table">
                <thead><tr><th><?php echo t('username', 'Username'); ?></th><th><?php echo t('role', 'Role'); ?></th><th><?php echo t('created', 'Created'); ?></th><th><?php echo t('actions', 'Actions'); ?></th></tr></thead>
                <tbody>
                    <?php foreach ($all_users as $u): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                        <td><span class="badge badge-primary"><?php echo htmlspecialchars($u['role'] ?? 'admin'); ?></span></td>
                        <td><?php echo $u['created_at']; ?></td>
                        <td>
                            <button onclick="showChangePassword(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['username']); ?>')" class="btn btn-outline" style="padding:0.4rem; color:var(--primary);" title="<?php echo t('change_password', 'Change Password'); ?>">
                                <i class="fas fa-key"></i>
                            </button>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <a href="?tab=users&delete_user=<?php echo $u['id']; ?>" class="btn btn-outline" style="padding:0.4rem; color:var(--danger);" onclick="return confirm('<?php echo t('delete_confirm', 'Delete?'); ?>')" title="<?php echo t('delete', 'Delete'); ?>">
                                    <i class="fas fa-user-minus"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        
        <!-- Change Password Modal -->
        <div id="passwordModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
            <div class="admin-card" style="max-width: 400px; margin: 2rem; position: relative;">
                <button onclick="closePasswordModal()" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer;">&times;</button>
                <h3 style="margin-bottom: 1.5rem;" id="passwordModalTitle"><?php echo t('change_password', 'Change Password'); ?></h3>
                <form method="POST">
                    <input type="hidden" name="change_password" value="1">
                    <input type="hidden" name="user_id" id="passwordUserId">
                    <div class="form-group">
                        <label><?php echo t('new_password', 'New Password'); ?></label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">
                        <i class="fas fa-save"></i> <?php echo t('change_password', 'Change Password'); ?>
                    </button>
                    <button type="button" onclick="closePasswordModal()" class="btn btn-outline" style="width:100%; margin-top:0.5rem;">
                        <?php echo t('cancel', 'Cancel'); ?>
                    </button>
                </form>
            </div>
        </div>
        
        <script>
        function showChangePassword(userId, username) {
            document.getElementById('passwordUserId').value = userId;
            document.getElementById('passwordModalTitle').textContent = '<?php echo t('change_password', 'Change Password'); ?>: ' + username;
            document.getElementById('passwordModal').style.display = 'flex';
        }
        function closePasswordModal() {
            document.getElementById('passwordModal').style.display = 'none';
        }
        </script>
    </div>
<?php endif; ?>

<?php if ($active_tab == 'settings'): ?>
    <section class="admin-card" style="padding: 2.5rem; max-width: 800px;">
        <h2 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas fa-cog" style="color: var(--primary);"></i> <?php echo t('site_configuration', 'Site Configuration'); ?>
        </h2>
        <form method="POST">
            <input type="hidden" name="update_settings" value="1">
            <div class="form-group">
                <label><?php echo t('site_title', 'Site Title'); ?></label>
                <input type="text" name="settings[site_title]" class="form-control" value="<?php echo htmlspecialchars($site_settings['site_title'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label><?php echo t('hero_title_setting', 'Hero Title'); ?></label>
                <input type="text" name="settings[hero_title]" class="form-control" value="<?php echo htmlspecialchars($site_settings['hero_title'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label><?php echo t('hero_description', 'Hero Description'); ?></label>
                <textarea name="settings[hero_desc]" class="form-control" rows="3"><?php echo htmlspecialchars($site_settings['hero_desc'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label><?php echo t('logo_url', 'Logo URL'); ?></label>
                <input type="text" name="settings[logo_url]" class="form-control" value="<?php echo htmlspecialchars($site_settings['logo_url'] ?? 'assets/img/logo.svg'); ?>">
            </div>
            <div class="form-group">
                <label><?php echo t('primary_color', 'Ana Renk'); ?></label>
                <input type="color" name="settings[primary_color]" class="form-control" value="<?php echo htmlspecialchars($site_settings['primary_color'] ?? '#6366f1'); ?>" style="height: 50px; padding: 5px;">
            </div>
            <div class="form-group">
                <label><?php echo t('github_url', 'GitHub URL'); ?></label>
                <input type="text" name="settings[github_url]" class="form-control" value="<?php echo htmlspecialchars($site_settings['github_url'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label><?php echo t('twitter_url', 'Twitter URL'); ?></label>
                <input type="text" name="settings[twitter_url]" class="form-control" value="<?php echo htmlspecialchars($site_settings['twitter_url'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label><?php echo t('footer_text', 'Alt Bilgi (Footer) Metni'); ?></label>
                <input type="text" name="settings[footer_text]" class="form-control" value="<?php echo htmlspecialchars($site_settings['footer_text'] ?? 'Artado Ecosystem'); ?>">
            </div>
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="settings[maintenance_mode]" value="1" <?php echo ($site_settings['maintenance_mode'] ?? '0') == '1' ? 'checked' : ''; ?> style="width: auto;">
                    <?php echo t('maintenance_mode', 'Maintenance Mode'); ?>
                </label>
                <small style="color: var(--text-muted); font-size: 0.85rem;"><?php echo t('maintenance_mode_desc', 'When enabled, only admins can access the site.'); ?></small>
            </div>
            <div class="form-group">
                <label><?php echo t('maintenance_message', 'Maintenance Message'); ?></label>
                <textarea name="settings[maintenance_message]" class="form-control" rows="3"><?php echo htmlspecialchars($site_settings['maintenance_message'] ?? 'Site is under maintenance. Please check back later.'); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?php echo t('save_settings', 'Save Settings'); ?>
            </button>
        </form>
    </section>

    <section class="admin-card" style="padding: 2.5rem; max-width: 800px; margin-top: 2rem;">
        <h2 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas fa-key" style="color: var(--primary);"></i> <?php echo t('change_my_password', 'Change My Password'); ?>
        </h2>
        <form method="POST">
            <input type="hidden" name="change_own_password" value="1">
            <div class="form-group">
                <label><?php echo t('current_password', 'Current Password'); ?></label>
                <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label><?php echo t('new_password', 'New Password'); ?></label>
                <input type="password" name="new_password" class="form-control" required minlength="6">
                <small style="color: var(--text-muted); font-size: 0.85rem;"><?php echo t('password_min_length', 'Minimum 6 characters'); ?></small>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?php echo t('update', 'Güncelle'); ?>
            </button>
        </form>
    </section>
<?php endif; ?>

<!-- SimpleMDE scripts -->
<script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>
<script>
    <?php if ($active_tab == 'content'): ?>
    var element = document.getElementById("markdown-editor");
    if (element) {
        var simplemde = new SimpleMDE({ 
            element: element,
            spellChecker: false,
            autosave: {
                enabled: true,
                uniqueId: "admin-editor-<?php echo $editing_page['id'] ?? 'new'; ?>",
                delay: 1000,
            },
            promptURLs: true,
            placeholder: "İçeriği Markdown formatında yazın...",
            toolbar: [
                "bold", "italic", "heading", "|",
                "code", "quote", "unordered-list", "ordered-list", "|",
                "link", "image", "table", "|",
                "preview", "side-by-side", "fullscreen", "|",
                {
                    name: "guide",
                    action: "https://simplemde.com/markdown-guide",
                    className: "fa fa-question-circle",
                    title: "Markdown Guide",
                },
            ],
        });

        // Right-click formatting menu
        const editorWrapper = document.querySelector('.CodeMirror');
        const contextMenu = document.createElement('div');
        contextMenu.className = 'admin-context-menu';
        contextMenu.style.cssText = `
            display:none; 
            position:fixed; 
            z-index:10000; 
            background:var(--bg-card); 
            border:1px solid var(--border); 
            border-radius:var(--radius-md); 
            box-shadow:var(--shadow-xl); 
            padding:0.75rem; 
            min-width:200px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        `;
        document.body.appendChild(contextMenu);

        const menuItems = [
            { label: 'Kalın', icon: 'bold', action: () => simplemde.toggleBold() },
            { label: 'İtalik', icon: 'italic', action: () => simplemde.toggleItalic() },
            { label: 'Kod Bloğu', icon: 'code', action: () => simplemde.toggleCodeBlock() },
            { label: 'Link', icon: 'link', action: () => simplemde.drawLink() },
            { label: 'Resim', icon: 'image', action: () => simplemde.drawImage() },
            { label: 'Liste', icon: 'list-ul', action: () => simplemde.toggleUnorderedList() },
            { label: 'H2 Başlık', icon: 'heading', action: () => simplemde.toggleHeading2() },
            { label: 'H3 Başlık', icon: 'heading', action: () => simplemde.toggleHeading3() },
            { label: 'Yatay Çizgi', icon: 'minus', action: () => simplemde.drawHorizontalRule() }
        ];

        menuItems.forEach(item => {
            const div = document.createElement('div');
            div.style.cssText = 'padding:0.75rem 1rem; cursor:pointer; display:flex; align-items:center; gap:1rem; border-radius:10px; font-size:0.95rem; transition:var(--transition-fast); font-weight:500;';
            div.innerHTML = `<i class="fas fa-${item.icon}" style="width:20px; color:var(--primary); font-size:1rem;"></i> <span>${item.label}</span>`;
            div.onmouseover = () => {
                div.style.background = 'var(--bg-alt)';
                div.style.color = 'var(--primary)';
            };
            div.onmouseout = () => {
                div.style.background = 'transparent';
                div.style.color = 'inherit';
            };
            div.onclick = (e) => {
                e.stopPropagation();
                item.action();
                contextMenu.style.display = 'none';
            };
            contextMenu.appendChild(div);
        });

        editorWrapper.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            
            // Adjust position for mobile/small screens
            let left = e.clientX;
            let top = e.clientY;
            
            const menuWidth = 200;
            const menuHeight = 400; // rough estimate
            
            if (window.innerWidth < 768) {
                // Center on mobile
                left = (window.innerWidth - menuWidth) / 2;
                top = (window.innerHeight - menuHeight) / 2;
                if (top < 10) top = 10;
            } else {
                // Ensure menu stays within viewport
                if (left + menuWidth > window.innerWidth) left -= menuWidth;
                if (top + menuHeight > window.innerHeight) top -= menuHeight;
            }

            contextMenu.style.top = `${top}px`;
            contextMenu.style.left = `${left}px`;
            contextMenu.style.display = 'block';
        });

        document.addEventListener('click', () => {
            contextMenu.style.display = 'none';
        });
    }
    <?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>
