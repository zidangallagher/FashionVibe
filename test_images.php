<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::getInstance();
    $products = $db->getCollection('products');
    
    // Debug: Tampilkan semua produk yang ada sebelum update
    echo "<h2>Debug: Existing Products Before Update</h2>";
    $existingProducts = $products->find([]);
    foreach ($existingProducts as $prod) {
        error_log("Before Update - Product: {$prod->name}, Image: {$prod->image}");
    }
    
    // Menambahkan array URL gambar Unsplash dengan URL yang lebih spesifik
    $unsplashImages = [
        'Kemeja Casual Modern' => 'https://plus.unsplash.com/premium_photo-1671149028241-a35a315cb203?q=80&w=1974&auto=format&fit=crop',
        'Dress Elegant' => 'https://images.unsplash.com/photo-1585487000160-6ebcfceb0d03?q=80&w=1934&auto=format&fit=crop',
        'Celana Chino Slim' => 'https://images.unsplash.com/photo-1584865288642-42078afe6942?q=80&w=1973&auto=format&fit=crop'
    ];
    
    // Update URL gambar di database dengan pengecekan
    foreach ($unsplashImages as $productName => $imageUrl) {
        // Hapus produk yang ada terlebih dahulu
        $products->deleteMany(['name' => $productName]);
        error_log("Deleted existing product: {$productName}");
        
        // Insert produk baru dengan data lengkap
        $result = $products->insertOne([
            'name' => $productName,
            'description' => 'Deskripsi ' . $productName,
            'price' => 100000,
            'stock' => 10,
            'category' => 'Fashion',
            'image' => $imageUrl,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]);
        error_log("Inserted new product {$productName} with URL: {$imageUrl}");
    }
    
    // Debug: Tampilkan semua produk setelah update
    echo "<h2>Debug: Updated Products</h2>";
    $updatedProducts = $products->find([]);
    foreach ($updatedProducts as $prod) {
        error_log("After Update - Product: {$prod->name}, Image: {$prod->image}");
    }
    
    $allProducts = $products->find([]);
    
    echo "<html><head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<style>
        .product-card {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .product-image {
            max-width: 300px;
            height: auto;
            border-radius: 4px;
            display: block;
            margin: 10px 0;
        }
        .status-text {
            margin-top: 8px;
            font-size: 14px;
        }
        .debug-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            word-break: break-all;
            background: #f5f5f5;
            padding: 5px;
            border-radius: 4px;
        }
    </style>";
    echo "</head><body style='font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px;'>";
    echo "<h1>Test Gambar Produk</h1>";
    
    foreach ($allProducts as $product) {
        echo "<div class='product-card'>";
        echo "<h2>{$product->name}</h2>";
        
        // Debug: Tampilkan informasi lengkap produk
        echo "<pre class='debug-info'>";
        print_r(json_decode(json_encode($product), true));
        echo "</pre>";
        
        // Pastikan menggunakan URL Unsplash, bukan path lokal
        $imageUrl = $unsplashImages[$product->name] ?? $product->image;
        echo "<img class='product-image' src='{$imageUrl}' alt='{$product->name}' crossorigin='anonymous' onerror=\"this.onerror=null; console.error('Failed to load:', this.src);\" />";
        echo "<p class='status-text'>Status gambar: <span id='status_{$product->_id}'></span></p>";
        echo "<p class='debug-info'>URL Gambar: {$imageUrl}</p>";
        echo "</div>";
    }
    
    // JavaScript untuk mengecek status gambar dengan error handling yang lebih baik
    echo "<script>
        document.querySelectorAll('img').forEach(img => {
            img.onload = function() {
                const id = this.parentElement.querySelector('p span').id;
                document.getElementById(id).innerHTML = '<span style=\"color: green;\">Berhasil dimuat</span>';
                console.log('Successfully loaded:', this.src);
            };
            img.onerror = function() {
                const id = this.parentElement.querySelector('p span').id;
                document.getElementById(id).innerHTML = '<span style=\"color: red;\">Gagal dimuat: ' + this.src + '</span>';
                console.error('Failed to load image:', this.src);
            };
        });
    </script>";
    echo "</body></html>";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
    error_log("Error in test_images.php: " . $e->getMessage());
} 