<?php
session_start();
require 'koneksi.php';

$username = $_POST['username'];
$password = $_POST['password'];

$query = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$query->execute([$username]);
$user = $query->fetch();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['username'] = $user['username'];
    $_SESSION['nama']     = $user['nama_lengkap'];
    $_SESSION['level']    = $user['level'];

    if ($user['level'] == 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: user_dashboard.php");
    }
} else {
    // Simpan pesan error ke session
    $_SESSION['msg'] = "Username atau Password salah!";
    $_SESSION['msg_type'] = "danger";
    header("Location: index.php");
}
exit();
?>