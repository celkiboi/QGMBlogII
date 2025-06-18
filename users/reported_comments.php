<?php
require_once '../db/db.php';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM comment_reports WHERE resolved IS NULL");
$countStmt->execute();
$totalReports = $countStmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT
        comment_reports.*,
        comments.content AS comment_content,
        comments.created_at AS comment_timestamp,
        articles.title AS article_title,
        articles.uuid AS article_uuid,
        reporter.username AS reporter_username,
        commenter.username AS commenter_username
    FROM comment_reports
    JOIN comments ON comment_reports.comment_id = comments.id
    JOIN articles ON comments.article_id = articles.id
    JOIN users AS reporter ON comment_reports.reporter_id = reporter.id
    JOIN users AS commenter ON comments.user_id = commenter.id
    ORDER BY comment_reports.created_at DESC
    LIMIT 10;
");
$stmt->execute();
$reports = $stmt->fetchAll();

if (empty($reports)): ?>
    <p>No comments have been reported so far. </p>
<?php else: ?>
    <div class="reported-comments-wrapper">
        <h2>Reported comments:</h2>
        <span>Sort by:</span>
        <input type="radio" name="report-sorting" id="report-sort-date-reported" value="date-reported" checked>
        <label for="report-sort-date-reported">Date reported</label>
        <input type="radio" name="report-sorting" id="report-sort-article-title" value="article-title">
        <label for="report-sort-article-title">Article title</label>
        <input type="radio" name="report-sorting" id="report-sort-comment-date" value="comment-date">
        <label for="report-sort-comment-date">Comment date</label>
        <select name="report-sorting-order">
            <option value="descending">Descending</option>
            <option value="ascending">Ascending</option>
        </select>
        <table>
            <thead>
                <tr>
                    <th>Comment on</th>
                    <th>Commented by</th>
                    <th>Reported by</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $report): ?>
                    <tr class="report-row" id="report-row-<?= htmlspecialchars($report['id']) ?>" data-comment-id="<?= htmlspecialchars($report['comment_id']) ?>">
                        <td><?= htmlspecialchars($report['article_title']) ?></td>
                        <td><?= htmlspecialchars($report['reporter_username']) ?></td>
                        <td><?= htmlspecialchars($report['commenter_username']) ?></td>
                        <td><button type="button" onclick="expandComment(<?= htmlspecialchars($report['id']) ?>)">Show comment</button></td>
                    </tr>
                    <tr class="report-comment-row" style="display: none;" id="report-comment-<?= htmlspecialchars($report['id']) ?>" data-comment-id="<?= htmlspecialchars($report['comment_id']) ?>">
                        <td colspan="4">
                            <div class="comment">
                                <h4><i><?= htmlspecialchars($report['commenter_username']) ?>:</i></h4>
                                <p><?= htmlspecialchars($report['comment_content']) ?></p>
                                <span><i><?= htmlspecialchars($report['comment_timestamp']) ?></i></span>
                                <button type="button" onclick="deleteComment(<?= htmlspecialchars($report['id']) ?>, <?= htmlspecialchars($report['comment_id']) ?>)">Delete Comment</button>
                                <button type="button" onclick="deleteReport(<?= htmlspecialchars($report['id']) ?>)">Delete Report</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                
            </tbody>
        </table>
        <?php if ($totalReports > 10): ?>
            <button id="load-more-reports" onclick="loadMoreReports()">Load more</button>
        <?php endif; ?>
    </div>
<?php endif; ?>

<script src="../scripts/comment_reports.js"></script>