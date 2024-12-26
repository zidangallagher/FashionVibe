<?php
require_once __DIR__ . '/../../middleware/AdminMiddleware.php';
AdminMiddleware::handle();

require_once __DIR__ . '/../templates/admin_header.php';
require_once __DIR__ . '/../../controllers/ProductController.php';
require_once __DIR__ . '/../../controllers/OrderController.php';

$productController = new ProductController();
$orderController = new OrderController();

// Get statistics
$productsResult = $productController->getAllProducts();
$products = $productsResult['success'] ? $productsResult['data']['products'] : [];
$totalProducts = count($products);

$ordersResult = $orderController->getAllOrders();
$orders = $ordersResult['success'] ? $ordersResult['data'] : [];
$totalOrders = count($orders);

$totalRevenue = 0;
foreach ($orders as $order) {
    if ($order->status !== 'cancelled') {
        $totalRevenue += $order->total;
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Dashboard Admin</h1>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Produk</h3>
            <p class="text-3xl font-bold text-blue-600"><?= $totalProducts ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Pesanan</h3>
            <p class="text-3xl font-bold text-blue-600"><?= $totalOrders ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Pendapatan</h3>
            <p class="text-3xl font-bold text-blue-600">Rp <?= number_format($totalRevenue, 0, ',', '.') ?></p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Manajemen Produk</h2>
            <div class="space-y-4">
                <a href="/admin/products" class="block bg-blue-600 text-white text-center px-4 py-2 rounded hover:bg-blue-700 transition-colors">
                    Kelola Produk
                </a>
                <a href="/admin/categories" class="block bg-green-600 text-white text-center px-4 py-2 rounded hover:bg-green-700 transition-colors">
                    Kelola Kategori
                </a>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Manajemen Pesanan</h2>
            <div class="space-y-4">
                <a href="/admin/orders" class="block bg-purple-600 text-white text-center px-4 py-2 rounded hover:bg-purple-700 transition-colors">
                    Kelola Pesanan
                </a>
                <a href="/admin/users" class="block bg-yellow-600 text-white text-center px-4 py-2 rounded hover:bg-yellow-700 transition-colors">
                    Kelola Pengguna
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?> 