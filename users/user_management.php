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
                <tr class="user-row" id="user-<?= htmlspecialchars($user['id']) ?>">
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <?php if ($user['role'] === 'admin'): ?>
                        <td>Admin</td>
                    <?php else: ?>
                        <td>
                            <select name="role">
                                <option value="regular" <?= $user['role'] === 'regular' ? 'selected' : '' ?>>Regular</option>
                                <option value="staff" <?= $user['role'] === 'staff' ? 'selected' : '' ?>>Staff</option>
                            </select>
                        </td>
                    <?php endif; ?>
                    <td><?= htmlspecialchars($user['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<script src="../scripts/user_management.js"></script>