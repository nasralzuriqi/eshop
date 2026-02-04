document.addEventListener('DOMContentLoaded', () => {
    const API_BASE_URL = '../api/routes.php';

    // Function to load components
    const loadComponent = async (url, containerId) => {
        try {
            const response = await fetch(url);
            document.getElementById(containerId).innerHTML = await response.text();
            if (containerId === 'header-container') {
                // Scripts are now loaded and initialized here to ensure the DOM is ready
                const headerScript = document.createElement('script');
                headerScript.src = 'assets/js/header.js';
                headerScript.onload = () => initializeHeader();
                document.body.appendChild(headerScript);

                const authScript = document.createElement('script');
                authScript.src = 'assets/js/auth.js';
                authScript.onload = () => initializeAuth();
                document.body.appendChild(authScript);
            }
        } catch (error) {
            console.error(`Failed to load component: ${url}`, error);
        }
    };

    // Load header and footer
    loadComponent('_header.html', 'header-container');
    loadComponent('_footer.html', 'footer-container');

    // Fetch and display featured products
    const fetchFeaturedProducts = async () => {
        const grid = document.getElementById('featured-products-grid');
        if (!grid) return; // Don't run if the element doesn't exist

        try {
            const response = await fetch(`${API_BASE_URL}?resource=products&action=read`); // In a real app, you'd have a specific 'featured' endpoint
            const result = await response.json();
            grid.innerHTML = '';
            if (result.status === 'success') {
                // Just show the first 8 for now
                result.data.slice(0, 8).forEach(product => {
                    const card = `
                        <div class="bg-white rounded-lg shadow-md overflow-hidden transform hover:scale-105 transition-transform duration-300">
                            <a href="product.html?id=${product.id}">
                                <img src="../${product.main_image_url}" alt="${product.name}" class="w-full h-56 object-cover">
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
                    grid.innerHTML += card;
                });
            }
        } catch (error) {
            console.error('Failed to fetch featured products:', error);
        }
    };

    // Fetch and display hero slider
    const fetchHeroSlider = async () => {
        const sliderContainer = document.getElementById('hero-slider-container');
        if (!sliderContainer) return; // Don't run if the element doesn't exist

        try {
            const response = await fetch(`${API_BASE_URL}?resource=ui&action=hero_sliders`);
            const result = await response.json();

            if (result.status === 'success' && result.data.length > 0) {
                sliderContainer.innerHTML = ''; // Clear existing content
                const indicatorsContainer = document.getElementById('slider-indicators');
                indicatorsContainer.innerHTML = ''; // Clear existing indicators

                result.data.forEach((slide, index) => {
                    const slideElement = document.createElement('div');
                    slideElement.className = 'absolute inset-0 w-full h-full transition-opacity duration-1000 ease-in-out';
                    slideElement.style.opacity = index === 0 ? '1' : '0';
                    slideElement.style.backgroundImage = `url('../${slide.image_url}')`;
                    slideElement.style.backgroundSize = 'cover';
                    slideElement.style.backgroundPosition = 'center';

                    slideElement.innerHTML = `
                        <div class="absolute inset-0 bg-black bg-opacity-40 flex flex-col items-center justify-center text-white text-center p-4">
                            <h1 class="text-4xl md:text-6xl font-bold">${slide.title}</h1>
                            <p class="mt-4 text-lg md:text-xl">${slide.subtitle}</p>
                            <a href="${slide.btn_link}" class="mt-8 px-6 py-3 bg-indigo-600 rounded-md font-semibold hover:bg-indigo-700 transition-colors">${slide.btn_text}</a>
                        </div>
                    `;
                    sliderContainer.appendChild(slideElement);

                    const indicator = document.createElement('button');
                    indicator.className = 'w-3 h-3 rounded-full transition-colors';
                    indicator.setAttribute('aria-label', `Go to slide ${index + 1}`);
                    indicatorsContainer.appendChild(indicator);
                });

                let currentSlide = 0;
                const slides = sliderContainer.children;
                const indicators = indicatorsContainer.children;
                const totalSlides = slides.length;
                let slideInterval;

                const showSlide = (index) => {
                    for (let i = 0; i < totalSlides; i++) {
                        slides[i].style.opacity = i === index ? '1' : '0';
                        indicators[i].classList.toggle('bg-white', i === index);
                        indicators[i].classList.toggle('bg-white/50', i !== index);
                    }
                };

                Array.from(indicators).forEach((indicator, index) => {
                    indicator.addEventListener('click', () => {
                        stopSlider();
                        currentSlide = index;
                        showSlide(currentSlide);
                        startSlider();
                    });
                });

                const next = () => {
                    currentSlide = (currentSlide + 1) % totalSlides;
                    showSlide(currentSlide);
                };

                const prev = () => {
                    currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
                    showSlide(currentSlide);
                };

                const startSlider = () => {
                    slideInterval = setInterval(next, 5000); // Change slide every 5 seconds
                };

                const stopSlider = () => {
                    clearInterval(slideInterval);
                };

                document.getElementById('next-slide').addEventListener('click', () => {
                    stopSlider();
                    next();
                    startSlider();
                });

                document.getElementById('prev-slide').addEventListener('click', () => {
                    stopSlider();
                    prev();
                    startSlider();
                });
                
                sliderContainer.parentElement.addEventListener('mouseenter', stopSlider);
                sliderContainer.parentElement.addEventListener('mouseleave', startSlider);

                startSlider();
            }
        } catch (error) {
            console.error('Failed to fetch hero slider:', error);
        }
    };

    const updateCartCount = async () => {
        try {
            const response = await fetch(`${API_BASE_URL}?resource=cart&action=get_cart_count`);
            const result = await response.json();
            if (result.status === 'success') {
                const cartCountEl = document.getElementById('cart-item-count');
                if (cartCountEl) {
                    cartCountEl.textContent = result.data.count;
                }
            }
        } catch (error) {
            console.error('Failed to fetch cart count:', error);
        }
    };

    const showToast = (message) => {
        const toast = document.getElementById('toast-notification');
        if (toast) {
            toast.textContent = message;
            toast.classList.remove('opacity-0', 'translate-y-20');
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-20');
            }, 3000); // Hide after 3 seconds
        }
    };

    const addToCart = async (productId, quantity = 1) => {
        try {
            const response = await fetch(`${API_BASE_URL}?resource=cart`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId, quantity: quantity })
            });
            const result = await response.json();
            if (result.status === 'success') {
                showToast('Product added to cart!');
                updateCartCount();
            } else {
                showToast(`Error: ${result.message}`);
            }
        } catch (error) {
            console.error('Add to cart failed:', error);
            showToast('An error occurred.');
        }
    };

    // Global event listener for add to cart buttons
    document.addEventListener('click', (e) => {
        const cartButton = e.target.closest('.add-to-cart-btn');
        if (cartButton) {
            const productId = cartButton.dataset.id;
            addToCart(productId);
        }
    });

    // Search form handler for both desktop and mobile
    document.addEventListener('submit', (e) => {
        if (e.target && (e.target.id === 'search-form' || e.target.id === 'mobile-search-form')) {
            e.preventDefault();
            const searchInput = e.target.querySelector('input[type="search"]');
            if (searchInput) {
                const query = searchInput.value.trim();
                if (query) {
                    window.location.href = `shop.html?search=${encodeURIComponent(query)}`;
                }
            }
        }
    });

    const fetchShopSettings = async () => {
        try {
            const response = await fetch(`${API_BASE_URL}?resource=shop_settings`);
            const result = await response.json();

            if (result.status === 'success') {
                const settings = result.data;

                // Header
                document.getElementById('shop-name-header').textContent = settings.site_name;
                document.getElementById('shop-phone-header').textContent = settings.phone_number;
                document.getElementById('shop-email-header').textContent = settings.email;
                document.getElementById('shop-address-header').textContent = settings.address;

                // Footer
                document.getElementById('shop-name-footer').textContent = settings.site_name;
                document.getElementById('shop-address-footer').textContent = settings.address;
                document.getElementById('shop-phone-footer').textContent = `Phone: ${settings.phone_number}`;
                document.getElementById('shop-email-footer').textContent = `Email: ${settings.email}`;
                
                const socialLinksContainer = document.getElementById('social-links-footer');
                socialLinksContainer.innerHTML = ''; // Clear existing
                if (settings.facebook_url) socialLinksContainer.innerHTML += `<a href="${settings.facebook_url}" class="text-gray-400 hover:text-white">Facebook</a>`;
                if (settings.instagram_url) socialLinksContainer.innerHTML += `<a href="${settings.instagram_url}" class="text-gray-400 hover:text-white">Instagram</a>`;
                if (settings.tiktok_url) socialLinksContainer.innerHTML += `<a href="${settings.tiktok_url}" class="text-gray-400 hover:text-white">TikTok</a>`;

                const whatsappContainer = document.getElementById('whatsapp-link-footer');
                if (settings.phone_number && whatsappContainer) {
                    const whatsappNumber = settings.phone_number.replace(/\D/g, '');
                    whatsappContainer.innerHTML = `<a href="https://wa.me/${whatsappNumber}" target="_blank" class="text-gray-400 hover:text-white flex items-center"><i class="fab fa-whatsapp text-lg mr-2"></i> Chat on WhatsApp</a>`;
                }

                document.getElementById('copyright-footer').innerHTML = `&copy; ${new Date().getFullYear()} ${settings.site_name}. All Rights Reserved.`;
            }
        } catch (error) {
            console.error('Failed to fetch shop settings:', error);
        }
    };

    // Initial calls
    fetchFeaturedProducts();
    fetchHeroSlider();
    fetchShopSettings();
    // We need to wait for the header to load to update the cart count
    const headerContainer = document.getElementById('header-container');
    const observer = new MutationObserver((mutationsList, observer) => {
        for(const mutation of mutationsList) {
            if (mutation.type === 'childList' && document.getElementById('cart-item-count')) {
                updateCartCount();
                observer.disconnect();
                return;
            }
        }
    });
    observer.observe(headerContainer, { childList: true, subtree: true });
});
