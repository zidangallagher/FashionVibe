<?php
require_once __DIR__ . '/../../controllers/ProductController.php';
require_once __DIR__ . '/../templates/header.php';

$productController = new ProductController();
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$category = isset($_GET['category']) ? $_GET['category'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : null;

if ($search) {
    $result = $productController->searchProducts($search, $category);
    $products = $result['success'] ? $result['data'] : [];
    $totalPages = 1;
} else {
    $result = $productController->getAllProducts($page, 12, $category);
    if ($result['success']) {
        $products = $result['data']['products'];
        $totalPages = $result['data']['total_pages'];
    } else {
        $products = [];
        $totalPages = 1;
    }
}

$categories = $productController->getAllCategories();
$categoryList = $categories['success'] ? $categories['data'] : [];
?>

<div class="container mx-auto px-4 py-8">
    <!-- Search and Filter Section -->
    <div class="mb-8">
        <form action="/products" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Cari produk..." class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="w-full md:w-48">
                <select name="category" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categoryList as $cat): ?>
                        <option value="<?= htmlspecialchars($cat->name) ?>" <?= $category === $cat->name ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Cari
            </button>
        </form>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($products as $product): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <img src="<?= htmlspecialchars($product->image) ?>" alt="<?= htmlspecialchars($product->name) ?>" class="w-full h-64 object-cover">
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= htmlspecialchars($product->name) ?></h3>
                    <p class="text-gray-600 text-sm mb-2 line-clamp-2"><?= htmlspecialchars($product->description) ?></p>
                    <div class="flex items-center justify-between mt-4">
                        <span class="text-xl font-bold text-gray-900">Rp <?= number_format($product->price, 0, ',', '.') ?></span>
                        <a href="/products/<?= (string)$product->_id ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Detail
                        </a>
                    </div>
                    <div class="mt-2 text-sm text-gray-500">
                        Stok: <?= $product->stock ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($products)): ?>
            <div class="col-span-full text-center py-8">
                <p class="text-gray-500">Tidak ada produk yang ditemukan</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1 && !$search): ?>
        <div class="mt-8 flex justify-center">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?><?= $category ? '&category=' . urlencode($category) : '' ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only">Previous</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-blue-50 text-sm font-medium text-blue-600">
                            <?= $i ?>
                        </span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?><?= $category ? '&category=' . urlencode($category) : '' ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                            <?= $i ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?><?= $category ? '&category=' . urlencode($category) : '' ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only">Next</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?> 