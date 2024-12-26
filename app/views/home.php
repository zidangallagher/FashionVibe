<?php require_once __DIR__ . '/templates/header.php'; ?>
<?php 
require_once __DIR__ . '/../controllers/ProductController.php';

// Inisialisasi ProductController
$productController = new ProductController();
$featuredResult = $productController->getFeaturedProducts();
$featuredProducts = $featuredResult['success'] ? $featuredResult['data'] : [];
?>

<div class="flex flex-col md:flex-row items-center justify-between py-12">
    <div class="md:w-1/2 mb-8 md:mb-0">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">Temukan Gaya Fashion Terbaikmu</h1>
        <p class="text-lg text-gray-600 mb-8">Koleksi pakaian terbaru dengan kualitas terbaik untuk tampilan yang sempurna.</p>
        <div class="flex space-x-4">
            <a href="/products" class="bg-blue-600 text-white px-6 py-3 rounded-lg text-lg font-medium hover:bg-blue-700 transition-colors">
                Belanja Sekarang
            </a>
            <a href="/categories" class="border-2 border-blue-600 text-blue-600 px-6 py-3 rounded-lg text-lg font-medium hover:bg-blue-50 transition-colors">
                Lihat Kategori
            </a>
        </div>
    </div>
    <div class="md:w-1/2 flex justify-center">
        <lottie-player
            src="https://assets2.lottiefiles.com/packages/lf20_kkflmtur.json"
            background="transparent"
            speed="1"
            style="width: 400px; height: 400px;"
            loop
            autoplay>
        </lottie-player>
    </div>
</div>

<div class="mt-16">
    <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Produk Unggulan</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php foreach ($featuredProducts as $product): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow">
            <img src="<?= htmlspecialchars($product->image) ?>" alt="<?= htmlspecialchars($product->name) ?>" class="w-full h-64 object-cover">
            <div class="p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-2"><?= htmlspecialchars($product->name) ?></h3>
                <p class="text-gray-600 mb-2"><?= htmlspecialchars($product->description) ?></p>
                <p class="text-xl font-bold text-gray-900 mb-4">Rp <?= number_format($product->price, 0, ',', '.') ?></p>
                <a href="/products/<?= (string)$product->_id ?>" class="block text-center bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    Lihat Detail
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="mt-16 bg-blue-50 rounded-xl p-8">
    <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Mengapa Memilih FashionVibe?</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="text-center">
            <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2">Kualitas Terjamin</h3>
            <p class="text-gray-600">Produk berkualitas tinggi dengan bahan terbaik</p>
        </div>
        <div class="text-center">
            <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2">Pengiriman Cepat</h3>
            <p class="text-gray-600">Pengiriman ke seluruh Indonesia</p>
        </div>
        <div class="text-center">
            <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2">Pembayaran Aman</h3>
            <p class="text-gray-600">Transaksi aman dan terpercaya</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?> 