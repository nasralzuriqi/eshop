document.addEventListener('DOMContentLoaded', () => {
    const API_BASE_URL = '../api/routes.php';
    const productContainer = document.getElementById('product-detail-container');

    const getProductId = () => {
        const params = new URLSearchParams(window.location.search);
        return params.get('id');
    };

    const fetchProductDetails = async () => {
        const productId = getProductId();
        if (!productId) {
            productContainer.innerHTML = '<p>Product not found.</p>';
            return;
        }

        const response = await fetch(`${API_BASE_URL}?resource=products&action=read_one&id=${productId}`);
        const result = await response.json();

        if (result.status === 'success') {
            const product = result.data;
            document.title = `${product.name} - Perfume Shop`;

            let galleryHtml = '';
            product.images.forEach(img => {
                galleryHtml += `<img src="../${img.image_url}" class="w-full h-auto object-cover cursor-pointer thumbnail">`;
            });

            let attributesHtml = '';
            product.attributes.forEach(attr => {
                attributesHtml += `<div class="border-b py-2"><span class="font-semibold">${attr.attribute_key}:</span> ${attr.attribute_value}</div>`;
            });

            let inspiredByHtml = '';
            if (product.linked_product) {
                inspiredByHtml = `
                    <div class="mt-6 bg-gray-100 p-4 rounded-lg flex items-center">
                        <img src="../${product.linked_product.main_image_url}" class="w-16 h-16 rounded-md object-cover mr-4">
                        <div>
                            <p class="text-sm text-gray-600">Inspired By</p>
                            <p class="font-semibold">${product.linked_product.name}</p>
                        </div>
                    </div>
                `;
            }

            const productHtml = `
                <!-- Left Side: Image Gallery -->
                <div>
                    <div class="mb-4">
                        <img id="main-product-image" src="../${product.main_image_url}" class="w-full h-auto object-cover rounded-lg shadow-md">
                    </div>
                    <div class="grid grid-cols-4 gap-2">
                        ${galleryHtml}
                    </div>
                </div>

                <!-- Right Side: Details -->
                <div>
                    <h1 class="text-4xl font-bold">${product.name}</h1>
                    <p class="text-gray-500 text-lg mt-2">${product.brand_name}</p>
                    <p class="text-3xl font-bold text-indigo-600 mt-4">$${product.price}</p>
                    <div class="mt-6">
                        <button class="w-full px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 add-to-cart-btn" data-id="${product.id}">Add to Cart</button>
                    </div>
                    ${inspiredByHtml}
                    <div class="mt-8">
                        <h3 class="font-bold text-xl mb-2">Description</h3>
                        <p class="text-gray-700">${product.description}</p>
                    </div>
                    <div class="mt-8">
                        <h3 class="font-bold text-xl mb-2">Scent Notes</h3>
                        <div class="space-y-2">${attributesHtml}</div>
                    </div>
                </div>
            `;
            productContainer.innerHTML = productHtml;

            // Add event listener for thumbnails
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.addEventListener('click', () => {
                    document.getElementById('main-product-image').src = thumb.src;
                });
            });

        } else {
            productContainer.innerHTML = `<p>${result.message}</p>`;
        }
    };

    fetchProductDetails();
});
