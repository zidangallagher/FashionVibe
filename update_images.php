<?php
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::getInstance();
    $products = $db->getCollection('products');
    
    // Array gambar baru
    $newImages = [
        'Kaos Essential Black' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=1000&auto=format&fit=crop',
        'Celana Jeans Slim Navy' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?q=80&w=1000&auto=format&fit=crop',
        'Kemeja Flanel Premium' => 'https://images.unsplash.com/photo-1589310243389-96a5483213a8?q=80&w=1000&auto=format&fit=crop',
        'Jaket Denim Classic' => 'https://images.unsplash.com/photo-1495105787522-5334e3ffa0ef?q=80&w=1000&auto=format&fit=crop'
    ];
    
    // Update gambar untuk setiap produk
    foreach ($newImages as $productName => $imageUrl) {
        // Verifikasi gambar bisa diakses
        $headers = @get_headers($imageUrl);
        if ($headers && strpos($headers[0], '200') !== false) {
            $result = $products->updateOne(
                ['name' => $productName],
                ['$set' => ['image' => $imageUrl]]
            );
            
            if ($result->getModifiedCount() > 0) {
                echo "Berhasil mengupdate gambar untuk produk: {$productName}<br>";
                echo "URL Gambar Baru: {$imageUrl}<br><br>";
            } else {
                echo "Tidak ada perubahan untuk produk: {$productName}<br>";
            }
        } else {
            echo "Gagal mengupdate gambar untuk {$productName}: URL tidak bisa diakses<br>";
        }
    }
    
    // Tampilkan semua produk setelah update
    echo "<h2>Produk Setelah Update:</h2>";
    $allProducts = $products->find([]);
    foreach ($allProducts as $product) {
        echo "<div style='margin: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 8px;'>";
        echo "<h3>{$product->name}</h3>";
        echo "<img src='{$product->image}' style='max-width: 300px; height: auto; border-radius: 4px;' alt='{$product->name}'><br>";
        echo "<p><strong>Kategori:</strong> {$product->category}</p>";
        echo "<p><strong>URL Gambar:</strong> {$product->image}</p>";
        echo "</div>";
    }
    
    echo "<br><a href='/products'>Kembali ke halaman produk</a>";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
    error_log("Error in update_images.php: " . $e->getMessage());
} 