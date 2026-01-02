<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require 'koneksi.php';

// 1. Ambil token dari URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Akses ditolak: Token tidak ditemukan.");
}

// 2. Validasi Token di Database (Cek apakah ada dan belum kadaluarsa)
$stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND token_expiry > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    // Jika token salah atau sudah lewat 1 jam
    die("Link tidak valid atau sudah kadaluarsa. Silakan minta link reset baru di halaman Lupa Password.");
}

// 3. Proses Ganti Password Baru
if (isset($_POST['update_password'])) {
    $password_baru = $_POST['password'];
    $konfirmasi    = $_POST['konfirmasi_password'];

    if ($password_baru !== $konfirmasi) {
        $error = "Konfirmasi password tidak cocok!";
    } elseif (strlen($password_baru) < 5) {
        $error = "Password minimal 5 karakter!";
    } else {
        // Enkripsi password baru
        $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);

        // Update ke database dan HAPUS token agar tidak bisa dipakai lagi
        $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE id = ?");
        $update->execute([$hashed_password, $user['id']]);

        // Beri notifikasi sukses dan arahkan ke login
        $_SESSION['msg'] = "Password berhasil diperbarui! Silakan login dengan password baru.";
        $_SESSION['msg_type'] = "success";
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setel Ulang Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; }
        .card { border-radius: 15px; border: none; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center align-items-center vh-100">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h4 class="fw-bold text-center mb-4">Password Baru</h4>
                    <p class="text-muted small text-center">Halo <strong><?= htmlspecialchars($user['nama_lengkap']); ?></strong>, silakan buat password baru Anda.</p>
                    
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger small"><?= $error; ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Password Baru</label>
                            <input type="password" name="password" class="form-control" placeholder="Minimal 5 karakter" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Konfirmasi Password</label>
                            <input type="password" name="konfirmasi_password" class="form-control" placeholder="Ulangi password" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" name="update_password" class="btn btn-success btn-lg">Update Password</button>
                            <a href="index.php" class="btn btn-link text-decoration-none text-muted small">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>