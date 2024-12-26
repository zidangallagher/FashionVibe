<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../controllers/CartController.php';

class OrderController {
    private $db;
    private $orders;
    private $cartController;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->orders = $this->db->getCollection('orders');
        $this->cartController = new CartController();
    }

    public function createOrder($orderData) {
        try {
            // Dapatkan cart items
            $cartResult = $this->cartController->getCart();
            if (!$cartResult['success']) {
                throw new Exception('Gagal mendapatkan data keranjang');
            }

            if (empty($cartResult['data']['items'])) {
                throw new Exception('Keranjang belanja kosong');
            }

            $cartItems = $cartResult['data']['items'];
            $totalPrice = $cartResult['data']['total'];

            // Hitung biaya pengiriman
            $shippingCost = $this->calculateShippingCost($orderData['shipping_method']);
            $grandTotal = $totalPrice + $shippingCost;

            // Buat array produk untuk pesanan
            $orderProducts = [];
            foreach ($cartItems as $item) {
                $orderProducts[] = [
                    'product_id' => new MongoDB\BSON\ObjectId($item['product']['_id']),
                    'name' => $item['product']['name'],
                    'price' => $item['product']['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal']
                ];
            }

            // Buat pesanan baru
            $order = [
                'user_id' => new MongoDB\BSON\ObjectId($_SESSION['user']['_id']),
                'products' => $orderProducts,
                'shipping_info' => $orderData['shipping_info'],
                'shipping_method' => $orderData['shipping_method'],
                'shipping_cost' => $shippingCost,
                'payment_method' => $orderData['payment_method'],
                'subtotal' => $totalPrice,
                'total' => $grandTotal,
                'status' => 'pending',
                'created_at' => new MongoDB\BSON\UTCDateTime(),
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ];

            // Simpan pesanan
            $result = $this->orders->insertOne($order);
            if (!$result->getInsertedCount()) {
                throw new Exception('Gagal menyimpan pesanan');
            }

            // Update status cart items menjadi ordered
            $updateResult = $this->cartController->updateCartStatus('ordered');
            if (!$updateResult['success']) {
                // Rollback pesanan jika gagal update cart
                $this->orders->deleteOne(['_id' => $result->getInsertedId()]);
                throw new Exception('Gagal memperbarui status keranjang');
            }

            return [
                'success' => true,
                'message' => 'Pesanan berhasil dibuat',
                'data' => [
                    'order_id' => (string) $result->getInsertedId(),
                    'total' => $grandTotal
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function calculateShippingCost($method) {
        $baseCost = [
            'regular' => 10000,
            'express' => 20000,
            'same_day' => 30000
        ];

        return $baseCost[$method] ?? 0;
    }

    public function getOrders($userId = null) {
        try {
            $filter = [];
            if ($userId) {
                $filter['user_id'] = new MongoDB\BSON\ObjectId($userId);
            }

            $orders = $this->orders->find($filter, [
                'sort' => ['created_at' => -1]
            ])->toArray();

            $formattedOrders = [];
            foreach ($orders as $order) {
                $formattedOrders[] = [
                    '_id' => (string) $order['_id'],
                    'products' => $order['products'],
                    'shipping_info' => $order['shipping_info'],
                    'shipping_method' => $order['shipping_method'],
                    'shipping_cost' => $order['shipping_cost'],
                    'payment_method' => $order['payment_method'],
                    'subtotal' => $order['subtotal'],
                    'total' => $order['total'],
                    'status' => $order['status'],
                    'created_at' => $order['created_at']->toDateTime()->format('Y-m-d H:i:s')
                ];
            }

            return [
                'success' => true,
                'data' => $formattedOrders
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getOrderById($orderId) {
        try {
            $order = $this->orders->findOne([
                '_id' => new MongoDB\BSON\ObjectId($orderId)
            ]);

            if (!$order) {
                throw new Exception('Pesanan tidak ditemukan');
            }

            return [
                'success' => true,
                'data' => [
                    '_id' => (string) $order['_id'],
                    'products' => $order['products'],
                    'shipping_info' => $order['shipping_info'],
                    'shipping_method' => $order['shipping_method'],
                    'shipping_cost' => $order['shipping_cost'],
                    'payment_method' => $order['payment_method'],
                    'subtotal' => $order['subtotal'],
                    'total' => $order['total'],
                    'status' => $order['status'],
                    'created_at' => $order['created_at']->toDateTime()->format('Y-m-d H:i:s')
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getAllOrders() {
        try {
            $orders = $this->orders->find([], [
                'sort' => ['created_at' => -1]
            ])->toArray();

            return [
                'success' => true,
                'data' => $orders
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    public function updateOrderStatus($id, $status) {
        try {
            // Validate status
            $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
            if (!in_array($status, $validStatuses)) {
                return [
                    'success' => false,
                    'message' => 'Status pesanan tidak valid'
                ];
            }

            $result = $this->orders->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($id)],
                [
                    '$set' => [
                        'status' => $status,
                        'updated_at' => new MongoDB\BSON\UTCDateTime()
                    ]
                ]
            );

            if ($result->getModifiedCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Status pesanan berhasil diupdate'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Pesanan tidak ditemukan atau tidak ada perubahan'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    public function getUserOrders($userId) {
        try {
            $orders = $this->orders->find(
                ['user_id' => new MongoDB\BSON\ObjectId($userId)],
                ['sort' => ['created_at' => -1]]
            )->toArray();

            return [
                'success' => true,
                'data' => $orders
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }
} 