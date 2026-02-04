const initializeAuth = () => {
    const API_BASE_URL = '../api/routes.php';

    const checkLoginStatus = async () => {
        try {
            const response = await fetch(`${API_BASE_URL}?resource=auth&action=check_status`);
            const result = await response.json();

            const authLinksContainer = document.getElementById('auth-links');
            const mobileAuthLinksContainer = document.getElementById('mobile-auth-links');
            if (!authLinksContainer || !mobileAuthLinksContainer) return;

            let desktopLinks = '';
            let mobileLinks = '';

            if (result.status === 'success' && result.is_logged_in) {
                desktopLinks = `
                    <a href="my_orders.html" class="text-sm font-medium text-gray-700 hover:text-indigo-600">My Orders</a>
                    <span class="text-sm text-gray-800">Hi, ${result.username}</span>
                    <a href="#" id="logout-btn" class="text-sm font-medium text-red-600 hover:text-red-800">Logout</a>
                `;
                mobileLinks = `
                    <a href="my_orders.html" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 rounded">My Orders</a>
                    <a href="#" id="mobile-logout-btn" class="block px-4 py-2 text-red-600 hover:bg-red-50 rounded">Logout</a>
                `;
            } else {
                desktopLinks = `
                    <a href="login.html" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Log in</a>
                    <a href="register.html" class="text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-2 rounded-md">Register</a>
                `;
                mobileLinks = `
                    <a href="login.html" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 rounded">Log in</a>
                    <a href="register.html" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 rounded">Register</a>
                `;
            }

            authLinksContainer.innerHTML = desktopLinks;
            mobileAuthLinksContainer.innerHTML = mobileLinks;

            const handleLogout = async (e) => {
                e.preventDefault();
                await fetch(`${API_BASE_URL}?resource=auth&action=logout`);
                window.location.reload();
            };

            const logoutBtn = document.getElementById('logout-btn');
            if (logoutBtn) logoutBtn.addEventListener('click', handleLogout);

            const mobileLogoutBtn = document.getElementById('mobile-logout-btn');
            if (mobileLogoutBtn) mobileLogoutBtn.addEventListener('click', handleLogout);
        } catch (error) {
            console.error('Failed to check login status:', error);
        }
    };

    checkLoginStatus();
};
