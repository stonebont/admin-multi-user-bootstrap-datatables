<?php
session_start();
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama_lengkap'];
    $user_id = $_SESSION['user_id'];
    $password = $_POST['password'];
    $response = ['status' => 'error', 'message' => 'Gagal memperbarui profil'];

    try {
        // 1. Update Nama Lengkap
        if (!empty($password)) {
            $pass_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, password = ? WHERE id = ?");
            $stmt->execute([$nama, $pass_hash, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ? WHERE id = ?");
            $stmt->execute([$nama, $user_id]);
        }
        $_SESSION['nama'] = $nama;

        // 2. Logika Upload Foto
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $folder = "uploads/"; // Pastikan folder ini ada
            
            // Buat folder jika belum ada
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }

            $file_name = $_FILES['foto']['name'];
            $file_tmp  = $_FILES['foto']['tmp_name'];
            $ext       = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_name  = "user_" . $user_id . "_" . time() . "." . $ext;

            // Validasi format
            $allowed_ext = ['jpg', 'jpeg', 'png'];
            if (in_array(strtolower($ext), $allowed_ext)) {
                if (move_uploaded_file($file_tmp, $folder . $new_name)) {
                    // Update nama file di database
                    $update_foto = $pdo->prepare("UPDATE users SET foto = ? WHERE id = ?");
                    $update_foto->execute([$new_name, $user_id]);
                    
                    // Update session foto jika Anda menggunakannya
                    $_SESSION['foto'] = $new_name;
                    $response['status'] = 'success';
                } else {
                    $response['message'] = 'Gagal mengupload gambar ke server.';
                }
            } else {
                $response['message'] = 'Format gambar harus JPG, JPEG, atau PNG.';
            }
        } else {
            // Jika tidak ada upload foto, tetap anggap sukses karena nama/pass mungkin berubah
            $response['status'] = 'success';
        }

        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}