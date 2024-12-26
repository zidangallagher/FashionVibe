<?php
require_once __DIR__ . '/../../helpers/SessionHelper.php';
require_once __DIR__ . '/../../controllers/OrderController.php';

// Debug session
SessionHelper::debug();

// Redirect ke login jika belum login
if (!SessionHelper::isLoggedIn()) {
    error_log('User not logged in, redirecting to login');
    header('Location: /login');
    exit;
}

$orderController = new OrderController();

// Debug user ID
$userId = SessionHelper::getUserId();
error_log('User ID from session: ' . ($userId ?? 'null'));

if (!$userId) {
    error_log('No user ID found, redirecting to login');
    header('Location: /login');
    exit;
}

$result = $orderController->getOrders($userId);
error_log('Order result: ' . json_encode($result));
$orders = $result['success'] ? $result['data'] : [];

require_once __DIR__ . '/../templates/header.php';
?>

<div class="min-h-screen bg-gray-100 py-8">
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-8">Pesanan Saya</h1>

        <?php if (empty($orders)): ?>
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <p class="text-gray-600 mb-4">Anda belum memiliki pesanan</p>
            <a href="/products" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                Mulai Belanja
            </a>
        </div>
        <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($orders as $order): ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold">Order #<?= substr($order['_id'], -8) ?></h3>
                        <p class="text-sm text-gray-500"><?= $order['created_at'] ?></p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm 
                        <?php
                        switch ($order['status']) {
                            case 'pending':
                                echo 'bg-yellow-100 text-yellow-800';
                                break;
                            case 'processing':
                                echo 'bg-blue-100 text-blue-800';
                                break;
                            case 'shipped':
                                echo 'bg-purple-100 text-purple-800';
                                break;
                            case 'delivered':
                                echo 'bg-green-100 text-green-800';
                                break;
                            case 'cancelled':
                                echo 'bg-red-100 text-red-800';
                                break;
                            default:
                                echo 'bg-gray-100 text-gray-800';
                        }
                        ?>">
                        <?= ucfirst($order['status']) ?>
                    </span>
                </div>

                <div class="border-t border-b py-4 mb-4">
                    <?php foreach ($order['products'] as $product): ?>
                    <div class="flex items-center py-2">
                        <div class="flex-1">
                            <h4 class="text-sm font-medium"><?= htmlspecialchars($product['name']) ?></h4>
                            <p class="text-sm text-gray-500"><?= $product['quantity'] ?> x Rp <?= number_format($product['price'], 0, ',', '.') ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium">Rp <?= number_format($product['subtotal'], 0, ',', '.') ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span>Subtotal</span>
                        <span>Rp <?= number_format($order['subtotal'], 0, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Biaya Pengiriman (<?= ucfirst($order['shipping_method']) ?>)</span>
                        <span>Rp <?= number_format($order['shipping_cost'], 0, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between text-sm font-bold">
                        <span>Total</span>
                        <span>Rp <?= number_format($order['total'], 0, ',', '.') ?></span>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    <div class="border-t pt-4">
                        <h4 class="text-sm font-semibold mb-2">Informasi Pengiriman</h4>
                        <p class="text-sm text-gray-600">
                            <?= htmlspecialchars($order['shipping_info']['name']) ?><br>
                            <?= htmlspecialchars($order['shipping_info']['phone']) ?><br>
                            <?= htmlspecialchars($order['shipping_info']['address']) ?><br>
                            <?= htmlspecialchars($order['shipping_info']['city']) ?>, 
                            <?= htmlspecialchars($order['shipping_info']['province']) ?> 
                            <?= htmlspecialchars($order['shipping_info']['postal_code']) ?>
                        </p>
                    </div>

                    <div class="border-t pt-4">
                        <h4 class="text-sm font-semibold mb-2">Metode Pembayaran</h4>
                        <p class="text-sm text-gray-600">
                            <?php
                            switch ($order['payment_method']) {
                                case 'bank_transfer':
                                    echo 'Transfer Bank';
                                    break;
                                case 'credit_card':
                                    echo 'Kartu Kredit';
                                    break;
                                case 'e_wallet':
                                    echo 'E-Wallet';
                                    break;
                                default:
                                    echo ucfirst($order['payment_method']);
                            }
                            ?>
                        </p>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="/orders/<?= $order['_id'] ?>" 
                       class="inline-block bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        Lihat Detail
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?> 