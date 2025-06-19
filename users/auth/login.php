<?php
$title = 'Login';
require '../../db/db.php';
require 'auth.php';
include '../../layouts/nav.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch();

    if ($user && password_verify($_POST['password'], $user['password_hash'])) {
        login($user);
        header('Location: ../dashboard.php');
        exit;
    }

    $error = "Invalid username or password.";
}
?>

<main class="auth-page">
    <div class="auth-wrapper">
        <h2>Login</h2>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <label>
                Username
                <input type="text" name="username" placeholder="Username" required>
            </label>

            <label>
                Password
                <input name="password" type="password" placeholder="Password" required>
            </label>

            <button type="submit" id="login-button">Login</button>
        </form>

        <p class="alt-link">
            Not a user? <a href="register.php">Register here</a>
        </p>
    </div>
</main>
