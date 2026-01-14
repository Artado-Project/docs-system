<?php
require_once 'db.php';

$query = $_GET['q'] ?? '';
$page_title = 'Search results for "' . htmlspecialchars($query) . '"';

include 'header.php';

if (empty($query)) {
    echo "<h1>Search</h1><p>Please enter a search term.</p>";
} else {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE title LIKE ? OR content LIKE ? ORDER BY title ASC");
    $searchTerm = "%$query%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $results = $stmt->fetchAll();

    echo "<h1>Search Results for \"" . htmlspecialchars($query) . "\"</h1>";
    echo "<p style='margin-bottom: 2rem; color: var(--text-muted);'>" . count($results) . " results found</p>";

    if ($results) {
        foreach ($results as $row) {
            echo "<div style='margin-bottom: 2rem; padding: 1.5rem; border: 1px solid var(--border-color); border-radius: 12px;'>";
            echo "<h3><a href='docs.php?slug=" . $row['slug'] . "'>" . htmlspecialchars($row['title']) . "</a></h3>";
            // Simple snippet extractor
            $snippet = strip_tags($row['content']);
            $pos = stripos($snippet, $query);
            $start = max(0, $pos - 100);
            $snippet = substr($snippet, $start, 250);
            echo "<p style='color: var(--text-muted); margin-top: 0.5rem;'>" . htmlspecialchars($snippet) . "...</p>";
            echo "</div>";
        }
    } else {
        echo "<p>No matches found for your search term.</p>";
    }
}

include 'footer.php';
?>
