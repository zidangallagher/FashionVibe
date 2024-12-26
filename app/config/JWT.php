<?php

class JWTConfig {
    // Waktu kadaluarsa token dalam detik (default: 24 jam)
    public static $JWT_EXPIRE = 86400;
    
    // Kunci rahasia untuk menandatangani token (gunakan string acak yang panjang)
    public static $JWT_SECRET = "your_super_secret_key_here_make_it_long_and_random_123!@#";
} 