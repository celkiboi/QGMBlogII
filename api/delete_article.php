<?php
require_once '../users/auth/auth.php';
require_once '../db/db.php';
require_once '../utils/folder.php';

header('Content-Type: application/json');

if (!is_logged_in() || has_role('regular')) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['error' => 'This endpoint only responds to DELETE requests']);
    exit;
}

$_DELETE = file_get_contents('php://input');
$_DELETE = json_decode($_DELETE, true);

if (!isset($_DELETE['uuid'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing UUID']);
    exit;
}

$uuid = $_DELETE['uuid'];
if (!preg_match('/^[a-f0-9\-]{36}$/', $uuid)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid UUID']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM articles WHERE uuid = '$uuid';");
$stmt->execute();
$articleData = $stmt->fetch();

if (current_user()['id'] !== $articleData['writer_id']) {
    http_response_code(403);
    echo json_encode(['error' => 'You are not the writer of this article']);
    exit;
}

$folderPath = __DIR__ . '/../articles/' . $uuid;
if (!is_dir($folderPath)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid uuid']);
    exit;
}
deleteFolderRecursive($folderPath);

$articleId = $articleData['id'];
$stmt = $pdo->prepare("DELETE FROM articles WHERE id = $articleId");
$stmt->execute();

echo json_encode(['success' => true]);
