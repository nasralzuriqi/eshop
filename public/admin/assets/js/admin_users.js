document.addEventListener('DOMContentLoaded', () => {
    const API_URL = '../../api/routes.php?resource=admin_users';
    const tableBody = document.getElementById('users-table-body');
    const modal = document.getElementById('user-modal');
    const form = document.getElementById('user-form');

    const fetchUsers = async () => {
        const res = await fetch(API_URL);
        const { data } = await res.json();
        tableBody.innerHTML = data.map(user => `
            <tr>
                <td class="px-6 py-4">${user.username}</td>
                <td class="px-6 py-4">${user.email}</td>
                <td class="px-6 py-4">${user.role}</td>
                <td class="px-6 py-4">
                    <button class="text-indigo-600 edit-btn" data-user='${JSON.stringify(user)}'>Edit</button>
                    ${user.id != 1 ? `<button class="text-red-600 ml-4 delete-btn" data-id="${user.id}">Delete</button>` : ''}
                </td>
            </tr>
        `).join('');
    };

    const openModal = (user = null) => {
        form.reset();
        document.getElementById('user-id').value = user ? user.id : '';
        document.getElementById('username').value = user ? user.username : '';
        document.getElementById('email').value = user ? user.email : '';
        document.getElementById('full_name').value = user ? user.full_name : '';
        document.getElementById('role').value = user ? user.role : 'editor';
        document.getElementById('user-modal-title').textContent = user ? 'Edit User' : 'Add New User';
        modal.classList.remove('hidden');
    };

    document.getElementById('add-user-btn').addEventListener('click', () => openModal());
    document.getElementById('cancel-user-btn').addEventListener('click', () => modal.classList.add('hidden'));

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        const res = await fetch(API_URL, { method: 'POST', body: formData });
        const result = await res.json();

        if (result.status === 'success') {
            modal.classList.add('hidden');
            fetchUsers();
            Swal.fire('Success', result.message, 'success');
        } else {
            Swal.fire('Error', result.message, 'error');
        }
    });

    tableBody.addEventListener('click', async (e) => {
        if (e.target.classList.contains('edit-btn')) {
            openModal(JSON.parse(e.target.dataset.user));
        }
        if (e.target.classList.contains('delete-btn')) {
            const id = e.target.dataset.id;
            Swal.fire({
                title: 'Delete this user?',
                icon: 'warning',
                showCancelButton: true,
            }).then(async (result) => {
                if (result.isConfirmed) {
                    await fetch(API_URL, { method: 'DELETE', body: JSON.stringify({ id }), headers: { 'Content-Type': 'application/json' } });
                    fetchUsers();
                    Swal.fire('Deleted!', 'User has been deleted.', 'success');
                }
            });
        }
    });

    fetchUsers();
});
