document.addEventListener('DOMContentLoaded', () => {
    const API_URL = '../../api/routes.php?resource=categories';
    const tableBody = document.getElementById('categories-table-body');
    const modal = document.getElementById('category-modal');
    const form = document.getElementById('category-form');
    const modalTitle = document.getElementById('category-modal-title');
    const parentSelect = document.getElementById('parent_id');

    const fetchCategories = async () => {
        const res = await fetch(API_URL);
        const { data } = await res.json();
        tableBody.innerHTML = data.map(cat => `
            <tr data-id="${cat.id}" data-name="${cat.name}" data-slug="${cat.slug}" data-parent="${cat.parent_id || ''}">
                <td class="px-6 py-4">${cat.name}</td>
                <td class="px-6 py-4">${cat.slug}</td>
                <td class="px-6 py-4">${cat.parent_name || 'None'}</td>
                <td class="px-6 py-4">
                    <button class="text-indigo-600 edit-btn">Edit</button>
                    <button class="text-red-600 ml-4 delete-btn">Delete</button>
                </td>
            </tr>
        `).join('');
        
        // Populate parent category dropdown
        parentSelect.innerHTML = '<option value="">None</option>';
        data.forEach(cat => {
            parentSelect.innerHTML += `<option value="${cat.id}">${cat.name}</option>`;
        });
    };

    const openModal = (id = null, name = '', slug = '', parentId = '') => {
        document.getElementById('category-id').value = id;
        document.getElementById('category-name').value = name;
        document.getElementById('category-slug').value = slug;
        parentSelect.value = parentId;
        modalTitle.textContent = id ? 'Edit Category' : 'Add Category';
        modal.classList.remove('hidden');
    };

    document.getElementById('add-category-btn').addEventListener('click', () => openModal());
    document.getElementById('cancel-category-btn').addEventListener('click', () => modal.classList.add('hidden'));

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('category-id').value;
        const name = document.getElementById('category-name').value;
        const slug = document.getElementById('category-slug').value;
        const parent_id = parentSelect.value;
        const method = id ? 'PUT' : 'POST';
        const body = JSON.stringify({ id, name, slug, parent_id });

        await fetch(API_URL, { method, body, headers: { 'Content-Type': 'application/json' } });
        modal.classList.add('hidden');
        fetchCategories();
        Swal.fire('Success', `Category ${id ? 'updated' : 'created'}!`, 'success');
    });

    tableBody.addEventListener('click', async (e) => {
        const row = e.target.closest('tr');
        if (!row) return;
        const id = row.dataset.id;

        if (e.target.classList.contains('edit-btn')) {
            openModal(id, row.dataset.name, row.dataset.slug, row.dataset.parent);
        }
        if (e.target.classList.contains('delete-btn')) {
            Swal.fire({
                title: 'Delete this category?',
                icon: 'warning',
                showCancelButton: true,
            }).then(async (result) => {
                if (result.isConfirmed) {
                    await fetch(API_URL, { method: 'DELETE', body: JSON.stringify({ id }), headers: { 'Content-Type': 'application/json' } });
                    fetchCategories();
                    Swal.fire('Deleted!', 'Category has been deleted.', 'success');
                }
            });
        }
    });

    fetchCategories();
});
