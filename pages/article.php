<?php
require_once '../db/db.php';
require_once '../users/auth/auth.php';

$uuid = $_GET['uuid'] ?? null;

if (!$uuid || !preg_match('/^[a-f0-9\-]{36}$/', $uuid)) {
    http_response_code(400);
    echo "<h1>400 Bad Request</h1><p>Invalid or missing UUID.</p>";
    exit;
}

$articleDir = __DIR__ . "/../articles/$uuid";
$metaFile = "$articleDir/article.json";
$coverImage = "$articleDir/cover.webp";
$imagesPath = "articles/$uuid/images";

if (!file_exists($metaFile) || !file_exists($coverImage)) {
    http_response_code(404);
    echo "<h1>401 Forbidden</h1><p>Article cannot be found.</p>";
    exit;
}

$article = json_decode(file_get_contents($metaFile), true);

if ($article['is_published'] === false) {
    http_response_code(401);
    echo "<h1>404</h1><p>Article cannot be viewed.</p>";
    exit;
}

$title = htmlspecialchars($article['title'] ?? 'Untitled');
include '../layouts/nav.php';
?>

<main class="article">
    <h1><?= $title ?></h1>
    <p><em><?= htmlspecialchars($article['short_description'] ?? '') ?></em></p>

    <img src="../articles/<?= htmlspecialchars($uuid) ?>/cover.webp" alt="Cover Image" style="max-width: 100%; height: auto;">

    <?php foreach ($article['article_elements'] as $block): ?>
        <?php if ($block['type'] === 'paragraph'): ?>
            <p><?= nl2br(htmlspecialchars($block['value'])) ?></p>

        <?php elseif ($block['type'] === 'quote'): ?>
            <blockquote><em><?= htmlspecialchars($block['value']) ?></em></blockquote>

        <?php elseif ($block['type'] === 'subtitle'): ?>
            <h2><?= htmlspecialchars($block['value']) ?></h2>

        <?php elseif ($block['type'] === 'image'): ?>
            <img src="../articles/<?= htmlspecialchars($uuid) ?>/images/<?= htmlspecialchars($block['src']) ?>" alt="Article Image" style="max-width: 100%; height: auto;">

        <?php endif; ?>
    <?php endforeach; ?>
</main>

</body>
</html>
