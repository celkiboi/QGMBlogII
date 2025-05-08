<?php
$title = 'Register';
require_once '../../db/db.php';
require_once 'auth.php';

if (is_logged_in()) {
    http_response_code(403);
    echo "<h1>403 Forbidden</h1><p>You are already logged in.</p>";
    exit;
}

include '../../layouts/nav.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = 'regular';

    if (strlen($username) < 5 || strlen($password) < 6) {
        $error = "Username must be at least 5 characters and password at least 6.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $error = "Username already taken.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $password_hash, $role]);

            $user_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            login($user);

            header('Location: ../dashboard.php');
            exit;
        }
    }
}
?>

<h2>Register</h2>

<?php if ($error): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post">
    <label>Username: <input type="text" name="username" required></label><br><br>
    <label>Password: <input type="password" name="password" required></label><br><br>
    <button type="submit">Register</button>
</form>