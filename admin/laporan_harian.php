<?php
session_start();
require 'function.php';
include 'koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['id_admin'])) {
    header("Location: login.php");  // Arahkan ke halaman login jika admin belum login
    exit;
}

// Ambil id_admin dari sesi
$id_admin = $_SESSION['id_admin'];

// Inisialisasi variabel
$siswa = [];
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? max(10, (int)$_GET['per_page']) : 10;
$start = ($page - 1) * $perPage;
$tahunAjaran = $_GET['tahun_ajaran'] ?? '';
$bulan = $_GET['bulan'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';

// Ambil data tahun ajaran dari tabel kategori_pembayaran
$queryTahunAjaran = "SELECT DISTINCT tahun_ajaran FROM kategori_pembayaran ORDER BY tahun_ajaran DESC";
$resultTahunAjaran = mysqli_query($mysqli, $queryTahunAjaran);

// Query untuk mengambil data siswa dan total pembayaran dengan filter
$query = "SELECT 
            s.nis_siswa, s.nama_siswa, s.jenis_kelamin, s.kelas,
            t.jenis_pembayaran, t.jenjang, t.total_pertahun, t.bulan, t.jumlah_bayar, t.diskon, t.tanggal_bayar, t.teller, t.tahun_ajaran
          FROM siswa s
          JOIN total t ON s.nis_siswa = t.nis_siswa
          WHERE 1";  // Mulai query dengan kondisi dasar

// Kondisi untuk admin tertentu
if ($id_admin != 1) {
    $query .= " AND t.id_admin = '$id_admin'";  // Admin selain id_admin = 1 hanya dapat melihat data mereka sendiri
}

if ($tahunAjaran) {
    $query .= " AND t.tahun_ajaran = '$tahunAjaran'";
}
if ($bulan) {
    $query .= " AND t.bulan = '$bulan'";
}
if ($tanggal) {
    $query .= " AND DATE(t.tanggal_bayar) = '$tanggal'"; // Filter berdasarkan tanggal
}

$query .= " ORDER BY t.tanggal_bayar ASC LIMIT $start, $perPage";

$result = mysqli_query($mysqli, $query);

// Variabel untuk akumulasi total
$totalJumlahBayar = 0;
$totalDiskon = 0;
$totalSetelahDiskon = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $row['sisa_pembayaran'] = $row['total_pertahun'] - ($row['jumlah_bayar'] + $row['diskon']);
    $siswa[] = $row;

    // Akumulasi nilai total
    $totalJumlahBayar += $row["jumlah_bayar"];
    $totalDiskon += $row["diskon"];
}
$totalSetelahDiskon = $totalJumlahBayar + $totalDiskon;

// Hitung total data untuk pagination
$totalQuery = "SELECT COUNT(*) FROM siswa s JOIN total t ON s.nis_siswa = t.nis_siswa WHERE t.jenis_pembayaran = 'uang masuk'";

if ($id_admin != 1) {
    $totalQuery .= " AND t.id_admin = '$id_admin'";
}
if ($tahunAjaran) {
    $totalQuery .= " AND t.tahun_ajaran = '$tahunAjaran'";
}
if ($bulan) {
    $totalQuery .= " AND t.bulan = '$bulan'";
}
if ($tanggal) {
    $totalQuery .= " AND DATE(t.tanggal_bayar) = '$tanggal'";
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
    <title>Laporan Harian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .text-nowrap {
        white-space: nowrap; /* Mencegah teks turun ke baris baru */
    }
    .table thead th {
        vertical-align: middle; /* Menjaga teks di header berada di tengah secara vertikal */
    }
    .table-responsive {
    -ms-overflow-style: scrollbar; /* Untuk IE/Edge */
    scrollbar-width: thin; /* Untuk Firefox */
}
.container-fluid.mt-4 {
    padding-left: 20px; /* Memberikan jarak dari sidebar */
    padding-right: 20px; /* Memberikan jarak di sebelah kanan jika diperlukan */
}

.sidebar {
    margin-right: 20px; /* Memberikan jarak di sidebar sebelah kanan */
}

.table-responsive {
    margin-top: 20px; /* Memberikan jarak antar elemen pada tabel */
}

/* Jika sidebar adalah fixed, Anda bisa menambahkan margin pada konten */
.main-content {
    margin-left: 250px; /* Ganti sesuai dengan lebar sidebar jika fixed */
}
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
<?php include "navbar.php"; ?>

<div class="container-fluid mt-4">
    <h6 class="font-weight-bold text-primary">Laporan Harian</h6>
    <!-- Filter Form -->
    <form method="get" action="" class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <label for="tahun_ajaran">Tahun Ajaran:</label>
                <select id="tahun_ajaran" name="tahun_ajaran" class="form-select">
                    <option value="">Pilih Tahun Ajaran</option>
                    <?php while ($row = mysqli_fetch_assoc($resultTahunAjaran)): ?>
                        <option value="<?= $row['tahun_ajaran']; ?>" <?= $tahunAjaran == $row['tahun_ajaran'] ? 'selected' : '' ?>>
                            <?= $row['tahun_ajaran']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="tanggal">Tanggal:</label>
                <input type="date" id="tanggal" name="tanggal" class="form-control" value="<?= $tanggal; ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary mt-4">Filter</button>
                <a href="download_csv.php?page=<?= $page; ?>&per_page=<?= $perPage ?>&tahun_ajaran=<?= $tahunAjaran ?>&bulan=<?= $bulan ?>&tanggal=<?= $tanggal ?>" class="btn btn-success mt-4 ms-2">Download CSV</a>
            </div>
        </div>
    </form>

    <!-- Tabel -->
    <form id="form-pilih" method="post">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr class="text-center">
                        <th><input type="checkbox" id="select_all"></th>
                        <th class="text-nowrap">No</th>
                        <th class="text-nowrap">NIS</th>
                        <th class="text-nowrap">Nama Siswa</th>
                        <th class="text-nowrap">Jenis Kelamin</th>
                        <th class="text-nowrap">Kelas</th>
                        <th class="text-nowrap">Jenis Pembayaran</th>
                        <th class="text-nowrap">Jenjang</th>
                        <th class="text-nowrap">Tahun Ajaran</th>
                        <th class="text-nowrap">Biaya Pertahun</th>
                        <th class="text-nowrap">Jumlah Bayar</th>
                        <th class="text-nowrap">Diskon</th>
                        <th class="text-nowrap">Total Bayar Setelah Diskon</th>
                        <th class="text-nowrap">Bulan</th>
                        <th class="text-nowrap">Tanggal Pembayaran</th>
                        <th class="text-nowrap">Sisa Pembayaran</th>
                        <th class="text-nowrap">Teller</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = $start + 1; ?>
                    <?php foreach ($siswa as $row): ?>
                        <tr>
                            <td class="text-center"><input type="checkbox" name="siswa[]" value="<?= htmlspecialchars(json_encode($row)); ?>"></td>
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
                            <td><?= $row["tanggal_bayar"]; ?></td>
                            <td><?= "Rp. " . number_format($row["sisa_pembayaran"], 0, ',', '.'); ?></td>
                            <td><?= $row["teller"]; ?></td>
                        </tr>
                        <?php $i++; ?>
                    <?php endforeach; ?>
                </tbody>
         
            </table>
        </div>
    </form>
</div>
       
    
<!-- Pagination Section -->
<div class="d-flex justify-content-between align-items-center mt-3">
        <!-- Pagination -->
       <!-- Pagination -->
<ul class="pagination mb-7 mt-10 w-100 justify-content-center">
    <!-- Tombol Sebelumnya -->
    <?php if ($page > 1): ?>
        <li class="page-item">
            <a href="?page=<?= $page - 1; ?>&per_page=<?= $perPage ?>&tahun_ajaran=<?= $tahunAjaran ?>&tanggal=<?= $tanggal ?>" class="page-link">‹</a>
        </li>
    <?php endif; ?>

    <!-- Tautan Halaman -->
    <?php 
    $startPage = max(1, $page - 2);
    $endPage = min($totalPages, $page + 2);

    for ($p = $startPage; $p <= $endPage; $p++): 
    ?>
        <li class="page-item <?= $p == $page ? 'active' : '' ?>">
            <a href="?page=<?= $p; ?>&per_page=<?= $perPage ?>&tahun_ajaran=<?= $tahunAjaran ?>&tanggal=<?= $tanggal ?>" class="page-link"><?= $p; ?></a>
        </li>
    <?php endfor; ?>

    <!-- Tombol Berikutnya -->
    <?php if ($page < $totalPages): ?>
        <li class="page-item">
            <a href="?page=<?= $page + 1; ?>&per_page=<?= $perPage ?>&tahun_ajaran=<?= $tahunAjaran ?>&tanggal=<?= $tanggal ?>" class="page-link">›</a>
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
<script>
    // Fungsi untuk memfilter kolom dalam tabel
    function filterTable(column, value) {
        var table = document.querySelector('table');
        var rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(function(row) {
            var cell = row.querySelectorAll('td');
            var found = false;

            // Cari nilai di setiap kolom berdasarkan index
            switch (column) {
                case 'nis_siswa':
                    found = cell[1].textContent.toLowerCase().includes(value.toLowerCase());
                    break;
                case 'nama_siswa':
                    found = cell[2].textContent.toLowerCase().includes(value.toLowerCase());
                    break;
                case 'jenis_kelamin':
                    found = cell[3].textContent.toLowerCase().includes(value.toLowerCase());
                    break;
                case 'kelas':
                    found = cell[4].textContent.toLowerCase().includes(value.toLowerCase());
                    break;
                case 'jenis_pembayaran':
                    found = cell[5].textContent.toLowerCase().includes(value.toLowerCase());
                    break;
                case 'jenjang':
                    found = cell[6].textContent.toLowerCase().includes(value.toLowerCase());
                    break;
                case 'tahun_ajaran':
                    found = cell[7].textContent.toLowerCase().includes(value.toLowerCase());
                    break;
                case 'biaya_pertahun':
                    found = cell[8].textContent.toLowerCase().includes(value.toLowerCase());
                    break;
                case 'jumlah_bayar':
                    found = cell[9].textContent.toLowerCase().includes(value.toLowerCase());
                    break;
                case 'diskon':
                    found = cell[10].textContent.toLowerCase().includes(value.toLowerCase());
                    break;
                case 'setelah diskon':
                    found = cell[11].textContent.toLowerCase().includes(value.toLowerCase());
                    break;
                case 'bulan':
                    found = cell[12].textContent.toLowerCase().includes(value.toLowerCase());
                    break;
                case 'tanggal_pembayaran':
                    found = cell[13].textContent.toLowerCase().includes(value.toLowerCase());
                    break;
                case 'sisa_pembayaran':
                    found = cell[14].textContent.toLowerCase().includes(value.toLowerCase());
                    break;
                case 'teller':
                    found = cell[15].textContent.toLowerCase().includes(value.toLowerCase());
                    break;
            }

            if (found) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>

   <script>
    document.getElementById('select_all').addEventListener('click', function () {
        const checkboxes = document.querySelectorAll('input[name^="siswa"]');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });

</script>
<script>
    document.getElementById('select_all').addEventListener('click', function () {
        const checkboxes = document.querySelectorAll('input[name="siswa[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
