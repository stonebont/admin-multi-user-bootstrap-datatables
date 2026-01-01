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
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .navbar-custom { background-color: #2c3e50; }
        .card { border: none; border-radius: 12px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">Admin Panel</a>
        <div class="ms-auto d-flex align-items-center">
            <span class="text-white me-3">Selamat Datang, <strong><?= htmlspecialchars($_SESSION['nama']); ?></strong></span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
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
                                        <a href="hapus_user.php?id=<?= $row['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger btn-hapus">Hapus</a>
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

</body>
</html>