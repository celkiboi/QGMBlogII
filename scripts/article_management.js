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
});