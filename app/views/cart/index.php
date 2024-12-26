<?php
require_once __DIR__ . '/../../helpers/SessionHelper.php';

// Debug session
SessionHelper::debug();

// Redirect ke login jika belum login
if (!SessionHelper::isLoggedIn()) {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../../controllers/CartController.php';

$cartController = new CartController();
$cartResult = $cartController->getCart();

// Log cart result untuk debugging
error_log('Cart result: ' . json_encode($cartResult));

// Tampilkan error jika ada
if (!$cartResult['success']) {
    error_log('Error getting cart: ' . $cartResult['message']);
}

$cartItems = $cartResult['success'] ? $cartResult['data']['items'] : [];
$totalPrice = $cartResult['success'] ? $cartResult['data']['total'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
    <?php require_once __DIR__ . '/../templates/header.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Keranjang Belanja</h1>

        <?php if (!$cartResult['success']): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($cartResult['message']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <div class="text-center py-8">
                <p class="text-gray-600 mb-4">Keranjang belanja Anda kosong</p>
                <a href="/" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">
                    Mulai Belanja
                </a>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <?php if (!empty($item['product']['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($item['product']['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['product']['name']); ?>"
                                                 class="w-16 h-16 object-cover rounded">
                                        <?php endif; ?>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($item['product']['name']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        Rp <?php echo number_format($item['product']['price'], 0, ',', '.'); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="number" 
                                           value="<?php echo $item['quantity']; ?>" 
                                           min="1"
                                           class="w-20 px-2 py-1 border rounded"
                                           onchange="updateQuantity('<?php echo $item['_id']; ?>', this.value)">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        Rp <?php echo number_format($item['product']['price'] * $item['quantity'], 0, ',', '.'); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button onclick="removeFromCart('<?php echo $item['_id']; ?>')"
                                            class="text-red-600 hover:text-red-900">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-6 py-4 text-right font-medium">Total Belanja:</td>
                            <td colspan="2" class="px-6 py-4 font-bold">
                                Rp <?php echo number_format($totalPrice, 0, ',', '.'); ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-6 flex justify-between">
                <a href="/" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">
                    Lanjut Belanja
                </a>
                <a href="/checkout" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">
                    Checkout
                </a>
            </div>
        <?php endif; ?>
    </main>

    <script>
    function updateQuantity(cartId, quantity) {
        if (isNaN(quantity) || quantity < 1) {
            alert('Jumlah produk tidak valid');
            return;
        }

        const data = new URLSearchParams();
        data.append('cart_id', cartId);
        data.append('quantity', quantity);

        fetch('/app/api/cart.php?action=update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: data.toString()
        })
        .then(async response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                console.error('Invalid content type:', contentType);
                const text = await response.text();
                console.error('Response text:', text);
                throw new Error('Server tidak mengembalikan response JSON yang valid');
            }

            const data = await response.json();
            console.log('Response data:', data);

            if (!response.ok) {
                throw new Error(data.message || 'Terjadi kesalahan pada server');
            }

            if (!data.success) {
                throw new Error(data.message || 'Gagal mengupdate jumlah produk');
            }

            return data;
        })
        .then(data => {
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'Terjadi kesalahan saat mengupdate jumlah produk');
            location.reload();
        });
    }

    function removeFromCart(cartId) {
        if (!confirm('Apakah Anda yakin ingin menghapus produk ini dari keranjang?')) {
            return;
        }

        const data = new URLSearchParams();
        data.append('cart_id', cartId);

        fetch('/app/api/cart.php?action=remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: data.toString()
        })
        .then(async response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                console.error('Invalid content type:', contentType);
                const text = await response.text();
                console.error('Response text:', text);
                throw new Error('Server tidak mengembalikan response JSON yang valid');
            }

            const data = await response.json();
            console.log('Response data:', data);

            if (!response.ok) {
                throw new Error(data.message || 'Terjadi kesalahan pada server');
            }

            if (!data.success) {
                throw new Error(data.message || 'Gagal menghapus produk dari keranjang');
            }

            return data;
        })
        .then(data => {
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'Terjadi kesalahan saat menghapus produk dari keranjang');
            location.reload();
        });
    }
    </script>

    <?php require_once __DIR__ . '/../templates/footer.php'; ?>
</body>
</html> 