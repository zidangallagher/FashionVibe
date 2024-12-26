<?php
require_once __DIR__ . '/../../controllers/ProductController.php';
require_once __DIR__ . '/../../controllers/WishlistController.php';
require_once __DIR__ . '/../templates/header.php';

$productController = new ProductController();
$productId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$productId) {
    header('Location: /products');
    exit();
}

$result = $productController->getProductById($productId);
if (!$result['success']) {
    header('Location: /products');
    exit();
}

$product = $result['data'];

// Check if product is in wishlist
$inWishlist = false;
if (isset($_SESSION['user'])) {
    $wishlistController = new WishlistController();
    $wishlist = $wishlistController->getWishlist();
    if ($wishlist['success']) {
        foreach ($wishlist['data'] as $item) {
            if ($item['id'] === $_GET['id']) {
                $inWishlist = true;
                break;
            }
        }
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row -mx-4">
            <!-- Product Image -->
            <div class="md:flex-1 px-4">
                <div class="h-[460px] rounded-lg bg-gray-100 mb-4">
                    <img class="w-full h-full object-cover rounded-lg" src="<?= htmlspecialchars($product->image) ?>" alt="<?= htmlspecialchars($product->name) ?>">
                </div>
            </div>
            
            <!-- Product Details -->
            <div class="md:flex-1 px-4">
                <h2 class="text-3xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($product->name) ?></h2>
                <div class="flex mb-4">
                    <div class="mr-4">
                        <span class="font-bold text-gray-700">Kategori:</span>
                        <span class="text-gray-600"><?= htmlspecialchars($product->category) ?></span>
                    </div>
                    <div>
                        <span class="font-bold text-gray-700">Stok:</span>
                        <span class="text-gray-600"><?= $product->stock ?></span>
                    </div>
                </div>
                <div class="flex items-center mb-4">
                    <span class="font-bold text-gray-700 mr-2">Harga:</span>
                    <span class="text-2xl font-bold text-blue-600">Rp <?= number_format($product->price, 0, ',', '.') ?></span>
                </div>
                <div class="mb-4">
                    <span class="font-bold text-gray-700">Deskripsi:</span>
                    <p class="text-gray-600 mt-2"><?= htmlspecialchars($product->description) ?></p>
                </div>
                
                <?php if ($product->stock > 0): ?>
                <div class="mb-4">
                    <label class="font-bold text-gray-700">Jumlah:</label>
                    <div class="flex items-center mt-2">
                        <button type="button" onclick="updateQuantity(-1)" class="text-gray-500 focus:outline-none focus:text-gray-600">
                            <svg class="h-5 w-5" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M20 12H4"></path>
                            </svg>
                        </button>
                        <input class="mx-2 border text-center w-16" type="number" value="1" min="1" max="<?= $product->stock ?>">
                        <button type="button" onclick="updateQuantity(1)" class="text-gray-500 focus:outline-none focus:text-gray-600">
                            <svg class="h-5 w-5" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex space-x-4">
                    <button onclick="addToCart('<?= $productId ?>')" 
                            class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="mr-2">
                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </span>
                        Tambah ke Keranjang
                    </button>
                    
                    <button onclick="toggleWishlist('<?= $productId ?>')" 
                            id="wishlistBtn"
                            class="flex-1 px-6 py-3 border <?= $inWishlist ? 'bg-red-100 border-red-500 text-red-600' : 'border-gray-300 text-gray-700' ?> rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="mr-2">
                            <svg class="w-5 h-5 inline" fill="<?= $inWishlist ? 'currentColor' : 'none' ?>" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </span>
                        <?= $inWishlist ? 'Hapus dari Wishlist' : 'Tambah ke Wishlist' ?>
                    </button>
                </div>
                <?php else: ?>
                <div class="mb-4">
                    <span class="inline-block bg-red-100 text-red-700 px-4 py-2 rounded">Stok Habis</span>
                </div>
                <?php endif; ?>

                <?php if (!isset($_SESSION['user'])): ?>
                <p class="mt-4 text-sm text-gray-500">
                    <a href="/login" class="text-blue-600 hover:underline">Login</a> untuk menambahkan ke wishlist
                </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Related Products -->
        <div class="mt-16">
            <h3 class="text-2xl font-bold text-gray-800 mb-6">Produk Terkait</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <?php
                $relatedProducts = $productController->getAllProducts(1, 4, $product->category);
                if ($relatedProducts['success']) {
                    foreach ($relatedProducts['data']['products'] as $relatedProduct):
                        if ($relatedProduct->_id != $product->_id):
                ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <img src="<?= htmlspecialchars($relatedProduct->image) ?>" alt="<?= htmlspecialchars($relatedProduct->name) ?>" class="w-full h-48 object-cover">
                        <div class="p-4">
                            <h4 class="text-lg font-semibold text-gray-900 mb-2"><?= htmlspecialchars($relatedProduct->name) ?></h4>
                            <p class="text-gray-600 text-sm mb-2 line-clamp-2"><?= htmlspecialchars($relatedProduct->description) ?></p>
                            <div class="flex items-center justify-between mt-4">
                                <span class="text-lg font-bold text-gray-900">Rp <?= number_format($relatedProduct->price, 0, ',', '.') ?></span>
                                <a href="/products/<?= (string)$relatedProduct->_id ?>" class="text-blue-600 hover:text-blue-700">Detail</a>
                            </div>
                        </div>
                    </div>
                <?php
                        endif;
                    endforeach;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script>
function toggleWishlist(productId) {
    <?php if (!isset($_SESSION['user'])): ?>
    window.location.href = '/login';
    return;
    <?php endif; ?>

    const btn = document.getElementById('wishlistBtn');
    const isInWishlist = btn.classList.contains('bg-red-100');
    const url = `/api/wishlist/${isInWishlist ? 'remove' : 'add'}/${productId}`;

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (isInWishlist) {
                btn.classList.remove('bg-red-100', 'border-red-500', 'text-red-600');
                btn.classList.add('border-gray-300', 'text-gray-700');
                btn.querySelector('svg').setAttribute('fill', 'none');
            } else {
                btn.classList.remove('border-gray-300', 'text-gray-700');
                btn.classList.add('bg-red-100', 'border-red-500', 'text-red-600');
                btn.querySelector('svg').setAttribute('fill', 'currentColor');
            }
            // Tambahkan kembali ikon setelah mengubah textContent
            const icon = `<span class="mr-2">
                <svg class="w-5 h-5 inline" fill="${isInWishlist ? 'none' : 'currentColor'}" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
            </span>`;
            btn.innerHTML = icon + (isInWishlist ? 'Tambah ke Wishlist' : 'Hapus dari Wishlist');
        }
        alert(data.message);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memproses permintaan');
    });
}

function addToCart(productId) {
    <?php if (!isset($_SESSION['user'])): ?>
    window.location.href = '/login';
    return;
    <?php endif; ?>

    const quantityInput = document.querySelector('input[type="number"]');
    const quantity = parseInt(quantityInput.value);

    if (isNaN(quantity) || quantity < 1) {
        alert('Jumlah produk tidak valid');
        return;
    }

    // Tampilkan loading
    const addButton = event.currentTarget;
    const originalText = addButton.innerHTML;
    addButton.disabled = true;
    addButton.innerHTML = 'Menambahkan...';

    // Gunakan URLSearchParams untuk mengirim data
    const data = new URLSearchParams();
    data.append('product_id', productId);
    data.append('quantity', quantity);

    fetch('/app/api/cart.php?action=add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json'
        },
        body: data.toString()
    })
    .then(async response => {
        let responseData;
        const contentType = response.headers.get('content-type');
        const responseText = await response.text();
        
        try {
            responseData = JSON.parse(responseText);
        } catch (e) {
            console.error('Failed to parse JSON:', responseText);
            throw new Error('Server tidak mengembalikan response JSON yang valid');
        }

        if (!response.ok) {
            throw new Error(responseData.message || 'Terjadi kesalahan pada server');
        }

        if (!responseData.success) {
            throw new Error(responseData.message || 'Gagal menambahkan produk ke keranjang');
        }

        return responseData;
    })
    .then(data => {
        alert(data.message || 'Produk berhasil ditambahkan ke keranjang');
        window.location.href = '/cart';
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || 'Terjadi kesalahan saat menambahkan ke keranjang');
    })
    .finally(() => {
        // Kembalikan tombol ke keadaan semula
        addButton.disabled = false;
        addButton.innerHTML = originalText;
    });
}

function updateQuantity(change) {
    const input = document.querySelector('input[type="number"]');
    const currentValue = parseInt(input.value);
    const maxStock = parseInt(input.getAttribute('max'));
    
    let newValue = currentValue + change;
    
    if (newValue < 1) {
        newValue = 1;
    } else if (newValue > maxStock) {
        newValue = maxStock;
        alert('Jumlah melebihi stok yang tersedia');
    }
    
    input.value = newValue;
}
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?> 