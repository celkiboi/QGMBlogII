<?php
require_once '../users/auth/auth.php';
require_once '../db/db.php';

header('Content-Type: application/json');

if (!$_POST 
    || !isset($_POST['start_index']) 
    || !isset($_POST['end_index']) 
    || !isset($_POST['sort_type'])
    || !isset($_POST['sort_order'])
    || !isset($_POST['only_from_user'])
    ) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing data']);
    exit;
}

if ($_POST['only_from_user'] === true
    && (!is_logged_in() || current_user()['role'] === 'user') 
    ){
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$user_id;
$where_query = "WHERE status = 'published'";

if (isset($_SESSION['user']) && $_POST['only_from_user'] === true) {
    $user_id = current_user()['id'];
    $where_query = "WHERE `writer_id` = $user_id";
}

$order_query;
if ($_POST['sort_type'] === 'date-edited') 
    $order_query = 'ORDER BY updated_at';
elseif ($_POST['sort_type'] === 'title')
    $order_query = 'ORDER BY title';
else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid sort type']);
    exit;
}

$order_type_query;
if ($_POST['sort_order'] === 'ascending') 
    $order_type_query = 'ASC';
elseif ($_POST['sort_order'] === 'descending')
    $order_type_query = 'DESC';
else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid sort order']);
    exit;
}

$countQuery = 'SELECT COUNT(*) FROM articles $where_query';
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute();
$totalPosts = $countStmt->fetchColumn();

if ($_POST['start_index'] > $totalPosts) {
    http_response_code(400);
    echo json_encode(['error' => 'Starting index too high']);
    exit;
}

$query = "SELECT * FROM articles $where_query $order_query $order_type_query";
$stmt = $pdo->prepare($query);
$stmt->execute();
$posts = $stmt->fetchAll();

$start = $_POST['start_index'];
$end = ($_POST['end_index'] < $totalPosts) ? $_POST['end_index'] : $totalPosts;

$filtered_posts = array();
for ($i = $start; $i < $end; $i++) {
    $post = $posts[$i];
    unset($post['id']);
    unset($post['writer_id']);
    array_push($filtered_posts, $post);
}

$more_exists = $totalPosts > $end;

echo json_encode(['sucess' => true, 'posts' => $filtered_posts, 'more_exists' => $more_exists]);