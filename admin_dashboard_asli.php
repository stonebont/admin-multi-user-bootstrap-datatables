<?php
session_start();
// 1. Proteksi Halaman: Cek apakah user sudah login dan apakah dia admin
if (!isset($_SESSION['level']) || $_SESSION['level'] != "admin") {
    $_SESSION['msg'] = "Anda tidak memiliki hak akses ke halaman tersebut!";
    $_SESSION['msg_type'] = "danger";
    header("Location: index.php");
    exit();
}
require 'koneksi.php';
// Hitung Total User
$stmt_total = $pdo->query("SELECT COUNT(*) FROM users");
$total_user = $stmt_total->fetchColumn();
// Hitung Admin
$stmt_admin = $pdo->query("SELECT COUNT(*) FROM users WHERE level = 'admin'");
$total_admin = $stmt_admin->fetchColumn();
// Hitung User Biasa
$stmt_reg = $pdo->query("SELECT COUNT(*) FROM users WHERE level = 'user'");
$total_reg = $stmt_reg->fetchColumn();
// Ambil Aktivitas Terbaru (3 log terakhir)
$stmt_last_activity = $pdo->query("SELECT aksi, waktu FROM logs ORDER BY waktu DESC LIMIT 1");
$last_activity = $stmt_last_activity->fetch();
// 2. Ambil data semua user dari database
try {
    $query = $pdo->query("SELECT id, nama_lengkap, username, level FROM users");
    $users = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Management User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.0.0/css/dataTables.bootstrap5.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/3.0.0/css/buttons.bootstrap5.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
	<!--icon bootstrap di card statistik-->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!--highcharts-->
	<script src="https://code.highcharts.com/highcharts.js"></script>
	<script src="https://code.highcharts.com/modules/exporting.js"></script>
	<script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .navbar-custom { background-color: #2c3e50; }
        .card { border: none; border-radius: 12px; }
		body {
			/* Berikan jarak agar konten tidak tertutup navbar (sekitar 120px) */
			padding-top: 50px; 
			background-color: #f8f9fa;
		}
		/* Memperhalus tampilan chart agar tidak terpotong saat di-scroll */
		#chartUser {
			min-height: 400px;
		}
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">
            <i class="bi bi-shield-shaded"></i> ADMIN PANEL
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
					<div class="ms-auto d-flex align-items-center">
						<span class="text-white me-0">Selamat Datang <strong><?= htmlspecialchars($_SESSION['nama']);?></strong> -</span>
						<a class="nav-link active me-1" href="admin_dashboard.php">Dashboard</a>
						<a href="logout.php" class="btn btn-outline-light btn-sm" onclick="return confirm('Yakin ingin logout?')">Logout</a>
					</div>
                    
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold m-0">Manajemen Pengguna</h4>
                    <a href="register.php" class="btn btn-primary">+ Tambah User Baru</a>
                </div>
				<!--card statistik-->
				<div class="row mb-4">
					<div class="col-md-4">
						<div class="card bg-primary text-white shadow">
							<div class="card-body">
								<div class="d-flex justify-content-between align-items-center">
									<div>
										<h6 class="text-uppercase mb-1">Total Pengguna</h6>
										<h2 class="mb-0"><?= $total_user; ?></h2>
									</div>
									<i class="bi bi-people-fill fs-1 opacity-50"></i>
								</div>
							</div>
						</div>
					</div>

					<div class="col-md-4">
						<div class="card bg-success text-white shadow">
							<div class="card-body">
								<div class="d-flex justify-content-between align-items-center">
									<div>
										<h6 class="text-uppercase mb-1">Administrator</h6>
										<h2 class="mb-0"><?= $total_admin; ?></h2>
									</div>
									<i class="bi bi-shield-lock-fill fs-1 opacity-50"></i>
								</div>
							</div>
						</div>
					</div>

					<div class="col-md-4">
						<div class="card bg-warning text-dark shadow">
							<div class="card-body">
								<div class="d-flex justify-content-between align-items-center">
									<div>
										<h6 class="text-uppercase mb-1">Aktivitas Terakhir</h6>
										<p class="mb-0 small fw-bold">
											<?= $last_activity ? $last_activity['aksi'] : 'Belum ada data'; ?>
										</p>
										<small class="opacity-75"><?= $last_activity ? $last_activity['waktu'] : '-'; ?></small>
									</div>
									<i class="bi bi-clock-history fs-1 opacity-50"></i>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!--grafik highcharts-->
				<div class="row mt-4">
					<div class="col-md-8">
						<div class="card shadow">
							<div class="card-body">
								<div id="chartUser"></div>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="card shadow">
							<div class="card-header bg-white fw-bold">Keterangan Level</div>
							<div class="card-body">
								<p class="small text-muted">Pembagian hak akses pengguna saat ini dalam sistem:</p>
								<ul class="list-group list-group-flush">
									<li class="list-group-item d-flex justify-content-between align-items-center">
										Administrator
										<span class="badge bg-success rounded-pill"><?= $total_admin; ?></span>
									</li>
									<li class="list-group-item d-flex justify-content-between align-items-center">
										User Biasa
										<span class="badge bg-primary rounded-pill"><?= $total_reg; ?></span>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<!--dataTables-->
                <div class="table-responsive">
                    <table id="tabelUser" class="table table-hover align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Lengkap</th>
                                <th>Username</th>
                                <th>Level Hak Akses</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach($users as $row): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td><?= htmlspecialchars($row['username']); ?></td>
                                <td>
                                    <?php if($row['level'] == 'admin'): ?>
                                        <span class="badge bg-danger">ADMINISTRATOR</span>
                                    <?php else: ?>
                                        <span class="badge bg-info text-dark">USER BIASA</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="edit_user.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                                        <a href="hapus_user.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm btn-hapus">Hapus</a>
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

<?php
// Ambil data log terbaru
$query_log = $pdo->query("SELECT * FROM logs ORDER BY waktu DESC LIMIT 100");
$logs = $query_log->fetchAll();
?>

<div class="container mt-2 mb-4">
	<div class="row">
		<div class="col-12">
			<div class="card shadow-sm p-4">
		<div class="card-header bg-dark text-white">
			<h5 class="mb-0">Riwayat Aktivitas Sistem (Log)</h5>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table id="tabelLog" class="table table-sm table-striped">
					<thead>
						<tr>
							<th>Waktu</th>
							<th>User</th>
							<th>Aksi</th>
							<th>Detail</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($logs as $log): ?>
						<tr>
							<td class="small"><?= $log['waktu']; ?></td>
							<td><span class="badge bg-secondary"><?= $log['username']; ?></span></td>
							<td><strong><?= $log['aksi']; ?></strong></td>
							<td class="small"><?= htmlspecialchars($log['detail']); ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
		</div>
	</div>
</div>

<script>
    // Tambahkan inisialisasi DataTable untuk tabel Log
    $(document).ready(function() {
        $('#tabelLog').DataTable({
            pageLength: 10,
            order: [[0, 'desc']] // Urutkan dari waktu terbaru
        });
    });
</script>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/2.0.0/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.0/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.0/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.0/js/buttons.bootstrap5.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.0/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.0/js/buttons.print.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Inisialisasi DataTables 2.0 (Hanya satu kali panggil)
    const table = $('#tabelUser').DataTable({
        layout: {
            topStart: {
                buttons: [
                    { extend: 'excel', className: 'btn btn-sm btn-success' },
                    { extend: 'pdf', className: 'btn btn-sm btn-danger' },
                    { extend: 'print', className: 'btn btn-sm btn-dark' }
                ]
            }
        },
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json', // Opsional: Bahasa Indonesia
            search: "_INPUT_",
            searchPlaceholder: "Cari data user...",
        },
        pageLength: 10
    });

    // Event Delegasi untuk Tombol Hapus (SweetAlert2)
    $('#tabelUser').on('click', '.btn-hapus', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');

        Swal.fire({
            title: "Hapus User?",
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Ya, Hapus!",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
});
</script>

<script>
Highcharts.chart('chartUser', {
    chart: {
        type: 'pie',
        backgroundColor: 'transparent'
    },
    title: {
        text: 'Persentase Level Pengguna'
    },
    tooltip: {
        pointFormat: '{series.name}: <b>{point.y} orang ({point.percentage:.1f}%)</b>'
    },
    accessibility: {
        point: {
            valueSuffix: '%'
        }
    },
    plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
                enabled: true,
                format: '<b>{point.name}</b>: {point.percentage:.1f} %'
            },
            showInLegend: true
        }
    },
    series: [{
        name: 'Jumlah',
        colorByPoint: true,
        data: [{
            name: 'Administrator',
            y: <?= $total_admin; ?>,
            color: '#198754' // Warna Hijau (Success)
        }, {
            name: 'User Biasa',
            y: <?= $total_reg; ?>,
            color: '#0d6efd' // Warna Biru (Primary)
        }]
    }]
});
</script>
<script>
$(document).ready(function() {
    // Inisialisasi Tabel User (yang sudah ada sebelumnya)
    $('#tabelUser').DataTable();

    // Inisialisasi Tabel Log (Baru)
    $('#tabelLog').DataTable({
        "pageLength": 5, // Menampilkan 5 baris saja per halaman agar rapi
        "lengthMenu": [5, 10, 25, 50], // Opsi pilihan jumlah baris
        "order": [[2, "desc"]], // Urutkan berdasarkan kolom waktu (indeks 2) secara terbaru
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json" // Opsional: Bahasa Indonesia
        }
    });

    // Kode SweetAlert Hapus User Anda tetap di bawah sini...
    $('.btn-hapus').on('click', function(e) {
        // ... kode hapus sebelumnya ...
    });
});
</script>
</body>
</html>