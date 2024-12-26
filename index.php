<?php
session_start();

// Get the request URI
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/';

// Remove query string
$request_uri = strtok($request_uri, '?');

// Remove trailing slash
$request_uri = rtrim($request_uri, '/');

// Handle API routes
if (preg_match('/^\/api\/(\w+)(?:\/([^\/]+))?(?:\/([^\/]+))?$/', $request_uri, $matches)) {
    $controller = $matches[1];
    $_GET['action'] = $matches[2] ?? '';
    if (isset($matches[3])) {
        $_GET['action'] .= '/' . $matches[3];
    }
    $api_file = "app/api/{$controller}.php";
    
    if (file_exists($api_file)) {
        include $api_file;
        exit;
    }
}

// If empty request URI, set to home
if (empty($request_uri) || $request_uri === $base_path) {
    $request_uri = '/home';
}

// Define routes
$routes = [
    '/home' => 'app/views/home.php',
    '/login' => 'app/views/auth/login.php',
    '/register' => 'app/views/auth/register.php',
    '/profile' => 'app/views/profile/index.php',
    '/products' => 'app/views/products/index.php',
    '/cart' => 'app/views/cart/index.php',
    '/checkout' => 'app/views/checkout/index.php',
    '/orders' => 'app/views/orders/index.php',
    '/categories' => 'app/views/categories/index.php',
    '/admin' => 'app/views/admin/index.php',
    '/admin/products' => 'app/views/admin/products.php',
    '/admin/products/create' => 'app/views/admin/products_create.php',
    '/admin/categories' => 'app/views/admin/categories.php',
    '/admin/orders' => 'app/views/admin/orders.php',
    '/admin/users' => 'app/views/admin/users.php',
];

// Check if route exists
if (isset($routes[$request_uri])) {
    $file_path = $routes[$request_uri];
} else {
    // Check for dynamic routes
    if (preg_match('/^\/products\/([^\/]+)$/', $request_uri, $matches)) {
        $_GET['id'] = $matches[1];
        $file_path = 'app/views/products/detail.php';
    } else if (preg_match('/^\/categories\/([^\/]+)$/', $request_uri, $matches)) {
        $_GET['id'] = $matches[1];
        $file_path = 'app/views/categories/detail.php';
    } else if (preg_match('/^\/orders\/([^\/]+)$/', $request_uri, $matches)) {
        $_GET['id'] = $matches[1];
        $file_path = 'app/views/orders/detail.php';
    } else if (preg_match('/^\/admin\/products\/edit\/([^\/]+)$/', $request_uri, $matches)) {
        $_GET['id'] = $matches[1];
        $file_path = 'app/views/admin/products_edit.php';
    } else {
        // Route not found
        http_response_code(404);
        include 'app/views/404.php';
        exit;
    }
}

// Check if file exists
if (!file_exists($file_path)) {
    http_response_code(404);
    include 'app/views/404.php';
    exit;
}

// Include the file
include $file_path; 