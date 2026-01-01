<?php
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama     = $_POST['nama_lengkap'];
    $username = $_POST['username'];
    $level    = $_POST['level'];
    
    // Enkripsi password dengan Bcrypt
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        // Cek apakah username sudah ada
        $cek = $pdo->prepare("SELECT username FROM users WHERE username = ?");
        $cek->execute([$username]);
        
        if ($cek->rowCount() > 0) {
            echo "<script>alert('Username sudah digunakan!'); window.history.back();</script>";
        } else {
            // Insert data ke database
            $sql = "INSERT INTO users (nama_lengkap, username, password, level) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nama, $username, $password, $level]);

            echo "<script>alert('Registrasi Berhasil!'); window.location='index.php';</script>";
        }
    } catch (PDOException $e) {
        die("Terjadi kesalahan: " . $e->getMessage());
    }
}
?>