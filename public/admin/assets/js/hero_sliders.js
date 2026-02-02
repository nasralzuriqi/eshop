document.addEventListener('DOMContentLoaded', () => {
    const API_URL = '../../api/routes.php?resource=ui&action=hero_sliders';
    const container = document.getElementById('sliders-container');
    const modal = document.getElementById('slider-modal');
    const form = document.getElementById('slider-form');

    const fetchSliders = async () => {
        const res = await fetch(API_URL);
        const { data } = await res.json();
        container.innerHTML = data.map(slider => `
            <div class="bg-white p-4 rounded-lg shadow-md">
                <img src="../../${slider.image_url}" class="w-full h-40 object-cover rounded-md mb-4">
                <h4 class="font-bold">${slider.title}</h4>
                <p class="text-sm text-gray-600">Order: ${slider.sort_order} | ${slider.is_active ? 'Active' : 'Inactive'}</p>
                <div class="mt-4">
                    <button class="text-sm text-indigo-600 edit-btn" data-slider='${JSON.stringify(slider)}'>Edit</button>
                    <button class="text-sm text-red-600 ml-2 delete-btn" data-id="${slider.id}">Delete</button>
                </div>
            </div>
        `).join('');
    };

    const openModal = (slider = null) => {
        form.reset();
        document.getElementById('slider-id').value = slider ? slider.id : '';
        document.getElementById('title').value = slider ? slider.title : '';
        document.getElementById('subtitle').value = slider ? slider.subtitle : '';
        document.getElementById('btn_text').value = slider ? slider.btn_text : '';
        document.getElementById('btn_link').value = slider ? slider.btn_link : '';
        document.getElementById('sort_order').value = slider ? slider.sort_order : 0;
        document.getElementById('is_active').checked = slider ? slider.is_active : false;
        document.getElementById('current-image').src = slider ? `../../${slider.image_url}` : '';
        document.getElementById('current-image').classList.toggle('hidden', !slider);
        document.getElementById('slider-modal-title').textContent = slider ? 'Edit Slider' : 'Add New Slider';
        modal.classList.remove('hidden');
    };

    document.getElementById('add-slider-btn').addEventListener('click', () => openModal());
    document.getElementById('cancel-slider-btn').addEventListener('click', () => modal.classList.add('hidden'));

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        if (!document.getElementById('is_active').checked) {
            formData.append('is_active', '0');
        }

        const res = await fetch(API_URL, { method: 'POST', body: formData });
        const result = await res.json();

        if (result.status === 'success') {
            modal.classList.add('hidden');
            fetchSliders();
            Swal.fire('Success', result.message, 'success');
        } else {
            Swal.fire('Error', result.message, 'error');
        }
    });

    container.addEventListener('click', async (e) => {
        if (e.target.classList.contains('edit-btn')) {
            const sliderData = JSON.parse(e.target.dataset.slider);
            openModal(sliderData);
        }
        if (e.target.classList.contains('delete-btn')) {
            const id = e.target.dataset.id;
            Swal.fire({
                title: 'Delete this slider?',
                icon: 'warning',
                showCancelButton: true,
            }).then(async (result) => {
                if (result.isConfirmed) {
                    await fetch(API_URL, { method: 'DELETE', body: JSON.stringify({ id }), headers: { 'Content-Type': 'application/json' } });
                    fetchSliders();
                    Swal.fire('Deleted!', 'Slider has been deleted.', 'success');
                }
            });
        }
    });

    fetchSliders();
});
