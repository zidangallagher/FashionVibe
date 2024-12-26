<?php
require_once __DIR__ . '/../../middleware/AdminMiddleware.php';
AdminMiddleware::handle();

require_once __DIR__ . '/../templates/admin_header.php';
require_once __DIR__ . '/../../controllers/ProductController.php';

$productController = new ProductController();
$productId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$productId) {
    header('Location: /admin/products');
    exit;
}

$productResult = $productController->getProductById($productId);
if (!$productResult['success']) {
    header('Location: /admin/products');
    exit;
}

$product = $productResult['data'];
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Edit Produk</h1>
            <a href="/admin/products" class="text-blue-600 hover:text-blue-800">
                Kembali ke Daftar Produk
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <form id="editProductForm" class="space-y-6">
                <input type="hidden" id="productId" value="<?= htmlspecialchars($product->_id) ?>">
                
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Produk</label>
                    <input type="text" id="name" name="name" required
                        value="<?= htmlspecialchars($product->name) ?>"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea id="description" name="description" rows="4" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($product->description) ?></textarea>
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">Kategori</label>
                    <select id="category" name="category" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Kategori</option>
                        <option value="Kemeja" <?= $product->category === 'Kemeja' ? 'selected' : '' ?>>Kemeja</option>
                        <option value="Celana" <?= $product->category === 'Celana' ? 'selected' : '' ?>>Celana</option>
                        <option value="Kaos" <?= $product->category === 'Kaos' ? 'selected' : '' ?>>Kaos</option>
                        <option value="Jaket" <?= $product->category === 'Jaket' ? 'selected' : '' ?>>Jaket</option>
                    </select>
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Harga (Rp)</label>
                    <input type="number" id="price" name="price" required min="0"
                        value="<?= $product->price ?>"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700">Stok</label>
                    <input type="number" id="stock" name="stock" required min="0"
                        value="<?= $product->stock ?>"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">URL Gambar</label>
                    <input type="url" id="image" name="image" required
                        value="<?= htmlspecialchars($product->image) ?>"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-sm text-gray-500">Masukkan URL gambar dari Unsplash atau sumber lainnya</p>
                </div>

                <!-- Preview Gambar -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Preview Gambar</label>
                    <img src="<?= htmlspecialchars($product->image) ?>" alt="Preview" class="w-full max-w-md h-auto rounded-lg shadow-sm">
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="window.location.href='/admin/products'"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('editProductForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const productId = document.getElementById('productId').value;
    const formData = {
        name: document.getElementById('name').value,
        description: document.getElementById('description').value,
        category: document.getElementById('category').value,
        price: parseFloat(document.getElementById('price').value),
        stock: parseInt(document.getElementById('stock').value),
        image: document.getElementById('image').value
    };

    // Tampilkan loading state
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Menyimpan...';
    submitButton.disabled = true;

    fetch('/api/products/' + productId, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Produk berhasil diperbarui');
            window.location.href = '/admin/products';
        } else {
            alert('Gagal memperbarui produk: ' + data.message);
            // Reset button state
            submitButton.textContent = originalText;
            submitButton.disabled = false;
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan: ' + error.message);
        // Reset button state
        submitButton.textContent = originalText;
        submitButton.disabled = false;
    });
});

// Preview gambar saat URL berubah
document.getElementById('image').addEventListener('input', function(e) {
    const imageUrl = e.target.value;
    const previewImg = document.querySelector('img[alt="Preview"]');
    if (imageUrl) {
        previewImg.src = imageUrl;
    }
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?> 