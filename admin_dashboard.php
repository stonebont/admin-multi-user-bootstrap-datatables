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
// Cari bagian query data tabel, ubah menjadi seperti ini:
// Cari bagian query data tabel, ubah menjadi seperti ini:
$users = $pdo->query("SELECT id, nama_lengkap, username, email, level, is_active FROM users")->fetchAll(PDO::FETCH_ASSOC);
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
		
		@media (max-width: 768px) {
    /* Saat sidebar terbuka di mobile, berikan efek sedikit gelap pada konten */
    body.sb-sidenav-toggled #page-content-wrapper {
        opacity: 0.5;
        cursor: pointer;
        pointer-events: auto; /* Memastikan area ini bisa menerima klik */
    }
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
            <a href="#tabelUser" class="list-group-item list-group-item-action">
                <i class="bi bi-people me-2"></i> Manajemen User
            </a>
			<a href="#tabelLogLengkap" class="list-group-item list-group-item-action">
                <i class="bi bi-clock-history me-2"></i> Riwayat Logs
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
                            <li><a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalEditProfil">
    <i class="bi bi-person-circle"></i> Edit Profil
</a></li>
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
                    <div class="card shadow p-3 mb-3">
						<div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
							<span class="small fw-bold"><i class="bi bi-clock-history me-2"></i>Riwayat</span>
						</div>
					</div>
            </div>

            <div class="card shadow p-4 mb-5 mt-1">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold">Daftar Pengguna</h5>
                    <a href="register.php" class="btn btn-primary btn-sm">+ Tambah User</a>
                </div>
                <div class="table-responsive">
                    <table id="tabelUser" class="table table-hover align-middle">
						<thead class="table-light">
							<tr>
								<th>No</th>
								<th>Nama</th>
								<th>Username</th><th>Email</th>
								<th>Level</th>
								<th>Status</th> <th>Aksi</th>
							</tr>
						</thead>
						<tbody>
							<?php $no=1; foreach($users as $u): ?>
							<tr>
								<td><?= $no++ ?></td>
								<td><?= htmlspecialchars($u['nama_lengkap']) ?></td>
								<td><?= htmlspecialchars($u['username']) ?></td>
								<td><a href="mailto:<?= htmlspecialchars($u['email']) ?>" class="text-decoration-none small"><?= htmlspecialchars($u['email']) ?></a></td>
								<td>
									<span class="badge <?= $u['level']=='admin'?'bg-danger':'bg-info' ?>">
										<?= strtoupper($u['level']) ?>
									</span>
								</td>
								<td>
									<?php if($u['is_active'] == 1): ?>
										<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Aktif</span>
									<?php else: ?>
										<span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i> Non-aktif</span>
									<?php endif; ?>
								</td>
								<td>
									<div class="btn-group">
										<?php if($u['id'] != $_SESSION['user_id']): ?>
											<button class="btn btn-sm <?= $u['is_active'] == 1 ? 'btn-outline-secondary' : 'btn-success' ?> btn-toggle-status" 
													data-id="<?= $u['id'] ?>" 
													data-status="<?= $u['is_active'] ?>"
													title="<?= $u['is_active'] == 1 ? 'Non-aktifkan User' : 'Aktifkan User' ?>">
												<i class="bi <?= $u['is_active'] == 1 ? 'bi-person-dash' : 'bi-person-check' ?>"></i>
											</button>
										<?php endif; ?>
										
										<a href="edit_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
										
										<?php if($u['id'] != $_SESSION['user_id']): ?>
											<a href="hapus_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger btn-hapus"><i class="bi bi-trash"></i></a>
										<?php endif; ?>
									</div>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
                </div>
			</div>
<div class="row mt-4 mb-5">
    <div class="col-12">
        <div class="card shadow border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Riwayat Aktivitas Sistem</h5>
                <button class="btn btn-sm btn-outline-light" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh Log
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tabelLogLengkap" class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="15%">Waktu</th>
                                <th width="15%">User</th>
                                <th width="20%">Aksi</th>
                                <th width="50%">Detail Aktivitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($logs as $log): ?>
                            <tr>
                                <td class="small text-muted">
                                    <i class="bi bi-calendar-event me-1"></i> <?= date('d M Y', strtotime($log['waktu'])); ?><br>
                                    <i class="bi bi-clock me-1"></i> <?= date('H:i:s', strtotime($log['waktu'])); ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary px-2 py-2">
                                        <i class="bi bi-person-fill me-1"></i> <?= htmlspecialchars($log['username']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($log['aksi']); ?></span>
                                </td>
                                <td class="text-muted small">
                                    <?= htmlspecialchars($log['detail']); ?>
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
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditProfil" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-gear"></i> Edit Profil Saya</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditProfil" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" value="<?= $_SESSION['nama']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?= $_SESSION['username']; ?>" readonly>
                        <small class="text-muted text-xs">*Username tidak dapat diubah</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin ganti">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Foto Profil</label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
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
<script src="https://cdn.datatables.net/buttons/3.0.0/js/buttons.html5.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script src="https://cdn.datatables.net/buttons/3.0.0/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.0/js/buttons.print.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>




<script>
$(document).ready(function() {
    // 1. Sidebar Toggle
    $(document).ready(function() {
    // 1. Fungsi Utama Toggle Sidebar
    $("#sidebarToggle").click(function(e) {
        e.stopPropagation(); // Mencegah event bubbling
        e.preventDefault();
        $("body").toggleClass("sb-sidenav-toggled");
        
        // Resize Highcharts agar grafik menyesuaikan lebar baru
        setTimeout(() => { window.dispatchEvent(new Event('resize')); }, 300);
    });

    // 2. Menutup Sidebar saat Klik di Luar (Area Konten)
    // Hanya bekerja saat mode sidebar terbuka (sb-sidenav-toggled)
    $("#page-content-wrapper").click(function() {
        if ($("body").hasClass("sb-sidenav-toggled")) {
            // Cek jika layar sedang di mode mobile/minimize (lebar < 768px)
            // Atau jika Anda ingin fitur ini di semua ukuran layar, hapus kondisi width
            if ($(window).width() <= 768) {
                $("body").removeClass("sb-sidenav-toggled");
            }
        }
    });

    // 3. Mencegah penutupan saat mengeklik di dalam sidebar itu sendiri
    $("#sidebar-wrapper").click(function(e) {
        e.stopPropagation();
    });
});
    // 4. DataTables User
   // Logic untuk Aktifkan/Non-aktifkan User
$('#tabelUser').on('click', '.btn-toggle-status', function() {
    const userId = $(this).data('id');
    const currentStatus = $(this).data('status');
    const newStatus = currentStatus == 1 ? 0 : 1;
    const actionText = newStatus == 1 ? 'MENGAKTIFKAN' : 'MENONAKTIFKAN';
    const color = newStatus == 1 ? '#198754' : '#6c757d';

    Swal.fire({
        title: "Konfirmasi Status",
        text: `Apakah Anda yakin ingin ${actionText} user ini?`,
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: color,
        confirmButtonText: "Ya, Ubah!",
        cancelButtonText: "Batal"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'update_status_user.php', // Pastikan file ini sudah dibuat
                type: 'POST',
                data: { id: userId, status: newStatus },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload(); 
                        });
                    } else {
                        Swal.fire("Gagal", res.message, "error");
                    }
                },
                error: function() {
                    Swal.fire("Error", "Gagal menghubungi server.", "error");
                }
            });
        }
    });
}).DataTable({
        layout: {
            topStart: {
                buttons: [
                    // Tombol Excel
                    {
                        extend: 'excelHtml5',
                        text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        exportOptions: { columns: [0, 1, 2, 3, 4] }
                    },
                    // Tombol PDF
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        exportOptions: { columns: [0, 1, 2, 3, 4] },
                        // Mengatur orientasi kertas
                        customize: function (doc) {
                            doc.content[1].table.widths = ['5%', '25%', '20%', '30%', '20%'];
                        }
                    },
                    // Tombol Print
                    {
                        extend: 'print',
                        text: '<i class="bi bi-printer me-1"></i> Print',
                        className: 'btn btn-dark btn-sm',
                        exportOptions: { columns: [0, 1, 2, 3, 4] }
                    }
                ]
            }
        },
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
        }
    });

    // 5. DataTables Log
   $('#tabelLogLengkap').DataTable({
        "order": [[0, "desc"]], // Urutkan berdasarkan waktu terbaru
        "pageLength": 5,
        "lengthMenu": [5, 10, 25, 50, 100],
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json",
            "search": "Cari aktivitas:",
            "searchPlaceholder": "Ketik user, aksi, atau detail..."
        },
        "dom": '<"row mb-3"<"col-md-6"l><"col-md-6"f>>rt<"row mt-3"<"col-md-6"i><"col-md-6"p>>'
    });

    // 6. SweetAlert Hapus
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

    // 7. Highcharts
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
	// 8. Edit Profil Admin
	  $('#formEditProfil').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: 'update_profil_ajax.php', // File pemroses baru
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                let data = JSON.parse(response);
                if (data.status === 'success') {
                    alert('Profil berhasil diperbarui!');
                    location.reload(); // Refresh untuk melihat perubahan nama/foto
                } else {
                    alert('Gagal: ' + data.message);
                }
            }
        });
    });
});
</script>
</body>
</html>