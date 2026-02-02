document.addEventListener('DOMContentLoaded', () => {
    const API_BASE_URL = '../api/routes.php';
    const cartContainer = document.getElementById('cart-container');

    const fetchCart = async () => {
        const response = await fetch(`${API_BASE_URL}?resource=cart`);
        const result = await response.json();

        if (result.status === 'success') {
            if (result.data.items.length === 0) {
                cartContainer.innerHTML = '<p>Your cart is empty.</p>';
                return;
            }

            let itemsHtml = '';
            result.data.items.forEach(item => {
                itemsHtml += `
                    <div class="flex items-center justify-between border-b py-4">
                        <div class="flex items-center">
                            <img src="../${item.main_image_url}" class="w-20 h-20 object-cover rounded-md mr-4">
                            <div>
                                <p class="font-semibold">${item.product_name}</p>
                                <p class="text-gray-600">Quantity: ${item.quantity}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold">$${(item.price * item.quantity).toFixed(2)}</p>
                            <button class="text-red-500 text-sm mt-1 remove-from-cart-btn" data-id="${item.id}">Remove</button>
                        </div>
                    </div>
                `;
            });

            const summaryHtml = `
                <div class="mt-6 text-right">
                    <p class="text-2xl font-bold">Total: <span class="text-indigo-600">$${result.data.total.toFixed(2)}</span></p>
                    <a href="checkout.html" class="inline-block mt-4 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700">Proceed to Checkout</a>
                </div>
            `;

            cartContainer.innerHTML = itemsHtml + summaryHtml;
        } else {
            // This will trigger if user is not logged in
            cartContainer.innerHTML = `<p class="text-center">${result.message} Please <a href="login.html" class="text-indigo-600">login</a> to view your cart.</p>`;
        }
    };

    // Handle remove from cart
    cartContainer.addEventListener('click', async (e) => {
        if (e.target.classList.contains('remove-from-cart-btn')) {
            const itemId = e.target.dataset.id;
            const response = await fetch(`${API_BASE_URL}?resource=cart&id=${itemId}`, { method: 'DELETE' });
            const result = await response.json();
            if (result.status === 'success') {
                fetchCart(); // Refresh cart view
            }
        }
    });

    fetchCart();
});
