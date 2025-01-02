<?php
require 'function.php';
include 'koneksi.php';

// Memulai session untuk mengambil id_admin
session_start();  // Mulai session
if (!isset($_SESSION['id_admin'])) {
    echo "<script>alert('Anda belum login!'); window.location.href = 'login.php';</script>";
    exit;
}

$id_admin = $_SESSION['id_admin'];  // Ambil id_admin dari session

// Inisialisasi variabel
$siswa = [];
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? max(10, (int)$_GET['per_page']) : 10;
$start = ($page - 1) * $perPage;

// Cek apakah admin adalah admin utama (id_admin = 1)
if ($id_admin == 1) {
    // Admin 1 bisa melihat semua data siswa
    $query = "SELECT 
                nis_siswa, nama_siswa, jenis_kelamin, jenjang, kelas,
                nama_ayah, no_hp_ayah, nama_ibu, no_hp_ibu, alamat, tahun_ajaran, tanggal_lulus
              FROM siswa_lulus
              LIMIT $start, $perPage";
} else {
    // Admin lainnya hanya melihat data siswa terkait id_admin mereka
    $query = "SELECT 
                nis_siswa, nama_siswa, jenis_kelamin, jenjang, kelas,
                nama_ayah, no_hp_ayah, nama_ibu, no_hp_ibu, alamat, tahun_ajaran, tanggal_lulus
              FROM siswa_lulus
              WHERE id_admin = '$id_admin'
              LIMIT $start, $perPage";
}

$result = mysqli_query($mysqli, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $siswa[] = $row;
}

// Hitung total data untuk pagination
if ($id_admin == 1) {
    // Admin 1 menghitung total seluruh data siswa
    $totalQuery = "SELECT COUNT(*) FROM siswa_lulus";
} else {
    // Admin lainnya hanya menghitung data mereka sendiri
    $totalQuery = "SELECT COUNT(*) FROM siswa_lulus WHERE id_admin = '$id_admin'";
}

$totalResult = mysqli_query($mysqli, $totalQuery);
$totalRows = mysqli_fetch_row($totalResult)[0];
$totalPages = ceil($totalRows / $perPage);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Ciomas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .text-nowrap { white-space: nowrap; }
        .table thead th {
            vertical-align: middle;
        }
        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
<?php include "navbar.php"; ?>

<div class="container-fluid mt-4">
    <h6 class="font-weight-bold text-primary">Daftar Siswa Lulus</h6>
    <form method="post" action="">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr class="text-center">
                    <th>No</th>
                    <th>NIS</th>
                    <th class="text-nowrap">Nama Siswa</th>
                    <th class="text-nowrap">Jenis Kelamin</th>
                    <th class="text-nowrap">Jenjang</th>
                    <th class="text-nowrap">Kelas</th>
                    <th class="text-nowrap">Nama Ayah</th>
                    <th class="text-nowrap">No HP Ayah</th>
                    <th class="text-nowrap">Nama Ibu</th>
                    <th class="text-nowrap">No HP Ibu</th>
                    <th class="text-nowrap">Alamat</th>
                    <th class="text-nowrap">Tahun Ajaran</th>
                    <th class="text-nowrap">Tanggal Kelulusan</th>
                </tr>
            </thead>
            <tbody >
                <?php $i = $start + 1; ?>
                <?php foreach ($siswa as $row): ?>
                    <tr>
                        <td class="text-center text-nowrap"><?= $i; ?></td>
                        <td class="text-center text-nowrap"><?= $row["nis_siswa"]; ?></td>
                        <td class="text-nowrap"><?= $row["nama_siswa"]; ?></td>
                        <td class="text-nowrap"><?= $row["jenis_kelamin"]; ?></td>
                        <td class="text-nowrap"><?= $row["jenjang"]; ?></td>
                        <td class="text-center text-nowrap"><?= $row["kelas"]; ?></td> <!-- Kelas di-center-kan -->
                        <td class="text-nowrap"><?= $row["nama_ayah"]; ?></td>
                        <td class="text-nowrap"><?= $row["no_hp_ayah"]; ?></td>
                        <td class="text-nowrap"><?= $row["nama_ibu"]; ?></td>
                        <td class="text-nowrap"><?= $row["no_hp_ibu"]; ?></td>
                        <td class="text-nowrap"><?= $row["alamat"]; ?></td>
                        <td class="text-nowrap"><?= $row["tahun_ajaran"]; ?></td>
                        <td class="text-nowrap"><?= date('d-m-Y', strtotime($row["tanggal_lulus"])); ?></td> <!-- Format tanggal -->
                    </tr>
                    <?php $i++; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

   <!-- Pagination -->
<nav aria-label="Page navigation example">
    <div class="d-flex justify-content-between align-items-center">
        <!-- Pagination (centered) -->
        <ul class="pagination justify-content-center w-100">
            <li class="page-item <?= ($page == 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page - 1 ?>&per_page=<?= $perPage ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&per_page=<?= $perPage ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= ($page == $totalPages) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page + 1 ?>&per_page=<?= $perPage ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>

        <!-- Rows per page (aligned to the right) -->
        <div class="d-flex align-items-center">
            <label for="per_page" class="me-2">Rows per page: </label>
            <select id="per_page" class="form-select" style="width: auto;" onchange="window.location='?page=<?= $page; ?>&per_page=' + this.value;">
                <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100</option>
            </select>
        </div>
    </div>
</nav>


    </form>

    <!-- Dropdown Rows Per Page -->
    <?php include "footer.php"; ?>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
