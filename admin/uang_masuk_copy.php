<?php
require 'function.php';
include 'koneksi.php';

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
            t.jenis_pembayaran, t.jenjang, t.total_pertahun, t.bulan, t.jumlah_bayar, t.diskon, t.tanggal_bayar, t.teller, t.tahun_ajaran
          FROM siswa s
          JOIN total t ON s.nis_siswa = t.nis_siswa
          WHERE t.jenis_pembayaran = 'uang masuk'";

if ($tahunAjaran) {
    $query .= " AND t.tahun_ajaran = '$tahunAjaran'";
}
if ($bulan) {
    $query .= " AND t.bulan = '$bulan'";
}

$query .= " ORDER BY t.tanggal_bayar ASC LIMIT $start, $perPage";

$result = mysqli_query($mysqli, $query);

// Variabel untuk akumulasi total
$totalJumlahBayar = 0; // Total jumlah yang sudah dibayarkan
$totalDiskon = 0;      // Total diskon yang diberikan
$totalSetelahDiskon = 0; // Total setelah diskon (kumulatif)
$remainingPayment = 0; // Sisa pembayaran kumulatif
while ($row = mysqli_fetch_assoc($result)) {
    // Hitung sisa pembayaran berdasarkan total biaya pertahun dikurangi jumlah bayar + diskon
    $remainingPayment = $row['total_pertahun'] - ($totalJumlahBayar + $row['jumlah_bayar'] + $totalDiskon + $row['diskon']);
    
    // Jika sisa pembayaran negatif, set ke 0
    $row['sisa_pembayaran'] = $remainingPayment > 0 ? $remainingPayment : 0;
    $siswa[] = $row;

    // Akumulasi nilai total
    $totalJumlahBayar += $row["jumlah_bayar"];
    $totalDiskon += $row["diskon"];
    $totalSetelahDiskon = $totalJumlahBayar + $totalDiskon;
}

// Hitung total data untuk pagination
$totalQuery = "SELECT COUNT(*) FROM siswa s JOIN total t ON s.nis_siswa = t.nis_siswa WHERE t.jenis_pembayaran = 'uang masuk'";
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
    <title>Admin Ciomas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .text-nowrap {
        white-space: nowrap; /* Mencegah teks turun ke baris baru */
    }
    .table thead th {
        vertical-align: middle; /* Menjaga teks di header berada di tengah secara vertikal */
    }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
<?php include "navbar.php"; ?>

<div class="container-fluid mt-4">
    <h6 class="font-weight-bold text-primary">Daftar Uang Masuk</h6>
    <!-- Filter Form -->
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
            <label for="bulan">Bulan:</label>
            <select id="bulan" name="bulan" class="form-select">
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
        </div>
    </div>
</form>
<form id="form-cetak" method="post" action="cetak_pembayaran.php">
        <button type="submit" class="btn btn-primary mt-4" id="btn-cetak">Cetak Bukti Pembayaran</button>
    </form>
    <br>

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
                <th class="text-nowrap">Total Bayar Setelah Diskon</th>
                <th class="text-nowrap">Bulan</th>
                <th class="text-nowrap">Tanggal Pembayaran</th>
                <th class="text-nowrap">Sisa Pembayaran</th>
                

                <th class="text-nowrap">Teller</th>
            </tr>
            </thead>
            <thead>
                <!-- Baris Pencarian -->
                <tr class="filters">
               
                    <th></th> <!-- Kolom kosong untuk nomor -->
                    <th><input type="text" class="form-control" placeholder="Cari " onkeyup="filterTable('nis_siswa', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari " onkeyup="filterTable('nama_siswa', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari " onkeyup="filterTable('jenis_kelamin', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari " onkeyup="filterTable('kelas', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari " onkeyup="filterTable('jenis_pembayaran', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari " onkeyup="filterTable('jenjang', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari " onkeyup="filterTable('tahun_ajaran', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari " onkeyup="filterTable('biaya_pertahun', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari " onkeyup="filterTable('jumlah_bayar', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari " onkeyup="filterTable('diskon', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari " onkeyup="filterTable('setelah diskon', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari " onkeyup="filterTable('bulan', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari " onkeyup="filterTable('tanggal_pembayaran', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari " onkeyup="filterTable('sisa_pembayaran', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari " onkeyup="filterTable('teler', this.value)"></th>
                    

                </tr>
            </thead>
            <tbody>
    <?php $i = $start + 1; ?>
    <?php foreach ($siswa as $row): ?>
        <tr>
        <td class="text-center">
                            <input type="checkbox" name="siswa[<?= $row['nis_siswa']; ?>]" value="<?= $row['kelas']; ?>"> <!-- Checkbox for each student -->
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
            <td class="text-nowrap"><?= "Rp. " . number_format($row["diskon"], 0, ',', '.'); ?></td>
            <td><?= "Rp. " . number_format($row["jumlah_bayar"] + $row["diskon"], 0, ',', '.'); ?></td>
            <td><?= $row["bulan"]; ?></td>
            <td><?= date('d-m-Y', strtotime($row["tanggal_bayar"])); ?></td>
            <td><?= "Rp. " . number_format($row["sisa_pembayaran"], 0, ',', '.'); ?></td>
            
            <!-- Kolom Sisa Pembayaran -->
            
           
            
            <td><?= $row["teller"]; ?></td>
        </tr>
        <?php $i++; ?>
    <?php endforeach; ?>

    <!-- Baris Total -->
    <tr class="font-weight-bold bg-light">
        <td colspan="10">Jumlah</td>
        <td><?= "Rp. " . number_format($totalJumlahBayar, 0, ',', '.'); ?></td>
        <td class="text-nowrap"><?= "Rp. " . number_format($totalDiskon, 0, ',', '.'); ?></td>
        <td><?= "Rp. " . number_format($totalSetelahDiskon, 0, ',', '.'); ?></td>
        <td colspan="3"></td>
    </tr>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
