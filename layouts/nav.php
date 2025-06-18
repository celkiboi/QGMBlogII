<?php
require_once __DIR__ . '/_shared.php';
require_once __DIR__ . '/../users/auth/auth.php';
?>

<nav>
    <button id="nav-toggle" aria-label="Toggle menu">☰</button>
    <div id="nav-title-text"><h2><a href="<?= BASE_URL ?>index.php">QGMBlog</a></h2></div>
    <div class="nav-links">
        <a href="<?= BASE_URL ?>index.php">Home</a>
        <div class="nav-divider">|</div>
        <a href="<?= BASE_URL ?>/pages/about.php">About</a>
        <div class="nav-divider">|</div>

        <?php if (is_logged_in()): ?>
            <p>Logged in as <strong><?= htmlspecialchars(current_user()['username']) ?></strong></p>
            <div class="nav-divider">|</div>
            <a href="<?= BASE_URL ?>users/dashboard.php">Dashboard</a>
            <div class="nav-divider">|</div>
        <?php if (has_role('admin') || has_role('staff')): ?>
            <a href="<?= BASE_URL ?>pages/write.php">Write</a>
            <div class="nav-divider">|</div>
        <?php endif; ?>
            <a href="<?= BASE_URL ?>users/auth/logout.php">Logout</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>users/auth/login.php">Login</a>
        <?php endif; ?>
    </div>
</nav>

<script src="/QGMBlogII/scripts/nav.js"></script>
