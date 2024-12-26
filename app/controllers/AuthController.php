<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/JWT.php';

use Firebase\JWT\JWT;

class AuthController {
    private $db;
    private $users;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->users = $this->db->getCollection('users');
    }

    public function login($email, $password) {
        try {
            // Validate input
            if (!$email || !$password) {
                return [
                    'success' => false,
                    'message' => 'Email dan password harus diisi'
                ];
            }

            $user = $this->users->findOne(['email' => $email]);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Email atau password salah'
                ];
            }

            if (!password_verify($password, $user->password)) {
                return [
                    'success' => false,
                    'message' => 'Email atau password salah'
                ];
            }

            // Create session
            $_SESSION['user'] = [
                '_id' => (string) $user->_id,
                'email' => $user->email,
                'role' => $user->role,
                'name' => $user->name
            ];

            // Generate JWT token
            $payload = [
                'user_id' => (string) $user->_id,
                'email' => $user->email,
                'role' => $user->role,
                'exp' => time() + JWTConfig::$JWT_EXPIRE
            ];

            $jwt = JWT::encode($payload, JWTConfig::$JWT_SECRET, 'HS256');
            
            // Set JWT token in cookie
            setcookie('token', $jwt, [
                'expires' => time() + JWTConfig::$JWT_EXPIRE,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);

            // Redirect based on user role if it's not an API request
            if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
                if ($user->role === 'admin') {
                    header('Location: /admin');
                } else {
                    header('Location: /');
                }
                exit;
            }

            return [
                'success' => true,
                'message' => 'Login berhasil',
                'token' => $jwt,
                'user' => [
                    'email' => $user->email,
                    'role' => $user->role,
                    'name' => $user->name
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    public function register() {
        // Validate input
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $password_confirmation = $_POST['password_confirmation'] ?? '';
        
        // Validate required fields
        if (!$name || !$email || !$password || !$password_confirmation) {
            $_SESSION['error'] = 'Semua field harus diisi';
            header('Location: /register');
            exit;
        }
        
        // Validate password match
        if ($password !== $password_confirmation) {
            $_SESSION['error'] = 'Password dan konfirmasi password tidak cocok';
            header('Location: /register');
            exit;
        }
        
        // Validate password length
        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Password minimal 6 karakter';
            header('Location: /register');
            exit;
        }
        
        // Check if email already exists
        $existingUser = $this->users->findOne(['email' => $email]);
        
        if ($existingUser) {
            $_SESSION['error'] = 'Email sudah terdaftar';
            header('Location: /register');
            exit;
        }
        
        // Create new user
        try {
            $this->users->insertOne([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'user',
                'created_at' => new MongoDB\BSON\UTCDateTime()
            ]);
            
            // Set success message and redirect to login
            $_SESSION['success'] = 'Pendaftaran berhasil! Silakan login.';
            header('Location: /login');
            exit;
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.';
            header('Location: /register');
            exit;
        }
    }

    public function logout() {
        // Clear session
        session_unset();
        session_destroy();
        
        return [
            'success' => true,
            'message' => 'Logout berhasil'
        ];
    }

    public function getCurrentUser() {
        if (isset($_SESSION['user'])) {
            return $_SESSION['user'];
        }
        return null;
    }

    public function isAdmin() {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === 'admin';
    }

    public function verifyToken($token) {
        try {
            $decoded = JWT::decode($token, JWTConfig::$JWT_SECRET, ['HS256']);
            return [
                'success' => true,
                'user' => (array) $decoded
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Token tidak valid'
            ];
        }
    }

    public function getAllUsers() {
        try {
            $users = $this->users->find([], [
                'sort' => ['created_at' => -1]
            ])->toArray();

            // Get order statistics for each user
            $orders = $this->db->getCollection('orders');
            foreach ($users as &$user) {
                $orderStats = $orders->aggregate([
                    [
                        '$match' => [
                            'user_id' => (string) $user->_id,
                            'status' => ['$ne' => 'cancelled']
                        ]
                    ],
                    [
                        '$group' => [
                            '_id' => null,
                            'order_count' => ['$sum' => 1],
                            'total_spent' => ['$sum' => '$total']
                        ]
                    ]
                ])->toArray();

                if (!empty($orderStats)) {
                    $user->order_count = $orderStats[0]->order_count;
                    $user->total_spent = $orderStats[0]->total_spent;
                } else {
                    $user->order_count = 0;
                    $user->total_spent = 0;
                }
            }

            return [
                'success' => true,
                'data' => $users
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    public function getUserById($id) {
        try {
            $user = $this->users->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Pengguna tidak ditemukan'
                ];
            }

            // Get order statistics
            $orders = $this->db->getCollection('orders');
            $orderStats = $orders->aggregate([
                [
                    '$match' => [
                        'user_id' => (string) $user->_id,
                        'status' => ['$ne' => 'cancelled']
                    ]
                ],
                [
                    '$group' => [
                        '_id' => null,
                        'order_count' => ['$sum' => 1],
                        'total_spent' => ['$sum' => '$total']
                    ]
                ]
            ])->toArray();

            if (!empty($orderStats)) {
                $user->order_count = $orderStats[0]->order_count;
                $user->total_spent = $orderStats[0]->total_spent;
            } else {
                $user->order_count = 0;
                $user->total_spent = 0;
            }

            return [
                'success' => true,
                'data' => $user
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    public function updateUserRole($id, $role) {
        try {
            // Check if user exists
            $user = $this->users->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Pengguna tidak ditemukan'
                ];
            }

            // Prevent updating super admin
            if ($user->email === $_ENV['ADMIN_EMAIL']) {
                return [
                    'success' => false,
                    'message' => 'Tidak dapat mengubah role super admin'
                ];
            }

            // Validate role
            if (!in_array($role, ['admin', 'user'])) {
                return [
                    'success' => false,
                    'message' => 'Role tidak valid'
                ];
            }

            $result = $this->users->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($id)],
                ['$set' => [
                    'role' => $role,
                    'updated_at' => new MongoDB\BSON\UTCDateTime()
                ]]
            );

            if ($result->getModifiedCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Role pengguna berhasil diperbarui'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Tidak ada perubahan pada role pengguna'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    public function updateUserStatus($id, $status) {
        try {
            // Check if user exists
            $user = $this->users->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Pengguna tidak ditemukan'
                ];
            }

            // Prevent updating super admin
            if ($user->email === $_ENV['ADMIN_EMAIL']) {
                return [
                    'success' => false,
                    'message' => 'Tidak dapat mengubah status super admin'
                ];
            }

            // Validate status
            if (!in_array($status, ['active', 'suspended'])) {
                return [
                    'success' => false,
                    'message' => 'Status tidak valid'
                ];
            }

            $result = $this->users->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($id)],
                ['$set' => [
                    'status' => $status,
                    'updated_at' => new MongoDB\BSON\UTCDateTime()
                ]]
            );

            if ($result->getModifiedCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Status pengguna berhasil diperbarui'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Tidak ada perubahan pada status pengguna'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    public function deleteUser($id) {
        try {
            // Check if user exists
            $user = $this->users->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Pengguna tidak ditemukan'
                ];
            }

            // Prevent deleting super admin
            if ($user->email === $_ENV['ADMIN_EMAIL']) {
                return [
                    'success' => false,
                    'message' => 'Tidak dapat menghapus super admin'
                ];
            }

            $result = $this->users->deleteOne(['_id' => new MongoDB\BSON\ObjectId($id)]);

            if ($result->getDeletedCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Pengguna berhasil dihapus'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Gagal menghapus pengguna'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }
} 