document.addEventListener('DOMContentLoaded', () => {
    const API_BASE_URL = '../api/routes.php';

    // DOM Elements
    const productsGrid = document.getElementById('shop-products-grid');
    const categoryFilter = document.getElementById('category-filter');
    const brandFilter = document.getElementById('brand-filter');
    const typeFilter = document.getElementById('type-filter');
    const shopTitle = document.getElementById('shop-title');
    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('search-input');
    const sortSelect = document.getElementById('sort-select');
    const clearFiltersBtn = document.getElementById('clear-filters-btn');
    const loadingSpinner = document.getElementById('loading-spinner');

    let state = {
        category: 'all',
        brand: 'all',
        type: 'all',
        search: '',
        sort: 'created_at_desc'
    };

    const renderProducts = (products) => {
        productsGrid.innerHTML = '';
        if (!products || products.length === 0) {
            productsGrid.innerHTML = '<p class="col-span-full text-center text-gray-500 text-lg py-8">No products found matching your criteria.</p>';
            return;
        }
        products.forEach(product => {
            const discountPrice = parseFloat(product.discount_price);
            const originalPrice = parseFloat(product.price);

            const card = `
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:-translate-y-1 transition-transform duration-300">
                    <a href="product.html?id=${product.id}" class="block">
                        <img src="../${product.main_image_url}" alt="${product.name}" class="w-full h-64 object-cover">
                    </a>
                    <div class="p-4">
                        <p class="text-sm text-gray-500 mb-1">${product.brand_name}</p>
                        <h3 class="text-lg font-semibold text-gray-800 truncate">${product.name}</h3>
                        <div class="flex justify-between items-center mt-3">
                            <div>
                                <span class="text-xl font-bold text-indigo-600">$${discountPrice > 0 ? discountPrice.toFixed(2) : originalPrice.toFixed(2)}</span>
                                ${discountPrice > 0 ? `<span class="text-sm text-gray-500 line-through ml-2">$${originalPrice.toFixed(2)}</span>` : ''}
                            </div>
                            <button class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-full hover:bg-indigo-700 transition-colors duration-300 add-to-cart-btn" data-id="${product.id}">Add to Cart</button>
                        </div>
                    </div>
                </div>
            `;
            productsGrid.innerHTML += card;
        });
    };

    const fetchAndRenderFilters = async () => {
        try {
            const [catRes, brandRes] = await Promise.all([
                fetch(`${API_BASE_URL}?resource=categories`),
                fetch(`${API_BASE_URL}?resource=brands`)
            ]);
            const catData = await catRes.json();
            const brandData = await brandRes.json();

            categoryFilter.innerHTML = '<li><a href="#" class="filter-link text-gray-600 hover:text-indigo-600 transition-colors duration-200 active" data-type="category" data-id="all">All Categories</a></li>';
            if (catData.status === 'success') {
                catData.data.forEach(cat => {
                    categoryFilter.innerHTML += `<li><a href="#" class="filter-link text-gray-600 hover:text-indigo-600 transition-colors duration-200" data-type="category" data-id="${cat.id}">${cat.name}</a></li>`;
                });
            }

            brandFilter.innerHTML = '<li><a href="#" class="filter-link text-gray-600 hover:text-indigo-600 transition-colors duration-200 active" data-type="brand" data-id="all">All Brands</a></li>';
            if (brandData.status === 'success') {
                brandData.data.forEach(brand => {
                    brandFilter.innerHTML += `<li><a href="#" class="filter-link text-gray-600 hover:text-indigo-600 transition-colors duration-200" data-type="brand" data-id="${brand.id}">${brand.name}</a></li>`;
                });
            }
        } catch (error) {
            console.error('Failed to load filters:', error);
            categoryFilter.innerHTML = '<li class="text-red-500">Error loading categories.</li>';
            brandFilter.innerHTML = '<li class="text-red-500">Error loading brands.</li>';
        }
    };

    const fetchProducts = async () => {
        loadingSpinner.classList.remove('hidden');
        productsGrid.innerHTML = '';

        const queryParams = new URLSearchParams({
            resource: 'products',
            action: 'read',
            ...state
        });

        try {
            const response = await fetch(`${API_BASE_URL}?${queryParams}`);
            const result = await response.json();
            renderProducts(result.status === 'success' ? result.data : []);
        } catch (error) {
            console.error('Failed to fetch products:', error);
            renderProducts([]);
        } finally {
            loadingSpinner.classList.add('hidden');
        }
    };

    const updateURL = () => {
        const params = new URLSearchParams();
        Object.entries(state).forEach(([key, value]) => {
            if (value && value !== 'all' && (key !== 'sort' || value !== 'created_at_desc')) {
                params.set(key, value);
            }
        });
        const newUrl = `${window.location.pathname}?${params}`;
        window.history.pushState({}, '', newUrl);
    };

    const updateUI = () => {
        document.querySelectorAll('.filter-link.active').forEach(el => el.classList.remove('active'));
        document.querySelector(`[data-type='category'][data-id='${state.category}']`)?.classList.add('active');
        document.querySelector(`[data-type='brand'][data-id='${state.brand}']`)?.classList.add('active');
        document.querySelector(`[data-type='type'][data-id='${state.type}']`)?.classList.add('active');
        searchInput.value = state.search;
        sortSelect.value = state.sort;

        if (state.search) {
            shopTitle.textContent = `Search Results for "${state.search}"`;
        } else {
            shopTitle.textContent = 'All Products';
        }
    };

    const handleStateChange = (type, value) => {
        state[type] = value;
        updateUI();
        fetchProducts();
        updateURL();
    };

    const initFromURL = () => {
        const params = new URLSearchParams(window.location.search);
        state.category = params.get('category') || 'all';
        state.brand = params.get('brand') || 'all';
        state.type = params.get('type') || 'all';
        state.search = params.get('search') || '';
        state.sort = params.get('sort') || 'created_at_desc';
    };

    // Event Listeners
    [categoryFilter, brandFilter, typeFilter].forEach(filterContainer => {
        filterContainer.addEventListener('click', (e) => {
            if (e.target.classList.contains('filter-link')) {
                e.preventDefault();
                handleStateChange(e.target.dataset.type, e.target.dataset.id);
            }
        });
    });

    searchForm.addEventListener('submit', (e) => {
        e.preventDefault();
        handleStateChange('search', searchInput.value.trim());
    });

    sortSelect.addEventListener('change', () => {
        handleStateChange('sort', sortSelect.value);
    });

    clearFiltersBtn.addEventListener('click', () => {
        state = {
            category: 'all',
            brand: 'all',
            type: 'all',
            search: '',
            sort: 'created_at_desc'
        };
        fetchProducts();
        updateURL();
        updateUI();
    });

    // Initial Load
    const init = async () => {
        await fetchAndRenderFilters();
        initFromURL();
        updateUI();
        fetchProducts();
    };

    init();
});
