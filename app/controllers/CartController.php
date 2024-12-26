<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/SessionHelper.php';

class CartController {
    private $db;
    private $carts;
    private $products;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getDatabase();
        $this->carts = $this->db->selectCollection('carts');
        $this->products = $this->db->selectCollection('products');
    }

    private function validateUser() {
        try {
            if (!SessionHelper::isLoggedIn()) {
                return [
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu'
                ];
            }

            $userId = SessionHelper::getUserId();
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'Data user tidak valid, silakan login ulang'
                ];
            }

            return [
                'success' => true,
                'userId' => $userId
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function addToCart($productId, $quantity = 1) {
        try {
            $validation = $this->validateUser();
            if (!$validation['success']) {
                return $validation;
            }
            $userId = $validation['userId'];

            // Validasi product
            try {
                $productObjectId = new MongoDB\BSON\ObjectId($productId);
            } catch (Exception $e) {
                throw new Exception('ID produk tidak valid');
            }

            $product = $this->products->findOne(['_id' => $productObjectId]);
            if (!$product) {
                throw new Exception('Produk tidak ditemukan');
            }

            if ($product->stock < $quantity) {
                throw new Exception('Stok produk tidak mencukupi');
            }

            // Cek apakah produk sudah ada di keranjang
            $existingCart = $this->carts->findOne([
                'user_id' => $userId,
                'product_id' => (string) $productObjectId,
                'status' => 'active'
            ]);

            if ($existingCart) {
                $newQuantity = $existingCart->quantity + $quantity;
                if ($newQuantity > $product->stock) {
                    throw new Exception('Total jumlah melebihi stok yang tersedia');
                }

                $this->carts->updateOne(
                    ['_id' => $existingCart->_id],
                    ['$set' => [
                        'quantity' => $newQuantity,
                        'updated_at' => new MongoDB\BSON\UTCDateTime()
                    ]]
                );

                return [
                    'success' => true,
                    'message' => 'Jumlah produk di keranjang berhasil diperbarui'
                ];
            }

            // Tambah produk baru ke keranjang
            $this->carts->insertOne([
                'user_id' => $userId,
                'product_id' => (string) $productObjectId,
                'quantity' => (int) $quantity,
                'price' => (float) $product->price,
                'status' => 'active',
                'created_at' => new MongoDB\BSON\UTCDateTime(),
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]);

            return [
                'success' => true,
                'message' => 'Produk berhasil ditambahkan ke keranjang'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function updateCartItem($cartId, $quantity) {
        try {
            $validation = $this->validateUser();
            if (!$validation['success']) {
                return $validation;
            }
            $userId = $validation['userId'];
            
            // Get cart item
            $cartItem = $this->carts->findOne([
                '_id' => new MongoDB\BSON\ObjectId($cartId),
                'user_id' => $userId,
                'status' => 'active'
            ]);

            if (!$cartItem) {
                return [
                    'success' => false,
                    'message' => 'Item tidak ditemukan di keranjang'
                ];
            }

            // Check product stock
            $product = $this->products->findOne(['_id' => new MongoDB\BSON\ObjectId($cartItem->product_id)]);
            if (!$product || $product->stock < $quantity) {
                return [
                    'success' => false,
                    'message' => 'Stok produk tidak mencukupi'
                ];
            }

            // Update quantity
            $this->carts->updateOne(
                ['_id' => $cartItem->_id],
                ['$set' => [
                    'quantity' => (int) $quantity,
                    'updated_at' => new MongoDB\BSON\UTCDateTime()
                ]]
            );

            return [
                'success' => true,
                'message' => 'Jumlah produk berhasil diperbarui'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    public function removeFromCart($cartId) {
        try {
            $validation = $this->validateUser();
            if (!$validation['success']) {
                return $validation;
            }
            $userId = $validation['userId'];

            $result = $this->carts->deleteOne([
                '_id' => new MongoDB\BSON\ObjectId($cartId),
                'user_id' => $userId,
                'status' => 'active'
            ]);

            if ($result->getDeletedCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Produk berhasil dihapus dari keranjang'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Produk tidak ditemukan di keranjang'
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    public function getCart() {
        try {
            $validation = $this->validateUser();
            if (!$validation['success']) {
                return $validation;
            }
            $userId = $validation['userId'];

            error_log("Debug - Getting cart for user: $userId");

            // Debug query parameters
            $query = [
                'user_id' => $userId,
                'status' => 'active'
            ];
            error_log('Debug - Cart query: ' . json_encode($query));

            $cartItems = $this->carts->find($query)->toArray();
            error_log('Debug - Found cart items: ' . json_encode($cartItems));

            $items = [];
            $total = 0;

            foreach ($cartItems as $item) {
                try {
                    error_log('Debug - Processing cart item: ' . json_encode($item));
                    $product = $this->products->findOne(['_id' => new MongoDB\BSON\ObjectId($item['product_id'])]);
                    error_log('Debug - Found product: ' . json_encode($product));
                    
                    if ($product) {
                        $subtotal = $item['quantity'] * $item['price'];
                        $items[] = [
                            '_id' => (string) $item['_id'],
                            'product' => [
                                '_id' => (string) $product['_id'],
                                'name' => $product['name'],
                                'price' => (float) $product['price'],
                                'stock' => (int) $product['stock'],
                                'image' => $product['image'] ?? null
                            ],
                            'quantity' => (int) $item['quantity'],
                            'price' => (float) $item['price'],
                            'subtotal' => $subtotal
                        ];
                        $total += $subtotal;
                    } else {
                        error_log('Debug - Product not found for cart item: ' . $item['product_id']);
                    }
                } catch (\Exception $e) {
                    error_log("Debug - Error processing cart item: " . $e->getMessage());
                    continue;
                }
            }

            $result = [
                'success' => true,
                'data' => [
                    'items' => $items,
                    'total' => $total
                ]
            ];
            error_log('Debug - Final cart result: ' . json_encode($result));
            return $result;

        } catch (\Exception $e) {
            error_log("Debug - Error in getCart: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data keranjang: ' . $e->getMessage()
            ];
        }
    }

    public function updateCartStatus($status) {
        try {
            $validation = $this->validateUser();
            if (!$validation['success']) {
                return $validation;
            }
            $userId = $validation['userId'];

            // Update semua item di keranjang yang statusnya active
            $result = $this->carts->updateMany(
                [
                    'user_id' => $userId,
                    'status' => 'active'
                ],
                [
                    '$set' => [
                        'status' => $status,
                        'updated_at' => new MongoDB\BSON\UTCDateTime()
                    ]
                ]
            );

            return [
                'success' => true,
                'message' => 'Status keranjang berhasil diperbarui'
            ];

        } catch (Exception $e) {
            error_log("Error updating cart status: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui status keranjang'
            ];
        }
    }
} 