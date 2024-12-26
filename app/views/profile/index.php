<?php
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../../controllers/WishlistController.php';

// Redirect ke halaman login jika belum login
if (!isset($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

$user = $_SESSION['user'];
$wishlistController = new WishlistController();
$wishlistResult = $wishlistController->getWishlist();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <!-- Profile Header -->
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Profil Pengguna
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Informasi detail akun Anda
            </p>
        </div>
        
        <!-- Profile Information -->
        <div class="px-4 py-5 sm:p-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">
                        Nama Lengkap
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <?php echo htmlspecialchars($user['name']); ?>
                    </dd>
                </div>
                
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">
                        Email
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <?php echo htmlspecialchars($user['email']); ?>
                    </dd>
                </div>
                
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">
                        Role
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Wishlist Section -->
    <div class="mt-8">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Wishlist Saya
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Daftar produk yang Anda simpan
                </p>
            </div>
            
            <div class="px-4 py-5 sm:p-6">
                <?php if ($wishlistResult['success'] && !empty($wishlistResult['data'])): ?>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <?php foreach ($wishlistResult['data'] as $product): ?>
                            <div class="relative bg-white border rounded-lg shadow-sm overflow-hidden">
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     class="w-full h-48 object-cover">
                                     
                                <div class="p-4">
                                    <h4 class="text-lg font-medium text-gray-900">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </h4>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                    </p>
                                    
                                    <div class="mt-4 flex justify-between">
                                        <button onclick="removeFromWishlist('<?php echo $product['id']; ?>')"
                                                class="inline-flex items-center px-3 py-2 border border-red-300 text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            Hapus dari Wishlist
                                        </button>
                                        <a href="/products/<?php echo $product['id']; ?>"
                                           class="inline-flex items-center px-3 py-2 border border-blue-300 text-sm leading-4 font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Lihat Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <p class="text-gray-500">Belum ada produk di wishlist Anda</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
async function removeFromWishlist(productId) {
    try {
        const response = await fetch(`/api/wishlist/remove/${productId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Reload halaman untuk memperbarui wishlist
            window.location.reload();
        } else {
            alert(data.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan saat menghapus produk dari wishlist');
    }
}
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?> 