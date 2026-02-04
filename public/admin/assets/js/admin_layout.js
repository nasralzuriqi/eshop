(() => {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const mainContent = document.querySelector('.main-content');

    if (sidebarToggle && sidebar && mainContent) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            mainContent.classList.toggle('sidebar-open');
        });
    }

    const profileButton = document.getElementById('profile-button');
    const profileDropdown = document.getElementById('profile-dropdown');

    if (profileButton) {
        profileButton.addEventListener('click', (e) => {
            e.stopPropagation();
            profileDropdown.classList.toggle('hidden');
        });
    }

    document.addEventListener('click', (e) => {
        if (profileDropdown && !profileDropdown.classList.contains('hidden')) {
            if (!profileButton.contains(e.target)) {
                profileDropdown.classList.add('hidden');
            }
        }
    });

    // Set active sidebar link
    const currentPath = window.location.pathname.split('/').pop();
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    sidebarLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        }
    });

    // Admin user name and logout
    const adminUser = JSON.parse(sessionStorage.getItem('admin_user'));
    const adminUserName = document.getElementById('admin-user-name');
    if (adminUser && adminUser.username) {
        if(adminUserName) adminUserName.textContent = `Welcome, ${adminUser.username}`;
    }

    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            sessionStorage.removeItem('admin_user');
            window.location.href = 'login.html';
        });
    }
})();
