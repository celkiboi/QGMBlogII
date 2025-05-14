<?php
require_once '../users/auth/auth.php';
require_once '../db/db.php';

header('Content-Type: application/json');

if (!is_logged_in() || !has_role('admin')) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

if (!$_POST 
    || !isset($_POST['start_index']) 
    || !isset($_POST['end_index']) 
    || !isset($_POST['sort_type'])
    || !isset($_POST['sort_order'])
    ) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing data']);
    exit;
}

$sortType;
if ($_POST['sort_type'] === 'username') {
    $sortType = 'username'; 
}
else if ($_POST['sort_type'] === 'date-joined') {
    $sortType = 'created_at';
}
else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid sort argument']);
    exit;
}

$sortOrder;
if ($_POST['sort_order'] === 'ascending') {
    $sortOrder = 'ASC'; 
}
else if ($_POST['sort_order'] === 'descending') {
    $sortOrder = 'DESC';
}
else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid order argument']);
    exit;
}


$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users");
$countStmt->execute();
$totalUsers = $countStmt->fetchColumn();

if ($_POST['start_index'] > $totalUsers) {
    http_response_code(400);
    echo json_encode(['error' => 'Starting index too high']);
    exit;
}

$query = "SELECT * FROM users ORDER BY $sortType $sortOrder";
$stmt = $pdo->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll();

$start = $_POST['start_index'];
$end = ($_POST['end_index'] < $totalUsers) ? $_POST['end_index'] : $totalUsers;

$filtered_users = array();
for ($i = $start; $i < $end; $i++) {
    $user = $users[$i];
    unset($user['password_hash']);
    array_push($filtered_users, $user);
}

$more_exists = $totalUsers > $end;

echo json_encode(['sucess' => true, 'users' => $filtered_users, 'more_exists' => $more_exists]);