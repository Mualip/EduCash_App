<?php


require 'function.php';
include 'koneksi.php';
session_start();

// Pastikan id_admin ada dalam session, jika tidak, redirect ke halaman login
if (!isset($_SESSION['id_admin'])) {
    header("Location: login.php");
    exit;
}

$id_admin = $_SESSION['id_admin'];  // Ambil id_admin dari session


// Inisialisasi variabel untuk pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? max(10, (int)$_GET['per_page']) : 10;
$start = ($page - 1) * $perPage;

// Hitung total data kategori pembayaran
$totalQuery = "SELECT COUNT(*) FROM kategori_pembayaran";
$totalResult = mysqli_query($mysqli, $totalQuery);
$totalRows = mysqli_fetch_row($totalResult)[0];
$totalPages = ceil($totalRows / $perPage);

// Ambil data kategori pembayaran dengan pagination
$query = "SELECT id_kategori, jenis_pembayaran, tahun_ajaran, jenjang, kelas 
          FROM kategori_pembayaran 
          LIMIT $start, $perPage";
$result = mysqli_query($mysqli, $query);

// Inisialisasi array data kategori
$kategori_pembayaran = [];
while ($row = mysqli_fetch_assoc($result)) {
    $kategori_pembayaran[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kategori Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        th, td {
            text-align: center;
            vertical-align: middle;
        }
        .table thead th {
            background-color: #f8f9fa;
            text-align: center;
        }
        .table td, .table th {
            padding: 10px;
        }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
<?php include "navbar.php"; ?>

<div class="container-fluid mt-4">
    <h6 class="font-weight-bold text-primary">Edit Kategori Pembayaran</h6>
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead>
                <tr class="text-center">
                    <th>No</th>
                    <th>Jenis Pembayaran</th>
                    <th>Tahun Ajaran</th>
                    <th>Jenjang</th>
                    <th>Kelas</th>
                    <th>Edit</th>
                    <th>Hapus</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = $start + 1; ?>
                <?php foreach ($kategori_pembayaran as $row): ?>
                    <tr>
                        <td class="text-center"><?= $i; ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($row["jenis_pembayaran"]); ?></td>
                        <td class="text-nowrap"><?= htmlspecialchars($row["tahun_ajaran"]); ?></td>
                        <td class="text-center text-nowrap"><?= $row["jenjang"]; ?></td>
                        <td class="text-center text-nowrap"><?= $row["kelas"]; ?></td>
                        <td class="text-center">
                            <a href="edit_kat.php?id_kategori=<?= urlencode($row['id_kategori']); ?>" class="btn btn-primary btn-sm">Edit</a>
                        </td>
                        <td class="text-center">
                            <a href="hapus.php?id_kategori=<?= urlencode($row['id_kategori']); ?>" onclick="return confirm('Apakah yakin akan menghapus data ini?')" class="btn btn-danger btn-sm">Hapus</a>
                        </td>
                    </tr>
                    <?php $i++; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <ul class="pagination">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a href="?page=<?= $page - 1; ?>&per_page=<?= $perPage ?>" class="page-link">‹</a>
                </li>
            <?php endif; ?>

            <?php 
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            for ($p = $startPage; $p <= $endPage; $p++): 
            ?>
                <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                    <a href="?page=<?= $p; ?>&per_page=<?= $perPage ?>" class="page-link"><?= $p; ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a href="?page=<?= $page + 1; ?>&per_page=<?= $perPage ?>" class="page-link">›</a>
                </li>
            <?php endif; ?>
        </ul>

        <!-- Dropdown Rows Per Page -->
        <div class="d-flex align-items-center">
            <label for="per_page" class="me-2 mb-0">Rows per page: </label>
            <select id="per_page" class="form-select" style="width: auto;" onchange="window.location='?page=<?= $page; ?>&per_page=' + this.value;">
                <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100</option>
            </select>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
