<?php
    require '../auth/auth.php';
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

<a href="../auth/logout.php">Logout</a>
