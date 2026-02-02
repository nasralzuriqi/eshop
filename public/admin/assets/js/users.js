document.addEventListener('DOMContentLoaded', () => {
    const API_BASE_URL = '../../api/routes.php';
    const tableBody = document.getElementById('users-table-body');

    const fetchUsers = async () => {
        const response = await fetch(`${API_BASE_URL}?resource=users&action=read`);
        const result = await response.json();

        tableBody.innerHTML = '';
        if (result.status === 'success') {
            result.data.forEach(user => {
                const row = `
                    <tr>
                        <td class="px-6 py-4">${user.id}</td>
                        <td class="px-6 py-4">${user.full_name}</td>
                        <td class="px-6 py-4">${user.email}</td>
                        <td class="px-6 py-4">${user.phone || 'N/A'}</td>
                        <td class="px-6 py-4">${new Date(user.created_at).toLocaleDateString()}</td>
                    </tr>
                `;
                tableBody.innerHTML += row;
            });
        }
    };

    fetchUsers();
});
