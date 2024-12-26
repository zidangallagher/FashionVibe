<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FashionVibe - Your Style, Your Way</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Lottie Animation -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    
    <!-- Custom CSS -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        .nav-link {
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: #3B82F6;
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
    </style>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="/" class="text-2xl font-bold text-blue-600">FashionVibe</a>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="/" class="nav-link text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">Beranda</a>
                        <a href="/products" class="nav-link text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">Produk</a>
                        <a href="/categories" class="nav-link text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">Kategori</a>
                        <?php if(isset($_SESSION['user'])): ?>
                        <a href="/orders" class="nav-link text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">Pesanan</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center">
                    <?php if(isset($_SESSION['user'])): ?>
                        <a href="/profile" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">Profil</a>
                        <a href="/cart" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">Keranjang</a>
                        <a href="/api/auth/logout" class="ml-4 bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Keluar</a>
                    <?php else: ?>
                        <a href="/login" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">Masuk</a>
                        <a href="/register" class="ml-4 bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
</body>
</html> 