# FashionVibe - Toko Baju Online

FashionVibe adalah aplikasi web toko baju online yang dibangun menggunakan PHP dan MongoDB. Aplikasi ini memiliki fitur lengkap untuk manajemen produk, kategori, dan pesanan dengan antarmuka yang modern dan responsif.

## Fitur

- Autentikasi user dan admin
- Manajemen produk (CRUD)
- Manajemen kategori
- Pencarian dan filter produk
- Keranjang belanja
- Sistem pemesanan
- Dashboard admin
- Responsive design

## Teknologi yang Digunakan

- PHP 7.4+
- MongoDB
- TailwindCSS
- JavaScript
- JWT untuk autentikasi
- Lottie untuk animasi

## Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MongoDB 4.4 atau lebih tinggi
- Apache/Nginx web server
- Composer (PHP package manager)
- Node.js dan NPM (untuk development)

## Instalasi

1. Clone repositori ini:
```bash
git clone https://github.com/username/fashionvibe.git
cd fashionvibe
```

2. Install dependencies PHP menggunakan Composer:
```bash
composer install
```

3. Salin file `.env.example` menjadi `.env` dan sesuaikan konfigurasi:
```bash
cp .env.example .env
```

4. Sesuaikan konfigurasi MongoDB di file `.env`:
```bash
MONGODB_URI=mongodb://localhost:27017
MONGODB_DB=fashionvibe
```

5. Jalankan migrasi database:
```bash
php app/database/migrate.php
```

6. Pastikan folder `public/assets/images/products` memiliki permission yang benar:
```bash
chmod -R 755 public/assets/images/products
```

7. Konfigurasi virtual host Apache/Nginx untuk mengarahkan ke folder public.

## Struktur Folder

```
fashionvibe/
├── app/
│   ├── config/
│   ├── controllers/
│   ├── database/
│   └── views/
├── public/
│   ├── assets/
│   └── index.php
├── vendor/
├── .env
├── .htaccess
├── composer.json
└── README.md
```

## Penggunaan

### Admin
1. Akses `/login` dan masuk menggunakan kredensial admin:
   - Email: admin@fashionvibe.com
   - Password: admin123

2. Melalui dashboard admin, Anda dapat:
   - Mengelola produk
   - Melihat dan mengelola pesanan
   - Mengelola kategori
   - Melihat statistik penjualan

### User
1. Buat akun baru atau login jika sudah memiliki akun
2. Browse produk dan tambahkan ke keranjang
3. Checkout dan lakukan pembayaran
4. Pantau status pesanan

## Keamanan

- Semua password di-hash menggunakan algoritma bcrypt
- Implementasi JWT untuk autentikasi API
- Validasi input untuk mencegah XSS dan SQL injection
- Proteksi CSRF pada form
- Headers keamanan untuk mencegah clickjacking dan sniffing

## Pengembangan

Untuk pengembangan lokal:

1. Clone repositori
2. Install dependencies
3. Sesuaikan konfigurasi di `.env`
4. Jalankan server development:
```bash
php -S localhost:8000 -t public
```

## Kontribusi

1. Fork repositori
2. Buat branch fitur (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## Lisensi

Distributed under the MIT License. See `LICENSE` for more information.

## Kontak

Your Name - [@yourusername](https://twitter.com/yourusername)

Project Link: [https://github.com/username/fashionvibe](https://github.com/username/fashionvibe) 