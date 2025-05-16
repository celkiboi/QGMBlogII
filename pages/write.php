<?php
require_once '../users/auth/auth.php';

if (!is_logged_in() || has_role('regular')) {
    http_response_code(403);
    echo "<h1>403 Forbidden</h1><p>You do not have access to this page.</p>";
    exit;
}

$title = 'New article';
include '../layouts/nav.php';
?>

<h1>Write a New Article</h1>

<form id="article-form" enctype="multipart/form-data" method="post">
    <input type="hidden" name="api-endpoint" value="../api/create_article.php">
    <label>Title: <input type="text" name="title" required></label><br>
    <label>Short description: <input type="text" name="short-description" required></label><br>
    <label>Cover Photo: <input type="file" name="cover_photo" accept="image/*"></label>

    <div id="article-body"></div>

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

    <button type="button" id="submit-article-unpublished-btn">Submit Article Unpublished</button>
    <button type="button" id="submit-article-published-btn">Submit Article Published</button>
</form>

<script src="../scripts/write.js"></script>
