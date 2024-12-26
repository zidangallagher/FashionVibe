<?php
require_once __DIR__ . '/../middleware/AdminMiddleware.php';
require_once __DIR__ . '/../controllers/ProductController.php';

header('Content-Type: application/json');

$productController = new ProductController();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if (!empty($action)) {
                // Get single product
                $result = $productController->getProductById($action);
            } else {
                // Get all products
                $result = $productController->getAllProducts();
            }
            break;

        case 'POST':
            AdminMiddleware::handle();
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data');
            }
            $result = $productController->createProduct($data);
            break;

        case 'PUT':
            AdminMiddleware::handle();
            if (empty($action)) {
                throw new Exception('Product ID is required');
            }
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data');
            }
            $result = $productController->updateProduct($action, $data);
            break;

        case 'DELETE':
            AdminMiddleware::handle();
            if (empty($action)) {
                throw new Exception('Product ID is required');
            }
            $result = $productController->deleteProduct($action);
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