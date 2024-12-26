<?php
require_once __DIR__ . '/../config/database.php';

class ProductController {
    private $db;
    private $products;
    private $categories;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->products = $this->db->getCollection('products');
        $this->categories = $this->db->getCollection('categories');
    }

    public function getAllProducts($page = 1, $limit = 12, $category = null) {
        try {
            $skip = ($page - 1) * $limit;
            $filter = [];
            
            if ($category) {
                $filter['category'] = $category;
            }

            $options = [
                'skip' => $skip,
                'limit' => $limit,
                'sort' => ['created_at' => -1]
            ];

            $products = $this->products->find($filter, $options)->toArray();
            $total = $this->products->countDocuments($filter);

            return [
                'success' => true,
                'data' => [
                    'products' => $products,
                    'total' => $total,
                    'page' => $page,
                    'total_pages' => ceil($total / $limit)
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    public function getProductById($id) {
        try {
            $product = $this->products->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
            
            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Produk tidak ditemukan'
                ];
            }

            return [
                'success' => true,
                'data' => $product
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    public function createProduct($data) {
        try {
            // Validate required fields
            $requiredFields = ['name', 'price', 'stock', 'category'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return [
                        'success' => false,
                        'message' => "Field '$field' harus diisi"
                    ];
                }
            }

            // Check if category exists
            $category = $this->categories->findOne(['name' => $data['category']]);
            if (!$category) {
                return [
                    'success' => false,
                    'message' => 'Kategori tidak valid'
                ];
            }

            // Default Unsplash images berdasarkan nama produk
            $unsplashImages = [
                'Kemeja Casual Modern' => 'https://plus.unsplash.com/premium_photo-1671149028241-a35a315cb203?q=80&w=1974&auto=format&fit=crop',
                'Dress Elegant' => 'https://images.unsplash.com/photo-1585487000160-6ebcfceb0d03?q=80&w=1934&auto=format&fit=crop',
                'Celana Chino Slim' => 'https://images.unsplash.com/photo-1584865288642-42078afe6942?q=80&w=1973&auto=format&fit=crop'
            ];

            // Prepare product data
            $product = [
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'price' => (float) $data['price'],
                'stock' => (int) $data['stock'],
                'category' => $data['category'],
                'image' => $data['image'] ?? ($unsplashImages[$data['name']] ?? 'https://images.unsplash.com/photo-1585487000160-6ebcfceb0d03?q=80&w=1934&auto=format&fit=crop'),
                'created_at' => new MongoDB\BSON\UTCDateTime()
            ];

            $result = $this->products->insertOne($product);

            if ($result->getInsertedCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Produk berhasil ditambahkan',
                    'data' => [
                        'id' => (string) $result->getInsertedId()
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Gagal menambahkan produk'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    public function updateProduct($id, $data) {
        try {
            $updateData = [];

            // Only update provided fields
            if (isset($data['name'])) $updateData['name'] = $data['name'];
            if (isset($data['description'])) $updateData['description'] = $data['description'];
            if (isset($data['price'])) $updateData['price'] = (float) $data['price'];
            if (isset($data['stock'])) $updateData['stock'] = (int) $data['stock'];
            if (isset($data['category'])) {
                // Check if category exists
                $category = $this->categories->findOne(['name' => $data['category']]);
                if (!$category) {
                    return [
                        'success' => false,
                        'message' => 'Kategori tidak valid'
                    ];
                }
                $updateData['category'] = $data['category'];
            }
            if (isset($data['image'])) $updateData['image'] = $data['image'];

            if (empty($updateData)) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada data yang diupdate'
                ];
            }

            $result = $this->products->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($id)],
                ['$set' => $updateData]
            );

            if ($result->getModifiedCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Produk berhasil diupdate'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Produk tidak ditemukan atau tidak ada perubahan'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    public function deleteProduct($id) {
        try {
            $result = $this->products->deleteOne(['_id' => new MongoDB\BSON\ObjectId($id)]);

            if ($result->getDeletedCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Produk berhasil dihapus'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Produk tidak ditemukan'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    public function searchProducts($query, $category = null) {
        try {
            $filter = [
                '$or' => [
                    ['name' => ['$regex' => $query, '$options' => 'i']],
                    ['description' => ['$regex' => $query, '$options' => 'i']]
                ]
            ];

            if ($category) {
                $filter['category'] = $category;
            }

            $products = $this->products->find($filter)->toArray();

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

    public function getAllCategories() {
        try {
            $categories = $this->categories->find([], ['sort' => ['name' => 1]])->toArray();
            
            return [
                'success' => true,
                'data' => $categories
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    public function getFeaturedProducts() {
        try {
            $filter = ['is_featured' => true];
            $options = [
                'limit' => 6,
                'sort' => ['created_at' => -1]
            ];

            $products = $this->products->find($filter, $options)->toArray();

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