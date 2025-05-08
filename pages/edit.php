<?php
require_once '../users/auth/auth.php';

if (!is_logged_in() || has_role('regular')) {
    http_response_code(403);
    echo "<h1>403 Forbidden</h1><p>You do not have access to this page.</p>";
    exit;
}

$uuid = $_GET['uuid'] ?? null;

if (!$uuid || !preg_match('/^[a-f0-9\-]{36}$/', $uuid)) {
    http_response_code(400);
    echo "<h1>400 Bad Request</h1><p>Invalid or missing UUID.</p>";
    exit;
}

$articleDir = __DIR__ . "/../articles/$uuid";
$metaFile = "$articleDir/article.json";

if (!file_exists($metaFile)) {
    http_response_code(404);
    echo "<h1>404 Not Found</h1><p>Article not found.</p>";
    exit;
}

$article = json_decode(file_get_contents($metaFile), true);

$title = 'Edit: ' . htmlspecialchars($article['title'] ?? 'Untitled');
include '../layouts/nav.php';
?>

<h1>Edit Article</h1>

<form id="article-form" enctype="multipart/form-data" method="post">
    <input type="hidden" name="uuid" value="<?= htmlspecialchars($uuid) ?>">
    <input type="hidden" name="api-endpoint" value="../api/edit_article.php">

    <label>Title: <input type="text" name="title" required value="<?= htmlspecialchars($article['title'] ?? '') ?>"></label><br>
    <label>Short description: <input type="text" name="short-description" required value="<?= htmlspecialchars($article['short_description'] ?? '') ?>"></label><br>
    <label>Replace Cover Photo: <input type="file" name="cover_photo" accept="image/*"></label>
    


    <div id="article-body">
        <?php
        $blockCounter = 0;
        foreach ($article['article_elements'] as $block):
            $type = $block['type'];
            $value = htmlspecialchars($block['value'] ?? '');
            $src = htmlspecialchars($block['src'] ?? '');
            $blockCounter++;
        ?>
            <div class="article-item" id="article-id-<?= $blockCounter ?>" data-type="<?= $type ?>" style="position: relative;">
                <button style="position:absolute;top:5px;right:5px;display:none;" onclick="this.parentElement.remove(); return false;">X</button>

                <?php if ($type === 'paragraph'): ?>
                    <label>Paragraph:<br>
                        <textarea name="content[]" rows="1" style="overflow:hidden;resize:none;"><?= $value ?></textarea>
                    </label>
                <?php elseif ($type === 'quote'): ?>
                    <label>Quote:<br>
                        <input type="text" name="content[]" value="<?= $value ?>">
                    </label>
                <?php elseif ($type === 'subtitle'): ?>
                    <label>Subtitle:<br>
                        <input type="text" name="content[]" value="<?= $value ?>">
                    </label>
                <?php elseif ($type === 'image'): ?>
                    <label>Replace Image:<br>
                        <input type="file" name="content[]" accept="image/*">
                    </label>
                    <p><small>Current image: <?= $src ?></small></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <button type="button" id="add-element-btn">+</button> <br><br>
    <button type="button" id="submit-article-unpublished-btn">Save Unpublished</button>
    <button type="button" id="submit-article-published-btn">Save Published</button>
</form>

<div id="element-chooser" style="display:none;">
    <button data-type="paragraph">Paragraph</button>
    <button data-type="quote">Quote</button>
    <button data-type="subtitle">Subtitle</button>
    <button data-type="image">Image</button>
</div>

<script src="../scripts/write.js"></script>
