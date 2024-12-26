<?php
require_once __DIR__ . '/../config/database.php';

class WishlistController {
    private $db;
    private $wishlists;
    private $products;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->wishlists = $this->db->getCollection('wishlists');
        $this->products = $this->db->getCollection('products');
    }

    public function addToWishlist($productId) {
        try {
            if (!isset($_SESSION['user'])) {
                return [
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu'
                ];
            }

            $userId = $_SESSION['user']['_id'];
            
            // Check if product exists
            $product = $this->products->findOne(['_id' => new MongoDB\BSON\ObjectId($productId)]);
            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Produk tidak ditemukan'
                ];
            }

            // Check if product already in wishlist
            $existingWishlist = $this->wishlists->findOne([
                'user_id' => $userId,
                'product_id' => $productId
            ]);

            if ($existingWishlist) {
                return [
                    'success' => false,
                    'message' => 'Produk sudah ada di wishlist'
                ];
            }

            // Add to wishlist
            $this->wishlists->insertOne([
                'user_id' => $userId,
                'product_id' => $productId,
                'created_at' => new MongoDB\BSON\UTCDateTime()
            ]);

            return [
                'success' => true,
                'message' => 'Produk berhasil ditambahkan ke wishlist'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    public function removeFromWishlist($productId) {
        try {
            if (!isset($_SESSION['user'])) {
                return [
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu'
                ];
            }

            $userId = $_SESSION['user']['_id'];

            $result = $this->wishlists->deleteOne([
                'user_id' => $userId,
                'product_id' => $productId
            ]);

            if ($result->getDeletedCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Produk berhasil dihapus dari wishlist'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Produk tidak ditemukan di wishlist'
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    public function getWishlist() {
        try {
            if (!isset($_SESSION['user'])) {
                return [
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu'
                ];
            }

            $userId = $_SESSION['user']['_id'];

            $wishlistItems = $this->wishlists->find(['user_id' => $userId]);
            $products = [];

            foreach ($wishlistItems as $item) {
                $product = $this->products->findOne(['_id' => new MongoDB\BSON\ObjectId($item->product_id)]);
                if ($product) {
                    $products[] = [
                        'id' => (string) $product->_id,
                        'name' => $product->name,
                        'price' => $product->price,
                        'image' => $product->image,
                        'added_at' => $item->created_at
                    ];
                }
            }

            return [
                'success' => true,
                'data' => $products
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }
} 