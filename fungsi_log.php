<?php
function tulis_log($pdo, $username, $aksi, $detail) {
    try {
        $sql = "INSERT INTO logs (username, aksi, detail, waktu) VALUES (?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $aksi, $detail]);
    } catch (PDOException $e) {
        // Gagal mencatat log tidak boleh menghentikan aplikasi
    }
}
?>