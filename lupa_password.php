<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require 'koneksi.php';

// Load PHPMailer via Composer
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['request_reset'])) {
    $username = $_POST['username'];
    
    // 1. Cek apakah username ada dan memiliki email
    $stmt = $pdo->prepare("SELECT id, nama_lengkap, email FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        if (empty($user['email'])) {
            $_SESSION['msg'] = "Akun Anda tidak memiliki data email. Silakan hubungi admin.";
            $_SESSION['msg_type'] = "warning";
        } else {
            // 2. Buat Token Rahasia
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Berlaku 1 jam

            // 3. Simpan token ke database
            $update = $pdo->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE id = ?");
            $update->execute([$token, $expiry, $user['id']]);

            // 4. Konfigurasi Link Reset
            $reset_link = "http://localhost/multi_user_gemini/reset_password.php?token=" . $token;

            // 5. Kirim Email Menggunakan PHPMailer
            $mail = new PHPMailer(true);

            try {
				// Aktifkan kode di bawah ini jika ingin melihat laporan error yang detail
				// $mail->SMTPDebug = 2;
                // Pengaturan Server SMTP Gmail
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'stonebont@gmail.com';      // GANTI dengan Gmail Anda
                $mail->Password   = 'jvgpmjjkfzduxogb';      // GANTI dengan 16 Digit App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Pengaturan Pengirim & Penerima
                $mail->setFrom('stonebont@gmail.com', 'Admin System');
                $mail->addAddress($user['email'], $user['nama_lengkap']);

                // Konten Email
                $mail->isHTML(true);
                $mail->Subject = 'Permintaan Reset Password - ' . $user['nama_lengkap'];
                $mail->Body    = "
                    <h3>Halo, " . htmlspecialchars($user['nama_lengkap']) . "</h3>
                    <p>Kami menerima permintaan untuk menyetel ulang password akun Anda.</p>
                    <p>Silakan klik tombol atau link di bawah ini untuk melanjutkan:</p>
                    <a href='$reset_link' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reset Password Sekarang</a>
                    <br><br>
                    <p>Atau copy link berikut ke browser Anda:</p>
                    <p>$reset_link</p>
                    <hr>
                    <p><small>Link ini akan kadaluarsa dalam 1 jam. Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini.</small></p>
                ";

                $mail->send();
                $_SESSION['msg'] = "Instruksi reset password telah dikirim ke email: " . $user['email'];
                $_SESSION['msg_type'] = "success";
            } catch (Exception $e) {
                $_SESSION['msg'] = "Gagal mengirim email. Error: {$mail->ErrorInfo}";
                $_SESSION['msg_type'] = "danger";
            }
        }
    } else {
        $_SESSION['msg'] = "Username <strong>$username</strong> tidak ditemukan dalam sistem.";
        $_SESSION['msg_type'] = "danger";
    }
    header("Location: lupa_password.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 15px; border: none; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center align-items-center vh-100">
        <div class="col-md-5">
            <div class="text-center mb-4">
                <h2 class="fw-bold">Bantuan Password</h2>
                <p class="text-muted">Masukkan username Anda untuk menerima link reset.</p>
            </div>

            <?php if(isset($_SESSION['msg'])): ?>
			<script>
				Swal.fire({
					icon: '<?= $_SESSION['msg_type'] ?>', // success, error, info, atau warning
					title: 'Informasi',
					html: '<?= $_SESSION['msg'] ?>', // Menggunakan 'html' agar link simulasi bisa diklik jika ada
					showConfirmButton: true
				});
			</script>
			<?php unset($_SESSION['msg']); unset($_SESSION['msg_type']); endif; ?>
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label fw-bold">Username</label>
                            <input type="text" name="username" id="username" class="form-control form-control-lg" placeholder="Contoh: budi_santoso" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" name="request_reset" class="btn btn-primary btn-lg">Kirim Link Reset</button>
                            <a href="index.php" class="btn btn-link text-decoration-none text-muted">Kembali ke Login</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <p class="text-center mt-4 text-muted small">&copy; 2026 Admin System - All Rights Reserved</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>