<?php
require_once __DIR__ . '/../controllers/WishlistController.php';

header('Content-Type: application/json');

$wishlistController = new WishlistController();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    // Validasi session
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Silakan login terlebih dahulu'
        ]);
        exit;
    }

    switch ($method) {
        case 'GET':
            // Get wishlist items
            $result = $wishlistController->getWishlist();
            break;

        case 'POST':
            // Handle add/remove actions
            if (empty($action)) {
                throw new Exception('Action is required');
            }

            $path = explode('/', $action);
            $actionType = $path[0];
            $productId = $path[1] ?? '';

            if (empty($productId)) {
                throw new Exception('Product ID is required');
            }

            switch ($actionType) {
                case 'add':
                    $result = $wishlistController->addToWishlist($productId);
                    break;

                case 'remove':
                    $result = $wishlistController->removeFromWishlist($productId);
                    break;

                default:
                    throw new Exception('Invalid action type');
            }
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