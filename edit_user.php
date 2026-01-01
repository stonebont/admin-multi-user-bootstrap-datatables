<?php
session_start();
// Proteksi: Hanya admin yang boleh akses
if (!isset($_SESSION['level']) || $_SESSION['level'] != "admin") {
    header("Location: index.php");
    exit();
}

require 'koneksi.php';

// Ambil ID dari URL
$id = $_GET['id'] ?? '';

// Ambil data user lama berdasarkan ID
$query = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$query->execute([$id]);
$user = $query->fetch();

// Jika user tidak ditemukan
if (!$user) {
    die("User tidak ditemukan!");
}

// Proses Update ketika tombol simpan diklik
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama     = $_POST['nama_lengkap'];
    $username = $_POST['username'];
    $level    = $_POST['level'];
    $password = $_POST['password'];

    try {
        if (!empty($password)) {
            // Jika password diisi, maka update password juga
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET nama_lengkap=?, username=?, level=?, password=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nama, $username, $level, $hashed_password, $id]);
        } else {
            // Jika password kosong, jangan update password
            $sql = "UPDATE users SET nama_lengkap=?, username=?, level=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nama, $username, $level, $id]);
        }

        $_SESSION['msg'] = "Data user berhasil diperbarui!";
        $_SESSION['msg_type'] = "success";
        header("Location: admin_dashboard.php");
        exit();
    } catch (PDOException $e) {
        $error = "Gagal memperbarui data: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header bg-warning text-dark fw-bold">
                    Edit Data Pengguna
                </div>
                <div class="card-body p-4">
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?= $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password (Kosongkan jika tidak ingin ganti)</label>
                            <input type="password" name="password" class="form-control" placeholder="Masukkan password baru">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Level User</label>
                            <select name="level" class="form-select">
                                <option value="admin" <?= $user['level'] == 'admin' ? 'selected' : ''; ?>>Administrator</option>
                                <option value="user" <?= $user['level'] == 'user' ? 'selected' : ''; ?>>User Biasa</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="admin_dashboard.php" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-warning">Update Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>