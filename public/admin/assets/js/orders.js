document.addEventListener('DOMContentLoaded', () => {
    const API_BASE_URL = '../../api/routes.php';
    const tableBody = document.getElementById('orders-table-body');
    const modal = document.getElementById('order-details-modal');
    const modalContent = document.getElementById('modal-content');
    const closeModalBtn = document.getElementById('close-modal-btn');

    const fetchOrders = async () => {
        const response = await fetch(`${API_BASE_URL}?resource=orders&action=readAll`);
        const result = await response.json();

        tableBody.innerHTML = '';
        if (result.status === 'success') {
            result.data.forEach(order => {
                const row = `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">#${order.id}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${order.customer_name}</td>
                        <td class="px-6 py-4 whitespace-nowrap">$${parseFloat(order.total_amount).toFixed(2)}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <select class="status-select border-gray-300 rounded-md shadow-sm" data-id="${order.id}">
                                <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="processing" ${order.status === 'processing' ? 'selected' : ''}>Processing</option>
                                <option value="shipped" ${order.status === 'shipped' ? 'selected' : ''}>Shipped</option>
                                <option value="delivered" ${order.status === 'delivered' ? 'selected' : ''}>Delivered</option>
                                <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                            </select>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">${new Date(order.created_at).toLocaleDateString()}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button class="text-indigo-600 hover:text-indigo-900 view-details-btn" data-id="${order.id}">View</button>
                        </td>
                    </tr>
                `;
                tableBody.innerHTML += row;
            });
        }
    };

    const openModalWithOrder = async (orderId) => {
        const response = await fetch(`${API_BASE_URL}?resource=orders&action=readOne&id=${orderId}`);
        const result = await response.json();

        if (result.status === 'success') {
            const order = result.data;
            let itemsHtml = '';
            order.items.forEach(item => {
                itemsHtml += `
                    <div class="flex justify-between items-center py-2 border-b">
                        <span>${item.product_name} (x${item.quantity})</span>
                        <span>$${(item.price * item.quantity).toFixed(2)}</span>
                    </div>
                `;
            });

            modalContent.innerHTML = `
                <div class="space-y-4">
                    <div><strong>Order ID:</strong> #${order.id}</div>
                    <div><strong>Customer:</strong> ${order.customer_name} (${order.customer_email})</div>
                    <div><strong>Shipping Address:</strong> ${order.shipping_address}</div>
                    <div><strong>Phone:</strong> ${order.shipping_phone}</div>
                    <div class="border-t pt-4 mt-4">
                        <h4 class="font-bold mb-2">Items</h4>
                        ${itemsHtml}
                    </div>
                    <div class="text-right font-bold text-xl border-t pt-4 mt-4">Total: $${parseFloat(order.total_amount).toFixed(2)}</div>
                </div>
            `;
            modal.classList.remove('hidden');
        } else {
            Swal.fire('Error', 'Could not fetch order details.', 'error');
        }
    };

    tableBody.addEventListener('click', (e) => {
        if (e.target.classList.contains('view-details-btn')) {
            openModalWithOrder(e.target.dataset.id);
        }
    });

    tableBody.addEventListener('change', async (e) => {
        if (e.target.classList.contains('status-select')) {
            const orderId = e.target.dataset.id;
            const newStatus = e.target.value;
            
            const response = await fetch(`${API_BASE_URL}?resource=orders&action=updateStatus`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: orderId, status: newStatus })
            });

            const result = await response.json();
            if (result.status === 'success') {
                Swal.fire('Success', 'Order status updated!', 'success');
            } else {
                Swal.fire('Error', 'Failed to update status.', 'error');
            }
        }
    });

    closeModalBtn.addEventListener('click', () => modal.classList.add('hidden'));
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });

    fetchOrders();
});
