<?php
require 'koneksi.php';

// Mengambil parameter untuk pagination dan filter
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? max(10, (int)$_GET['per_page']) : 10;
$start = ($page - 1) * $perPage;

$tahun_ajaran = isset($_GET['tahun_ajaran']) ? $_GET['tahun_ajaran'] : '';
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Query untuk mengambil data siswa dan pembayaran dengan filter tahun ajaran, bulan, tanggal, dan status
$query = "SELECT 
            s.nis_siswa, s.nama_siswa, s.jenis_kelamin, s.kelas,
            t.jenis_pembayaran, t.jenjang, t.total_pertahun, t.bulan, t.jumlah_bayar, t.diskon, t.tanggal_bayar, t.teller, t.tahun_ajaran, t.status_pembayaran
          FROM siswa s
          JOIN total t ON s.nis_siswa = t.nis_siswa
          WHERE 1=1";

// Menambahkan filter tahun ajaran jika ada
if ($tahun_ajaran != '') {
    $query .= " AND t.tahun_ajaran = '$tahun_ajaran'";
}

// Menambahkan filter bulan jika ada
if ($bulan != '') {
    $query .= " AND t.bulan = '$bulan'";
}

// Menambahkan filter tanggal pembayaran jika ada
if ($tanggal != '') {
    $query .= " AND DATE(t.tanggal_bayar) = '$tanggal'";
}

// Menambahkan filter status pembayaran jika ada
if ($status != '') {
    $query .= " AND t.status_pembayaran = '$status'";
}

// Menambahkan LIMIT untuk pagination
$query .= " LIMIT $start, $perPage";

$result = mysqli_query($mysqli, $query);

// Set header untuk CSV file
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="data_uang_masuk.csv"');

// Outputkan file CSV
$output = fopen('php://output', 'w');

// Menulis header CSV
fputcsv($output, ['No', 'NIS', 'Nama Siswa', 'Jenis Kelamin', 'Kelas', 'Jenis Pembayaran', 'Jenjang', 'Tahun Ajaran', 'Biaya Pertahun', 'Jumlah Bayar', 'Diskon', 'Total Bayar Setelah Diskon', 'Bulan', 'Tanggal Pembayaran', 'Sisa Pembayaran', 'Teller', 'Status Pembayaran']);

// Menulis data
$no = 1;
while ($row = mysqli_fetch_assoc($result)) {
    $totalBayarSetelahDiskon = $row['jumlah_bayar'] + $row['diskon'];
    $sisaPembayaran = $row['total_pertahun'] - $totalBayarSetelahDiskon;
    
    fputcsv($output, [
        $no,
        $row['nis_siswa'],
        $row['nama_siswa'],
        $row['jenis_kelamin'],
        $row['kelas'],
        $row['jenis_pembayaran'],
        $row['jenjang'],
        $row['tahun_ajaran'],
        "Rp. " . number_format($row['total_pertahun'], 0, ',', '.'),
        "Rp. " . number_format($row['jumlah_bayar'], 0, ',', '.'),
        "Rp. " . number_format($row['diskon'], 0, ',', '.'),
        "Rp. " . number_format($totalBayarSetelahDiskon, 0, ',', '.'),
        $row['bulan'],
        date('d-m-Y', strtotime($row['tanggal_bayar'])),
        "Rp. " . number_format($sisaPembayaran, 0, ',', '.'),
        $row['teller'],
        $row['status_pembayaran']
    ]);
    $no++;
}

fclose($output);
exit;
?>
