document.getElementById('login-form').addEventListener('submit', async function(event) {
    event.preventDefault();

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    const response = await fetch('../../api/routes.php?resource=auth&action=admin_login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ username, password })
    });

    const result = await response.json();

    if (result.status === 'success') {
        // Store admin info in session storage for use across the admin panel
        sessionStorage.setItem('admin_user', JSON.stringify(result.data));
        Swal.fire({
            icon: 'success',
            title: 'Login Successful',
            text: 'Redirecting to dashboard...',
            timer: 1500,
            showConfirmButton: false
        }).then(() => {
            window.location.href = 'dashboard.html';
        });
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: result.message
        });
    }
});
