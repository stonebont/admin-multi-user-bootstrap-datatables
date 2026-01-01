<?php
session_start();
if($_SESSION['level'] != "admin") exit();
require 'koneksi.php';

$id = $_GET['id'];
$sql = "DELETE FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);

if($stmt->execute([$id])) {
    echo "<script>alert('User berhasil dihapus'); window.location='admin_dashboard.php';</script>";
}
?>