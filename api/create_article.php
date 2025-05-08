<?php
require_once '../users/auth/auth.php';
require_once '../db/db.php';

header('Content-Type: application/json');

if (!is_logged_in() || has_role('regular')) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

if (!isset($_FILES['cover_photo']) || !isset($_POST['metadata'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing data']);
    exit;
}

$metadata = json_decode($_POST['metadata'], true);
if (!$metadata || !isset($metadata['title'], $metadata['short_description'], $metadata['article_elements'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid metadata']);
    exit;
}

function generate_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

$uuid = generate_uuid();
$basePath = __DIR__ . '/../articles/' . $uuid;
$imagesPath = $basePath . '/images';

mkdir($imagesPath, 0777, true);

$coverTmp = $_FILES['cover_photo']['tmp_name'];
move_uploaded_file($coverTmp, "$basePath/cover.webp");

if (!empty($_FILES['images']['tmp_name'])) {
    foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
        $name = $_FILES['images']['name'][$index];
        move_uploaded_file($tmpName, "$imagesPath/$name");
    }
}

file_put_contents("$basePath/article.json", json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$stmt = $pdo->prepare("INSERT INTO articles (uuid, writer_id, title, summary, status) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([
    $uuid,
    current_user()['id'],
    $metadata['title'],
    $metadata['short_description'],
    $metadata['is_published'] ? 'published' : 'draft'
]);

echo json_encode(['success' => true, 'uuid' => $uuid]);
