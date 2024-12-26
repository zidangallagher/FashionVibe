<?php
require_once __DIR__ . '/../middleware/AdminMiddleware.php';
require_once __DIR__ . '/../controllers/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    AdminMiddleware::handle();

    switch ($method) {
        case 'GET':
            if (!empty($action)) {
                // Get single user
                $result = $authController->getUserById($action);
            } else {
                // Get all users
                $result = $authController->getAllUsers();
            }
            break;

        case 'PUT':
            if (empty($action)) {
                throw new Exception('User ID is required');
            }

            // Check if it's a role or status update
            $path = explode('/', $action);
            $userId = $path[0];
            $updateType = $path[1] ?? '';

            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data');
            }

            switch ($updateType) {
                case 'role':
                    if (!isset($data['role'])) {
                        throw new Exception('Role is required');
                    }
                    $result = $authController->updateUserRole($userId, $data['role']);
                    break;

                case 'status':
                    if (!isset($data['status'])) {
                        throw new Exception('Status is required');
                    }
                    $result = $authController->updateUserStatus($userId, $data['status']);
                    break;

                default:
                    throw new Exception('Invalid update type');
            }
            break;

        case 'DELETE':
            if (empty($action)) {
                throw new Exception('User ID is required');
            }
            $result = $authController->deleteUser($action);
            break;

        default:
            throw new Exception('Method not allowed');
    }

    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 