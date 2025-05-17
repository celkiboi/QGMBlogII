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
                    const statusCell = row.querySelector('td:nth-child(2)');
                    statusCell.textContent = data.new_status.charAt(0).toUpperCase() + data.new_status.slice(1);

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

    document.querySelectorAll('form[action$="delete_article.php"]').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const uuidInput = form.querySelector('input[name="uuid"]');
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
                    const tableBody = form.parentNode.parentNode.parentNode;
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
                        const row = form.parentNode.parentNode;
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
    });
});