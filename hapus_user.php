<?php
session_start();
if($_SESSION['level'] != "admin") exit();
require 'koneksi.php';
require 'fungsi_log.php';

$id = $_GET['id'];
$sql = "DELETE FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);

if($stmt->execute([$id])) {
	tulis_log($pdo, $_SESSION['username'], "Hapus User", "Menghapus user dengan ID: " . $id);
    echo "<script>alert('User berhasil dihapus'); window.location='admin_dashboard.php';</script>";
}
?>