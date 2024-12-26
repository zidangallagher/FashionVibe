<?php
require_once __DIR__ . '/../../middleware/AdminMiddleware.php';
AdminMiddleware::handle();

require_once __DIR__ . '/../templates/admin_header.php';
require_once __DIR__ . '/../../controllers/OrderController.php';

$orderController = new OrderController();
$ordersResult = $orderController->getAllOrders();
$orders = $ordersResult['success'] ? $ordersResult['data'] : [];
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header dengan tombol kembali -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-4">
            <a href="/admin" class="flex items-center text-blue-600 hover:text-blue-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10.707 3.293a1 1 0 010 1.414L6.414 9H17a1 1 0 110 2H6.414l4.293 4.293a1 1 0 11-1.414 1.414l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Manajemen Pesanan</h1>
    </div>

    <!-- Filter dan Pencarian -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" id="search" placeholder="Cari pesanan..." class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex-1">
                <select id="status" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <button class="bg-gray-100 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-200 transition-colors">
                Filter
            </button>
        </div>
    </div>

    <!-- Tabel Pesanan -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pesanan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">#<?= substr($order->_id, -8) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= date('d/m/Y H:i', $order->created_at->toDateTime()->getTimestamp()) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= $order->shipping_info->name ?? 'N/A' ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">Rp <?= number_format($order->total, 0, ',', '.') ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <select onchange="updateOrderStatus('<?= $order->_id ?>', this.value)" 
                            class="text-sm rounded-full px-3 py-1 <?= getStatusColor($order->status) ?>">
                            <option value="pending" <?= $order->status === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= $order->status === 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="shipped" <?= $order->status === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                            <option value="delivered" <?= $order->status === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $order->status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button onclick="viewOrderDetails('<?= $order->_id ?>')" 
                                class="text-blue-600 hover:text-blue-900">Detail</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Detail Pesanan -->
<div id="orderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Detail Pesanan</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="orderDetails" class="mt-4">
                <!-- Detail pesanan akan dimuat di sini -->
            </div>
        </div>
    </div>
</div>

<?php
function getStatusColor($status) {
    switch ($status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'processing':
            return 'bg-blue-100 text-blue-800';
        case 'shipped':
            return 'bg-purple-100 text-purple-800';
        case 'delivered':
            return 'bg-green-100 text-green-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}
?>

<script>
const modal = document.getElementById('orderModal');
const orderDetails = document.getElementById('orderDetails');

function viewOrderDetails(orderId) {
    fetch(`/api/orders/${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const order = data.data;
                let productsHtml = '';
                
                order.products.forEach(product => {
                    productsHtml += `
                        <div class="flex justify-between items-center py-2 border-b">
                            <div>
                                <div class="font-medium">${product.name}</div>
                                <div class="text-sm text-gray-600">Qty: ${product.quantity}</div>
                            </div>
                            <div class="text-right">
                                <div>Rp ${numberFormat(product.price)}</div>
                                <div class="text-sm text-gray-600">Subtotal: Rp ${numberFormat(product.subtotal)}</div>
                            </div>
                        </div>
                    `;
                });

                orderDetails.innerHTML = `
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <h4 class="font-medium mb-2">Informasi Pengiriman</h4>
                            <div class="text-sm">
                                <p>Nama: ${order.shipping_info.name}</p>
                                <p>Alamat: ${order.shipping_info.address}</p>
                                <p>Telepon: ${order.shipping_info.phone}</p>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-medium mb-2">Informasi Pesanan</h4>
                            <div class="text-sm">
                                <p>ID: #${order._id.substr(-8)}</p>
                                <p>Tanggal: ${new Date(order.created_at.$date).toLocaleString()}</p>
                                <p>Status: ${order.status}</p>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <h4 class="font-medium mb-2">Produk</h4>
                        ${productsHtml}
                    </div>
                    <div class="border-t pt-4">
                        <div class="flex justify-between mb-2">
                            <span>Subtotal</span>
                            <span>Rp ${numberFormat(order.subtotal)}</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span>Biaya Pengiriman</span>
                            <span>Rp ${numberFormat(order.shipping_cost)}</span>
                        </div>
                        <div class="flex justify-between font-bold">
                            <span>Total</span>
                            <span>Rp ${numberFormat(order.total)}</span>
                        </div>
                    </div>
                `;
                modal.classList.remove('hidden');
            } else {
                alert('Gagal memuat detail pesanan: ' + data.message);
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan: ' + error.message);
        });
}

function updateOrderStatus(orderId, status) {
    if (!confirm('Apakah Anda yakin ingin mengubah status pesanan ini?')) {
        return;
    }

    fetch(`/api/orders/${orderId}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Status pesanan berhasil diperbarui');
            location.reload();
        } else {
            alert('Gagal memperbarui status: ' + data.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan: ' + error.message);
    });
}

function closeModal() {
    modal.classList.add('hidden');
    orderDetails.innerHTML = '';
}

function numberFormat(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}

// Filter pesanan
document.getElementById('status').addEventListener('change', function(e) {
    const status = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const orderStatus = row.querySelector('select').value.toLowerCase();
        if (!status || orderStatus === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Pencarian pesanan
document.getElementById('search').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const orderId = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
        const customerName = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
        if (orderId.includes(searchTerm) || customerName.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Close modal when clicking outside
modal.addEventListener('click', function(e) {
    if (e.target === modal) {
        closeModal();
    }
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?> 