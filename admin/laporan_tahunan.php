<?php
require 'function.php';
include 'koneksi.php';

// Mulai session untuk mendapatkan id_admin dari session
session_start();

// Mendapatkan ID Admin yang sedang login
$idAdmin = isset($_SESSION['id_admin']) ? $_SESSION['id_admin'] : null;

// Inisialisasi variabel
$siswa = [];
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? max(10, (int)$_GET['per_page']) : 10;
$start = ($page - 1) * $perPage;
$tahunAjaran = isset($_GET['tahun_ajaran']) ? $_GET['tahun_ajaran'] : '';

// Ambil data tahun ajaran dari tabel kategori_pembayaran
$queryTahunAjaran = "SELECT DISTINCT tahun_ajaran FROM kategori_pembayaran ORDER BY tahun_ajaran DESC";
$resultTahunAjaran = mysqli_query($mysqli, $queryTahunAjaran);

// Query untuk mengambil data siswa dan total pembayaran dengan filter
$query = "SELECT 
            s.nis_siswa, s.nama_siswa, s.jenis_kelamin, s.kelas,
            t.jenis_pembayaran, t.jenjang, t.total_pertahun, t.bulan, t.jumlah_bayar, t.diskon, t.tanggal_bayar, t.teller, t.tahun_ajaran, t.sisa_pembayaran
          FROM siswa s
          JOIN total t ON s.nis_siswa = t.nis_siswa
          WHERE 1";

// Jika admin bukan ID 1, filter berdasarkan nis_siswa atau ID Admin
if ($idAdmin !== 1) {
    $query .= " AND t.id_admin = '$idAdmin'";  // Sesuaikan dengan kolom yang ada di tabel "total"
}

if ($tahunAjaran) {
    $query .= " AND t.tahun_ajaran = '$tahunAjaran'";
}

$query .= " ORDER BY t.tanggal_bayar ASC LIMIT $start, $perPage";

$result = mysqli_query($mysqli, $query);

// Variabel untuk akumulasi total
$totalJumlahBayar = 0;
$totalDiskon = 0;
$totalSetelahDiskon = 0;
$totalSisaPembayaran = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $siswa[] = $row;

    // Akumulasi nilai total
    $totalJumlahBayar += $row["jumlah_bayar"];
    $totalDiskon += $row["diskon"];
    $totalSetelahDiskon = $totalJumlahBayar + $totalDiskon;

    // Akumulasi sisa pembayaran
    $totalSisaPembayaran += $row["sisa_pembayaran"];
}

// Hitung total data untuk pagination
$totalQuery = "SELECT COUNT(*) FROM siswa s JOIN total t ON s.nis_siswa = t.nis_siswa WHERE t.jenis_pembayaran = 'uang masuk'";

// Jika admin bukan ID 1, filter berdasarkan nis_siswa atau ID Admin
if ($idAdmin !== 1) {
    $totalQuery .= " AND t.id_admin = '$idAdmin'";  // Sesuaikan dengan kolom yang ada di tabel "total"
}

if ($tahunAjaran) {
    $totalQuery .= " AND t.tahun_ajaran = '$tahunAjaran'";
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
    <title>Laporan Tahunan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .text-nowrap {
        white-space: nowrap;
        /* Mencegah teks turun ke baris baru */
    }

    .table thead th {
        vertical-align: middle;
        /* Menjaga teks di header berada di tengah secara vertikal */
    }

    .table-responsive {
        -ms-overflow-style: scrollbar;
        /* Untuk IE/Edge */
        scrollbar-width: thin;
        /* Untuk Firefox */
    }
    </style>
</head>

<body>
    <?php include "sidebar.php"; ?>
    <?php include "navbar.php"; ?>

    <div class="container-fluid mt-4">
        <h6 class="font-weight-bold text-primary">Laporan Tahunan</h6>
        <!-- Filter Form -->
        <form method="get" action="" class="mb-3">
    <div class="row">
        <div class="col-md-6">
            <label for="tahun_ajaran">Tahun Ajaran:</label>
            <select id="tahun_ajaran" name="tahun_ajaran" class="form-select">
                <option value="">Pilih Tahun Ajaran</option>
                <?php while ($row = mysqli_fetch_assoc($resultTahunAjaran)): ?>
                <option value="<?= $row['tahun_ajaran']; ?>"
                    <?= $tahunAjaran == $row['tahun_ajaran'] ? 'selected' : '' ?>>
                    <?= $row['tahun_ajaran']; ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-6">
            <button type="submit" class="btn btn-primary mt-4">Filter</button>
            <!-- Tombol untuk Download CSV -->
            <a href="download_csv.php?page=<?= $page; ?>&per_page=<?= $perPage ?>&tahun_ajaran=<?= $tahunAjaran ?>"
                class="btn btn-success mt-4 ms-2">Download CSV</a>
        </div>
    </div>
</form>
   <!-- Tabel Data Siswa -->
   <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr class="text-center">
                    <th><input type="checkbox" id="select_all"></th>
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Jenis Kelamin</th>
                    <th>Kelas</th>
                    <th>Jenis Pembayaran</th>
                    <th>Jenjang</th>
                    <th>Tahun Ajaran</th>
                    <th>Biaya Pertahun</th>
                    <th>Jumlah Bayar</th>
                    <th>Diskon</th>
                    <th>Total Bayar Setelah Diskon</th>
                    <th>Bulan</th>
                    <th>Tanggal Pembayaran</th>
                    <th>Sisa Pembayaran</th>
                    <th>Teller</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = $start + 1; ?>
                <?php foreach ($siswa as $row): ?>
                <tr>
                    <td class="text-center">
                        <input type="checkbox" name="siswa[]" value="<?= htmlspecialchars(json_encode($row)); ?>">
                    </td>
                    <td class="text-center"><?= $i; ?></td>
                    <td class="text-center"><?= $row["nis_siswa"]; ?></td>
                    <td><?= $row["nama_siswa"]; ?></td>
                    <td><?= $row["jenis_kelamin"]; ?></td>
                    <td><?= $row["kelas"]; ?></td>
                    <td><?= $row["jenis_pembayaran"]; ?></td>
                    <td><?= $row["jenjang"]; ?></td>
                    <td><?= $row["tahun_ajaran"]; ?></td>
                    <td><?= "Rp. " . number_format($row["total_pertahun"], 0, ',', '.'); ?></td>
                    <td><?= "Rp. " . number_format($row["jumlah_bayar"], 0, ',', '.'); ?></td>
                    <td><?= "Rp. " . number_format($row["diskon"], 0, ',', '.'); ?></td>
                    <td><?= "Rp. " . number_format($row["jumlah_bayar"] + $row["diskon"], 0, ',', '.'); ?></td>
                    <td><?= $row["bulan"]; ?></td>
                    <td class="text-center"><?= date('d-m-Y', strtotime($row["tanggal_bayar"])); ?></td>
                    <td class="text-nowrap"><?= "Rp. " . number_format($row["sisa_pembayaran"], 0, ',', '.'); ?></td>
                    <td class="text-center"><?= $row["teller"]; ?></td>
                </tr>
                <?php $i++; endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Section -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <!-- Pagination -->
        <ul class="pagination mb-7 mt-10 w-100 justify-content-center">
            <!-- Tombol Sebelumnya -->
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a href="?page=<?= $page - 1; ?>&per_page=<?= $perPage ?>&tahun_ajaran=<?= $tahunAjaran ?>&bulan=<?= $bulan ?>" class="page-link">‹</a>
                </li>
            <?php endif; ?>

            <!-- Tautan Halaman -->
            <?php 
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);

            for ($p = $startPage; $p <= $endPage; $p++): 
            ?>
                <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                    <a href="?page=<?= $p; ?>&per_page=<?= $perPage ?>&tahun_ajaran=<?= $tahunAjaran ?>&bulan=<?= $bulan ?>" class="page-link"><?= $p; ?></a>
                </li>
            <?php endfor; ?>

            <!-- Tombol Berikutnya -->
            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a href="?page=<?= $page + 1; ?>&per_page=<?= $perPage ?>&tahun_ajaran=<?= $tahunAjaran ?>&bulan=<?= $bulan ?>" class="page-link">›</a>
                </li>
            <?php endif; ?>
        </ul>

        <!-- Dropdown Rows Per Page -->
        <div class="d-flex align-items-center">
            <label for="per_page" class="me-2 mb-0">Rows per page: </label>
            <select id="per_page" class="form-select" style="width: auto;" onchange="window.location='?page=<?= $page; ?>&per_page=' + this.value + '&tahun_ajaran=<?= $tahunAjaran ?>&bulan=<?= $bulan ?>'">
                <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100</option>
            </select>
        </div>
    </div>
</div>


        

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

    <script>
    // Select All Checkbox
    document.getElementById('select_all').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('input[name="siswa[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });
    </script>
</body>

</html>