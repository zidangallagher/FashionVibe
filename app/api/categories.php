<?php
require_once __DIR__ . '/../middleware/AdminMiddleware.php';
require_once __DIR__ . '/../controllers/CategoryController.php';

header('Content-Type: application/json');

$categoryController = new CategoryController();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if (!empty($action)) {
                // Get single category
                $result = $categoryController->getCategoryById($action);
            } else {
                // Get all categories
                $result = $categoryController->getAllCategories();
            }
            break;

        case 'POST':
            AdminMiddleware::handle();
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data');
            }
            $result = $categoryController->createCategory($data);
            break;

        case 'PUT':
            AdminMiddleware::handle();
            if (empty($action)) {
                throw new Exception('Category ID is required');
            }
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data');
            }
            $result = $categoryController->updateCategory($action, $data);
            break;

        case 'DELETE':
            AdminMiddleware::handle();
            if (empty($action)) {
                throw new Exception('Category ID is required');
            }
            $result = $categoryController->deleteCategory($action);
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