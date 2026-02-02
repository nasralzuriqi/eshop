document.addEventListener('DOMContentLoaded', () => {
    const API_BASE_URL = '../api/routes.php';
    const productsGrid = document.getElementById('shop-products-grid');
    const categoryFilter = document.getElementById('category-filter');
    const brandFilter = document.getElementById('brand-filter');
    const shopTitle = document.querySelector('main h1');

    let allProducts = [];
    const activeFilters = {
        category: 'all',
        brand: 'all',
        search: ''
    };

    const renderProducts = (products) => {
        productsGrid.innerHTML = '';
        if (products.length === 0) {
            productsGrid.innerHTML = '<p class="col-span-full text-center text-gray-500">No products found matching your criteria.</p>';
            return;
        }
        products.forEach(product => {
            const card = `
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <a href="product.html?id=${product.id}">
                        <img src="../${product.main_image_url}" class="w-full h-56 object-cover">
                    </a>
                    <div class="p-4">
                        <h3 class="text-lg font-semibold">${product.name}</h3>
                        <p class="text-gray-500">${product.brand_name}</p>
                        <div class="flex justify-between items-center mt-4">
                            <span class="text-xl font-bold text-indigo-600">$${product.price}</span>
                            <button class="px-3 py-1 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 add-to-cart-btn" data-id="${product.id}">Add to Cart</button>
                        </div>
                    </div>
                </div>
            `;
            productsGrid.innerHTML += card;
        });
    };

    const applyFilters = () => {
        let filtered = [...allProducts];

        // Apply search filter
        if (activeFilters.search) {
            const lowercasedQuery = activeFilters.search.toLowerCase();
            filtered = filtered.filter(p =>
                p.name.toLowerCase().includes(lowercasedQuery) ||
                p.brand_name.toLowerCase().includes(lowercasedQuery)
            );
        }

        // Apply category filter
        if (activeFilters.category !== 'all') {
            filtered = filtered.filter(p => p.category_id == activeFilters.category);
        }

        // Apply brand filter
        if (activeFilters.brand !== 'all') {
            filtered = filtered.filter(p => p.brand_id == activeFilters.brand);
        }

        renderProducts(filtered);
    };

    const fetchAndRenderFilters = async () => {
        // Categories
        const catRes = await fetch(`${API_BASE_URL}?resource=categories`);
        const catData = await catRes.json();
        categoryFilter.innerHTML = '<li><a href="#" class="filter-link active" data-type="category" data-id="all">All Categories</a></li>';
        if (catData.status === 'success') {
            catData.data.forEach(cat => {
                categoryFilter.innerHTML += `<li><a href="#" class="filter-link" data-type="category" data-id="${cat.id}">${cat.name}</a></li>`;
            });
        }

        // Brands
        const brandRes = await fetch(`${API_BASE_URL}?resource=brands`);
        const brandData = await brandRes.json();
        brandFilter.innerHTML = '<li><a href="#" class="filter-link active" data-type="brand" data-id="all">All Brands</a></li>';
        if (brandData.status === 'success') {
            brandData.data.forEach(brand => {
                brandFilter.innerHTML += `<li><a href="#" class="filter-link" data-type="brand" data-id="${brand.id}">${brand.name}</a></li>`;
            });
        }
    };

    const fetchAllProducts = async () => {
        const response = await fetch(`${API_BASE_URL}?resource=products&action=read`);
        const result = await response.json();
        if (result.status === 'success') {
            allProducts = result.data;
            // Check for search query from URL
            const urlParams = new URLSearchParams(window.location.search);
            const searchQuery = urlParams.get('search');
            if (searchQuery) {
                activeFilters.search = searchQuery;
                shopTitle.textContent = `Search Results for "${searchQuery}"`;
            }
            applyFilters();
        }
    };

    document.querySelector('.container').addEventListener('click', (e) => {
        if (e.target.classList.contains('filter-link')) {
            e.preventDefault();
            const type = e.target.dataset.type;
            const id = e.target.dataset.id;

            document.querySelectorAll(`[data-type='${type}']`).forEach(el => el.classList.remove('active'));
            e.target.classList.add('active');

            activeFilters[type] = id;
            applyFilters();
        }
    });

    fetchAndRenderFilters();
    fetchAllProducts();
});
