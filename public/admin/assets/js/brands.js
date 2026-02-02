document.addEventListener('DOMContentLoaded', () => {
    const API_URL = '../../api/routes.php?resource=brands';
    const tableBody = document.getElementById('brands-table-body');
    const modal = document.getElementById('brand-modal');
    const form = document.getElementById('brand-form');
    const modalTitle = document.getElementById('brand-modal-title');

    const fetchBrands = async () => {
        const res = await fetch(API_URL);
        const { data } = await res.json();
        tableBody.innerHTML = data.map(brand => `
            <tr data-id="${brand.id}">
                <td class="px-6 py-4">${brand.name}</td>
                <td class="px-6 py-4">
                    <button class="text-indigo-600 edit-btn">Edit</button>
                    <button class="text-red-600 ml-4 delete-btn">Delete</button>
                </td>
            </tr>
        `).join('');
    };

    const openModal = (id = null, name = '') => {
        document.getElementById('brand-id').value = id;
        document.getElementById('brand-name').value = name;
        modalTitle.textContent = id ? 'Edit Brand' : 'Add Brand';
        modal.classList.remove('hidden');
    };

    document.getElementById('add-brand-btn').addEventListener('click', () => openModal());
    document.getElementById('cancel-brand-btn').addEventListener('click', () => modal.classList.add('hidden'));

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('brand-id').value;
        const name = document.getElementById('brand-name').value;
        const method = id ? 'PUT' : 'POST';
        const body = JSON.stringify({ id, name });

        await fetch(API_URL, { method, body, headers: { 'Content-Type': 'application/json' } });
        modal.classList.add('hidden');
        fetchBrands();
        Swal.fire('Success', `Brand ${id ? 'updated' : 'created'}!`, 'success');
    });

    tableBody.addEventListener('click', async (e) => {
        const id = e.target.closest('tr').dataset.id;
        if (e.target.classList.contains('edit-btn')) {
            const name = e.target.closest('tr').children[0].textContent;
            openModal(id, name);
        }
        if (e.target.classList.contains('delete-btn')) {
            Swal.fire({
                title: 'Delete this brand?',
                icon: 'warning',
                showCancelButton: true,
            }).then(async (result) => {
                if (result.isConfirmed) {
                    await fetch(API_URL, { method: 'DELETE', body: JSON.stringify({ id }), headers: { 'Content-Type': 'application/json' } });
                    fetchBrands();
                    Swal.fire('Deleted!', 'Brand has been deleted.', 'success');
                }
            });
        }
    });

    fetchBrands();
});
