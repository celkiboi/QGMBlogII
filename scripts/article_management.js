function setupPostRow(row) {
    const visibilityForm = row.querySelector('form[action$="toggle_article_visibility.php"]');
    if (!visibilityForm)
        return;

    visibilityForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(visibilityForm);
        const row = visibilityForm.closest('tr');

        try {
            const response = await fetch(visibilityForm.action, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error("Request failed");
                
            const data = await response.json();

            if (data.success && data.new_status) {
                const statusCell = row.querySelector('td:nth-child(2)');
                statusCell.textContent = data.new_status.charAt(0).toUpperCase() + data.new_status.slice(1);

                const button = visibilityForm.querySelector('button[type="submit"]');
                button.textContent = data.new_status === 'published' ? 'Unpublish' : 'Publish';
            } else {
                alert('Unexpected server response.');
            }
        } catch (err) {
            alert('Failed to toggle visibility.');
            console.error(err);
        }
    });

    const deleteForm = row.querySelector('form[action$="delete_article.php"]');

    deleteForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const uuidInput = deleteForm.querySelector('input[name="uuid"]');
        if (!uuidInput)
            return;

        const uuid = uuidInput.value;

        try {
            const response = await fetch('../api/delete_article.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ uuid: uuid })
            });

            if (!response.ok) throw new Error("Request failed");
                
            const data = await response.json();

            if (data.success) {
                const tableBody = deleteForm.parentNode.parentNode.parentNode;
                const rows = tableBody.querySelectorAll('tr');

                if (rows.length === 1) {
                    const articlesDiv = tableBody.parentNode.parentNode;
                    tableBody.parentNode.remove();
                    articlesDiv.innerHTML += `
                        <p>You haven't written any articles yet. <a href="../pages/write.php">Write your first one</a>!</p>
                    `;
                    const articlesH2 = articlesDiv.querySelector('h2');
                    articlesH2.remove();
                }
                else {
                    const row = deleteForm.parentNode.parentNode;
                    row.remove();
                }

            } else {
                alert('Unexpected server response.');
            }
        } catch (err) {
            alert('Failed to delete.');
            console.error(err);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('tr.post-row').forEach((postRow) => {
        setupPostRow(postRow);     
    });
});

async function loadMorePosts() {
    const postRows = document.querySelectorAll('tr.post-row');
    var totalPostsLoaded = postRows.length;

    const postsToLoad = 10;

    const formData = new FormData();
    formData.append('start_index', totalPostsLoaded);
    formData.append('end_index', totalPostsLoaded + postsToLoad);

    const sortType = document.querySelector('input[name="post-sorting"]:checked');
    const sortOrder = document.querySelector('select[name="post-sorting-order"]');
    if (!sortType || !sortOrder) 
        return;

    formData.append('sort_type', sortType.value);
    formData.append('sort_order', sortOrder.value);

    formData.append('only_from_user', true);

    const response = await fetch('../api/post_pagination.php', {
        method: 'POST',
        body: formData
    });

    const postTableBody = postRows[0].parentElement;

    if (response.ok) {
        const data = await response.json();
        const newPosts = data['posts'];

        newPosts.forEach((post) => {
            const row = document.createElement('tr');
            row.className = "post-row";

            const titleTd = document.createElement('td');
            titleTd.textContent = post.title;
            row.appendChild(titleTd);

            const statusTd = document.createElement('td');
            statusTd.textContent = post['status'] === 'published' ? 'Published' : 'Draft';
            row.appendChild(statusTd);

            const editTd = document.createElement('td');
            editTd.innerHTML += `<a href="../pages/edit.php?uuid=${post['uuid']}">Edit</a>`;
            row.appendChild(editTd);

            const visibilityTd = document.createElement('td');
            const visiblityAction =  post['status'] === 'published' ? 'Unpublish' : 'Publish';
            visibilityTd.innerHTML += `
                <form method="post" action="../api/toggle_article_visibility.php" style="display:inline;">
                    <input type="hidden" name="uuid" value="${post['uuid']}">
                    <button type="submit">${visiblityAction}</button>
                </form>
            `;
            row.appendChild(visibilityTd);

            const deleteTd = document.createElement('td');
            deleteTd.innerHTML += `
                <form method="post" action="../api/delete_article.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this article?');">
                    <input type="hidden" name="uuid" value="${post['uuid']}">
                     <button type="submit">Delete</button>
                </form>
            `;
            row.appendChild(deleteTd);
            setupPostRow(row);

            postTableBody.appendChild(row);
        });

        if (data['more_exists'] === false) {
            const loadMoreButton = document.getElementById("load-more-posts");
            loadMoreButton.remove();
        }
    }
}

document.querySelectorAll('input[name="post-sorting"]').forEach(radio => {
    radio.addEventListener('change', sortPosts);
});

document.querySelector('select[name="post-sorting-order"]').addEventListener('change', sortPosts);

async function sortPosts() {
    const postRows = document.querySelectorAll('tr.post-row');
    const totalPostsLoaded = postRows.length;

    const formData = new FormData();
    formData.append('start_index', 0);
    formData.append('end_index', totalPostsLoaded);

    const sortType = document.querySelector('input[name="post-sorting"]:checked');
    const sortOrder = document.querySelector('select[name="post-sorting-order"]');
    if (!sortType || !sortOrder) 
        return;

    formData.append('sort_type', sortType.value);
    formData.append('sort_order', sortOrder.value);

    formData.append('only_from_user', true);

    const response = await fetch('../api/post_pagination.php', {
        method: 'POST',
        body: formData
    });

    const postTableBody = postRows[0].parentElement;

    if (response.ok) {
        const data = await response.json();
        const newPosts = data['posts'];

        postRows.forEach((post) => {
            post.remove();
        });

        newPosts.forEach((post) => {
            const row = document.createElement('tr');
            row.className = "post-row";

            const titleTd = document.createElement('td');
            titleTd.textContent = post.title;
            row.appendChild(titleTd);

            const statusTd = document.createElement('td');
            statusTd.textContent = post['status'] === 'published' ? 'Published' : 'Draft';
            row.appendChild(statusTd);

            const editTd = document.createElement('td');
            editTd.innerHTML += `<a href="../pages/edit.php?uuid=${post['uuid']}">Edit</a>`;
            row.appendChild(editTd);

            const visibilityTd = document.createElement('td');
            const visiblityAction =  post['status'] === 'published' ? 'Unpublish' : 'Publish';
            visibilityTd.innerHTML += `
                <form method="post" action="../api/toggle_article_visibility.php" style="display:inline;">
                    <input type="hidden" name="uuid" value="${post['uuid']}">
                    <button type="submit">${visiblityAction}</button>
                </form>
            `;
            row.appendChild(visibilityTd);

            const deleteTd = document.createElement('td');
            deleteTd.innerHTML += `
                <form method="post" action="../api/delete_article.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this article?');">
                    <input type="hidden" name="uuid" value="${post['uuid']}">
                     <button type="submit">Delete</button>
                </form>
            `;
            row.appendChild(deleteTd);
            setupPostRow(row);

            postTableBody.appendChild(row);
        });

        const loadMoreButton = document.getElementById("load-more-posts");
        if (data['more_exists'] === false && loadMoreButton) {
            loadMoreButton.remove();
        }
    }
}