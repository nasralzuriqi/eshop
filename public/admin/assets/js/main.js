// Check for admin session on every page load
(function() {
    const adminUser = sessionStorage.getItem('admin_user');
    if (!adminUser && !window.location.pathname.endsWith('login.html')) {
        window.location.href = 'login.html';
        return;
    }

    // Function to fetch and inject HTML components
    const loadComponent = async (url, containerId) => {
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('Component not found');
            const html = await response.text();
            document.getElementById(containerId).innerHTML = html;
        } catch (error) {
            console.error(`Failed to load component from ${url}:`, error);
        }
    };

    // Load sidebar and header into the respective containers
    // The actual HTML files for these will be created next
    if (document.getElementById('sidebar-container')) {
        loadComponent('_sidebar.html', 'sidebar-container');
    }
    if (document.getElementById('header-container')) {
        loadComponent('_header.html', 'header-container');
    }
})();

function logout() {
    sessionStorage.removeItem('admin_user');
    window.location.href = 'login.html';
}
