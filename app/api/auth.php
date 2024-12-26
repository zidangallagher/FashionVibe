<?php
require_once __DIR__ . '/../helpers/SessionHelper.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Set header JSON
header('Content-Type: application/json; charset=utf-8');

// Function untuk mengirim response JSON
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

try {
    $action = $_GET['action'] ?? '';
    $authController = new AuthController();

    switch ($action) {
        case 'login':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Method not allowed'
                ], 405);
            }

            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $result = $authController->login($email, $password);
            
            if ($result['success']) {
                sendJsonResponse($result);
            } else {
                sendJsonResponse($result, 401);
            }
            break;

        case 'logout':
            // Hapus token dari cookie
            setcookie('token', '', time() - 3600, '/');
            
            // Hapus session
            $result = $authController->logout();
            
            // Redirect ke halaman login
            header('Location: /login');
            exit;
            break;

        default:
            sendJsonResponse([
                'success' => false,
                'message' => 'Invalid action'
            ], 400);
    }
} catch (Exception $e) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ], 500);
} 