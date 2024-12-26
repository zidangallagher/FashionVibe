<?php
$images = [
    'kemeja1' => 'https://i.imgur.com/3vfmZHt.jpg',
    'dress1' => 'https://i.imgur.com/YVqn6YA.jpg',
    'celana1' => 'https://i.imgur.com/0PQHoj1.jpg',
    'jaket1' => 'https://i.imgur.com/kN3ksOC.jpg',
    'dress2' => 'https://i.imgur.com/w9xZy8M.jpg',
    'kemeja2' => 'https://i.imgur.com/UZrVxJY.jpg'
];

echo "<html><head><title>Test Gambar Imgur</title></head><body style='font-family: Arial, sans-serif;'>";
echo "<h1>Test Gambar dari Imgur</h1>";

foreach ($images as $name => $url) {
    echo "<div style='margin: 20px; padding: 10px; border: 1px solid #ccc;'>";
    echo "<h3>$name</h3>";
    echo "<img src='$url' style='max-width: 300px; height: auto;' onerror=\"this.onerror=null; this.src='https://via.placeholder.com/300x400?text=Error+Loading+Image'\" />";
    echo "<p>URL: <a href='$url' target='_blank'>$url</a></p>";
    echo "<p>Status: <span id='status_$name'></span></p>";
    echo "</div>";
}

echo "<script>
document.querySelectorAll('img').forEach(img => {
    const statusId = 'status_' + img.parentElement.querySelector('h3').textContent;
    img.onload = function() {
        document.getElementById(statusId).innerHTML = '<span style=\"color: green;\">✓ Berhasil dimuat</span>';
    };
    img.onerror = function() {
        document.getElementById(statusId).innerHTML = '<span style=\"color: red;\">✗ Gagal dimuat</span>';
    };
});
</script>";
echo "</body></html>"; 