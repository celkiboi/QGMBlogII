document.addEventListener('DOMContentLoaded', () => {
    const select = document.querySelector('select[name="comment-sorting-order"]');
    select.addEventListener('change', (event) => {
        const selectedValue = event.target.value;
        const uuid = document.querySelector('input[name="uuid"]')?.value;
        sortComments(selectedValue, uuid);
    });
});

async function sortComments(sortingOrder, uuid) {
    const numberOfCommentsLoaded = document.querySelectorAll('div.comment').length;

    const formData = new FormData();
    formData.append("start_index", 0);
    formData.append("end_index", numberOfCommentsLoaded);
    formData.append("sort_order", sortingOrder);
    formData.append("uuid", uuid);

    const response = await fetch('../api/comment_pagination.php', {
        method: 'POST',
        body: formData
    });

    if (response.ok) {
        const responseData = await response.json();

        const commentsContainer = document.querySelector('div.comments-container');
        commentsContainer.innerHTML = ``;

        const newComments = responseData['comments'];
        newComments.forEach(comment => {
            const commentDiv = document.createElement('div');
            commentDiv.className = 'comment';
            commentDiv.innerHTML = `
                <h4><i>${comment['username']}:</i></h4>
                <p>${comment['content']}</p>
                <span><i>${comment['created_at']}</i></span>
                <button type="button" class="report-button" id="report-button-${comment['id']}" onClick="reportComment(${comment.id}, this)">Report</button>
            `
            commentsContainer.append(commentDiv);
        });

        const loadMoreButton = document.getElementById("load-more-comments-button");
        if (responseData['more_exists'] === false && loadMoreButton) {
            loadMoreButton.remove();
        }

    } else {
        alert(`Error sorting comments.`);
    }
}

async function loadMoreComments() {
    const numberOfCommentsLoaded = document.querySelectorAll('div.comment').length;
    const uuid = document.querySelector('input[name="uuid"]')?.value;
    const sortingOrder = document.querySelector('select[name="comment-sorting-order"]')?.value;

    if (!uuid || !sortingOrder) {
        return;
    }

    const numberOfCommentsToLoad = 10;

    const formData = new FormData();
    formData.append("start_index", numberOfCommentsLoaded);
    formData.append("end_index", numberOfCommentsLoaded + numberOfCommentsToLoad);
    formData.append("sort_order", sortingOrder);
    formData.append("uuid", uuid);

    const response = await fetch('../api/comment_pagination.php', {
        method: 'POST',
        body: formData
    });

    if (response.ok) {
        const responseData = await response.json();

        const commentsContainer = document.querySelector('div.comments-container');

        const newComments = responseData['comments'];
        newComments.forEach(comment => {
            const commentDiv = document.createElement('div');
            commentDiv.className = 'comment';
            commentDiv.innerHTML = `
                <h4><i>${comment['username']}:</i></h4>
                <p>${comment['content']}</p>
                <span><i>${comment['created_at']}</i></span>
                <button type="button" class="report-button" id="report-button-${comment['id']}">Report</button>
            `
            commentsContainer.append(commentDiv);
        });

        if (responseData['more_exists'] === false) {
            const loadMoreButton = document.getElementById("load-more-comments-button");
            loadMoreButton.remove();
        }

    } else {
        alert(`Error sorting comments.`);
    }
}

async function postComment() {
    const commentInput = document.querySelector('.post-comment input[name="comment"]');
    const commentContent = commentInput.value.trim();
    const uuid = document.querySelector('input[name="uuid"]')?.value;

    if (!commentContent) {
        alert("Cannot submit an empty comment.");
        return;
    }

    const formData = new FormData();
    formData.append("content", commentContent);
    formData.append("uuid", uuid);

    const response = await fetch('../api/create_comment.php', {
        method: 'POST',
        body: formData
    });

    if (response.ok) {
        const responseData = await response.json()
        const commentContainer = document.querySelector('div.comments-container');

        const sortingOrder = document.querySelector('select[name="comment-sorting-order"]')?.value;

        const newComment = document.createElement('div');
        newComment.className = 'comment';
        newComment.innerHTML += `
            <h4><i>${responseData['username']}:</i></h4>
            <p>${commentContent}</p>
            <span><i>${getCurrentTimestamp()}</i></span>
            <button type="button" class="report-button" id="report-button-${responseData['id']}">Report</button>
        `;
        if (sortingOrder === 'newest-first')
            commentContainer.insertBefore(newComment, commentContainer.children[0]);
        else
            commentContainer.append(newComment);
    } else {
        alert(`Error submitting comment.`);
    }
}

function getCurrentTimestamp() {
    const now = new Date();

    const pad = n => n.toString().padStart(2, '0');

    const year = now.getFullYear();
    const month = pad(now.getMonth() + 1);
    const day = pad(now.getDate());
    const hours = pad(now.getHours());
    const minutes = pad(now.getMinutes());
    const seconds = pad(now.getSeconds());

    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

async function reportComment(id, reportButton) {
    const formData = new FormData();
    formData.append('comment_id', id);

    const response = await fetch('../api/report_comment.php', {
        method: 'POST',
        body: formData
    });

    if (response.ok) {
        
    }
}