<?php
require_once '../db/db.php';
require_once '../users/auth/auth.php';

$uuid = $_GET['uuid'] ?? null;

if (!$uuid || !preg_match('/^[a-f0-9\-]{36}$/', $uuid)) {
    http_response_code(400);
    echo "<h1>400 Bad Request</h1><p>Invalid or missing UUID.</p>";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM articles WHERE uuid = ?");
$stmt->execute([$uuid]);
$article = $stmt->fetch();

if ($article['status'] === "unpublished") {
    http_response_code(401);
    echo "<h1>404</h1><p>Article cannot be viewed.</p>";
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

$articleContent = json_decode(file_get_contents($metaFile), true);

$title = htmlspecialchars($articleContent['title'] ?? 'Untitled');
include '../layouts/nav.php';
?>

<main class="article">
    <h1><?= $title ?></h1>
    <p><em><?= htmlspecialchars($articleContent['short_description'] ?? '') ?></em></p>

    <img src="../articles/<?= htmlspecialchars($uuid) ?>/cover.webp" alt="Cover Image" style="max-width: 100%; height: auto;">

    <?php foreach ($articleContent['article_elements'] as $block): ?>
        <?php if ($block['type'] === 'paragraph'): ?>
            <p><?= nl2br(htmlspecialchars($block['value'])) ?></p>

        <?php elseif ($block['type'] === 'quote'): ?>
            <blockquote><em><?= htmlspecialchars($block['value']) ?></em></blockquote>

        <?php elseif ($block['type'] === 'subtitle'): ?>
            <h2><?= htmlspecialchars($block['value']) ?></h2>

        <?php elseif ($block['type'] === 'image'): ?>
            <img src="../articles/<?= htmlspecialchars($uuid) ?>/images/<?= htmlspecialchars($block['src']) ?>" alt="Article Image" style="max-width: 100%; height: auto;">
        
        <?php elseif ($block['type'] === 'table'): 
            $rows = $block['data'];
            $title = $block['title'];
        ?>
            <?php if (isset($title)): ?>
                <h3><?= htmlspecialchars($title) ?></h3>
            <?php endif; ?>
            <table>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($row as $item): ?>
                            <td><?= htmlspecialchars($item) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </table>
        
        <?php elseif ($block['type'] === 'youtube_video'): ?>
            <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($block['video_id']) ?>" frameborder="0" allowfullscreen></iframe>

        <?php endif; ?>
    <?php endforeach; ?>
</main>

</body>
</html>
