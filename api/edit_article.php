<?php
require_once '../users/auth/auth.php';
require_once '../db/db.php';

header('Content-Type: application/json');

if (!is_logged_in() || has_role('regular')) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

if (!isset($_POST['uuid'], $_POST['metadata'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing UUID or metadata']);
    exit;
}

$uuid = $_POST['uuid'];
if (!preg_match('/^[a-f0-9\-]{36}$/', $uuid)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid UUID']);
    exit;
}

$metadata = json_decode($_POST['metadata'], true);
if (
    !$metadata ||
    !isset($metadata['title'], $metadata['short_description'])
) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid metadata']);
    exit;
}

$basePath = __DIR__ . '/../articles/' . $uuid;
$metaFile = "$basePath/article.json";
$imagesPath = "$basePath/images";

if (!file_exists($metaFile)) {
    http_response_code(404);
    echo json_encode(['error' => 'Article not found']);
    exit;
}

if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $coverTmp = $_FILES['cover_photo']['tmp_name'];
    move_uploaded_file($coverTmp, "$basePath/cover.webp");
}

if (!empty($_FILES['images']['tmp_name'])) {
    if (!is_dir($imagesPath)) {
        mkdir($imagesPath, 0777, true);
    }

    foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
        $name = $_FILES['images']['name'][$index];
        $targetPath = "$imagesPath/$name";

        if (file_exists($targetPath)) {
            unlink($targetPath);
        }

        move_uploaded_file($tmpName, $targetPath);
    }
}

$date = date('Y-m-d_H-i-s');
$backupFile = "$basePath/$date.json";
rename($metaFile, $backupFile);

$stmt = $pdo->prepare("UPDATE articles SET title = ?, summary = ?, status = ?, updated_at = NOW() WHERE uuid = ? AND writer_id = ?");
$stmt->execute([
    $metadata['title'],
    $metadata['short_description'],
    $metadata['is_published'] ? 'published' : 'draft',
    $uuid,
    current_user()['id']
]);

unset($metadata['is_published']);
file_put_contents($metaFile, json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['success' => true, 'uuid' => $uuid]);
