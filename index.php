<?php
require_once './db/db.php';
$title = 'Home';
include './layouts/nav.php';

$stmt = $pdo->query("SELECT * FROM articles WHERE status='published' ORDER BY created_at DESC");
$allArticles = $stmt->fetchAll();
$totalArticles = count($allArticles);

$hero = array_slice($allArticles, 0, 3);
$remaining = array_slice($allArticles, 3, 7);

function cover($a){ 
    return "articles/{$a['uuid']}/cover.webp"; 
}

?>

<main class="container">
    <h1>Quick Garage Manager Blog</h1>
    <p class="lead">Your one-stop page for all car-related news!</p>

    <?php if ($totalArticles === 0): ?>
            <p style="text-align:center">No articles yet. Check back soon!</p>

    <?php else: ?>
        <?php if (!empty($hero)): ?>
            <a class="hero-hot" href="pages/article.php?uuid=<?= htmlspecialchars($hero[0]['uuid']) ?>">
                <div class="hero-bg" style="background-image:url('<?= cover($hero[0]) ?>')"></div>
                <div class="hero-overlay">
                    <h2><?= htmlspecialchars($hero[0]['title']) ?></h2>
                    <p><?= htmlspecialchars($hero[0]['summary']) ?></p>
                </div>
            </a>

            <?php if (count($hero) > 1): ?>
                <div class="hero-row">
                    <?php foreach (array_slice($hero, 1) as $h): ?>
                        <a class="hero-medium" href="pages/article.php?uuid=<?= htmlspecialchars($h['uuid']) ?>">
                            <div class="hero-bg" style="background-image:url('<?= cover($h) ?>')"></div>
                            <div class="hero-overlay">
                                <h2><?= htmlspecialchars($h['title']) ?></h2>
                                <p><?= htmlspecialchars($h['summary']) ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($remaining)): ?>
            <ul class="article-list">
                <?php foreach ($remaining as $article): ?>
                    <li>
                        <a class="article-hook" href="pages/article.php?uuid=<?= htmlspecialchars($article['uuid']) ?>">
                            <img class="article-thumb" src="<?= cover($article) ?>" alt="<?= htmlspecialchars($article['title']) ?>">

                            <div class="article-body">
                                <h3><?= htmlspecialchars($article['title']) ?></h3>
                                <p><?= htmlspecialchars($article['summary']) ?></p>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if ($totalArticles > 10): ?>
            <div id="load-more-posts-button-container">
                <button type="button" id="load-more-posts" onclick="loadMorePosts()">More</button>
            </div>
        <?php endif;?>
    <?php endif; ?>
</main>

<script src="scripts/articles.js"></script>
