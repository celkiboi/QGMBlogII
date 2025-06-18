<?php
require_once './db/db.php';
$title = 'Home';
include './layouts/nav.php';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE status = 'published' ORDER BY created_at");
$countStmt->execute();
$totalArticles = $countStmt->fetchColumn();

$stmt = $pdo->query("SELECT uuid, title FROM articles WHERE status = 'published' ORDER BY created_at DESC LIMIT 10");
$articles = $stmt->fetchAll();
?>

<main class="container">
    <h1>Welcome to the <i>Quick Garage Manager Blog</i></h1>
    <p>Your one-stop page for all news car related!</p>
    <main class="article-container">

    <h2>Latest Articles</h2>
    <ul>
        <?php foreach ($articles as $article): ?>
            <li class="post">
                <a href="pages/article.php?uuid=<?= htmlspecialchars($article['uuid']) ?>">
                    <?= htmlspecialchars($article['title']) ?>
                </a>
            </li>
        <?php endforeach; ?>
        <?php if ($totalArticles > 10): ?>
            <button type="button" id="load-more-posts" onclick="loadMorePosts()">More</button>
        <?php endif; ?>
    </ul>
</main>

<script src="scripts/articles.js"></script>
