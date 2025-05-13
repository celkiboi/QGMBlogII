<?php
require_once '../db/db.php';

$stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT 10");
$stmt->execute();
$users = $stmt->fetchAll();

if (empty($articles)): ?>
    <p>No users detected</p>
<?php else: ?>
    <h2>Users:</h2>
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>Role</th>
                <th>Created at</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td><?= htmlspecialchars($user['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>