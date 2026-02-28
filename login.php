<?php
require_once 'includes/db.php';
require_once 'includes/lang.php';

if (isset($_SESSION['user_id'])) {
    header('Location: admin.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$user]);
    $userData = $stmt->fetch();

    if ($userData && password_verify($pass, $userData['password'])) {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['username'] = $userData['username'];
        header('Location: admin.php');
        exit;
    } else {
        $error = t('invalid_credentials', 'Invalid username or password.');
    }
}

$page_title = t('login', 'Login');
$hide_sidebar = true;
include 'includes/header.php';
?>

<div style="max-width: 400px; margin: 10rem auto; padding: 2.5rem; background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); box-shadow: var(--shadow-lg);">
    <div style="text-align: center; margin-bottom: 2rem;">
        <div style="width: 100px; height: 100px; background: rgba(74, 95, 199, 0.1); border-radius: 24px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
            <img src="assets/img/logo.svg" alt="Artado" style="width: 70px; height: 70px;">
        </div>
        <h1 style="margin:0; font-size: 1.5rem;"><?php echo t('admin_access', 'Admin Access'); ?></h1>
        <p style="color: var(--text-muted);"><?php echo t('sign_in_continue', 'Please sign in to continue'); ?></p>
    </div>

    <?php if ($error): ?>
        <div style="background: rgba(224, 27, 36, 0.1); color: var(--danger); padding: 0.75rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; font-size: 0.9rem; text-align: center;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label><?php echo t('username', 'Username'); ?></label>
            <input type="text" name="username" class="form-control" required placeholder="admin">
        </div>
        <div class="form-group">
            <label><?php echo t('password', 'Password'); ?></label>
            <input type="password" name="password" class="form-control" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 1rem;">
            <?php echo t('sign_in', 'Sign In'); ?> <i class="fas fa-sign-in-alt"></i>
        </button>
    </form>
    
    <div style="margin-top: 2rem; text-align: center;">
        <a href="index.php" style="font-size: 0.9rem; color: var(--text-muted);"><i class="fas fa-arrow-left"></i> <?php echo t('back_to_docs', 'Back to Documentation'); ?></a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
