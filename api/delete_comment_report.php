<?php
require_once '../users/auth/auth.php';
require_once '../db/db.php';

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

if (!isset($_DELETE['report_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing report id']);
    exit;
}

$report_id = $_DELETE['report_id'];
$stmt = $pdo->prepare("SELECT * FROM comment_reports WHERE id = ?");
$stmt->execute([$report_id]);
$report = $stmt->fetch();

if (empty($report)) {
    http_response_code(400);
    echo json_encode(["error"=> "Report id not valid"]);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM comment_reports WHERE id = ?");
$stmt->execute([$report_id]);

echo json_encode(['success' => true]);