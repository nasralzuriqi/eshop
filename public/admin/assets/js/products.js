document.addEventListener('DOMContentLoaded', () => {
    // --- Element Selectors ---
    const addProductBtn = document.getElementById('add-product-btn');
    const productModal = document.getElementById('product-modal');
    const cancelBtn = document.getElementById('cancel-btn');
    const productForm = document.getElementById('product-form');
    const modalTitle = document.getElementById('modal-title');
    const tableBody = document.getElementById('products-table-body');
    const addAttributeBtn = document.getElementById('add-attribute-btn');
    const attributesContainer = document.getElementById('attributes-container');
    const productTypeSelect = document.getElementById('product_type');
    const inspiredByContainer = document.getElementById('inspired-by-container');
    const linkedProductLabel = document.getElementById('linked-product-label');

    const API_BASE_URL = '../../api/routes.php';

    // --- Helper Functions ---

    // Generic fetch function for populating select dropdowns
    const populateSelect = async (url, selectId, valueField, textField) => {
        const select = document.getElementById(selectId);
        select.innerHTML = '<option value="">Select an option</option>'; // Default option
        try {
            const response = await fetch(url);
            const result = await response.json();
            if (result.status === 'success') {
                result.data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item[valueField];
                    option.textContent = item[textField];
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error(`Failed to populate ${selectId}:`, error);
        }
    };

    // --- Event Listeners & Initial Load ---

    // Fetch and display all products on page load
    const fetchProducts = async () => {
        try {
            const response = await fetch(`${API_BASE_URL}?resource=products&action=read`);
            const result = await response.json();
            tableBody.innerHTML = ''; // Clear existing rows

            if (result.status === 'success') {
                result.data.forEach(product => {
                    const row = `
                        <tr data-id="${product.id}">
                            <td class="px-6 py-4"><img src="../../${product.main_image_url || 'assets/placeholder.png'}" class="w-16 h-16 object-cover rounded"></td>
                            <td class="px-6 py-4 font-medium">${product.name}</td>
                            <td class="px-6 py-4">${product.sku}</td>
                            <td class="px-6 py-4">$${product.price}</td>
                            <td class="px-6 py-4">${product.stock_quantity}</td>
                            <td class="px-6 py-4 text-sm font-medium">
                                <button class="text-indigo-600 hover:text-indigo-900 edit-btn">Edit</button>
                                <button class="text-red-600 hover:text-red-900 ml-4 delete-btn">Delete</button>
                            </td>
                        </tr>
                    `;
                    tableBody.innerHTML += row;
                });
            }
        } catch (error) {
            console.error('Failed to fetch products:', error);
            Swal.fire('Error', 'Could not load products from the server.', 'error');
        }
    };

    // Add a new attribute row to the form
    const addAttributeRow = (key = '', value = '') => {
        const div = document.createElement('div');
        div.className = 'flex items-center space-x-2 attribute-row';
        div.innerHTML = `
            <input type="text" name="attributes[][key]" placeholder="Attribute Name (e.g., Top Note)" class="flex-1 border-gray-300 rounded-md shadow-sm" value="${key}">
            <input type="text" name="attributes[][value]" placeholder="Attribute Value (e.g., Bergamot)" class="flex-1 border-gray-300 rounded-md shadow-sm" value="${value}">
            <button type="button" class="text-red-500 remove-attribute-btn">Remove</button>
        `;
        attributesContainer.appendChild(div);
    };

    // Show/hide the 'Inspired By' dropdown and change label based on product type
    productTypeSelect.addEventListener('change', () => {
        const selectedType = productTypeSelect.value;
        if (selectedType === 'inspired') {
            inspiredByContainer.classList.remove('hidden');
            linkedProductLabel.textContent = 'Inspired By (Link to Original)';
        } else if (selectedType === 'original') {
            inspiredByContainer.classList.remove('hidden');
            linkedProductLabel.textContent = 'Link Product';
        } else {
            inspiredByContainer.classList.add('hidden');
        }
    });

    // Handle adding and removing attribute rows
    addAttributeBtn.addEventListener('click', () => addAttributeRow());
    attributesContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-attribute-btn')) {
            e.target.closest('.attribute-row').remove();
        }
    });

    // Open the modal for a new product
    addProductBtn.addEventListener('click', () => {
        productForm.reset();
        document.getElementById('product-id').value = '';
        modalTitle.textContent = 'Add New Product';
        attributesContainer.innerHTML = '';
        document.getElementById('main-image-preview').classList.add('hidden');
        document.getElementById('gallery-preview-container').innerHTML = '';
        productModal.classList.remove('hidden');
    });

    // Close the modal
    cancelBtn.addEventListener('click', () => {
        productModal.classList.add('hidden');
    });

    // Handle form submission (Create or Update)
    productForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('product-id').value;
        const url = id ? `${API_BASE_URL}?resource=products&action=update` : `${API_BASE_URL}?resource=products&action=create`;
        
        const formData = new FormData();
        // Append standard form fields
        formData.append('id', id);
        formData.append('name', document.getElementById('name').value);
        formData.append('sku', document.getElementById('sku').value);
        formData.append('price', document.getElementById('price').value);
        formData.append('stock_quantity', document.getElementById('stock_quantity').value);
        formData.append('brand_id', document.getElementById('brand_id').value);
        formData.append('category_id', document.getElementById('category_id').value);
        formData.append('description', document.getElementById('description').value);
        formData.append('product_type', document.getElementById('product_type').value);
        formData.append('linked_product_id', document.getElementById('linked_product_id').value);

        // Append attributes
        document.querySelectorAll('.attribute-row').forEach((row, index) => {
            const key = row.querySelector('input[name*="key"]').value;
            const value = row.querySelector('input[name*="value"]').value;
            if (key && value) {
                formData.append(`attributes[${index}][key]`, key);
                formData.append(`attributes[${index}][value]`, value);
            }
        });

        // Append files
        const mainImage = document.getElementById('main_image').files[0];
        if (mainImage) formData.append('main_image', mainImage);

        const galleryImages = document.getElementById('gallery_images').files;
        for (let i = 0; i < galleryImages.length; i++) {
            formData.append('gallery_images[]', galleryImages[i]);
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.status === 'success') {
                Swal.fire('Success!', result.message, 'success');
                productModal.classList.add('hidden');
                fetchProducts(); // Refresh the list
            } else {
                Swal.fire('Error!', result.message, 'error');
            }
        } catch (error) {
            console.error('Form submission error:', error);
            Swal.fire('Error!', 'An unexpected error occurred.', 'error');
        }
    });

    // Handle Edit and Delete button clicks
    tableBody.addEventListener('click', async (e) => {
        const target = e.target;
        const row = target.closest('tr');
        if (!row) return;
        const id = row.dataset.id;

        // Handle Edit
        if (target.classList.contains('edit-btn')) {
            try {
                const response = await fetch(`${API_BASE_URL}?resource=products&action=read_one&id=${id}`);
                const result = await response.json();
                if (result.status === 'success') {
                    const product = result.data;
                    // Populate the form
                    productForm.reset();
                    attributesContainer.innerHTML = '';
                    document.getElementById('gallery-preview-container').innerHTML = '';

                    document.getElementById('product-id').value = product.id;
                    document.getElementById('name').value = product.name;
                    document.getElementById('sku').value = product.sku;
                    document.getElementById('price').value = product.price;
                    document.getElementById('stock_quantity').value = product.stock_quantity;
                    document.getElementById('description').value = product.description;
                    document.getElementById('brand_id').value = product.brand_id;
                    document.getElementById('category_id').value = product.category_id;
                    document.getElementById('product_type').value = product.product_type;
                    
                    // Handle 'Inspired By' and 'Linked Product'
                    const productType = product.product_type;
                    if (productType === 'inspired') {
                        inspiredByContainer.classList.remove('hidden');
                        linkedProductLabel.textContent = 'Inspired By (Link to Original)';
                        document.getElementById('linked_product_id').value = product.linked_product_id;
                    } else if (productType === 'original') {
                        inspiredByContainer.classList.remove('hidden');
                        linkedProductLabel.textContent = 'Link Product';
                        document.getElementById('linked_product_id').value = product.linked_product_id;
                    } else {
                        inspiredByContainer.classList.add('hidden');
                    }

                    // Show main image preview
                    const mainImagePreview = document.getElementById('main-image-preview');
                    if (product.main_image_url) {
                        mainImagePreview.src = `../../${product.main_image_url}`;
                        mainImagePreview.classList.remove('hidden');
                    } else {
                        mainImagePreview.classList.add('hidden');
                    }

                    // Populate attributes
                    product.attributes.forEach(attr => addAttributeRow(attr.attribute_key, attr.attribute_value));

                    // Show gallery previews
                    const galleryContainer = document.getElementById('gallery-preview-container');
                    product.images.forEach(img => {
                        galleryContainer.innerHTML += `
                            <div class="relative">
                                <img src="../../${img.image_url}" class="w-20 h-20 object-cover rounded">
                                <button type="button" class="absolute top-0 right-0 bg-red-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center delete-gallery-img-btn" data-img-id="${img.id}">&times;</button>
                            </div>
                        `;
                    });

                    modalTitle.textContent = `Edit Product: ${product.name}`;
                    productModal.classList.remove('hidden');
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            } catch (error) {
                console.error('Failed to fetch product details:', error);
                Swal.fire('Error', 'Could not load product details.', 'error');
            }
        }

        // Handle Delete
        if (target.classList.contains('delete-btn')) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch(`${API_BASE_URL}?resource=products&action=delete`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: id })
                        });
                        const deleteResult = await response.json();
                        if (deleteResult.status === 'success') {
                            Swal.fire('Deleted!', 'The product has been deleted.', 'success');
                            fetchProducts(); // Refresh list
                        } else {
                            Swal.fire('Error', deleteResult.message, 'error');
                        }
                    } catch (error) {
                        console.error('Delete error:', error);
                        Swal.fire('Error', 'An unexpected error occurred during deletion.', 'error');
                    }
                }
            });
        }
    });

    // --- Initial Data Loading ---
    populateSelect(`${API_BASE_URL}?resource=brands`, 'brand_id', 'id', 'name');
    populateSelect(`${API_BASE_URL}?resource=categories`, 'category_id', 'id', 'name');
    // For the 'Inspired By' dropdown, we need to fetch only 'original' products.
    // I will add a filter to the 'read_products.php' controller to handle this.
    populateSelect(`${API_BASE_URL}?resource=products&action=read&type=original`, 'linked_product_id', 'id', 'name');
    fetchProducts();
});
