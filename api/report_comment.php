<?php
require_once '../users/auth/auth.php';
require_once '../db/db.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

if (!isset($_POST['comment_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing data']);
    exit;
}

$comment_id = $_POST['comment_id'];
$stmt = $pdo->prepare("SELECT * FROM comments WHERE id = ?");
$stmt->execute([$comment_id]);
$comment = $stmt->fetch();

if (empty($comment)) {
    http_response_code(400);
    echo json_encode(['error' => 'Comment id not valid']);
    exit;
}

$reporter_id = current_user()['id'];
$stmt = $pdo->prepare("SELECT * FROM comment_reports WHERE comment_id = ? AND reporter_id = ?");
$stmt->execute([$comment_id, $reporter_id]);
$report = $stmt->fetchAll();

if (!empty($report)) {
    http_response_code(400);
    echo json_encode(['error' => 'Report already exists']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO comment_reports (comment_id, reporter_id, resolved) VALUES (?, ?, NULL);");
$stmt->execute([$comment_id, $reporter_id]);

echo json_encode(['success' => true]);