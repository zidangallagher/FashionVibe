<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Default admin credentials if not set in .env
if (!isset($_ENV['ADMIN_EMAIL'])) {
    $_ENV['ADMIN_EMAIL'] = 'admin@fashionvibe.com';
}
if (!isset($_ENV['ADMIN_PASSWORD'])) {
    $_ENV['ADMIN_PASSWORD'] = 'admin123456';
}

echo "Using admin email: {$_ENV['ADMIN_EMAIL']}\n";
echo "Default admin password: {$_ENV['ADMIN_PASSWORD']}\n";

// Run migrations
try {
    $db = Database::getInstance();
    $db->createCollections();
    
    // Create default admin user if not exists
    $users = $db->getCollection('users');
    $adminUser = $users->findOne(['email' => $_ENV['ADMIN_EMAIL']]);
    
    if (!$adminUser) {
        $users->insertOne([
            'email' => $_ENV['ADMIN_EMAIL'],
            'password' => password_hash($_ENV['ADMIN_PASSWORD'], PASSWORD_DEFAULT),
            'role' => 'admin',
            'name' => 'Admin FashionVibe',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]);
        echo "Default admin user created successfully with email: {$_ENV['ADMIN_EMAIL']}\n";
    } else {
        echo "Admin user already exists with email: {$_ENV['ADMIN_EMAIL']}\n";
    }

    // Create default categories if not exists
    $categories = $db->getCollection('categories');
    $defaultCategories = [
        [
            'name' => 'Kemeja',
            'description' => 'Koleksi kemeja pria dan wanita dengan berbagai model',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Celana',
            'description' => 'Koleksi celana pria dan wanita dengan berbagai gaya',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Kaos',
            'description' => 'Koleksi kaos dan t-shirt dengan desain modern',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Jaket',
            'description' => 'Koleksi jaket dan outerwear dengan gaya trendy',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]
    ];

    // Hapus kategori yang ada dan buat ulang
    $categories->deleteMany([]);
    foreach ($defaultCategories as $category) {
        $categories->insertOne($category);
        echo "Category '{$category['name']}' created successfully\n";
    }

    // Create sample products with verified images
    $products = $db->getCollection('products');
    $sampleProducts = [
        [
            'name' => 'Kaos Essential Black',
            'description' => 'Kaos hitam essential dengan bahan premium cotton combed 30s. Nyaman dipakai sehari-hari.',
            'price' => 89000.0,
            'stock' => 50,
            'category' => 'Kaos',
            'image' => 'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?q=80&w=1000&auto=format&fit=crop',
            'is_featured' => true,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Celana Jeans Slim Navy',
            'description' => 'Celana jeans slim fit dengan warna navy yang klasik. Cocok untuk casual dan semi-formal.',
            'price' => 299000.0,
            'stock' => 30,
            'category' => 'Celana',
            'image' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?q=80&w=1000&auto=format&fit=crop',
            'is_featured' => false,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Kemeja Flanel Premium',
            'description' => 'Kemeja flanel premium dengan motif kotak-kotak. Bahan tebal dan nyaman untuk cuaca dingin.',
            'price' => 159000.0,
            'stock' => 25,
            'category' => 'Kemeja',
            'image' => 'https://images.unsplash.com/photo-1589310243389-96a5483213a8?q=80&w=1000&auto=format&fit=crop',
            'is_featured' => true,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Jaket Denim Classic',
            'description' => 'Jaket denim klasik dengan washing sempurna. Cocok untuk gaya casual dan streetwear.',
            'price' => 399000.0,
            'stock' => 15,
            'category' => 'Jaket',
            'image' => 'https://images.unsplash.com/photo-1495105787522-5334e3ffa0ef?q=80&w=1000&auto=format&fit=crop',
            'is_featured' => true,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        // Produk tambahan untuk beranda
        [
            'name' => 'Kemeja Oxford White',
            'description' => 'Kemeja oxford putih klasik dengan bahan premium. Perfect untuk acara formal dan kantor.',
            'price' => 259000.0,
            'stock' => 20,
            'category' => 'Kemeja',
            'image' => 'https://images.unsplash.com/photo-1598033129183-c4f50c736f10?q=80&w=1000&auto=format&fit=crop',
            'is_featured' => true,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Kaos Stripe Navy',
            'description' => 'Kaos bergaris dengan kombinasi warna navy dan putih. Casual dan stylish.',
            'price' => 129000.0,
            'stock' => 35,
            'category' => 'Kaos',
            'image' => 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?q=80&w=1000&auto=format&fit=crop',
            'is_featured' => false,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Jaket Bomber Green',
            'description' => 'Jaket bomber dengan warna army green yang trendy. Cocok untuk gaya street style.',
            'price' => 449000.0,
            'stock' => 18,
            'category' => 'Jaket',
            'image' => 'https://images.unsplash.com/photo-1592878904946-b3cd8ae243d0?q=80&w=1000&auto=format&fit=crop',
            'is_featured' => true,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Celana Chino Khaki',
            'description' => 'Celana chino dengan warna khaki klasik. Versatile dan nyaman dipakai.',
            'price' => 279000.0,
            'stock' => 28,
            'category' => 'Celana',
            'image' => 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?q=80&w=1000&auto=format&fit=crop',
            'is_featured' => true,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]
    ];

    // Hapus produk yang ada dan buat ulang dengan verifikasi gambar
    $products->deleteMany([]);
    foreach ($sampleProducts as $product) {
        // Verifikasi gambar bisa diakses
        $headers = @get_headers($product['image']);
        if ($headers && strpos($headers[0], '200') !== false) {
            // Verifikasi kategori ada
            $category = $categories->findOne(['name' => $product['category']]);
            if ($category) {
                $result = $products->insertOne($product);
                echo "Product '{$product['name']}' created successfully\n";
                if ($product['is_featured']) {
                    echo "-> Added to featured products\n";
                }
            } else {
                echo "Failed to create product '{$product['name']}': Category not found\n";
            }
        } else {
            echo "Failed to create product '{$product['name']}': Image URL not accessible\n";
        }
    }

    echo "\nAll migrations completed successfully\n";
    echo "You can now login as admin with:\n";
    echo "Email: {$_ENV['ADMIN_EMAIL']}\n";
    echo "Password: {$_ENV['ADMIN_PASSWORD']}\n";
} catch (\Exception $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
} 