<?php
require_once '../users/auth/auth.php';
require_once '../db/db.php';

header('Content-Type: application/json');

if (!is_logged_in() || !has_role('admin')) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

if (!$_POST || !isset($_POST['user_id']) || !isset($_POST['new_role'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing data']);
}

if ($_POST['new_role'] !== 'staff' && $_POST['new_role'] !== 'regular') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data - new user role can only be staff or regular']);
}

$stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
$stmt->execute([
    $_POST['new_role'],
    $_POST['user_id']
]);

echo json_encode(['success' => true]);