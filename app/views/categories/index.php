<?php
require_once __DIR__ . '/../../controllers/ProductController.php';
require_once __DIR__ . '/../templates/header.php';

$productController = new ProductController();
$categories = $productController->getAllCategories();
$categoryList = $categories['success'] ? $categories['data'] : [];
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Kategori Produk</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($categoryList as $category): ?>
            <a href="/products?category=<?= urlencode($category->name) ?>" 
               class="block bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-2"><?= htmlspecialchars($category->name) ?></h2>
                    <p class="text-gray-600 text-sm mb-4"><?= htmlspecialchars($category->description) ?></p>
                    <div class="flex items-center text-blue-600">
                        <span class="text-sm font-medium">Lihat Produk</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>

        <?php if (empty($categoryList)): ?>
            <div class="col-span-full text-center py-8">
                <p class="text-gray-500">Tidak ada kategori yang ditemukan</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?> 