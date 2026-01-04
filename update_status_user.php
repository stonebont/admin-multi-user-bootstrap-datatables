<?php
session_start();
require 'koneksi.php';

// Pastikan hanya admin yang bisa mengakses file ini
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak!']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $status = $_POST['status']; // 1 untuk Aktif, 0 untuk Non-aktif

    try {
        // Update kolom is_active di database
        $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        // Opsional: Catat ke Log Aktivitas
        $admin_user = $_SESSION['username'];
        $ket_status = ($status == 1) ? 'Mengaktifkan' : 'Menonaktifkan';
        
        $stmt_log = $pdo->prepare("INSERT INTO logs (username, aksi, detail, waktu) VALUES (?, ?, ?, NOW())");
        $stmt_log->execute([$admin_user, 'Update Status', "$ket_status user dengan ID: $id"]);

        echo json_encode(['status' => 'success', 'message' => "User berhasil di-$ket_status."]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui database: ' . $e->getMessage()]);
    }
}