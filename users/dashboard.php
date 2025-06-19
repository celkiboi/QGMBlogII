<?php
    $title = 'Dashboard';
    require 'auth/auth.php';
    require_login();
    include '../layouts/nav.php';
?>

<h1 id="dashboard-title">Welcome, <?= htmlspecialchars(current_user()['username']) ?>!</h1>
<div id="your-role">
    <p>Your role: <?= current_user()['role'] ?></p>
</div>

<?php if (has_role('admin') || has_role('staff')): ?>
    <div class="dashboard-section your-articles-wrapper">
        <?php include 'written_articles.php'; ?>
    </div>
<?php endif; ?>

<?php if (has_role('admin')): ?>
    <div class="dashboard-section user-management-wrapper">
        <?php include 'user_management.php'; ?>
    </div>

    <div class="dashboard-section reported-comments-wrapper">
        <?php include 'reported_comments.php'; ?>
    </div>
<?php endif; ?>