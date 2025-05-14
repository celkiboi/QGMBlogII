function setupUserRow(row) {
    const select = row.querySelector('select[name="role"]');
    if (!select) return;

    if (!select.dataset.original) {
        select.dataset.original = select.value;
    }

    select.addEventListener('change', () => {
        let actionCell = row.querySelector('.update-action-cell');

        if (select.value !== select.dataset.original) {
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
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('tr.user-row').forEach(setupUserRow);
});


async function loadMoreUsers() {
    const userRows = document.querySelectorAll('tr.user-row');
    var totalUsersLoaded = userRows.length;

    const usersToLoad = 10;

    const formData = new FormData();
    formData.append('start_index', totalUsersLoaded);
    formData.append('end_index', totalUsersLoaded + usersToLoad);

    const sortType = document.querySelector('input[name="user-sorting"]:checked');
    const sortOrder = document.querySelector('select[name="user-sorting-order"]');
    if (!sortType || !sortOrder) 
        return;

    formData.append('sort_type', sortType.value);
    formData.append('sort_order', sortOrder.value);

    const response = await fetch('../api/user_pagination.php', {
        method: 'POST',
        body: formData
    });

    const userTableBody = userRows[0].parentElement;

    if (response.ok) {
        const data = await response.json();
        const newUsers = data['users'];

        newUsers.forEach(user => {
            const row = document.createElement('tr');
            row.className = 'user-row';
            row.id = `user-${user.id}`;

            const usernameTd = document.createElement('td');
            usernameTd.textContent = user.username;
            row.appendChild(usernameTd);

            const roleTd = document.createElement('td');
            if (user.role === 'admin') {
                roleTd.textContent = 'Admin';
            } else {
                const select = document.createElement('select');
                select.name = 'role';

                ['regular', 'staff'].forEach(role => {
                    const option = document.createElement('option');
                    option.value = role;
                    option.textContent = role.charAt(0).toUpperCase() + role.slice(1);
                    if (user.role === role) option.selected = true;
                    select.appendChild(option);
                });

                roleTd.appendChild(select);
            }
            row.appendChild(roleTd);

            const createdTd = document.createElement('td');
            createdTd.textContent = user.created_at;
            row.appendChild(createdTd);

            userTableBody.appendChild(row);
            setupUserRow(row);

            totalUsersLoaded++;
        });

        if (data['more_exists'] === false) {
            const loadMoreButton = document.getElementById("load-more-users");
            loadMoreButton.remove();
        }
    }

}

async function sortUsers() {
    const userRows = document.querySelectorAll('tr.user-row');
    var totalUsersLoaded = userRows.length;

    const formData = new FormData();
    formData.append('start_index', 0);
    formData.append('end_index', totalUsersLoaded);

    const sortType = document.querySelector('input[name="user-sorting"]:checked');
    const sortOrder = document.querySelector('select[name="user-sorting-order"]');
    if (!sortType || !sortOrder) 
        return;

    formData.append('sort_type', sortType.value);
    formData.append('sort_order', sortOrder.value);

    const response = await fetch('../api/user_pagination.php', {
        method: 'POST',
        body: formData
    });

    if (response.ok) {
        const data = await response.json();
        const newUsers = data['users'];

        const userTableBody = userRows[0].parentElement;
        userRows.forEach(userRow => {
            userRow.remove();
        })

        newUsers.forEach(user => {
            const row = document.createElement('tr');
            row.className = 'user-row';
            row.id = `user-${user.id}`;

            const usernameTd = document.createElement('td');
            usernameTd.textContent = user.username;
            row.appendChild(usernameTd);

            const roleTd = document.createElement('td');
            if (user.role === 'admin') {
                roleTd.textContent = 'Admin';
            } else {
                const select = document.createElement('select');
                select.name = 'role';

                ['regular', 'staff'].forEach(role => {
                    const option = document.createElement('option');
                    option.value = role;
                    option.textContent = role.charAt(0).toUpperCase() + role.slice(1);
                    if (user.role === role) option.selected = true;
                    select.appendChild(option);
                });

                roleTd.appendChild(select);
            }
            row.appendChild(roleTd);

            const createdTd = document.createElement('td');
            createdTd.textContent = user.created_at;
            row.appendChild(createdTd);

            userTableBody.appendChild(row);
            setupUserRow(row);

            totalUsersLoaded++;
        });

        const loadMoreButton = document.getElementById("load-more-users");
        if (data['more_exists'] === false && loadMoreButton) {
            loadMoreButton.remove();
        }
    }
}