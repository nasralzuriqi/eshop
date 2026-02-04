(function() {
    const adminUser = sessionStorage.getItem('admin_user');
    if (!adminUser && !window.location.pathname.endsWith('login.html')) {
        window.location.href = 'login.html';
        return;
    }

    const loadComponent = async (url, containerId, callback) => {
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error(`Component not found at ${url}`);
            const html = await response.text();
            const container = document.getElementById(containerId);
            if (container) {
                container.innerHTML = html;
                if (callback) callback();
            }
        } catch (error) {
            console.error(`Failed to load component:`, error);
        }
    };

    const loadAdminScripts = () => {
        const layoutScript = document.createElement('script');
        layoutScript.src = './assets/js/admin_layout.js';
        document.body.appendChild(layoutScript);
    };

    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('sidebar-container')) {
            loadComponent('_sidebar.html', 'sidebar-container');
        }
        if (document.getElementById('header-container')) {
            loadComponent('_header.html', 'header-container', loadAdminScripts);
        }
    });
})();
