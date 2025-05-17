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

$stmt = $pdo->prepare("SELECT * FROM articles WHERE uuid = '$uuid';");
$stmt->execute();
$article = $stmt->fetch();

if (current_user()['id'] !== $article['writer_id']) {
    http_response_code(403);
    echo "<h1>403 Forbidden</h1><p>You do not have access to this page.</p>";
    exit;
}

if (!$article) {
    http_response_code(404);
    echo json_encode(['error' => 'Article not found']);
    exit;
}

$newStatus = $article['status'] === 'published' ? 'draft' : 'published';

$update = $pdo->prepare("UPDATE articles SET status = ?, updated_at = NOW() WHERE id = ?");
$update->execute([$newStatus, $article['id']]);

echo json_encode(['success' => true, 'new_status' => $newStatus]);
