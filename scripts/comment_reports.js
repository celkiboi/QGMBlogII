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

document.querySelectorAll('input[name="report-sorting"]').forEach(radio => {
    radio.addEventListener('change', sortReports);
});

document.querySelector('select[name="report-sorting-order"]').addEventListener('change', sortReports);

async function sortReports() {
    const reportRows = document.querySelectorAll('tr.report-row');
    const reportCommentRows = document.querySelectorAll('tr.report-comment-row');
    var totalReportsLoaded = reportRows.length;

    const formData = new FormData();
    formData.append('start_index', 0);
    formData.append('end_index', totalReportsLoaded);

    const sortType = document.querySelector('input[name="report-sorting"]:checked');
    const sortOrder = document.querySelector('select[name="report-sorting-order"]');
    if (!sortType || !sortOrder) 
        return;

    formData.append('sort_type',  sortType.value);
    formData.append('sort_order', sortOrder.value);

    const result = await fetch('../api/report_pagination.php', { 
        method: 'POST', 
        body: formData
    });
    
    if (!result.ok) { 
        alert('Failed loading reports'); 
        return;
    }

    const data = await result.json();
    const tbody = reportRows[0].parentElement;
    const reports = data.reports;

    reportRows.forEach(reportRow => {
        reportRow.remove();
    });
    reportCommentRows.forEach(reportCommentRow => {
        reportCommentRow.remove();
    });

    reports.forEach(report => {
        const tr = document.createElement('tr');
        tr.className = 'report-row';
        tr.id = `report-row-${report.id}`;
        tr.dataset.commentId = report.comment_id;

        tr.innerHTML = `
            <td>${report.article_title}</td>
            <td>${report.reporter_username}</td>
            <td>${report.commenter_username}</td>
            <td><button type="button" onclick="expandComment(${report.id})">Show comment</button></td>
        `;
        tbody.appendChild(tr);

        const trComment = document.createElement('tr');
        trComment.className = 'report-comment-row';
        trComment.style.display = 'none';
        trComment.id = `report-comment-${report.id}`;
        trComment.dataset.commentId = report.comment_id;

        trComment.innerHTML = `
            <td colspan="4">
                <div class="comment">
                    <h4><i>${report.commenter_username}:</i></h4>
                    <p>${report.comment_content}</p>
                    <span><i>${report.comment_timestamp}</i></span>
                    <button type="button" onclick="deleteComment(${report.id}, ${report.comment_id})">Delete Comment</button>
                    <button type="button" onclick="deleteReport(${report.id})">Delete Report</button>
                </div>
            </td>
        `;
        tbody.appendChild(trComment);

        totalReportsLoaded++
    });

    if (!data.more_exists) {
        const button = document.getElementById('load-more-reports');
        if (button) 
            button.remove();
    }
}

async function loadMoreReports() {
    const reportRows = document.querySelectorAll('tr.report-row');
    var totalReportsLoaded = reportRows.length;

    const reportsToLoad = 10;

    const formData = new FormData();
    formData.append('start_index', totalReportsLoaded);
    formData.append('end_index',   totalReportsLoaded + reportsToLoad);

    const sortType = document.querySelector('input[name="report-sorting"]:checked');
    const sortOrder = document.querySelector('select[name="report-sorting-order"]');
    if (!sortType || !sortOrder) 
        return;

    formData.append('sort_type',  sortType.value);
    formData.append('sort_order', sortOrder.value);

    const result = await fetch('../api/report_pagination.php', { 
        method: 'POST', 
        body: formData
    });

    if (!result.ok) { 
        alert('Failed loading reports'); 
        return;
    }

    const data = await result.json();
    const tbody = reportRows[0].parentElement;
    const reports = data.reports;

    reports.forEach(report => {
        const tr = document.createElement('tr');
        tr.className = 'report-row';
        tr.id = `report-row-${report.id}`;
        tr.dataset.commentId = report.comment_id;

        tr.innerHTML = `
            <td>${report.article_title}</td>
            <td>${report.reporter_username}</td>
            <td>${report.commenter_username}</td>
            <td><button type="button" onclick="expandComment(${report.id})">Show comment</button></td>
        `;
        tbody.appendChild(tr);

        const trComment = document.createElement('tr');
        trComment.className = 'report-comment-row';
        trComment.style.display = 'none';
        trComment.id = `report-comment-${report.id}`;
        trComment.dataset.commentId = report.comment_id;

        trComment.innerHTML = `
            <td colspan="4">
                <div class="comment">
                    <h4><i>${report.commenter_username}:</i></h4>
                    <p>${report.comment_content}</p>
                    <span><i>${report.comment_timestamp}</i></span>
                    <button type="button" onclick="deleteComment(${report.id}, ${report.comment_id})">Delete Comment</button>
                    <button type="button" onclick="deleteReport(${report.id})">Delete Report</button>
                </div>
            </td>
        `;
        tbody.appendChild(trComment);

        totalReportsLoaded++;
    });

    if (!data.more_exists) {
        const button = document.getElementById('load-more-reports');
        if (button) 
            button.remove();
    }
}
