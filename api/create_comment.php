<?php
require_once '../users/auth/auth.php';
require_once '../db/db.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

if (!isset($_POST['content']) || !isset($_POST['uuid'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing data']);
    exit;
}

$uuid = $_POST['uuid'];
if (!preg_match('/^[a-f0-9\-]{36}$/', $uuid)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid UUID']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM articles WHERE uuid = '$uuid';");
$stmt->execute();
$articleData = $stmt->fetch();

if (!$articleData) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid UUID']);
    exit;
}

$stmt = $pdo->prepare("SELECT created_at FROM comments WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([current_user()['id']]);
$last_comment = $stmt->fetch();

if ($last_comment) {
    $lastTime = new DateTime($last_comment['created_at']);
    $now = new DateTime();

    $diff = $now->getTimestamp() - $lastTime->getTimestamp();

    if ($diff < 5) {
        http_response_code(403);
        echo json_encode(['error' => 'Spam detection triggered. Please wait a little bit before commenting again']);
        exit;
    }
}

$stmt = $pdo->prepare("INSERT INTO comments (user_id, article_id, content) VALUES (?, ?, ?)");
$stmt->execute([current_user()['id'], $articleData['id'], $_POST['content']]);
$commentId = $pdo->lastInsertId();

echo json_encode(['success' => true, 'comment_id' => $commentId, 'username' => current_user()['username']]);