<?php
require_once '../users/auth/auth.php';
require_once '../db/db.php';

header('Content-Type: application/json');

if (!is_logged_in() || has_role('regular')) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$uuid = $_POST['uuid'] ?? null;

if (!$uuid || !preg_match('/^[a-f0-9\-]{36}$/', $uuid)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid UUID']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, status FROM articles WHERE uuid = ? AND writer_id = ?");
$stmt->execute([$uuid, current_user()['id']]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    echo json_encode(['error' => 'Article not found or permission denied']);
    exit;
}

$newStatus = $article['status'] === 'published' ? 'draft' : 'published';

$update = $pdo->prepare("UPDATE articles SET status = ?, updated_at = NOW() WHERE id = ?");
$update->execute([$newStatus, $article['id']]);

echo json_encode(['success' => true, 'new_status' => $newStatus]);
