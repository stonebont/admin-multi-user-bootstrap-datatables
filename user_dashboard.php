<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != "user") {
    header("Location: index.php");
    exit();
}

require 'koneksi.php';

// Ambil data user terbaru
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch();

if (isset($_POST['update_profil'])) {
    $nama_baru = $_POST['nama_lengkap'];
    $pass_baru = $_POST['password'];
    $foto_nama = $user['foto']; // Default pakai foto lama

    // Logika Upload Foto
    if ($_FILES['foto']['error'] === 0) {
        $ekstensi_valid = ['jpg', 'jpeg', 'png'];
        $nama_file = $_FILES['foto']['name'];
        $ukuran_file = $_FILES['foto']['size'];
        $tmp_name = $_FILES['foto']['tmp_name'];
        $ekstensi_file = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

        if (in_array($ekstensi_file, $ekstensi_valid) && $ukuran_file < 2000000) { // Max 2MB
            $foto_nama = uniqid() . "." . $ekstensi_file;
            move_uploaded_file($tmp_name, 'uploads/' . $foto_nama);
            
            // Hapus foto lama jika bukan default.png
            if ($user['foto'] != 'default.png' && file_exists('uploads/' . $user['foto'])) {
                unlink('uploads/' . $user['foto']);
            }
        } else {
            $_SESSION['msg'] = "Format salah atau file terlalu besar (Max 2MB)!";
            $_SESSION['msg_type'] = "danger";
            header("Location: user_dashboard.php");
            exit();
        }
    }

    try {
        if (!empty($pass_baru)) {
            $hashed_pass = password_hash($pass_baru, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET nama_lengkap = ?, password = ?, foto = ? WHERE username = ?";
            $pdo->prepare($sql)->execute([$nama_baru, $hashed_pass, $foto_nama, $_SESSION['username']]);
        } else {
            $sql = "UPDATE users SET nama_lengkap = ?, foto = ? WHERE username = ?";
            $pdo->prepare($sql)->execute([$nama_baru, $foto_nama, $_SESSION['username']]);
        }
        
        $_SESSION['nama'] = $nama_baru;
        $_SESSION['msg'] = "Profil berhasil diperbarui!";
        $_SESSION['msg_type'] = "success";
        header("Location: user_dashboard.php");
        exit();
    } catch (PDOException $e) {
        die($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard - Upload Foto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary mb-4 shadow">
    <div class="container">
        <span class="navbar-brand">User Dashboard</span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-md-4 text-center">
            <div class="card p-4 shadow-sm">
                <?php 
                $path_foto = ($user['foto'] == 'default.png') ? 'https://ui-avatars.com/api/?name='.$user['nama_lengkap'] : 'uploads/'.$user['foto'];
                ?>
                <img src="<?= $path_foto ?>" class="rounded-circle img-thumbnail mx-auto mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                <h5><?= htmlspecialchars($user['nama_lengkap']); ?></h5>
                <p class="text-muted">@<?= $user['username']; ?></p>
				<hr>
                <div class="badge bg-info text-dark">Status: User Aktif</div>
            </div>
        </div>

        <div class="col-md-8">
            <?php if(isset($_SESSION['msg'])): ?>
                <div class="alert alert-<?= $_SESSION['msg_type']; ?> fade show mb-3">
                    <?= $_SESSION['msg']; unset($_SESSION['msg']); ?>
                </div>
            <?php endif; ?>

            <div class="card p-4 shadow-sm">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Ganti Foto Profil</label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                        <div class="form-text">Format: JPG, PNG. Maksimal 2MB.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Ganti Password (Opsional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin ganti password">
						<div class="form-text text-danger">*Hati-hati, jika diisi maka password lama akan diganti.</div>
					</div>
                    <button type="submit" name="update_profil" class="btn btn-primary w-100">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>