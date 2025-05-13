document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('tr.user-row').forEach(row => {
        const select = row.querySelector('select[name="role"]');
        if (!select) return;

        const originalValue = select.dataset.original;

        select.addEventListener('change', () => {
            let actionCell = row.querySelector('.update-action-cell');

            if (select.value !== originalValue) {
                if (!actionCell) {
                    actionCell = document.createElement('td');
                    actionCell.className = 'update-action-cell';

                    const button = document.createElement('button');
                    button.textContent = 'Update';
                    button.className = 'update-role-btn';

                    button.addEventListener('click', async () => {
                        const userId = row.id.replace('user-', '');
                        const newRole = select.value;

                        const formData = new FormData();
                        formData.append('user_id', userId);
                        formData.append('new_role', newRole);

                        const response = await fetch('../api/update_user_role.php', {
                            method: 'POST',
                            body: formData
                        });

                        if (response.ok) {
                            alert('Role updated successfully!');
                            row.removeChild(actionCell);
                            select.dataset.original = newRole;
                        } else {
                            alert('Failed to update role.');
                        }
                    });

                    actionCell.appendChild(button);
                    row.appendChild(actionCell);
                }
            } else if (actionCell) {
                row.removeChild(actionCell);
            }
        });
    });
});