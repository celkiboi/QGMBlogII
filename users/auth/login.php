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

<form method="post">
    <h2>Login</h2>
    <?php if ($error): ?>
        <p style="color:red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <input name="username" placeholder="Username" required>
    <input name="password" type="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>

<p>Not a user? Register <a href="register.php">here</a></p>
