document.addEventListener('DOMContentLoaded', () => {
    const API_BASE_URL = '../api/routes.php';
    const ordersListContainer = document.getElementById('orders-list');
    const ordersContainer = document.getElementById('orders-container');

    const fetchOrders = async () => {
        try {
            const response = await fetch(`${API_BASE_URL}?resource=customer_orders`);
            const result = await response.json();

            if (result.status === 'success') {
                if (result.data.length === 0) {
                    ordersListContainer.innerHTML = '<p class="text-center text-gray-500">You have no orders yet.</p>';
                    return;
                }

                let ordersHtml = '';
                result.data.forEach(order => {
                    let itemsHtml = '';
                    order.items.forEach(item => {
                        itemsHtml += `
                            <div class="flex items-center justify-between py-2">
                                <div class="flex items-center">
                                    <img src="../${item.main_image_url}" class="w-12 h-12 object-cover rounded-md mr-4">
                                    <div>
                                        <p class="font-semibold">${item.product_name}</p>
                                        <p class="text-sm text-gray-600">Quantity: ${item.quantity}</p>
                                    </div>
                                </div>
                                <p class="font-semibold text-gray-800">$${(item.price * item.quantity).toFixed(2)}</p>
                            </div>
                        `;
                    });

                    ordersHtml += `
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="p-4 bg-gray-100 border-b flex justify-between items-center">
                                <div>
                                    <p class="font-bold">Order #${order.id}</p>
                                    <p class="text-sm text-gray-600">Date: ${new Date(order.created_at).toLocaleDateString()}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-lg text-indigo-600">Total: $${parseFloat(order.total_amount).toFixed(2)}</p>
                                    <p class="text-sm font-semibold capitalize text-${order.status === 'completed' ? 'green' : 'yellow'}-600">${order.status}</p>
                                </div>
                            </div>
                            <div class="p-4 divide-y">
                                ${itemsHtml}
                            </div>
                        </div>
                    `;
                });
                ordersListContainer.innerHTML = ordersHtml;
            } else {
                ordersContainer.innerHTML = `<p class="text-center text-red-500">${result.message}. Please <a href="login.html" class="text-indigo-600">login</a>.</p>`;
            }
        } catch (error) {
            console.error('Failed to fetch orders:', error);
            ordersContainer.innerHTML = '<p class="text-center text-red-500">An error occurred while fetching your orders.</p>';
        }
    };

    fetchOrders();
});
