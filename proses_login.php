<?php
session_start();
// Set zona waktu sesuai lokasi Anda (WIB: Asia/Jakarta, WITA: Asia/Makassar)
date_default_timezone_set('Asia/Makassar');

// Di file koneksi.php, tambahkan perintah set timezone untuk MySQL setelah koneksi PDO
// $pdo->exec("SET time_zone = '+07:00'"); // Sesuaikan dengan lokasi Anda
require 'koneksi.php';
require 'fungsi_log.php';

$username = $_POST['username'];
$password = $_POST['password'];
$max_attempts = 3;
$lockout_time = 10; // Menit

$query = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$query->execute([$username]);
$user = $query->fetch();

if ($user) {
    // 1. Cek apakah akun sedang dikunci
    if ($user['login_attempts'] >= $max_attempts) {
        $last_attempt = strtotime($user['last_attempt_time']);
        $diff = (time() - $last_attempt) / 60; // Hitung selisih menit

        if ($diff < $lockout_time) {
            $sisa_waktu = ceil($lockout_time - $diff);
            $_SESSION['msg'] = "Akun dikunci! Silakan coba lagi dalam $sisa_waktu menit.";
            $_SESSION['msg_type'] = "danger";
            header("Location: index.php");
            exit();
        } else {
            // Waktu kunci habis, reset attempts
            $reset = $pdo->prepare("UPDATE users SET login_attempts = 0 WHERE id = ?");
            $reset->execute([$user['id']]);
            $user['login_attempts'] = 0;
        }
    }

    // 2. Verifikasi Password
    if (password_verify($password, $user['password'])) {
		// CEK APAKAH USER AKTIF
    if ($user['is_active'] == 0) {
        $_SESSION['msg'] = "Akun Anda telah dinonaktifkan oleh Admin. Silakan hubungi bantuan.";
        $_SESSION['msg_type'] = "danger";
        header("Location: index.php");
        exit();
    }
        // Login Berhasil: Reset attempts ke 0
        $reset = $pdo->prepare("UPDATE users SET login_attempts = 0, last_attempt_time = NULL WHERE id = ?");
        $reset->execute([$user['id']]);

        $_SESSION['user_id'] = $user['id'];
		$_SESSION['nama'] = $user['nama_lengkap'];
		$_SESSION['username'] = $user['username'];
		$_SESSION['level'] = $user['level'];


        header("Location: " . ($user['level'] == 'admin' ? "admin_dashboard.php" : "user_dashboard.php"));
		tulis_log($pdo, $user['username'], "Login", "User berhasil login ke sistem.");
		
    } else {
        // Password Salah: Tambah hitungan attempt
        $new_attempts = $user['login_attempts'] + 1;
        $update = $pdo->prepare("UPDATE users SET login_attempts = ?, last_attempt_time = NOW() WHERE id = ?");
        $update->execute([$new_attempts, $user['id']]);

        $sisa = $max_attempts - $new_attempts;
        $_SESSION['msg'] = ($sisa > 0) ? "Password salah! Sisa percobaan: $sisa" : "Terlalu banyak percobaan. Akun dikunci 10 menit.";
        $_SESSION['msg_type'] = "danger";
        header("Location: index.php");
    }
} else {
    $_SESSION['msg'] = "Username tidak terdaftar!";
    $_SESSION['msg_type'] = "danger";
    header("Location: index.php");
}
exit();