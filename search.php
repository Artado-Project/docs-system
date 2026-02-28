<?php
require_once 'includes/db.php';
require_once 'includes/lang.php';

$query = $_GET['q'] ?? '';
$page_title = t('search_results', 'Search results') . ' "' . htmlspecialchars($query) . '"';

include 'includes/header.php';

if (empty($query)) {
    echo "<div class='hero'><h1>" . t('search', 'Search') . "</h1><p>" . t('search_placeholder', 'Please enter a search term above.') . "</p></div>";
} else {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE title LIKE ? OR content LIKE ? ORDER BY title ASC");
    $searchTerm = "%$query%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $results = $stmt->fetchAll();

    echo "<h1>" . t('search_results', 'Search Results') . "</h1>";
    echo "<p style='margin-bottom: 2rem; color: var(--text-muted);'>" . t('found_results', 'Found') . " " . count($results) . " " . t('matching_pages', 'matching pages') . " \"" . htmlspecialchars($query) . "\"</p>";

    if ($results) {
        foreach ($results as $row) {
            ?>
            <div class="search-result-card" style="margin-bottom: 2rem; padding: 2rem; background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); transition: var(--transition); cursor: pointer; box-shadow: var(--shadow-sm);" onclick="location.href='docs.php?slug=<?php echo $row['slug']; ?>'">
                <h3 style="margin-bottom: 0.5rem;"><a href='docs.php?slug=<?php echo $row['slug']; ?>' style="color: var(--text-main);"><?php echo htmlspecialchars($row['title']); ?></a></h3>
                <?php
                $snippet = strip_tags($row['content']);
                $pos = stripos($snippet, $query);
                $start = max(0, $pos - 100);
                $snippet = mb_substr($snippet, $start, 250);
                ?>
                <p style='color: var(--text-muted); font-size: 0.95rem;'><?php echo htmlspecialchars($snippet); ?>...</p>
                <div style="margin-top: 1rem; font-size: 0.85rem; color: var(--primary); font-weight: 600;">
                    <?php echo t('read_more', 'Read more'); ?> <i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i>
                </div>
            </div>
            <?php
        }
    } else {
        echo "<div style='text-align:center; padding: 4rem; background: var(--bg-alt); border-radius: var(--radius-lg); border: 1px solid var(--border);'>";
        echo "<div style='width: 80px; height: 80px; background: rgba(74, 95, 199, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;'>";
        echo "<i class='fas fa-search' style='font-size: 2rem; color: var(--primary);'></i>";
        echo "</div>";
        echo "<h2 style='margin-bottom: 1rem;'>" . t('no_matches', 'No matches found') . "</h2>";
        echo "<p style='color: var(--text-muted); margin-bottom: 2rem;'>" . t('try_different', 'Try different keywords or check out the categories on the') . " <a href='index.php' style='color: var(--primary); font-weight: 600;'>" . t('home', 'Home page') . "</a>.</p>";
        echo "</div>";
    }
}

include 'includes/footer.php';
?>
