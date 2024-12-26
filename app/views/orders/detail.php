<?php
require_once __DIR__ . '/../../helpers/SessionHelper.php';
require_once __DIR__ . '/../../controllers/OrderController.php';

// Redirect ke login jika belum login
if (!SessionHelper::isLoggedIn()) {
    header('Location: /login');
    exit;
}

// Dapatkan ID pesanan dari URL
$orderId = $_GET['id'] ?? null;
if (!$orderId) {
    header('Location: /orders');
    exit;
}

$orderController = new OrderController();
$result = $orderController->getOrderById($orderId);

if (!$result['success']) {
    header('Location: /orders');
    exit;
}

$order = $result['data'];

require_once __DIR__ . '/../templates/header.php';
?>

<div class="min-h-screen bg-gray-100 py-8">
    <div class="container mx-auto px-4">
        <div class="mb-6">
            <a href="/orders" class="text-blue-600 hover:text-blue-700">
                &larr; Kembali ke Daftar Pesanan
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-2xl font-bold">Detail Pesanan</h1>
                    <p class="text-sm text-gray-500">Order #<?= substr($order['_id'], -8) ?></p>
                    <p class="text-sm text-gray-500"><?= $order['created_at'] ?></p>
                </div>
                <span class="px-4 py-2 rounded-full text-sm font-semibold
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Informasi Pengiriman -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="text-lg font-semibold mb-4">Informasi Pengiriman</h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Nama Penerima</p>
                            <p class="text-sm"><?= htmlspecialchars($order['shipping_info']['name']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Nomor Telepon</p>
                            <p class="text-sm"><?= htmlspecialchars($order['shipping_info']['phone']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Email</p>
                            <p class="text-sm"><?= htmlspecialchars($order['shipping_info']['email']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Alamat Pengiriman</p>
                            <p class="text-sm">
                                <?= htmlspecialchars($order['shipping_info']['address']) ?><br>
                                <?= htmlspecialchars($order['shipping_info']['city']) ?>, 
                                <?= htmlspecialchars($order['shipping_info']['province']) ?><br>
                                <?= htmlspecialchars($order['shipping_info']['postal_code']) ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Metode Pengiriman</p>
                            <p class="text-sm"><?= ucfirst($order['shipping_method']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Informasi Pembayaran -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="text-lg font-semibold mb-4">Informasi Pembayaran</h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Metode Pembayaran</p>
                            <p class="text-sm">
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
                        <div>
                            <p class="text-sm font-medium text-gray-600">Status Pembayaran</p>
                            <p class="text-sm">
                                <?php if ($order['status'] === 'pending'): ?>
                                    <span class="text-yellow-600">Menunggu Pembayaran</span>
                                <?php else: ?>
                                    <span class="text-green-600">Sudah Dibayar</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Produk -->
            <div class="mt-8">
                <h2 class="text-lg font-semibold mb-4">Detail Produk</h2>
                <div class="border rounded-lg overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Harga</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($order['products'] as $product): ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($product['name']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-500">
                                    Rp <?= number_format($product['price'], 0, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-500">
                                    <?= $product['quantity'] ?>
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-900">
                                    Rp <?= number_format($product['subtotal'], 0, ',', '.') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-500">
                                    Subtotal
                                </td>
                                <td class="px-6 py-3 text-right text-sm text-gray-900">
                                    Rp <?= number_format($order['subtotal'], 0, ',', '.') ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-500">
                                    Biaya Pengiriman
                                </td>
                                <td class="px-6 py-3 text-right text-sm text-gray-900">
                                    Rp <?= number_format($order['shipping_cost'], 0, ',', '.') ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-right text-sm font-bold text-gray-900">
                                    Total
                                </td>
                                <td class="px-6 py-3 text-right text-sm font-bold text-gray-900">
                                    Rp <?= number_format($order['total'], 0, ',', '.') ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <?php if ($order['status'] === 'pending'): ?>
            <!-- Instruksi Pembayaran -->
            <div class="mt-8 bg-blue-50 rounded-lg p-6">
                <h2 class="text-lg font-semibold mb-4">Instruksi Pembayaran</h2>
                <div class="space-y-4">
                    <?php if ($order['payment_method'] === 'bank_transfer'): ?>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Transfer ke rekening berikut:</p>
                        <p class="text-sm">
                            Bank BCA<br>
                            No. Rekening: 1234567890<br>
                            Atas Nama: PT Fashion Vibe Indonesia
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Nominal yang harus dibayar:</p>
                        <p class="text-lg font-bold text-blue-600">
                            Rp <?= number_format($order['total'], 0, ',', '.') ?>
                        </p>
                    </div>
                    <div class="text-sm text-gray-600">
                        <p>Catatan:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Mohon transfer sesuai nominal yang tertera</li>
                            <li>Pembayaran akan diverifikasi dalam 1x24 jam</li>
                            <li>Pesanan akan dibatalkan otomatis jika pembayaran tidak dilakukan dalam 24 jam</li>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?> 