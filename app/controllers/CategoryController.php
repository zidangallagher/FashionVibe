<?php
require_once __DIR__ . '/../config/database.php';

class CategoryController {
    private $db;
    private $categories;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->categories = $this->db->getCollection('categories');
    }

    public function getAllCategories() {
        try {
            $categories = $this->categories->find([], [
                'sort' => ['name' => 1]
            ])->toArray();

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

    public function getCategoryById($id) {
        try {
            $category = $this->categories->findOne([
                '_id' => new MongoDB\BSON\ObjectId($id)
            ]);

            if (!$category) {
                return [
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan'
                ];
            }

            return [
                'success' => true,
                'data' => $category
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    public function createCategory($data) {
        try {
            // Validate required fields
            if (!isset($data['name']) || empty($data['name'])) {
                return [
                    'success' => false,
                    'message' => 'Nama kategori harus diisi'
                ];
            }

            // Check if category already exists
            $existingCategory = $this->categories->findOne(['name' => $data['name']]);
            if ($existingCategory) {
                return [
                    'success' => false,
                    'message' => 'Kategori dengan nama tersebut sudah ada'
                ];
            }

            $category = [
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'created_at' => new MongoDB\BSON\UTCDateTime()
            ];

            $result = $this->categories->insertOne($category);

            if ($result->getInsertedCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Kategori berhasil ditambahkan',
                    'data' => [
                        'id' => (string) $result->getInsertedId()
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Gagal menambahkan kategori'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    public function updateCategory($id, $data) {
        try {
            // Validate required fields
            if (!isset($data['name']) || empty($data['name'])) {
                return [
                    'success' => false,
                    'message' => 'Nama kategori harus diisi'
                ];
            }

            // Check if category exists
            $existingCategory = $this->categories->findOne([
                'name' => $data['name'],
                '_id' => ['$ne' => new MongoDB\BSON\ObjectId($id)]
            ]);
            if ($existingCategory) {
                return [
                    'success' => false,
                    'message' => 'Kategori dengan nama tersebut sudah ada'
                ];
            }

            $updateData = [
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ];

            $result = $this->categories->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($id)],
                ['$set' => $updateData]
            );

            if ($result->getModifiedCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Kategori berhasil diperbarui'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan atau tidak ada perubahan'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    public function deleteCategory($id) {
        try {
            // Check if category is being used by products
            $products = $this->db->getCollection('products');
            $productCount = $products->countDocuments(['category' => $id]);

            if ($productCount > 0) {
                return [
                    'success' => false,
                    'message' => 'Kategori tidak dapat dihapus karena sedang digunakan oleh produk'
                ];
            }

            $result = $this->categories->deleteOne([
                '_id' => new MongoDB\BSON\ObjectId($id)
            ]);

            if ($result->getDeletedCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Kategori berhasil dihapus'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }
} 