<?php
require_once __DIR__ . '/../helpers/SessionHelper.php';
require_once __DIR__ . '/../controllers/OrderController.php';

// Matikan error reporting untuk output
error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Aktifkan error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-error.log');

// Set header JSON
header('Content-Type: application/json; charset=utf-8');

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
    // Validasi session
    if (!SessionHelper::isLoggedIn()) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Silakan login terlebih dahulu'
        ], 401);
    }

    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    $orderController = new OrderController();

    // Handle different HTTP methods
    switch ($method) {
        case 'GET':
            // Get order details
            if (!empty($action)) {
                $response = $orderController->getOrderById($action);
            } else {
                $response = $orderController->getAllOrders();
            }
            sendJsonResponse($response);
            break;

        case 'POST':
            // Validasi action untuk POST
            if ($action !== 'create') {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Invalid action'
                ], 400);
            }

            // Log request data
            error_log('Order request data: ' . json_encode($_POST));

            // Validasi input
            $requiredFields = [
                'name', 'phone', 'email', 'address', 
                'province', 'city', 'postal_code',
                'shipping_method', 'payment_method'
            ];

            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    sendJsonResponse([
                        'success' => false,
                        'message' => 'Field ' . $field . ' tidak boleh kosong'
                    ], 400);
                }
            }

            // Buat pesanan
            $orderData = [
                'shipping_info' => [
                    'name' => $_POST['name'],
                    'phone' => $_POST['phone'],
                    'email' => $_POST['email'],
                    'address' => $_POST['address'],
                    'province' => $_POST['province'],
                    'city' => $_POST['city'],
                    'postal_code' => $_POST['postal_code']
                ],
                'shipping_method' => $_POST['shipping_method'],
                'payment_method' => $_POST['payment_method']
            ];

            $response = $orderController->createOrder($orderData);
            
            // Log response
            error_log('Order response: ' . json_encode($response));

            if (!$response['success']) {
                sendJsonResponse([
                    'success' => false,
                    'message' => $response['message']
                ], 400);
            }

            sendJsonResponse($response);
            break;

        case 'PUT':
            // Handle status update
            if (empty($action)) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Order ID is required'
                ], 400);
            }

            $path = explode('/', $action);
            $orderId = $path[0];
            $updateType = $path[1] ?? '';

            if ($updateType === 'status') {
                $data = json_decode(file_get_contents('php://input'), true);
                if (!isset($data['status'])) {
                    sendJsonResponse([
                        'success' => false,
                        'message' => 'Status is required'
                    ], 400);
                }
                $response = $orderController->updateOrderStatus($orderId, $data['status']);
                sendJsonResponse($response);
            } else {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Invalid update type'
                ], 400);
            }
            break;

        default:
            sendJsonResponse([
                'success' => false,
                'message' => 'Method not allowed'
            ], 405);
    }

} catch (Throwable $e) {
    error_log('Order API Error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    sendJsonResponse([
        'success' => false,
        'message' => 'Terjadi kesalahan internal server: ' . $e->getMessage()
    ], 500);
} 