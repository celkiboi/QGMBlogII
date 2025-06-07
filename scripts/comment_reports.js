function expandComment(id) {
    const commentTr = document.getElementById(`report-comment-${id}`);
    const reportTr = document.getElementById(`report-row-${id}`);
    if (!commentTr || !reportTr) {
        return;
    }

    const showButton = reportTr.querySelector('button');

    commentTr.style.display = "block";
    showButton.textContent = "Hide comment";
    showButton.onclick = () => {
        hideComment(commentTr, showButton, id);
    };
}

function hideComment(commentTr, hideButton, id) {
    commentTr.style.display = "none";
    hideButton.textContent = "Show comment";
    hideButton.onclick = () => {
        expandComment(id);
    };
}

async function deleteComment(reportId, commentId) {
    const response = await fetch('../api/delete_reported_comment.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ report_id: reportId })
    });

    if (response.ok) {
        const sameCommentReports = document.querySelectorAll(`[data-comment-id="${commentId}"]`);

        if (!sameCommentReports) {
            return;
        }

        sameCommentReports.forEach((report) => {
            report.remove();
        });
    }
}

async function deleteReport(reportId) {
    const response = await fetch('../api/delete_comment_report.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ report_id: reportId })
    });

    if (response.ok) {
        const row = document.getElementById(`report-row-${reportId}`);
        const comment = document.getElementById(`report-comment-${reportId}`);

        if (!row || !comment) {
            return;
        }

        row.remove();
        comment.remove();
    }
}