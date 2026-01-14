<?php
require_once 'db.php';

// Very basic admin to add dummy data or manage pages
$page_title = 'Admin Panel';
include 'header.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $name = $_POST['name'];
        $slug = strtolower(str_replace(' ', '-', $name));
        $desc = $_POST['description'];
        $icon = $_POST['icon'];
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, icon) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $slug, $desc, $icon]);
        $message = "Category added successfully!";
    } elseif (isset($_POST['add_page'])) {
        $cat_id = $_POST['category_id'];
        $title = $_POST['title'];
        $slug = str_replace(' ', '-', strtolower($title));
        $content = $_POST['content'];
        $stmt = $pdo->prepare("INSERT INTO pages (category_id, title, slug, content) VALUES (?, ?, ?, ?)");
        $stmt->execute([$cat_id, $title, $slug, $content]);
        $message = "Page added successfully!";
    }
}

$cats = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<h1>Admin Panel</h1>
<?php if ($message): ?>
    <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    <section>
        <h2>Add Category</h2>
        <form method="POST" style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
            <input type="hidden" name="add_category" value="1">
            <input type="text" name="name" placeholder="Category Name" required style="padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-color); color: var(--text-color);">
            <textarea name="description" placeholder="Description" style="padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-color); color: var(--text-color);"></textarea>
            <input type="text" name="icon" placeholder="FontAwesome Icon (e.g. rocket)" style="padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-color); color: var(--text-color);">
            <button type="submit" style="padding: 1rem; background: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer;">Add Category</button>
        </form>
    </section>

    <section>
        <h2>Add Documentation Page</h2>
        <form method="POST" style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
            <input type="hidden" name="add_page" value="1">
            <select name="category_id" required style="padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-color); color: var(--text-color);">
                <?php foreach ($cats as $c): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="title" placeholder="Page Title" required style="padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-color); color: var(--text-color);">
            <textarea name="content" placeholder="HTML Content" required style="padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 8px; min-height: 200px; background: var(--bg-color); color: var(--text-color);"></textarea>
            <button type="submit" style="padding: 1rem; background: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer;">Add Page</button>
        </form>
    </section>
</div>

<?php include 'footer.php'; ?>
