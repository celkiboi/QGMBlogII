<?php
    $base_title = 'Quick Garage Manager Blog';
    $page_title = isset($title) ? "$title | $base_title" : $base_title;
    define('BASE_URL', '/QGMBlogII/');
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="/QGMBlogII/styles/style.css">
</head>
<body>
