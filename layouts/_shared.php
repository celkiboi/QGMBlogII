<?php
    $base_title = 'Quick Garage Manager Blog';
    $page_title = isset($title) ? "$title | $base_title" : $base_title;
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($page_title) ?></title>
</head>
<body>
