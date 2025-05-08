<?php
require_once __DIR__ . '/_shared.php';
require_once __DIR__ . '/../auth/auth.php';
?>

<nav>
    <a href="<?= BASE_URL ?>index.php">Home</a> |
    <a href="<?= BASE_URL ?>/pages/about.php">About</a>

    <?php if (is_logged_in()): ?>
        | Logged in as <strong><?= htmlspecialchars(current_user()['username']) ?></strong>

        <?php if (has_role('admin')): ?>
            | <a href="<?= BASE_URL ?>pages/dashboard.php">Dashboard</a>
        <?php endif; ?>

        <?php if (has_role('admin') || has_role('staff')): ?>
            | <a href="<?= BASE_URL ?>pages/write.php">Write</a>
        <?php endif; ?>

        | <a href="<?= BASE_URL ?>auth/logout.php">Logout</a>
    <?php else: ?>
        | <a href="<?= BASE_URL ?>auth/login.php">Login</a>
    <?php endif; ?>
</nav>
