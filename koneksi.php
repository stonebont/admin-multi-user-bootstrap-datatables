<?php
// Set timezone di PHP
date_default_timezone_set('Asia/Makassar');

$host = "localhost";
$user = "root";
$pass = "";
$db   = "multi_user_gemini";

try {
    // 1. Buat koneksi terlebih dahulu
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    
    // 2. Set mode error agar muncul jika ada masalah SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 3. Sekarang baru aman memanggil $pdo->exec() untuk sinkronisasi waktu database
    $pdo->exec("SET time_zone = '+08:00'");

} catch(PDOException $e) {
    die("Koneksi ke database gagal: " . $e->getMessage());
}
?>