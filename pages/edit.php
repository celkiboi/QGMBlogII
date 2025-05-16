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

                <?php elseif ($block['type'] === 'table'): 
                    $rows = $block['data'];
                    $title = $block['title'];
                    $title = !isset($title) ? "" : $title;
                    $rowCounter = 0;
                    $colCounter = 0;
                ?>
                    <div class="generated-table">
                        <label>Table title: 
                            <input type="text" name="title" value="<?= $title ?>">
                        </label>
                        <?php foreach($rows as $row): ?>
                            <div class="table-row" style="display: flex;">
                                <?php $colCounter = 0;
                                    foreach($row as $item): ?>
                                        <input type="text" name="table-<?= htmlspecialchars($blockCounter) ?>-cell-<?= htmlspecialchars($rowCounter) ?>-<?= htmlspecialchars($colCounter) ?>" id = "table-<?= htmlspecialchars($blockCounter) ?>-cell-<?= htmlspecialchars($rowCounter) ?>-<?= htmlspecialchars($colCounter) ?>" placeholder="<?= htmlspecialchars($rowCounter + 1) ?>,<?= htmlspecialchars($colCounter + 1) ?>" style="margin: 2px;" value=<?= htmlspecialchars($item) ?>>
                                    <?php $colCounter++; ?>
                                <?php endforeach; ?>
                            </div>
                            <?php $rowCounter++; ?>
                        <?php endforeach; ?>
                        <button type="button" id="table-<?= htmlspecialchars($blockCounter) ?>-add-row" onClick="addRow(this.parentNode, <?= htmlspecialchars($blockCounter) ?>)">Add Row</button>
                        <button type="button" id="table-<?= htmlspecialchars($blockCounter) ?>-add-col" onClick="addCol(this.parentNode, <?= htmlspecialchars($blockCounter) ?>)">Add Column</button>
                        <button type="button" id="table-<?= htmlspecialchars($blockCounter) ?>-remove-row" onClick="removeRow(this.parentNode)">Remove Row</button>
                        <button type="button" id="table-<?= htmlspecialchars($blockCounter) ?>-remove-row" onClick="removeCol(this.parentNode)">Remove Column</button>
                    </div>
                <?php endif; ?>
                
                <button class="article-item-delete-btn" style="display:none;" onclick="this.parentElement.remove(); return false;">X</button>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="element-controls">
        <button type="button" id="add-element-btn">+</button><br><br>
        <div id="element-chooser" style="display:none;">
            <button type="button" data-type="paragraph">Paragraph</button>
            <button type="button" data-type="quote">Quote</button>
            <button type="button" data-type="subtitle">Subtitle</button>
            <button type="button" data-type="image">Image</button>
            <button type="button" data-type="table">Table</button>
        </div>
    </div>

    <button type="button" id="submit-article-unpublished-btn">Save Unpublished</button>
    <button type="button" id="submit-article-published-btn">Save Published</button>
</form>

<script src="../scripts/write.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.article-item-delete-btn').forEach(btn => {
        const parent = btn.parentElement;

        parent.addEventListener('mouseenter', () => {
            btn.style.display = 'inline';
        });

        parent.addEventListener('mouseleave', () => {
            btn.style.display = 'none';
        });

        btn.style.display = 'none';
    });
});
</script>
