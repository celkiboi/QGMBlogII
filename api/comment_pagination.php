<?php 
require_once '../users/auth/auth.php';
require_once '../db/db.php';

header('Content-Type: application/json');

if (!$_POST 
    || !isset($_POST['start_index']) 
    || !isset($_POST['end_index'])
    || !isset($_POST['sort_order'])
    || !isset($_POST['uuid'])
    ) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing data']);
    exit;
}

$order_type_query;
if ($_POST['sort_order'] === 'oldest-first') 
    $order_type_query = 'ASC';
elseif ($_POST['sort_order'] === 'newest-first')
    $order_type_query = 'DESC';
else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid sort order']);
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

$stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE article_id = ? ORDER BY created_at $order_type_query");
$stmt->execute([$articleData['id']]);
$totalComments = $stmt->fetchColumn();

if ($_POST['start_index'] > $totalComments) {
    http_response_code(400);
    echo json_encode(['error' => 'Starting index too high']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT comments.*, users.username
    FROM comments
    JOIN users ON comments.user_id = users.id
    WHERE comments.article_id = ?
    ORDER BY comments.created_at $order_type_query;
");
$stmt->execute([$articleData['id']]);
$comments = $stmt->fetchAll();

$start = $_POST['start_index'];
$end = ($_POST['end_index'] < $totalComments) ? $_POST['end_index'] : $totalComments;

$filtered_comments = array();
for ($i = $start; $i < $end; $i++) {
    $comment = $comments[$i];
    unset($comment['article_id']);
    unset($comment['user_id']);
    array_push($filtered_comments, $comment);
}

$more_exists = $totalComments > $end;

echo json_encode(['sucess' => true, 'comments' => $filtered_comments, 'more_exists' => $more_exists]);