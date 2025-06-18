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

switch ($_POST['sort_type']) {
    case 'date-reported':
        $sortCol = 'comment_reports.created_at';
        break;
    case 'comment-date':
        $sortCol = 'comments.created_at';
        break;
    case 'article-title':
        $sortCol = 'articles.title';
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid sort type']);
        exit;
}

$sortDir = ($_POST['sort_order'] === 'ascending') ? 'ASC' :
           (($_POST['sort_order'] === 'descending') ? 'DESC' : null);

if ($sortDir === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid sort order']);
    exit;
}

$countStmt = $pdo->query("SELECT COUNT(*) FROM comment_reports WHERE resolved IS NULL");
$totalReports = (int) $countStmt->fetchColumn();

$start = (int) $_POST['start_index'];
$end   = min((int) $_POST['end_index'], $totalReports);

if ($start > $totalReports) {
    http_response_code(400);
    echo json_encode(['error' => 'Starting index too high']);
    exit;
}

$query = "
    SELECT 
        comment_reports.id,
        comment_reports.comment_id,
        comment_reports.created_at AS report_timestamp,
        comments.content AS comment_content,
        comments.created_at AS comment_timestamp,
        articles.title AS article_title,
        articles.uuid AS article_uuid,
        reporter.username AS reporter_username,
        commenter.username AS commenter_username
    FROM comment_reports
    JOIN comments ON comment_reports.comment_id = comments.id
    JOIN articles ON comments.article_id = articles.id
    JOIN users AS reporter  ON comment_reports.reporter_id = reporter.id
    JOIN users AS commenter ON comments.user_id = commenter.id
    WHERE comment_reports.resolved IS NULL
    ORDER BY $sortCol $sortDir
";
$stmt = $pdo->query($query);
$reports = $stmt->fetchAll();

$filtered = array_slice($reports, $start, $end - $start);

echo json_encode([
    'success'      => true,
    'reports'      => $filtered,
    'more_exists'  => $totalReports > $end
]);
