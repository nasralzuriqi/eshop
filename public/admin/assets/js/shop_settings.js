document.addEventListener('DOMContentLoaded', () => {
    const API_URL = '../../api/routes.php?resource=shop_settings';
    const form = document.getElementById('settings-form');
    const toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    // Load settings when the page loads
    const loadSettings = async () => {
        try {
            const response = await fetch(API_URL);
            const result = await response.json();
            
            if (result.status === 'success' && result.data) {
                // Fill the form with the settings data
                Object.entries(result.data).forEach(([key, value]) => {
                    const input = form.querySelector(`[name="${key}"]`);
                    if (input) {
                        input.value = value !== null ? value : '';
                    }
                });
            }
        } catch (error) {
            console.error('Error loading settings:', error);
            toast.fire({
                icon: 'error',
                title: 'Failed to load settings'
            });
        }
    };

    // Save settings when the form is submitted
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        e.stopPropagation();
        
        // Create a plain object with only the fields we want
        const formData = new FormData(form);
        const data = {};
        
        // Only include these fields in the request
        const allowedFields = [
            'site_name', 'email', 'phone_number', 'address',
            'facebook_url', 'instagram_url', 'tiktok_url',
            'whatsapp_number', 'currency_symbol'
        ];
        
        // Add only allowed fields to the data object
        allowedFields.forEach(field => {
            const value = formData.get(field);
            if (value !== null && value !== '') {
                data[field] = value;
            }
        });
        
        // Show loading state
        const saveButton = form.querySelector('button[type="submit"]');
        const originalButtonText = saveButton.innerHTML;
        saveButton.disabled = true;
        saveButton.innerHTML = 'Saving...';
        saveButton.classList.add('cursor-not-allowed', 'opacity-75');
        
        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.message || 'Failed to save settings');
            }
            
            if (result.status === 'success') {
                toast.fire({
                    icon: 'success',
                    title: 'Settings saved successfully!'
                });
            } else {
                throw new Error(result.message || 'Failed to save settings');
            }
        } catch (error) {
            console.error('Error saving settings:', error);
            toast.fire({
                icon: 'error',
                title: error.message || 'Failed to save settings'
            });
        } finally {
            // Reset button state
            saveButton.disabled = false;
            saveButton.innerHTML = originalButtonText;
            saveButton.classList.remove('cursor-not-allowed', 'opacity-75');
        }
        
        return false;
    });
    
    // Initialize the form
    loadSettings();
});
