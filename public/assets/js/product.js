document.addEventListener('DOMContentLoaded', () => {
    const API_BASE_URL = '../api/routes.php';
    const productContainer = document.getElementById('product-detail-container');
    const linkedProductSection = document.getElementById('linked-product-section');
    const linkedProductTitle = document.getElementById('linked-product-title');
    const linkedProductCard = document.getElementById('linked-product-card');

    const getProductId = () => new URLSearchParams(window.location.search).get('id');

    const renderProduct = (product) => {
        document.title = `${product.name} - Perfume Shop`;

        const discountPrice = parseFloat(product.discount_price);
        const originalPrice = parseFloat(product.price);

        let galleryThumbnails = `<img src="../${product.main_image_url}" alt="${product.name}" class="w-full h-20 object-cover rounded-lg cursor-pointer border-2 border-indigo-500 thumbnail">`;
        product.images.forEach(img => {
            galleryThumbnails += `<img src="../${img.image_url}" alt="${img.alt_text || product.name}" class="w-full h-20 object-cover rounded-lg cursor-pointer thumbnail">`;
        });

        let attributesHtml = '';
        if (product.attributes && product.attributes.length > 0) {
            product.attributes.forEach(attr => {
                attributesHtml += `<li class="flex justify-between py-2 border-b"><span class="font-semibold text-gray-600">${attr.attribute_key}</span><span class="text-gray-800">${attr.attribute_value}</span></li>`;
            });
        } else {
            attributesHtml = '<p>No scent notes available.</p>';
        }

        const productHtml = `
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Image Gallery -->
                <div>
                    <div class="main-image-container mb-4 rounded-lg shadow-lg bg-white">
                        <img id="main-product-image" src="../${product.main_image_url}" alt="${product.name}" class="w-full h-auto object-cover rounded-lg">
                    </div>
                    <div class="grid grid-cols-5 gap-4">
                        ${galleryThumbnails}
                    </div>
                </div>

                <!-- Product Details -->
                <div class="bg-white p-8 rounded-lg shadow-lg">
                    <a href="shop.html?brand=${product.brand_id}" class="text-gray-500 hover:text-indigo-600 uppercase text-sm font-semibold tracking-wider">${product.brand_name}</a>
                    <h1 class="text-4xl font-bold text-gray-800 mt-2">${product.name}</h1>
                    <div class="mt-4">
                        <span class="text-4xl font-bold text-indigo-600">$${discountPrice > 0 ? discountPrice.toFixed(2) : originalPrice.toFixed(2)}</span>
                        ${discountPrice > 0 ? `<span class="text-xl text-gray-400 line-through ml-3">$${originalPrice.toFixed(2)}</span>` : ''}
                    </div>
                    <div class="mt-6">
                        <button class="w-full px-8 py-4 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors duration-300 add-to-cart-btn flex items-center justify-center" data-id="${product.id}">
                            <i class="fas fa-shopping-cart mr-3"></i> Add to Cart
                        </button>
                    </div>
                    <div class="mt-8">
                        <h3 class="font-bold text-xl text-gray-800 mb-4 border-b pb-2">Description</h3>
                        <p class="text-gray-600 leading-relaxed">${product.description}</p>
                    </div>
                    <div class="mt-8">
                        <h3 class="font-bold text-xl text-gray-800 mb-4 border-b pb-2">Scent Notes</h3>
                        <ul class="space-y-2">${attributesHtml}</ul>
                    </div>
                </div>
            </div>
        `;
        productContainer.innerHTML = productHtml;

        // Render linked product(s) if they exist
        if (product.linked_product) { // Inspired product -> single original
            renderLinkedProduct(product.product_type, [product.linked_product]);
        } else if (product.linked_products && product.linked_products.length > 0) { // Original product -> multiple inspired
            renderLinkedProduct(product.product_type, product.linked_products);
        }

        // Add event listeners for gallery
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.addEventListener('click', (e) => {
                document.getElementById('main-product-image').src = e.target.src;
                document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('border-indigo-500'));
                e.target.classList.add('border-indigo-500');
            });
        });
    };

    const renderLinkedProduct = (type, linkedProducts) => {
        let title = '';
        if (type === 'inspired') {
            title = 'Original By';
        } else if (type === 'original') {
            title = 'Inspired By';
        }
        linkedProductTitle.textContent = title;

        let cardsHtml = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">';
        linkedProducts.forEach(product => {
            cardsHtml += `
                <a href="product.html?id=${product.id}" class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:-translate-y-1 transition-transform duration-300">
                    <img src="../${product.main_image_url}" alt="${product.name}" class="w-full h-48 object-cover">
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-800 truncate">${product.name}</h3>
                        <p class="text-indigo-600 font-semibold mt-2">View Product <i class="fas fa-arrow-right ml-1"></i></p>
                    </div>
                </a>
            `;
        });
        cardsHtml += '</div>';

        linkedProductCard.innerHTML = cardsHtml;
        linkedProductSection.classList.remove('hidden');
    };

    const fetchProductDetails = async () => {
        const productId = getProductId();
        if (!productId) {
            productContainer.innerHTML = '<p class="text-center text-red-500">Product ID is missing. Please select a product from the shop.</p>';
            return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}?resource=products&action=read_one&id=${productId}`);
            const result = await response.json();

            if (result.status === 'success') {
                renderProduct(result.data);
            } else {
                productContainer.innerHTML = `<p class="text-center text-red-500">Error: ${result.message}</p>`;
            }
        } catch (error) {
            console.error('Failed to fetch product details:', error);
            productContainer.innerHTML = '<p class="text-center text-red-500">An error occurred while fetching product data.</p>';
        }
    };

    fetchProductDetails();
});
