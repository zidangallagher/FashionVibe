<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::getInstance();
    
    // Buat kategori terlebih dahulu
    $categories = $db->getCollection('categories');
    $defaultCategories = [
        [
            'name' => 'Kemeja',
            'description' => 'Koleksi kemeja pria dan wanita',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Celana',
            'description' => 'Koleksi celana pria dan wanita',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Kaos',
            'description' => 'Koleksi kaos dan t-shirt',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Jaket',
            'description' => 'Koleksi jaket pria dan wanita',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]
    ];

    // Hapus kategori lama dan buat yang baru
    $categories->deleteMany([]);
    foreach ($defaultCategories as $category) {
        $categories->insertOne($category);
        echo "Kategori '{$category['name']}' berhasil dibuat<br>";
    }
    
    // Hapus semua produk yang ada
    $products = $db->getCollection('products');
    $products->deleteMany([]);
    echo "<br>Berhasil menghapus semua produk lama<br><br>";
    
    // Produk baru dengan gambar yang pasti bisa dimuat
    $newProducts = [
        [
            'name' => 'Kaos Essential Black',
            'description' => 'Kaos hitam essential dengan bahan premium cotton combed 30s. Nyaman dipakai sehari-hari.',
            'price' => 89000,
            'stock' => 50,
            'category' => 'Kaos',
            'image' => 'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?q=80&w=1000&auto=format&fit=crop',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Celana Jeans Slim Navy',
            'description' => 'Celana jeans slim fit dengan warna navy yang klasik. Cocok untuk casual dan semi-formal.',
            'price' => 299000,
            'stock' => 30,
            'category' => 'Celana',
            'image' => 'https://images.unsplash.com/photo-1542272604-787c3835535d?q=80&w=1000&auto=format&fit=crop',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Kemeja Flanel Premium',
            'description' => 'Kemeja flanel premium dengan motif kotak-kotak. Bahan tebal dan nyaman untuk cuaca dingin.',
            'price' => 159000,
            'stock' => 25,
            'category' => 'Kemeja',
            'image' => 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?q=80&w=1000&auto=format&fit=crop',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Jaket Denim Classic',
            'description' => 'Jaket denim klasik dengan washing sempurna. Cocok untuk gaya casual dan streetwear.',
            'price' => 399000,
            'stock' => 15,
            'category' => 'Jaket',
            'image' => 'https://images.unsplash.com/photo-1551537482-f2075a1d41f2?q=80&w=1000&auto=format&fit=crop',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]
    ];
    
    // Tambahkan produk baru dengan verifikasi gambar
    foreach ($newProducts as $product) {
        // Verifikasi gambar bisa diakses
        $headers = @get_headers($product['image']);
        if ($headers && strpos($headers[0], '200') !== false) {
            // Verifikasi kategori ada
            $category = $categories->findOne(['name' => $product['category']]);
            if ($category) {
                $result = $products->insertOne($product);
                if ($result->getInsertedCount() > 0) {
                    echo "Berhasil menambahkan produk: {$product['name']}<br>";
                    echo "Kategori: {$product['category']}<br>";
                    echo "URL Gambar: {$product['image']}<br><br>";
                }
            } else {
                echo "Gagal menambahkan produk {$product['name']}: Kategori tidak ditemukan<br>";
            }
        } else {
            echo "Gagal menambahkan produk {$product['name']}: URL gambar tidak bisa diakses<br>";
        }
    }
    
    echo "<br>Selesai mengupdate produk. <a href='/products'>Kembali ke halaman produk</a>";
    
    // Tampilkan preview produk
    echo "<h2>Preview Produk:</h2>";
    foreach ($newProducts as $product) {
        echo "<div style='margin: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 8px;'>";
        echo "<h3>{$product['name']}</h3>";
        echo "<img src='{$product['image']}' style='max-width: 300px; height: auto; border-radius: 4px;' alt='{$product['name']}'><br>";
        echo "<p><strong>Kategori:</strong> {$product['category']}</p>";
        echo "<p><strong>Harga:</strong> Rp " . number_format($product['price'], 0, ',', '.') . "</p>";
        echo "<p><strong>Stok:</strong> {$product['stock']}</p>";
        echo "<p>{$product['description']}</p>";
        echo "</div>";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
    error_log("Error in update_products.php: " . $e->getMessage());
} 