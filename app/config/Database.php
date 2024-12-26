<?php
require_once __DIR__ . '/../../vendor/autoload.php';

class Database {
    private static $instance = null;
    private $connection;
    private $database;

    private function __construct() {
        try {
            // Gunakan nilai default jika variabel environment tidak tersedia
            $mongoUri = $_ENV['MONGODB_URI'] ?? "mongodb://localhost:27017";
            $dbName = $_ENV['MONGODB_DB'] ?? "fashionvibe";

            $this->connection = new MongoDB\Client($mongoUri);
            $this->database = $this->connection->selectDatabase($dbName);

            if (!$this->database) {
                throw new Exception("Database tidak dapat diakses");
            }
        } catch (\Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getDatabase() {
        return $this->database;
    }

    public function getCollection($collection) {
        return $this->database->$collection;
    }

    public function createCollections() {
        // Create collections if they don't exist
        $collections = [
            'users' => [
                'validator' => [
                    '$jsonSchema' => [
                        'bsonType' => 'object',
                        'required' => ['email', 'password', 'role', 'created_at'],
                        'properties' => [
                            'email' => ['bsonType' => 'string'],
                            'password' => ['bsonType' => 'string'],
                            'role' => ['enum' => ['admin', 'user']],
                            'name' => ['bsonType' => 'string'],
                            'created_at' => ['bsonType' => 'date']
                        ]
                    ]
                ]
            ],
            'products' => [
                'validator' => [
                    '$jsonSchema' => [
                        'bsonType' => 'object',
                        'required' => ['name', 'price', 'stock', 'created_at'],
                        'properties' => [
                            'name' => ['bsonType' => 'string'],
                            'description' => ['bsonType' => 'string'],
                            'price' => ['bsonType' => 'double'],
                            'stock' => ['bsonType' => 'int'],
                            'image' => ['bsonType' => 'string'],
                            'category' => ['bsonType' => 'string'],
                            'created_at' => ['bsonType' => 'date']
                        ]
                    ]
                ]
            ],
            'carts' => [
                'validator' => [
                    '$jsonSchema' => [
                        'bsonType' => 'object',
                        'required' => ['user_id', 'product_id', 'quantity', 'status', 'created_at'],
                        'properties' => [
                            'user_id' => ['bsonType' => 'string'],
                            'product_id' => ['bsonType' => 'string'],
                            'quantity' => ['bsonType' => 'int'],
                            'price' => ['bsonType' => 'double'],
                            'status' => ['enum' => ['active', 'ordered']],
                            'created_at' => ['bsonType' => 'date'],
                            'updated_at' => ['bsonType' => 'date']
                        ]
                    ]
                ]
            ],
            'orders' => [
                'validator' => [
                    '$jsonSchema' => [
                        'bsonType' => 'object',
                        'required' => ['user_id', 'products', 'total', 'status', 'created_at'],
                        'properties' => [
                            'user_id' => ['bsonType' => 'objectId'],
                            'products' => [
                                'bsonType' => 'array',
                                'items' => [
                                    'bsonType' => 'object',
                                    'required' => ['product_id', 'quantity', 'price'],
                                    'properties' => [
                                        'product_id' => ['bsonType' => 'objectId'],
                                        'quantity' => ['bsonType' => 'int'],
                                        'price' => ['bsonType' => 'double']
                                    ]
                                ]
                            ],
                            'total' => ['bsonType' => 'double'],
                            'status' => ['enum' => ['pending', 'processing', 'shipped', 'delivered', 'cancelled']],
                            'shipping_address' => ['bsonType' => 'string'],
                            'created_at' => ['bsonType' => 'date']
                        ]
                    ]
                ]
            ]
        ];

        foreach ($collections as $name => $options) {
            try {
                $this->database->createCollection($name, $options);
                error_log("Collection '$name' created successfully");
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'Collection already exists') === false) {
                    error_log("Error creating collection '$name': " . $e->getMessage());
                } else {
                    error_log("Collection '$name' already exists");
                    // Update collection validator
                    try {
                        $this->database->command([
                            'collMod' => $name,
                            'validator' => $options['validator']
                        ]);
                    } catch (\Exception $e) {
                        error_log("Error updating validator for '$name': " . $e->getMessage());
                    }
                }
            }
        }

        // Create indexes
        try {
            $this->database->users->createIndex(['email' => 1], ['unique' => true]);
            $this->database->products->createIndex(['name' => 1]);
            $this->database->products->createIndex(['category' => 1]);
            $this->database->orders->createIndex(['user_id' => 1]);
            $this->database->orders->createIndex(['status' => 1]);
            $this->database->carts->createIndex(['user_id' => 1]);
            $this->database->carts->createIndex(['product_id' => 1]);
            error_log("Indexes created successfully");
        } catch (\Exception $e) {
            error_log("Error creating indexes: " . $e->getMessage());
        }
    }
} 