document.addEventListener('DOMContentLoaded', async () => {
    const API_BASE_URL = '../api/routes.php';

    try {
        const response = await fetch(`${API_BASE_URL}?resource=shop_settings`);
        const result = await response.json();

        if (result.status === 'success') {
            const settings = result.data;
            document.getElementById('shop-description').textContent = settings.shop_description || 'Welcome to our perfume shop. We are dedicated to bringing you the finest fragrances from around the world.';
            document.getElementById('shop-address').textContent = settings.address || '123 Perfume Lane, Fragrance City, 12345';
            document.getElementById('shop-phone').textContent = settings.phone_number || '+1 (234) 567-890';
            document.getElementById('shop-email').textContent = settings.email || 'contact@perfumeshop.com';

            const socialLinksContainer = document.getElementById('social-links-about');
            socialLinksContainer.innerHTML = ''; // Clear existing
            if (settings.facebook_url) {
                socialLinksContainer.innerHTML += `<a href="${settings.facebook_url}" target="_blank" class="text-gray-600 hover:text-indigo-600"><i class="fab fa-facebook-f text-3xl"></i></a>`;
            }
            if (settings.instagram_url) {
                socialLinksContainer.innerHTML += `<a href="${settings.instagram_url}" target="_blank" class="text-gray-600 hover:text-indigo-600"><i class="fab fa-instagram text-3xl"></i></a>`;
            }
            if (settings.tiktok_url) {
                socialLinksContainer.innerHTML += `<a href="${settings.tiktok_url}" target="_blank" class="text-gray-600 hover:text-indigo-600"><i class="fab fa-tiktok text-3xl"></i></a>`;
            }
            if (settings.phone_number) {
                 const whatsappNumber = settings.phone_number.replace(/\D/g, '');
                 socialLinksContainer.innerHTML += `<a href="https://wa.me/${whatsappNumber}" target="_blank" class="text-gray-600 hover:text-indigo-600"><i class="fab fa-whatsapp text-3xl"></i></a>`;
            }
        } else {
            console.error('Failed to load shop settings:', result.message);
            // Display default text if API fails
            document.getElementById('shop-description').textContent = 'Error loading shop information. Please try again later.';
        }
    } catch (error) {
        console.error('Error fetching shop settings:', error);
        document.getElementById('shop-description').textContent = 'An unexpected error occurred. Please try again later.';
    }
});
