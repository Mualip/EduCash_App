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
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';

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
    // Misalnya, admin selain ID 1 hanya dapat melihat data siswa yang terkait dengan mereka
    $query .= " AND t.id_admin = '$idAdmin'";  // Sesuaikan dengan kolom yang ada di tabel "total"
}

if ($tahunAjaran) {
    $query .= " AND t.tahun_ajaran = '$tahunAjaran'";
}

if ($bulan) {
    $query .= " AND t.bulan = '$bulan'";
}

$query .= " ORDER BY t.tanggal_bayar ASC LIMIT $start, $perPage";

$result = mysqli_query($mysqli, $query);

// Variabel untuk akumulasi total
$totalJumlahBayar = 0;
$totalDiskon = 0;
$totalSetelahDiskon = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $siswa[] = $row;

    // Akumulasi nilai total
    $totalJumlahBayar += $row["jumlah_bayar"];
    $totalDiskon += $row["diskon"];
    $totalSetelahDiskon = $totalJumlahBayar + $totalDiskon;
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

if ($bulan) {
    $totalQuery .= " AND t.bulan = '$bulan'";
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
    <title>Laporan Bulanan</title>
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
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
<?php include "navbar.php"; ?>

<div class="container-fluid mt-4">
    <h6 class="font-weight-bold text-primary">Laporan Bulanan</h6>
    
    <!-- Filter Form -->
    <form method="get" action="" class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <label for="tahun_ajaran">Tahun Ajaran:</label>
                <select id="tahun_ajaran" name="tahun_ajaran" class="form-select" onchange="this.form.submit()">
                    <option value="">Pilih Tahun Ajaran</option>
                    <?php while ($row = mysqli_fetch_assoc($resultTahunAjaran)): ?>
                        <option value="<?= $row['tahun_ajaran']; ?>" <?= $tahunAjaran == $row['tahun_ajaran'] ? 'selected' : '' ?>>
                            <?= $row['tahun_ajaran']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="bulan">Bulan:</label>
                <select id="bulan" name="bulan" class="form-select" onchange="this.form.submit()">
                    <option value="">Pilih Bulan</option>
                    <option value="Juli" <?= $bulan == 'Juli' ? 'selected' : '' ?>>Juli</option>
                    <option value="Agustus" <?= $bulan == 'Agustus' ? 'selected' : '' ?>>Agustus</option>
                    <option value="September" <?= $bulan == 'September' ? 'selected' : '' ?>>September</option>
                    <option value="Oktober" <?= $bulan == 'Oktober' ? 'selected' : '' ?>>Oktober</option>
                    <option value="November" <?= $bulan == 'November' ? 'selected' : '' ?>>November</option>
                    <option value="Desember" <?= $bulan == 'Desember' ? 'selected' : '' ?>>Desember</option>
                    <option value="Januari" <?= $bulan == 'Januari' ? 'selected' : '' ?>>Januari</option>
                    <option value="Februari" <?= $bulan == 'Februari' ? 'selected' : '' ?>>Februari</option>
                    <option value="Maret" <?= $bulan == 'Maret' ? 'selected' : '' ?>>Maret</option>
                    <option value="April" <?= $bulan == 'April' ? 'selected' : '' ?>>April</option>
                    <option value="Mei" <?= $bulan == 'Mei' ? 'selected' : '' ?>>Mei</option>
                    <option value="Juni" <?= $bulan == 'Juni' ? 'selected' : '' ?>>Juni</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary mt-4">Filter</button>
                <a href="download_csv.php?page=<?= $page; ?>&per_page=<?= $perPage ?>&tahun_ajaran=<?= $tahunAjaran ?>&bulan=<?= $bulan ?>" class="btn btn-success mt-4 ms-2">Download CSV</a>
            </div>
        </div>
    </form>
    
    <form id="form-pilih" method="post">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr class="text-center">
                        <th><input type="checkbox" id="select_all"></th> <!-- Select All Checkbox -->
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
                        <th class="text-nowrap">Sisa Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = $start + 1; ?>
                    <?php foreach ($siswa as $row): ?>
                        <tr>
                            <td class="text-center"><input type="checkbox" name="nis_siswa[]" value="<?= $row['nis_siswa']; ?>"></td>
                            <td class="text-center"><?= $no++; ?></td>
                            <td class="text-center"><?= $row['nis_siswa']; ?></td>
                            <td><?= $row['nama_siswa']; ?></td>
                            <td class="text-center"><?= $row['jenis_kelamin']; ?></td>
                            <td class="text-center"><?= $row['kelas']; ?></td>
                            <td><?= $row['jenis_pembayaran']; ?></td>
                            <td class="text-center"><?= $row['jenjang']; ?></td>
                            <td class="text-center"><?= $row['tahun_ajaran']; ?></td>
                            <td class="text-right"><?= number_format($row['total_pertahun'], 0, ',', '.'); ?></td>
                            <td class="text-right"><?= number_format($row['jumlah_bayar'], 0, ',', '.'); ?></td>
                            <td class="text-right"><?= number_format($row['diskon'], 0, ',', '.'); ?></td>
                            <td class="text-right"><?= number_format($row['sisa_pembayaran'], 0, ',', '.'); ?></td> <!-- Menampilkan sisa_pembayaran -->
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            
            </table>
        </div>
        

    <!-- Pagination -->
    <nav>
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>&per_page=<?= $perPage ?>&tahun_ajaran=<?= $tahunAjaran ?>&bulan=<?= $bulan ?>">Previous</a></li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&per_page=<?= $perPage ?>&tahun_ajaran=<?= $tahunAjaran ?>&bulan=<?= $bulan ?>"><?= $i ?></a></li>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>&per_page=<?= $perPage ?>&tahun_ajaran=<?= $tahunAjaran ?>&bulan=<?= $bulan ?>">Next</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
<?php include "footer.php"; ?>
<script>
    // Fungsi untuk memfilter berdasarkan nilai yang dimasukkan pengguna di kolom tabel
    function filterTable() {
        var tahunAjaranFilter = document.getElementById('tahun_ajaran_filter').value.toLowerCase();
        var bulanFilter = document.getElementById('bulan_filter').value.toLowerCase();
        var rows = document.querySelectorAll('tbody tr');

        rows.forEach(function(row) {
            var tahunAjaran = row.querySelector('td:nth-child(9)').textContent.toLowerCase();
            var bulan = row.querySelector('td:nth-child(13)').textContent.toLowerCase();

            if (tahunAjaran.includes(tahunAjaranFilter) && bulan.includes(bulanFilter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
