<?php
session_start();
// Proteksi: Hanya user biasa yang boleh akses
if (!isset($_SESSION['level']) || $_SESSION['level'] != "user") {
    header("Location: index.php");
    exit();
}

require 'koneksi.php';

// Ambil data user yang sedang login
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch();

// Logika Update Profil
if (isset($_POST['update_profil'])) {
    $nama_baru = $_POST['nama_lengkap'];
    $pass_baru = $_POST['password'];

    try {
        if (!empty($pass_baru)) {
            // Jika ganti password
            $hashed_pass = password_hash($pass_baru, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET nama_lengkap = ?, password = ? WHERE username = ?";
            $pdo->prepare($sql)->execute([$nama_baru, $hashed_pass, $_SESSION['username']]);
        } else {
            // Jika hanya ganti nama
            $sql = "UPDATE users SET nama_lengkap = ? WHERE username = ?";
            $pdo->prepare($sql)->execute([$nama_baru, $_SESSION['username']]);
        }
        
        // Update session nama agar tampilan di navbar berubah langsung
        $_SESSION['nama'] = $nama_baru;
        $_SESSION['msg'] = "Profil berhasil diperbarui!";
        $_SESSION['msg_type'] = "success";
        header("Location: user_dashboard.php");
        exit();
    } catch (PDOException $e) {
        $error = "Gagal update: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Profil Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; }
        .card { border: none; border-radius: 15px; }
        .nav-pills .nav-link.active { background-color: #0d6efd; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">UserArea</a>
        <div class="ms-auto">
            <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm text-center p-4">
                <div class="mb-3">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama']); ?>&background=random&size=128" class="rounded-circle shadow" alt="avatar">
                </div>
                <h4><?= htmlspecialchars($_SESSION['nama']); ?></h4>
                <p class="text-muted small">@<?= htmlspecialchars($_SESSION['username']); ?></p>
                <hr>
                <div class="badge bg-info text-dark">Status: User Aktif</div>
            </div>
        </div>

        <div class="col-md-8">
            <?php if(isset($_SESSION['msg'])): ?>
                <div class="alert alert-<?= $_SESSION['msg_type']; ?> alert-dismissible fade show">
                    <?= $_SESSION['msg']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['msg']); unset($_SESSION['msg_type']); ?>
            <?php endif; ?>

            <div class="card shadow-sm p-4">
                <h5 class="fw-bold mb-4">Pengaturan Akun</h5>
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username (Tidak dapat diubah)</label>
                        <input type="text" class="form-control bg-light" value="<?= $user['username']; ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin ganti password">
                        <div class="form-text text-danger">*Hati-hati, jika diisi maka password lama akan diganti.</div>
                    </div>
                    <hr>
                    <button type="submit" name="update_profil" class="btn btn-primary px-4">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>