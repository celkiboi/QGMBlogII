<?php
require_once '../db/db.php';

$userId = current_user()['id'];

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE writer_id = ?");
$countStmt->execute([$userId]);
$totalArticles = $countStmt->fetchColumn();

$stmt = $pdo->prepare("SELECT uuid, title, status FROM articles WHERE writer_id = ? ORDER BY updated_at DESC LIMIT 10");
$stmt->execute([$userId]);
$articles = $stmt->fetchAll();

if (empty($articles)): ?>
    <p>You haven't written any articles yet. <a href="../pages/write.php">Write your first one</a>!</p>
<?php else: ?>
    <h2>Your Articles</h2>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Edit</th>
                <th>Toggle Visibility</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $article): ?>
                <tr>
                    <td><?= htmlspecialchars($article['title']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($article['status'])) ?></td>
                    <td><a href="../pages/edit.php?uuid=<?= htmlspecialchars($article['uuid']) ?>">Edit</a></td>
                    <td>
                        <form method="post" action="../api/toggle_article_visibility.php" style="display:inline;">
                            <input type="hidden" name="uuid" value="<?= htmlspecialchars($article['uuid']) ?>">
                            <button type="submit">
                                <?= $article['status'] === 'published' ? 'Unpublish' : 'Publish' ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <form method="post" action="../api/delete_article.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this article?');">
                            <input type="hidden" name="uuid" value="<?= htmlspecialchars($article['uuid']) ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($totalArticles > 10): ?>
        <button type="submit">Load More</button>
    <?php endif; ?>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[action$="toggle_article_visibility.php"]').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(form);
            const row = form.closest('tr');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) throw new Error("Request failed");
                
                const data = await response.json();

                if (data.success && data.new_status) {
                    // Update status cell
                    const statusCell = row.querySelector('td:nth-child(2)');
                    statusCell.textContent = data.new_status.charAt(0).toUpperCase() + data.new_status.slice(1);

                    // Update button text
                    const button = form.querySelector('button[type="submit"]');
                    button.textContent = data.new_status === 'published' ? 'Unpublish' : 'Publish';
                } else {
                    alert('Unexpected server response.');
                }
            } catch (err) {
                alert('Failed to toggle visibility.');
                console.error(err);
            }
        });
    });
});
</script>