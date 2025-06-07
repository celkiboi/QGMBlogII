<?php
    $title = 'Dashboard';
    require 'auth/auth.php';
    require_login();
    include '../layouts/nav.php';
?>

<h1>Welcome, <?= htmlspecialchars(current_user()['username']) ?>!</h1>
<p>Your role: <?= current_user()['role'] ?></p>

<?php if (has_role('admin')): ?>
    <p>This is the admin dashboard.</p>
<?php elseif (has_role('staff')): ?>
    <p>This is the staff panel.</p>
<?php else: ?>
    <p>You are a regular user.</p>
<?php endif; ?>

<?php if (has_role('admin') || has_role('staff')): ?>
    <?php include 'written_articles.php'; ?>
<?php endif; ?>

<?php if (has_role('admin')): ?>
    <?php include 'user_management.php'; ?>
    <?php include 'reported_comments.php'; ?>
<?php endif; ?>

<a href="../auth/logout.php">Logout</a>