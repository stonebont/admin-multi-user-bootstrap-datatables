<?php
include 'koneksi.php';
session_start();

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data user terbaru
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama_lengkap'];
    $foto_nama = $_FILES['foto']['name'];
    $foto_tmp = $_FILES['foto']['tmp_name'];
    
    if (!empty($foto_nama)) {
        // Validasi Ekstensi
        $ekstensi_boleh = ['png', 'jpg', 'jpeg'];
        $x = explode('.', $foto_nama);
        $ekstensi = strtolower(end($x));
        $nama_baru = "profil_" . $user_id . "_" . time() . "." . $ekstensi;

        if (in_array($ekstensi, $ekstensi_boleh)) {
            // Upload file baru
            move_uploaded_file($foto_tmp, 'uploads/' . $nama_baru);
            
            // Hapus foto lama jika bukan default.png
            if ($user['foto'] != 'default.png' && file_exists('uploads/' . $user['foto'])) {
                unlink('uploads/' . $user['foto']);
            }

            // Update nama & foto
            $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, foto = ? WHERE id = ?");
            $stmt->execute([$nama, $nama_baru, $user_id]);
        }
    } else {
        // Update nama saja
        $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ? WHERE id = ?");
        $stmt->execute([$nama, $user_id]);
    }

    $_SESSION['nama_lengkap'] = $nama; // Update session agar navbar berubah
    header("Location: edit_profil.php?status=success");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">Edit Profil</div>
                    <div class="card-body text-center">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <img src="uploads/<?= $user['foto']; ?>" class="rounded-circle mb-3 border" width="150" height="150" style="object-fit: cover;">
                            
                            <div class="mb-3 text-start">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap']); ?>" required>
                            </div>

                            <div class="mb-3 text-start">
                                <label class="form-label">Ganti Foto Profil (PNG/JPG)</label>
                                <input type="file" name="foto" class="form-control" accept="image/*">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                                <a href="admin_dashboard.php" class="btn btn-secondary mt-2">Kembali ke Dashboard</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
    <script>
        Swal.fire('Berhasil!', 'Profil Anda telah diperbarui.', 'success');
    </script>
    <?php endif; ?>
</body>
</html>