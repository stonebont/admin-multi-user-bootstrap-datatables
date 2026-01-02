<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != "admin") {
    $_SESSION['msg'] = "Anda tidak memiliki hak akses ke halaman tersebut!";
    $_SESSION['msg_type'] = "danger";
    header("Location: index.php");
    exit();
}
require 'koneksi.php';

// Ambil data user untuk foto profil di header (Opsional jika ada kolom foto)
$stmt_me = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt_me->execute([$_SESSION['user_id']]);
$user_me = $stmt_me->fetch();

// Statistik
$total_user = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_admin = $pdo->query("SELECT COUNT(*) FROM users WHERE level = 'admin'")->fetchColumn();
$total_reg = $pdo->query("SELECT COUNT(*) FROM users WHERE level = 'user'")->fetchColumn();
$last_activity = $pdo->query("SELECT aksi, waktu FROM logs ORDER BY waktu DESC LIMIT 1")->fetch();

// Data Tabel
$users = $pdo->query("SELECT id, nama_lengkap, username, level FROM users")->fetchAll(PDO::FETCH_ASSOC);
$logs = $pdo->query("SELECT * FROM logs ORDER BY waktu DESC LIMIT 100")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Management User</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.0.0/css/dataTables.bootstrap5.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; overflow-x: hidden; }
        
        /* Sidebar CSS */
        #wrapper { display: flex; width: 100%; align-items: stretch; }
        #sidebar-wrapper {
            min-height: 100vh; width: 250px; margin-left: 0;
            transition: margin 0.25s ease-out; position: fixed; z-index: 1000;
        }
        #page-content-wrapper {
            width: 100%; padding-left: 250px; transition: padding 0.25s ease-out;
        }
        
        /* Toggle Condition */
        body.sb-sidenav-toggled #sidebar-wrapper { margin-left: -250px; }
        body.sb-sidenav-toggled #page-content-wrapper { padding-left: 0; }

        .sidebar-heading { padding: 1.5rem 1rem; font-size: 1.2rem; font-weight: bold; background: #1a252f; }
        .list-group-item { padding: 1rem 1.5rem; border: none; background: #2c3e50; color: #adb5bd; }
        .list-group-item:hover { background: #1a252f; color: white; }
        .list-group-item.active { background: #3498db; border: none; }
        
        .sticky-header { position: sticky; top: 0; z-index: 999; background: white; border-bottom: 1px solid #dee2e6; }
        .card { border: none; border-radius: 12px; }

        @media (max-width: 768px) {
            #sidebar-wrapper { margin-left: -250px; }
            #page-content-wrapper { padding-left: 0; }
            body.sb-sidenav-toggled #sidebar-wrapper { margin-left: 0; }
        }
    </style>
</head>
<body>

<div id="wrapper">
    <div class="bg-dark text-white" id="sidebar-wrapper">
        <div class="sidebar-heading text-center text-white border-bottom border-secondary">
            <i class="bi bi-shield-shaded"></i> ADMIN PANEL
        </div>
        <div class="list-group list-group-flush">
            <a href="admin_dashboard.php" class="list-group-item list-group-item-action active">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
            <a href="edit_profil.php" class="list-group-item list-group-item-action">
                <i class="bi bi-person-circle me-2"></i> Profil Saya
            </a>
            <a href="#tabelUser" class="list-group-item list-group-item-action">
                <i class="bi bi-people me-2"></i> Manajemen User
            </a>
            <div class="mt-auto">
                <hr class="mx-3 text-secondary">
                <a href="logout.php" class="list-group-item list-group-item-action text-danger fw-bold" onclick="return confirm('Logout sekarang?')">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <div id="page-content-wrapper">
        <header class="p-2 sticky-header shadow-sm">
            <div class="container-fluid d-flex align-items-center">
                <button class="btn btn-outline-dark btn-sm me-3" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <h5 class="mb-0 d-none d-md-block">Manajemen Sistem</h5>
                <div class="ms-auto d-flex align-items-center">
                    <span class="me-3 small text-muted d-none d-sm-block"><?= date('l, d M Y'); ?></span>
                    <div class="dropdown">
                        <a href="#" class="text-decoration-none d-flex align-items-center dropdown-toggle" data-bs-toggle="dropdown">
                            <img src="uploads/<?= $user_me['foto'] ?? 'default.png' ?>" class="rounded-circle border" width="35" height="35" style="object-fit: cover;">
                            <span class="ms-2 text-dark small fw-bold d-none d-md-inline"><?= $_SESSION['nama'] ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="edit_profil.php">Edit Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <div class="container-fluid p-4">
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card bg-primary text-white shadow">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><h6 class="small">TOTAL PENGGUNA</h6><h2><?= $total_user ?></h2></div>
                            <i class="bi bi-people-fill fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-success text-white shadow">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><h6 class="small">ADMINISTRATOR</h6><h2><?= $total_admin ?></h2></div>
                            <i class="bi bi-shield-lock-fill fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-warning text-dark shadow">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><h6 class="small">AKTIVITAS TERAKHIR</h6><p class="mb-0 small fw-bold"><?= $last_activity['aksi'] ?? '-' ?></p></div>
                            <i class="bi bi-clock-history fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-8 mb-3">
                    <div class="card shadow p-3">
                        <div id="chartUser"></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow p-3 mb-3">
                        <h6 class="fw-bold">Level Keterangan</h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">Admin <span class="badge bg-success"><?= $total_admin ?></span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">User <span class="badge bg-primary"><?= $total_reg ?></span></li>
                        </ul>
                    </div>
                    <div class="card shadow">
                        <div class="card-header bg-dark text-white small py-2">Log Terakhir</div>
                        <div class="card-body p-0">
                            <table id="tabelLog" class="table table-sm mb-0" style="font-size: 0.8rem;">
                                <thead><tr><th>Waktu</th><th>Aksi</th></tr></thead>
                                <tbody>
                                    <?php foreach(array_slice($logs, 0, 5) as $l): ?>
                                    <tr><td><?= $l['waktu'] ?></td><td><?= $l['aksi'] ?></td></tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow p-4 mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold">Daftar Pengguna</h5>
                    <a href="register.php" class="btn btn-primary btn-sm">+ Tambah User</a>
                </div>
                <div class="table-responsive">
                    <table id="tabelUser" class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr><th>No</th><th>Nama</th><th>Username</th><th>Level</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php $no=1; foreach($users as $u): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($u['nama_lengkap']) ?></td>
                                <td><?= htmlspecialchars($u['username']) ?></td>
                                <td><span class="badge <?= $u['level']=='admin'?'bg-danger':'bg-info' ?>"><?= strtoupper($u['level']) ?></span></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="edit_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                                        <a href="hapus_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-danger btn-hapus">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.0.0/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.0/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.0/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.0/js/buttons.bootstrap5.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>

<script>
$(document).ready(function() {
    // 1. Sidebar Toggle
    $("#sidebarToggle").click(function(e) {
        e.preventDefault();
        $("body").toggleClass("sb-sidenav-toggled");
        // Resize Highcharts agar grafik menyesuaikan lebar baru
        setTimeout(() => { window.dispatchEvent(new Event('resize')); }, 300);
    });

    // 2. DataTables User
    $('#tabelUser').DataTable({
        layout: {
            topStart: { buttons: ['excel', 'pdf', 'print'] }
        },
        language: { searchPlaceholder: "Cari user..." }
    });

    // 3. DataTables Log
    $('#tabelLog').DataTable({
        pageLength: 5,
        lengthChange: false,
        searching: false,
        info: false
    });

    // 4. SweetAlert Hapus
    $('#tabelUser').on('click', '.btn-hapus', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        Swal.fire({
            title: "Hapus User?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Ya, Hapus!"
        }).then((result) => {
            if (result.isConfirmed) window.location.href = url;
        });
    });

    // 5. Highcharts
    Highcharts.chart('chartUser', {
        chart: { type: 'pie' },
        title: { text: 'Komposisi Level Pengguna' },
        series: [{
            name: 'Jumlah',
            data: [
                { name: 'Admin', y: <?= $total_admin ?>, color: '#198754' },
                { name: 'User', y: <?= $total_reg ?>, color: '#0d6efd' }
            ]
        }]
    });
});
</script>
</body>
</html>