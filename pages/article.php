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

<?php

$stmt = $pdo->prepare("
    SELECT comments.*, users.username 
    FROM comments 
    JOIN users ON comments.user_id = users.id 
    WHERE comments.article_id = ?
    ORDER BY comments.created_at DESC
    LIMIT 3
");
$stmt->execute([$article['id']]);
$comments = $stmt->fetchAll();
?>

<form class="post-comment">
    <input type="hidden" name="uuid" value="<?= htmlspecialchars($uuid) ?>">
    <label>Comment <input type="text" name="comment" required></label><br>
    <button type="button" id="comment-post-button" onClick="postComment()">Post</button>
</form>

<form>
    <label for="comment-sorting-order">Sort comments by: </label>
    <select name="comment-sorting-order">
        <option value="newest-first">Newest first</option>
        <option value="oldest-first">Oldest first</option>
    </select>
</form>

<div class="comments-container">
    <?php foreach($comments as $comment): ?>
        <div class="comment">
            <h4><i><?= htmlspecialchars($comment['username']) ?>:</i></h4>
            <p><?= htmlspecialchars($comment['content']) ?></p>
            <span><i><?= htmlspecialchars($comment['created_at']) ?></i></span>
            <button type="button" class="report-button" id="report-button-<?= htmlspecialchars($comment['id']) ?>" onClick="reportComment(<?= htmlspecialchars($comment['id']) ?>, this)">Report</button>
        </div>
    <?php endforeach; ?>
</div>

<button type="button" id="load-more-comments-button" onClick="loadMoreComments()">Load more</button>

<script src="../scripts/comments.js"></script>