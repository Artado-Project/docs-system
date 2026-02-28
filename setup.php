<?php
require_once 'includes/db.php';
require_once 'includes/lang.php';

$page_title = t('database_setup', 'Database Setup');
$hide_sidebar = true;
include 'includes/header.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup'])) {
    try {
        // Read and parse schema.sql
        $sql = file_get_contents('schema.sql');
        
        // Remove USE statement as we're already connected to the database
        $sql = preg_replace('/USE\s+\w+;/i', '', $sql);
        
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        
        // Better SQL parsing - handle strings with semicolons inside
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        
        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            $prev = ($i > 0) ? $sql[$i-1] : '';
            
            if (!$inString && ($char === '"' || $char === "'")) {
                $inString = true;
                $stringChar = $char;
                $current .= $char;
            } elseif ($inString && $char === $stringChar && $prev !== '\\') {
                $inString = false;
                $current .= $char;
            } elseif (!$inString && $char === ';') {
                $stmt = trim($current);
                if (!empty($stmt)) {
                    $statements[] = $stmt;
                }
                $current = '';
            } else {
                $current .= $char;
            }
        }
        
        // Add last statement if exists
        $stmt = trim($current);
        if (!empty($stmt)) {
            $statements[] = $stmt;
        }
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    // Ignore errors for existing tables/data
                    if (strpos($e->getMessage(), 'already exists') === false && 
                        strpos($e->getMessage(), 'Duplicate entry') === false &&
                        strpos($e->getMessage(), 'Duplicate key') === false) {
                        throw $e;
                    }
                }
            }
        }
        
        // Ensure admin user exists with correct password
        $username = 'admin';
        $password = 'Semih+8589';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Delete existing admin to be sure
        try {
            $pdo->prepare("DELETE FROM users WHERE username = ?")->execute([$username]);
        } catch (PDOException $e) {
            // Table might not exist yet, continue
        }
        
        // Insert fresh admin
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin') ON DUPLICATE KEY UPDATE password = ?");
        $stmt->execute([$username, $hash, $hash]);

        $message = t('setup_success', 'Setup Successful! Database tables created.');
    } catch (PDOException $e) {
        $error = "Setup failed: " . $e->getMessage();
    }
}

// Check if tables exist
$tables_exist = false;
try {
    $pdo->query("SELECT 1 FROM categories LIMIT 1");
    $tables_exist = true;
} catch (PDOException $e) {
    $tables_exist = false;
}
?>

<div style="max-width: 600px; margin: 5rem auto; padding: 2.5rem; background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); box-shadow: var(--shadow-lg);">
    <div style="text-align: center; margin-bottom: 2rem;">
        <div style="width: 100px; height: 100px; background: rgba(74, 95, 199, 0.1); border-radius: 24px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
            <img src="assets/img/logo.svg" alt="Artado" style="width: 70px; height: 70px;">
        </div>
        <h1 style="margin:0; font-size: 1.75rem;"><?php echo t('database_setup', 'Database Setup'); ?></h1>
        <p style="color: var(--text-muted); margin-top: 0.5rem;"><?php echo t('setup_header_desc', 'Initialize your Artado documentation database'); ?></p>
    </div>

    <?php if ($message): ?>
        <div style="background: rgba(46, 194, 126, 0.1); color: var(--success); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; border-left: 4px solid var(--success);">
            <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            <div style="margin-top: 1rem; padding: 1rem; background: rgba(0,0,0,0.1); border-radius: var(--radius-sm);">
                <strong><?php echo t('default_credentials', 'Default Admin Credentials'); ?>:</strong><br>
                <?php echo t('username', 'Username'); ?>: <code>admin</code><br>
                <?php echo t('password', 'Password'); ?>: <code>admin123</code>
            </div>
            <div style="margin-top: 1.5rem;">
                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> <?php echo t('go_to_login', 'Go to Login'); ?>
                </a>
                <a href="index.php" class="btn btn-outline" style="margin-left: 0.5rem;">
                    <i class="fas fa-home"></i> <?php echo t('go_to_homepage', 'Go to Homepage'); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div style="background: rgba(224, 27, 36, 0.1); color: var(--danger); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; border-left: 4px solid var(--danger);">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if ($tables_exist && !$message): ?>
        <div style="background: rgba(74, 95, 199, 0.1); color: var(--primary); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; border-left: 4px solid var(--primary);">
            <i class="fas fa-info-circle"></i> <?php echo t('tables_exist_msg', 'Database tables already exist. You can reset them by running setup again.'); ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="setup" value="1">
        <div style="background: var(--bg-alt); padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
            <h3 style="margin: 0 0 1rem 0; font-size: 1.1rem;"><?php echo t('what_will_be_created', 'What will be created:'); ?></h3>
            <ul style="margin: 0; padding-left: 1.5rem; color: var(--text-muted);">
                <?php 
                $items = t('setup_items', ['Categories table', 'Pages table', 'Users table', 'Settings table', 'Default admin user (admin/admin123)', 'Sample categories and pages']);
                foreach ($items as $item): ?>
                    <li><?php echo $item; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">
            <i class="fas fa-database"></i> <?php echo $tables_exist ? t('reset_database', 'Reset Database') : t('create_database_tables', 'Create Database Tables'); ?>
        </button>
    </form>
    
    <div style="margin-top: 2rem; text-align: center;">
        <a href="index.php" style="font-size: 0.9rem; color: var(--text-muted);"><i class="fas fa-arrow-left"></i> <?php echo t('back_to_homepage', 'Back to Homepage'); ?></a>
    </div>
</div>

<?php include 'footer.php'; ?>
