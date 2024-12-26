<?php
require_once __DIR__ . '/../controllers/AuthController.php';

class AdminMiddleware {
    public static function handle() {
        $auth = new AuthController();
        
        // Cek apakah user sudah login dan role-nya admin
        if (!$auth->isAdmin()) {
            // Jika bukan admin, redirect ke home
            header('Location: /');
            exit;
        }
    }
} 