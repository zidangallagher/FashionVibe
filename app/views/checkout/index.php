<?php
require_once __DIR__ . '/../../helpers/SessionHelper.php';
require_once __DIR__ . '/../../controllers/CartController.php';

// Redirect ke login jika belum login
if (!SessionHelper::isLoggedIn()) {
    header('Location: /login');
    exit;
}

$cartController = new CartController();
$cartResult = $cartController->getCart();

if (!$cartResult['success'] || empty($cartResult['data']['items'])) {
    header('Location: /cart');
    exit;
}

$cartItems = $cartResult['data']['items'];
$totalPrice = $cartResult['data']['total'];

require_once __DIR__ . '/../templates/header.php';
?>

<div class="min-h-screen bg-gray-100 py-8">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row gap-8">
            <!-- Form Checkout -->
            <div class="md:w-2/3">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-2xl font-bold mb-6">Informasi Pengiriman</h2>
                    <form id="checkoutForm" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                                    Nama Lengkap
                                </label>
                                <input type="text" id="name" name="name" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">
                                    Nomor Telepon
                                </label>
                                <input type="tel" id="phone" name="phone" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                                Email
                            </label>
                            <input type="email" id="email" name="email" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="address">
                                Alamat Lengkap
                            </label>
                            <textarea id="address" name="address" rows="3" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="province">
                                    Provinsi
                                </label>
                                <select id="province" name="province" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Pilih Provinsi</option>
                                    <!-- Opsi provinsi akan diisi dengan JavaScript -->
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="city">
                                    Kota/Kabupaten
                                </label>
                                <select id="city" name="city" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Pilih Kota</option>
                                    <!-- Opsi kota akan diisi dengan JavaScript -->
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="postal_code">
                                    Kode Pos
                                </label>
                                <input type="text" id="postal_code" name="postal_code" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="shipping_method">
                                Metode Pengiriman
                            </label>
                            <select id="shipping_method" name="shipping_method" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih Metode Pengiriman</option>
                                <option value="regular">Regular (2-3 hari)</option>
                                <option value="express">Express (1-2 hari)</option>
                                <option value="same_day">Same Day</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="payment_method">
                                Metode Pembayaran
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label class="relative flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:border-blue-500">
                                    <input type="radio" name="payment_method" value="bank_transfer" class="mr-3" required>
                                    <span class="text-sm">Transfer Bank</span>
                                </label>
                                <label class="relative flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:border-blue-500">
                                    <input type="radio" name="payment_method" value="credit_card" class="mr-3" required>
                                    <span class="text-sm">Kartu Kredit</span>
                                </label>
                                <label class="relative flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:border-blue-500">
                                    <input type="radio" name="payment_method" value="e_wallet" class="mr-3" required>
                                    <span class="text-sm">E-Wallet</span>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Ringkasan Pesanan -->
            <div class="md:w-1/3">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <h2 class="text-xl font-bold mb-6">Ringkasan Pesanan</h2>
                    
                    <div class="space-y-4 mb-6">
                        <?php foreach ($cartItems as $item): ?>
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0 w-16 h-16">
                                <img src="<?= htmlspecialchars($item['product']['image']) ?>" 
                                     alt="<?= htmlspecialchars($item['product']['name']) ?>"
                                     class="w-full h-full object-cover rounded">
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium"><?= htmlspecialchars($item['product']['name']) ?></h3>
                                <p class="text-sm text-gray-500"><?= $item['quantity'] ?> x Rp <?= number_format($item['product']['price'], 0, ',', '.') ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="border-t pt-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span>Subtotal</span>
                            <span>Rp <?= number_format($totalPrice, 0, ',', '.') ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span>Biaya Pengiriman</span>
                            <span id="shipping_cost">-</span>
                        </div>
                        <div class="flex justify-between text-sm font-medium">
                            <span>Total</span>
                            <span id="total_price">Rp <?= number_format($totalPrice, 0, ',', '.') ?></span>
                        </div>
                    </div>

                    <button onclick="submitOrder()" 
                            class="w-full mt-6 bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        Buat Pesanan
                    </button>

                    <p class="mt-4 text-sm text-gray-500 text-center">
                        Dengan membuat pesanan, Anda menyetujui syarat dan ketentuan yang berlaku
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Fungsi untuk menangani submit pesanan
function submitOrder() {
    const form = document.getElementById('checkoutForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Tampilkan konfirmasi
    if (!confirm('Apakah Anda yakin ingin membuat pesanan ini?')) {
        return;
    }

    // Kumpulkan data form
    const formData = new FormData(form);
    const data = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
        data.append(key, value);
    }

    // Tampilkan loading
    const submitButton = document.querySelector('button[onclick="submitOrder()"]');
    const originalText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = 'Memproses...';

    // Kirim pesanan ke server
    fetch('/app/api/orders.php?action=create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json'
        },
        body: data.toString()
    })
    .then(async response => {
        const responseData = await response.json();
        if (!response.ok) {
            throw new Error(responseData.message || 'Terjadi kesalahan pada server');
        }
        return responseData;
    })
    .then(data => {
        alert(data.message || 'Pesanan berhasil dibuat!');
        window.location.href = '/orders';
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || 'Terjadi kesalahan saat membuat pesanan');
    })
    .finally(() => {
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
}

// Event listener untuk metode pengiriman
document.getElementById('shipping_method').addEventListener('change', function(e) {
    const shippingCost = calculateShippingCost(e.target.value);
    updateTotalPrice(shippingCost);
});

// Fungsi untuk menghitung biaya pengiriman
function calculateShippingCost(method) {
    const baseCost = {
        'regular': 10000,
        'express': 20000,
        'same_day': 30000
    };
    return baseCost[method] || 0;
}

// Fungsi untuk memperbarui total harga
function updateTotalPrice(shippingCost) {
    const subtotal = <?= $totalPrice ?>;
    const total = subtotal + shippingCost;
    
    document.getElementById('shipping_cost').textContent = `Rp ${formatNumber(shippingCost)}`;
    document.getElementById('total_price').textContent = `Rp ${formatNumber(total)}`;
}

// Fungsi untuk memformat angka
function formatNumber(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}

// Load data provinsi saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Simulasi data provinsi (dalam implementasi nyata, data ini akan diambil dari API)
    const provinces = [
        'DKI Jakarta',
        'Jawa Barat',
        'Jawa Tengah',
        'Jawa Timur',
        'Banten',
        'Bali'
    ];

    const provinceSelect = document.getElementById('province');
    provinces.forEach(province => {
        const option = document.createElement('option');
        option.value = province;
        option.textContent = province;
        provinceSelect.appendChild(option);
    });
});

// Event listener untuk perubahan provinsi
document.getElementById('province').addEventListener('change', function(e) {
    const citySelect = document.getElementById('city');
    citySelect.innerHTML = '<option value="">Pilih Kota</option>';

    // Simulasi data kota (dalam implementasi nyata, data ini akan diambil dari API berdasarkan provinsi yang dipilih)
    const cities = {
        'DKI Jakarta': ['Jakarta Pusat', 'Jakarta Utara', 'Jakarta Barat', 'Jakarta Selatan', 'Jakarta Timur'],
        'Jawa Barat': ['Bandung', 'Bogor', 'Depok', 'Bekasi', 'Cimahi'],
        'Jawa Tengah': ['Semarang', 'Solo', 'Yogyakarta', 'Magelang', 'Salatiga'],
        'Jawa Timur': ['Surabaya', 'Malang', 'Sidoarjo', 'Gresik', 'Pasuruan'],
        'Banten': ['Tangerang', 'Serang', 'Cilegon', 'Tangerang Selatan'],
        'Bali': ['Denpasar', 'Badung', 'Gianyar', 'Tabanan', 'Buleleng']
    };

    const selectedCities = cities[e.target.value] || [];
    selectedCities.forEach(city => {
        const option = document.createElement('option');
        option.value = city;
        option.textContent = city;
        citySelect.appendChild(option);
    });
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?> 