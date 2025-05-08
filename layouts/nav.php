<?php
require_once __DIR__ . '/_shared.php';
require_once __DIR__ . '/../users/auth/auth.php';
?>

<nav>
    <a href="<?= BASE_URL ?>index.php">Home</a> |
    <a href="<?= BASE_URL ?>/pages/about.php">About</a>

    <?php if (is_logged_in()): ?>
        | Logged in as <strong><?= htmlspecialchars(current_user()['username']) ?></strong>

        <a href="<?= BASE_URL ?>users/dashboard.php">Dashboard</a>

        <?php if (has_role('admin') || has_role('staff')): ?>
            | <a href="<?= BASE_URL ?>pages/write.php">Write</a>
        <?php endif; ?>

        | <a href="<?= BASE_URL ?>users/auth/logout.php">Logout</a>
    <?php else: ?>
        | <a href="<?= BASE_URL ?>users/auth/login.php">Login</a>
    <?php endif; ?>
</nav>
