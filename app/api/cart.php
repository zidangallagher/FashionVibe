<?php
// Matikan semua error reporting untuk output
error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Aktifkan error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-error.log');

// Mulai session sebelum output buffering
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set header JSON di awal
header('Content-Type: application/json; charset=utf-8');

// Matikan output buffering yang mungkin aktif
while (ob_get_level()) {
    ob_end_clean();
}

require_once __DIR__ . '/../helpers/SessionHelper.php';
require_once __DIR__ . '/../controllers/CartController.php';

// Function untuk mengirim response JSON
function sendJsonResponse($data, $statusCode = 200) {
    try {
        // Pastikan tidak ada output sebelumnya
        if (ob_get_length()) ob_clean();
        
        // Set status code
        http_response_code($statusCode);
        
        // Pastikan data bisa di-encode ke JSON
        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($jsonData === false) {
            throw new Exception('Failed to encode response to JSON: ' . json_last_error_msg());
        }
        
        // Log response untuk debugging
        error_log('Sending JSON response: ' . $jsonData);
        
        echo $jsonData;
        exit;
    } catch (Exception $e) {
        error_log('Error sending JSON response: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan internal server'
        ]);
        exit;
    }
}

try {
    // Log request details
    error_log(sprintf(
        "Cart API Request - Method: %s, URI: %s, Action: %s",
        $_SERVER['REQUEST_METHOD'],
        $_SERVER['REQUEST_URI'],
        $_GET['action'] ?? 'none'
    ));
    error_log("POST Data: " . json_encode($_POST));
    error_log("Session Data: " . json_encode($_SESSION));

    // Validasi session menggunakan SessionHelper
    if (!SessionHelper::isLoggedIn()) {
        error_log("User not logged in. Session data: " . json_encode($_SESSION));
        sendJsonResponse([
            'success' => false,
            'message' => 'Silakan login terlebih dahulu'
        ], 401);
        exit; // Pastikan script berhenti di sini
    }

    // Validasi request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse([
            'success' => false,
            'message' => 'Method not allowed'
        ], 405);
        exit; // Pastikan script berhenti di sini
    }

    // Validasi action
    $action = $_GET['action'] ?? '';
    if (!in_array($action, ['add', 'update', 'remove'])) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Invalid action'
        ], 400);
        exit; // Pastikan script berhenti di sini
    }

    $cartController = new CartController();
    $response = null;

    switch ($action) {
        case 'add':
            if (empty($_POST['product_id'])) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Product ID tidak boleh kosong'
                ], 400);
                exit;
            }
            
            $productId = trim($_POST['product_id']);
            $quantity = max(1, intval($_POST['quantity'] ?? 1));
            
            error_log("Adding to cart: Product ID = $productId, Quantity = $quantity");
            $response = $cartController->addToCart($productId, $quantity);
            break;

        case 'update':
            if (empty($_POST['cart_id'])) {
                throw new Exception('Cart ID tidak boleh kosong');
            }
            
            $cartId = trim($_POST['cart_id']);
            $quantity = max(1, intval($_POST['quantity'] ?? 1));
            
            error_log("Updating cart: Cart ID = $cartId, Quantity = $quantity");
            $response = $cartController->updateCartItem($cartId, $quantity);
            break;

        case 'remove':
            if (empty($_POST['cart_id'])) {
                throw new Exception('Cart ID tidak boleh kosong');
            }
            
            $cartId = trim($_POST['cart_id']);
            error_log("Removing from cart: Cart ID = $cartId");
            $response = $cartController->removeFromCart($cartId);
            break;
    }

    // Log response before sending
    error_log('Cart API Response before sending: ' . json_encode($response));

    if (!is_array($response)) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Invalid response format from controller'
        ], 500);
        exit;
    }

    $statusCode = isset($response['success']) && $response['success'] ? 200 : 400;
    sendJsonResponse($response, $statusCode);

} catch (Throwable $e) { // Ubah Exception menjadi Throwable untuk menangkap semua jenis error
    error_log('Cart API Error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    sendJsonResponse([
        'success' => false,
        'message' => 'Terjadi kesalahan internal server: ' . $e->getMessage()
    ], 500);
}
?> 