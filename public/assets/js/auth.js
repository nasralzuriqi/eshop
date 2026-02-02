document.addEventListener('DOMContentLoaded', () => {
    const API_BASE_URL = '../api/routes.php';

    const checkLoginStatus = async () => {
        try {
            const response = await fetch(`${API_BASE_URL}?resource=auth&action=check_status`);
            const result = await response.json();

            const authLinksContainer = document.getElementById('auth-links');
            if (!authLinksContainer) return;

            if (result.status === 'success' && result.is_logged_in) {
                authLinksContainer.innerHTML = `
                    <a href="my_orders.html" class="text-gray-800 dark:text-white hover:bg-gray-50 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-4 lg:px-5 py-2 lg:py-2.5 mr-2 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800">My Orders</a>
                    <span class="text-gray-800 dark:text-white font-medium">Welcome, ${result.username}</span>
                    <a href="#" id="logout-btn" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-4 lg:px-5 py-2 lg:py-2.5 mr-2 dark:bg-red-600 dark:hover:bg-red-700 focus:outline-none dark:focus:ring-red-800">Logout</a>
                `;

                const logoutBtn = document.getElementById('logout-btn');
                if (logoutBtn) {
                    logoutBtn.addEventListener('click', async (e) => {
                        e.preventDefault();
                        await fetch(`${API_BASE_URL}?resource=auth&action=logout`);
                        window.location.reload();
                    });
                }
            } else {
                authLinksContainer.innerHTML = `
                    <a href="login.html" class="text-gray-800 dark:text-white hover:bg-gray-50 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-4 lg:px-5 py-2 lg:py-2.5 mr-2 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800">Log in</a>
                    <a href="register.html" class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-4 lg:px-5 py-2 lg:py-2.5 mr-2 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">Register</a>
                `;
            }
        } catch (error) {
            console.error('Failed to check login status:', error);
        }
    };

    // We need to wait for the header to be loaded first
    const headerContainer = document.getElementById('header-container');
    const observer = new MutationObserver((mutationsList, observer) => {
        for(const mutation of mutationsList) {
            if (mutation.type === 'childList' && document.getElementById('auth-links')) {
                checkLoginStatus();
                observer.disconnect(); // Stop observing once the header is loaded
                return;
            }
        }
    });

    observer.observe(headerContainer, { childList: true, subtree: true });
});
