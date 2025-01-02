<?php
require('koneksi.php'); // Menghubungkan ke file koneksi database

session_start();
if (empty($_SESSION['id_admin'])) {
    header('Location: login.php'); // Arahkan ke halaman login jika belum login
    exit;
}

$idAdmin = $_SESSION['id_admin']; // Mengambil id_admin dari session
$namaSiswa = $_GET['nama_siswa'] ?? '';

// Inisialisasi resultPembayaran agar tidak undefined jika tidak ada siswa yang dipilih
$resultPembayaran = null;


// Query untuk mendapatkan data berdasarkan nama siswa yang dipilih
$query = "
    SELECT 
        siswa.nama_siswa AS nama_siswa,
        siswa.nis_siswa AS nis_siswa, 
        siswa.keaktifan AS keaktifan,
        siswa.kelas AS kelas,
        siswa.jenis_kelamin AS jenis_kelamin,
        siswa.jenjang AS jenjang
    FROM siswa
    WHERE siswa.id_admin = ? AND siswa.nama_siswa LIKE ?
";
$stmt = $mysqli->prepare($query);
$searchName = "%" . $namaSiswa . "%"; // Pencarian berdasarkan nama siswa
$stmt->bind_param("ss", $idAdmin, $searchName); // Bind id_admin dan nama_siswa
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Jangan lupa untuk menutup statement setelah pengambilan hasil pertama
//$stmt->free_result(); // Membebaskan hasil query
//$stmt->close();

// Query untuk mendapatkan daftar nama siswa untuk dropdown
$namaSiswaQuery = "
    SELECT DISTINCT nama_siswa 
    FROM siswa 
    WHERE id_admin = ?
    ORDER BY nama_siswa ASC
";
$namaSiswaStmt = $mysqli->prepare($namaSiswaQuery);
$namaSiswaStmt->bind_param("s", $idAdmin); // Bind id_admin
$namaSiswaStmt->execute();
$namaSiswaResult = $namaSiswaStmt->get_result();


// Tutup statement untuk nama siswa setelah mengambil hasil
//$namaSiswaStmt->free_result();
//$namaSiswaStmt->close();

// Query untuk mengambil kategori pembayaran berdasarkan id_admin
$kategoriQuery = "
    SELECT * 
    FROM kategori_pembayaran 
    WHERE id_admin = ?
    ORDER BY jenis_pembayaran ASC
";
$kategoriStmt = $mysqli->prepare($kategoriQuery);
$kategoriStmt->bind_param("s", $idAdmin); // Bind id_admin
$kategoriStmt->execute();
$kategoriResult = $kategoriStmt->get_result();

// Query untuk mengambil logo
$queryLogo = "SELECT logo FROM profil_sekolah WHERE id_admin = ? LIMIT 1";
$resultLogoStmt = $mysqli->prepare($queryLogo);
$resultLogoStmt->bind_param("s", $idAdmin);
$resultLogoStmt->execute();
$resultLogo = $resultLogoStmt->get_result();
$resultLogoStmt->free_result();
$resultLogoStmt->close();

// Memeriksa apakah nama siswa dipilih
if (!empty($namaSiswa)) {
    // Ambil NIS berdasarkan nama siswa yang dipilih
    $querySiswa = "SELECT nis_siswa FROM siswa WHERE nama_siswa = ? AND id_admin = ?";
    $stmtSiswa = $mysqli->prepare($querySiswa);
    $stmtSiswa->bind_param("ss", $namaSiswa, $idAdmin);
    $stmtSiswa->execute();
    $resultSiswa = $stmtSiswa->get_result();
    
    if ($resultSiswa->num_rows > 0) {
        $siswa = $resultSiswa->fetch_assoc();
        $nisSiswa = $siswa['nis_siswa']; // Dapatkan nis_siswa untuk pencarian pembayaran
    }
    $stmtSiswa->close();
    
   
    // Query untuk mengambil pembayaran berdasarkan nis_siswa dan id_admin
    $queryPembayaran = "
    SELECT 
        kategori_pembayaran.jenis_pembayaran,
        kategori_pembayaran.tahun_ajaran,
        biaya_pertahun.total_pertahun,
        total.jumlah_bayar,
        total.diskon,
        total.tanggal_bayar
    FROM kategori_pembayaran
    LEFT JOIN biaya_pertahun ON kategori_pembayaran.jenis_pembayaran = biaya_pertahun.jenis_biaya 
                             AND kategori_pembayaran.tahun_ajaran = biaya_pertahun.tahun_ajaran
    LEFT JOIN total ON kategori_pembayaran.jenis_pembayaran = total.jenis_pembayaran
    WHERE total.nis_siswa = ? AND total.id_admin = ?
    ORDER BY total.tanggal_bayar DESC
";
$stmtPembayaran = $mysqli->prepare($queryPembayaran);
$stmtPembayaran->bind_param("ss", $nisSiswa, $idAdmin);
$stmtPembayaran->execute();
$resultPembayaran = $stmtPembayaran->get_result();
}


// Cek apakah ada data pembayaran terakhir
if ($resultPembayaran && $resultPembayaran->num_rows > 0) {
    // Ambil data pembayaran terakhir
    $dataPembayaran = $resultPembayaran->fetch_assoc();
    
    // Ambil data pembayaran terakhir
    $biayaPertahun = $dataPembayaran['total_pertahun'];
    $jumlahBayar = $dataPembayaran['jumlah_bayar'];
    $diskon = $dataPembayaran['diskon'];
    $tanggalBayar = $dataPembayaran['tanggal_bayar'];

    // Hitung sisa pembayaran terakhir
    $sisaPembayaran = $biayaPertahun - $jumlahBayar - $diskon;

    // Cek jika ada pembayaran yang dilakukan dalam 5 menit terakhir
    $currentDateTime = new DateTime(); // Waktu saat ini
    $lastPaymentTime = new DateTime($tanggalBayar); // Waktu pembayaran terakhir
    $interval = $currentDateTime->diff($lastPaymentTime);

    // Jika pembayaran dilakukan dalam 5 menit terakhir, tampilkan baris kedua
    if ($interval->i < 5) {
        $jumlahBayarBaru = 50000;  // Contoh jumlah pembayaran baru
        $diskonBaru = 5000;        // Contoh diskon baru
        $sisaPembayaranBaru = $sisaPembayaran - $jumlahBayarBaru - $diskonBaru;
    }
}

/// Ambil data pembayaran pertama jika ada
if ($resultPembayaran && $resultPembayaran->num_rows > 0) {
    // Ambil data pembayaran pertama
    $dataPembayaran = $resultPembayaran->fetch_assoc();
    
    // Jika data pembayaran ada, ambil nilai-nilainya
    $biayaPertahun = $dataPembayaran['total_pertahun'] ?? 0;
    $jumlahBayar = $dataPembayaran['jumlah_bayar'] ?? 0;
    $diskon = $dataPembayaran['diskon'] ?? 0;
    $tanggalBayar = $dataPembayaran['tanggal_bayar'] ?? '';
    
    // Hitung sisa pembayaran
    $sisaPembayaran = $biayaPertahun - $jumlahBayar - $diskon;
} else {
    // Jika tidak ada data pembayaran, set nilai default
    $biayaPertahun = 0;
    $jumlahBayar = 0;
    $diskon = 0;
    $sisaPembayaran = 0;
    $tanggalBayar = '';
}


// Jika tidak ada pembayaran pertama, kita ambil nilai total_pertahun dari tabel total pertahun
// Pastikan total_pertahun sudah ada di tabel yang sesuai
if ($biayaPertahun == 0) {
    // Query untuk mendapatkan total_pertahun jika pembayaran pertama tidak ada
    $queryTotalPertahun = "SELECT total_pertahun FROM biaya_pertahun WHERE id_admin = ?"; // Ganti dengan query yang sesuai
    $stmt = $mysqli->prepare($queryTotalPertahun);
    $stmt->bind_param('i', $idSiswa); // Sesuaikan dengan parameter ID siswa
    $stmt->execute();
    $resultTotalPertahun = $stmt->get_result();

    if ($resultTotalPertahun && $resultTotalPertahun->num_rows > 0) {
        $row = $resultTotalPertahun->fetch_assoc();
        $biayaPertahun = $row['total_pertahun'];
    }
    
    // Set nilai default jika tidak ada total_pertahun
    if ($biayaPertahun == 0) {
        $biayaPertahun = 0;
    }
    $jumlahBayar = 0;
    $diskon = 0;
    $sisaPembayaran = $biayaPertahun;
}
?>









<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kuitansi Pembayaran</title>
    <!-- Link CSS untuk Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />

    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f4f4;
    }

    .container {
        width: 900px;
        margin: auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .header {
        position: relative;
        margin-bottom: none;
        display: flex;
        justify-content: flex-start;
        align-items: center;
        gap: 20px;
    }

    .header h1 {
        margin: 0;
        font-size: 12px;
        line-height: 1.2;
    }

    .header p {
        margin: 0;
        font-size: 10px;

    }

    .logo {
        width: 110px;
        margin-bottom: 0;
    }

    /* Untuk memastikan border pada tabel menyatu dan tampak baik di layar */
    table {
        border-collapse: collapse;
        /* Menggabungkan border agar tidak ada celah antar sel */
        width: 100%;
        /* Memastikan tabel memenuhi lebar kontainer */
    }

    /* Tabel Info Siswa tanpa border */
    .info-table {
        width: 100%;
        margin-bottom: none;
        border-collapse: collapse;
        padding: -10px;
    }

    .info-table td {
        padding: 10px;
        border: none;
        /* Menghapus border pada tabel info */
    }

    .info-table td {
        text-align: left;
    }

    /* Tabel Pembayaran dengan border */
    .payment-table {
        width: 100%;
        margin-bottom: 10px;
        border-collapse: collapse;
    }

    th,
    td {
        border: 3px solid #e0e0e0;
        /* Border hitam dan tipis di sekitar setiap sel */
        padding: 5px;
        /* Ruang dalam sel agar konten tidak terlalu rapat dengan border */
        text-align: center;
        /* Menyusun teks ke tengah */
        font-size: 12px;
        /* Ukuran font untuk teks di dalam sel */
    }

    .payment-table th,
    .payment-table td {
        border: 3px solid #e0e0e0;
        padding: 2px;
        text-align: center;
    }

    .payment-table th {
        background-color: #f2f2f2;
        /* Warna latar belakang header tabel */
        font-weight: bold;

    }

    .payment-table td {
        text-align: center;

    }

    .payment-table td.left-align {
        text-align: left;

    }

    .total-amount {
        text-align: right;
        font-size: 12px;
        font-weight: bold;
    }

    form {
        font-size: 12px;
        /* Ukuran font 12px untuk seluruh form */
    }

    .form-group {
        display: flex;
        align-items: center;

        margin-bottom: none;
        /* Mengurangi jarak antar form group */
        padding-bottom: 0;
        /* Menghilangkan padding bawah */
    }

    .form-group label {
        display: inline-block;
        width: 100px;
        /* Tentukan lebar label agar seragam */
        font-weight: bold;
    }

    .form-group span {
        margin: none;
        /* Spasi antara tanda ":" dan select */
    }

    .form-group input,
    .form-group select {
        width: calc(100% - 120px);
    }

    .footer {
        text-align: center;
        margin-top: 12px;
        font-size: 12px;
        color: #555;
    }

    .footer p {
        margin: 0;
    }

    .small-font {
        border: 3px solid #e0e0e0;
        padding: 2px;
        /* Ruang dalam sel agar konten tidak terlalu rapat dengan border */
        text-align: center;
        /* Menyusun teks ke tengah */
        font-size: 12px;
        /* Ukuran font untuk teks di dalam sel */
    }

    /* CSS untuk mencetak */
    @media print {

        /* Mengatur margin saat mencetak */
        @page {
            margin: 10mm;
            /* Sesuaikan margin jika perlu */
        }

        /* Sembunyikan elemen yang tidak diinginkan */
        body * {
            visibility: hidden;
        }

        .container,
        .container * {
            visibility: visible;
        }

        /* Menghilangkan border pada container saat dicetak */
        .container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            border: none;
            /* Menghilangkan border di container */
        }

        /* Hapus header browser dan footer browser */
        header,
        footer {
            display: none;
            border: none;
        }

        /* Mengatur jarak antar elemen dan memastikan elemen terlihat saat dicetak */
        .small-font {
            border: 3px solid #e0e0e0;
            padding: 1px 4px 1px 4px;
            /* Padding atas, kanan, bawah, kiri */
            text-align: center;
            /* Menyusun teks di tengah */
            font-size: 12px;
            visibility: visible;
            /* Pastikan elemen tetap terlihat saat dicetak */
            box-sizing: border-box;
            /* Memastikan padding dan border dihitung dalam ukuran elemen */
            align-items: center;
            justify-content: center;
        }

        /* Mengatur tabel agar rapi saat dicetak */
        .info-table {
            width: 100%;
            border-spacing: 0;
            /* Hapus jarak antar kolom */
            border-collapse: collapse;
        }

        .info-table td {
            padding: 3px;
            /* Menambahkan padding di dalam sel */
        }

        .form-group {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            margin: 0;
            /* Memastikan elemen berada dalam satu baris */
        }

        .form-group label {
            font-weight: bold;
            margin-right: 10px;
            /* Jarak antara label dan nilai */
        }

        .form-group span {
            margin-left: none;
        }

        /* Agar elemen form berada sejajar dalam satu baris */
        .info-table tr {
            display: flex;
            justify-content: flex-start;
            /* Memastikan form group berada di satu baris */
            flex-wrap: wrap;
        }

        .info-table td {
            width: 70%;
            /* Setiap kolom memiliki lebar 48% agar lebih rapat */
            display: inline-block;
            /* Memastikan form group berada di satu baris */
        }

        /* Sembunyikan elemen dengan nama siswa atau jenis kelamin jika diperlukan */
        #nama_siswa,
        .nama-siswa-class {
            display: none;
        }

        /* Untuk memastikan Nama Siswa dan Jenis Kelamin berada dalam satu baris */
        .info-table td .form-group {
            margin-bottom: 16px;
            /* Menghapus jarak bawah untuk elemen form group */
        }
    }

    /* Media Queries untuk Responsivitas */
    @media (max-width: 140px) {
        .container {
            width: 100%;
            padding: 10px;
        }

        .header h1 {
            font-size: 14px;
        }

        .header p {
            font-size: 13px;
        }

        .info-table,
        .payment-table {
            font-size: 12px;
        }

        .form-group label {
            width: 100px;
        }

        .form-group input,
        .form-group select {
            width: 150%;
        }

        .footer p {
            font-size: 12px;
        }
    }

    .custom-select {
        width: 80%;
        max-width: 1200px;
        /* Tentukan lebar maksimal */
        padding: none;
        /* Memberikan padding untuk kenyamanan pengguna */
    }
    </style>
</head>

<body>
    <?php include "sidebar.php"; ?>
    <div class="container">
        <div class="header">
            <!-- Menggunakan logo yang diambil dari database -->
            <img src="img/logoinis.png" alt="Logo" class="logo">
            <div>
                <h1>Bukti Pembayaran </h1>
                <p>IMAM NAWAWI</p>
                <p>Jl. Raya Ciomas Cikoneng Gg. Masjid No. 35 RT 001 RW 003, Pagelaran, Kec. Ciomas, Kab. Bogor provinsi
                    jawa barat</p>
            </div>
        </div>
        <br>
        <div style="border-top: 2px dashed #ccc; ">
            <form method="GET" action="">
            <table class="info-table" style="width: 100%;">
    <tr>
        <!-- Kolom pertama untuk Nama Siswa, NIS, dan Jenis Kelamin -->
        <td style="width: 60%; padding-right: -13px;">
        <div class="form-group" style="margin-bottom: -5px;">
    <label for="nama_siswa" style="display: inline-block; margin-right: 1px;"><strong>Nama Siswa</strong>:</label> <span>:</span><select name="nama_siswa" id="nama_siswa" onchange="this.form.submit()" class="custom-select"
        style="width: 400px; max-width: 700px; font-size: 12px; margin-left: 10px; text-align: left;">
        <option value="">Pilih Nama Siswa</option>
        <?php while ($siswa = $namaSiswaResult->fetch_assoc()) { ?>
        <option value="<?= htmlspecialchars($siswa['nama_siswa']); ?>"
            <?= $namaSiswa == $siswa['nama_siswa'] ? 'selected' : ''; ?>>
            <?= htmlspecialchars($siswa['nama_siswa']); ?>
        </option>
        <?php } ?>
    </select>
</div>

                            <?php if (!empty($namaSiswa) && isset($row) && $row) { ?>
                            <div class="form-group" style="margin-top: 5px;">
                                <label><strong>NIS Siswa</strong></label>
                                <span>: <?= htmlspecialchars($row['nis_siswa']); ?></span>
                            </div>
                            <div class="form-group" style="margin-top: -15px;">
                                <label><strong>Jenis Kelamin</strong></label>
                                <span>: <?= htmlspecialchars($row['jenis_kelamin']); ?></span>
                            </div>
                            <?php } else { ?>
                            <div class="form-group">
                                <label><strong>NIS Siswa</strong></label>
                                <span>: -</span>
                            </div>
                            <div class="form-group">
                                <label><strong>Jenis Kelamin</strong></label>
                                <span>: -</span>
                            </div>
                            <?php } ?>
                        </td>

                        <!-- Kolom kedua untuk Kelas, Status, dan Jenjang -->
                        <td style="width: 20%;">
                            <?php if (!empty($namaSiswa) && isset($row) && $row) { ?>
                            <div class="form-group" style="margin-top: 5px;">
                                <label><strong>Kelas</strong></label>
                                <span>: <?= htmlspecialchars($row['kelas']); ?></span>
                            </div>
                            <div class="form-group" style="margin-top: -15px;">
                                <label><strong>Status</strong></label>
                                <span>: <?= htmlspecialchars($row['keaktifan']); ?></span>
                            </div>
                            <div class="form-group" style="margin-top: -15px;">
                                <label><strong>Jenjang</strong></label>
                                <span>: <?= htmlspecialchars($row['jenjang']); ?></span>
                            </div>
                            <?php } else { ?>
                            <div class="form-group">
                                <label><strong>Kelas</strong></label>
                                <span>: -</span>
                            </div>
                            <div class="form-group">
                                <label><strong>Status</strong></label>
                                <span>: -</span>
                            </div>
                            <div class="form-group">
                                <label><strong>Jenjang</strong></label>
                                <span>: -</span>
                            </div>
                            <?php } ?>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php

// Tentukan waktu batas (misalnya 2 menit yang lalu)
$waktuSekarang = date('Y-m-d H:i:s'); // Waktu saat ini
$waktuBatas = date('Y-m-d H:i:s', strtotime('-2 minutes')); // Waktu 2 menit yang lalu

// Cek apakah nama siswa sudah dipilih
if (!empty($namaSiswa)) {
    // Query untuk mengambil pembayaran dalam 2 menit terakhir
    $sqlNewPayment = "SELECT total.*, siswa.nama_siswa 
        FROM total
        JOIN siswa ON total.nis_siswa = siswa.nis_siswa
        WHERE siswa.nama_siswa = '$namaSiswa' 
        AND total.tanggal_bayar >= '$waktuBatas' 
        ORDER BY total.tanggal_bayar ASC";  // Urutkan berdasarkan waktu (ASC untuk urutan dari yang lebih awal)
    $resultNewPayment = $mysqli->query($sqlNewPayment);


    




   // Query untuk memeriksa apakah siswa baru pertama kali melakukan pembayaran
   $sqlFirstPaymentCheck = "SELECT COUNT(*) as payment_count 
   FROM total 
   JOIN siswa ON total.nis_siswa = siswa.nis_siswa
   WHERE siswa.nama_siswa = '$namaSiswa'";
$resultFirstPaymentCheck = $mysqli->query($sqlFirstPaymentCheck);
$firstPaymentData = $resultFirstPaymentCheck->fetch_assoc();
$isFirstPayment = ($firstPaymentData['payment_count'] == 0); // Cek apakah ini adalah pembayaran pertama
?>




        <!-- Menampilkan informasi siswa -->
        <?php 
// Jika ada pembayaran, tampilkan tabel pembayaran
if ($resultNewPayment && $resultNewPayment->num_rows > 0) { ?>
        <table>
            <thead style="font-size: 12px; border: 3px solid #e0e0e0;">
                <tr>
                    <th style="border: 3px solid #e0e0e0;">No</th>
                    <th style="border: 3px solid #e0e0e0;">Jenis Pembayaran</th>
                    <th style="border: 3px solid #e0e0e0;">Tahun Ajaran</th>
                    <th style="border: 3px solid #e0e0e0;">Total Pertahun</th>
                    <th style="border: 3px solid #e0e0e0;">Jumlah Bayar</th>
                    <th style="border: 3px solid #e0e0e0;">Diskon</th>
                    <th style="border: 3px solid #e0e0e0;">Tanggal Pembayaran</th>
                    <th style="border: 3px solid #e0e0e0;">Sisa Pembayaran</th>
                </tr>
            </thead>
            <tbody>
                <?php
            $totalJumlahBayar = 0;
            $totalDiskon = 0;
            $totalSisaPembayaran = 0;
            $totalBiayaPertahun = 0;
            $i = 1;

            // Variabel untuk menyimpan nilai jenis pembayaran sebelumnya
            $lastJenisPembayaran = '';
            $lastSisaPembayaran = 0; // Variabel untuk menyimpan sisa pembayaran terakhir berdasarkan jenis pembayaran

            // Array untuk menyimpan semua data pembayaran
            $pembayaranData = [];

             // Fungsi untuk format jenis pembayaran
            function formatJenisPembayaran($jenisPembayaran) {
                $jenisArr = explode(' ', $jenisPembayaran);
                if (count($jenisArr) > 1) {
                    // Ambil dua huruf pertama dari setiap kata
                    return implode('', array_map(function($word) {
                        return strtoupper(substr($word, 0, 1));
                    }, $jenisArr));
                }
                // Jika hanya satu kata, kembalikan langsung
                return strtoupper($jenisPembayaran);
            }


// Jika ini adalah pembayaran pertama, tampilkan pembayaran pertama
            if ($isFirstPayment && $resultNewPayment && $resultNewPayment->num_rows > 0) {
                // Memastikan pembayaran pertama ditambahkan terlebih dahulu
                while ($pembayaran = $resultNewPayment->fetch_assoc()) {
                    $biaya_pertahun = $pembayaran['total_pertahun'];
                    $jumlahBayar = $pembayaran['jumlah_bayar'] ?? 0;
                    $diskon = $pembayaran['diskon'] ?? 0;
                    $tanggalBayar = $pembayaran['tanggal_bayar'] ?? '-';
                    $sisaPembayaran = $pembayaran['sisa_pembayaran'] ?? ($biaya_pertahun - $jumlahBayar - $diskon); // Ambil sisa pembayaran jika ada, jika tidak hitung manual

                    $totalSisaPembayaran += $sisaPembayaran;

                    // Menambahkan pembayaran pertama
                    $pembayaranData[] = [
                        'no' => $i++,
                        'jenis_pembayaran' => 'Pembayaran Pertama',
                        'tahun_ajaran' => htmlspecialchars($pembayaran['tahun_ajaran']),
                        'total_pertahun' => number_format($biaya_pertahun, 0, ',', '.'), // Tampilkan total_pertahun
                        'jumlah_bayar' => number_format($jumlahBayar, 0, ',', '.'),
                        'diskon' => number_format($diskon, 0, ',', '.'),
                        'tanggal_bayar' => htmlspecialchars($tanggalBayar),
                        'sisa_pembayaran' => number_format($sisaPembayaran, 0, ',', '.'),
                        'is_same_jenis' => false // Pembayaran pertama tanpa tergantung pada pembayaran sebelumnya
                    ];
                }
            }




                
            // Menampilkan pembayaran yang terjadi dalam 2 menit terakhir (baris kedua)
            while ($pembayaran = $resultNewPayment->fetch_assoc()) {
                $biaya_pertahun = $pembayaran['total_pertahun'];
                $jumlahBayar = $pembayaran['jumlah_bayar'] ?? 0;
                $diskon = $pembayaran['diskon'] ?? 0;
                $tanggalBayar = $pembayaran['tanggal_bayar'] ?? '-';
                $sisaPembayaran = $pembayaran['sisa_pembayaran'] ?? ($biaya_pertahun - $jumlahBayar - $diskon); // Ambil sisa pembayaran jika ada, jika tidak hitung manual

                // Update sisa pembayaran terakhir
                $lastSisaPembayaran = $sisaPembayaran;


               // Menjumlahkan total pembayaran, diskon, dan sisa pembayaran
               $totalJumlahBayar += $jumlahBayar;
               $totalDiskon += $diskon;



                //// Simpan data pembayaran dalam array
                $pembayaranData[] = [
                    'no' => $i++,
                    'jenis_pembayaran' => htmlspecialchars($pembayaran['jenis_pembayaran']),
                    'tahun_ajaran' => htmlspecialchars($pembayaran['tahun_ajaran']),
                    'total_pertahun' => number_format($biaya_pertahun, 0, ',', '.'), // Tampilkan total_pertahun
                    'jumlah_bayar' => number_format($jumlahBayar, 0, ',', '.'),
                    'diskon' => number_format($diskon, 0, ',', '.'),
                    'tanggal_bayar' => htmlspecialchars($tanggalBayar),
                    'sisa_pembayaran' => number_format($sisaPembayaran, 0, ',', '.'),
                    'is_same_jenis' => ($lastJenisPembayaran == $pembayaran['jenis_pembayaran']) // Menyimpan status apakah jenis pembayaran sama
                ];

                // Update nilai jenis pembayaran terakhir
                $lastJenisPembayaran = $pembayaran['jenis_pembayaran'];
            }

            // Tampilkan data pembayaran yang sudah dihitung sisa pembayarannya
            foreach ($pembayaranData as $data) { ?>
                <tr>
                    <td class="small-font"><?= $data['no']; ?></td>
                    <td class="small-font">
                        <?= ($data['is_same_jenis']) ? $data['jenis_pembayaran'] : $data['jenis_pembayaran']; ?></td>
                    <td class="small-font"><?= $data['tahun_ajaran']; ?></td>
                    <td class="small-font"><?= ($data['is_same_jenis']) ? '' : $data['total_pertahun']; ?></td>
                    <td class="small-font"><?= $data['jumlah_bayar']; ?></td>
                    <td class="small-font"><?= $data['diskon']; ?></td>
                    <td class="small-font text-nowrap"><?= $data['tanggal_bayar']; ?></td>
                    <td class="small-font"><?= $data['sisa_pembayaran']; ?></td>
                </tr>
                <?php 
            }
            ?>
            </tbody>
            <tfoot>
                <?php 
    if ($resultPembayaran && $resultPembayaran->num_rows > 0) {
        // Inisialisasi variabel total
        $totalSisaPembayaranFooter = 0; // Variabel untuk total sisa pembayaran di footer
        $totalBiayaPertahunFooter = 0; // Variabel untuk total biaya pertahun
        $totalJumlahBayarFooter = 0; // Variabel untuk total jumlah bayar
        $totalDiskonFooter = 0; // Variabel untuk total diskon
        
        // Array untuk menyimpan sisa pembayaran terakhir per jenis pembayaran
        $lastSisaPembayaranPerJenis = [];

        // Iterasi untuk menghitung total biaya pertahun, jumlah bayar, diskon, dan sisa pembayaran
        foreach ($pembayaranData as $data) {
            // Ambil data yang diperlukan
            $biayaPertahun = (int)str_replace('.', '', $data['total_pertahun']); // Total biaya pertahun
            $jumlahBayar = (int)str_replace('.', '', $data['jumlah_bayar']); // Jumlah bayar
            $diskon = (int)str_replace('.', '', $data['diskon']); // Diskon
            $jenisPembayaran = $data['jenis_pembayaran']; // Jenis pembayaran
            
            // Menjumlahkan nilai total
            $totalBiayaPertahunFooter += $biayaPertahun;
            $totalJumlahBayarFooter += $jumlahBayar;
            $totalDiskonFooter += $diskon;
            
            // Menyimpan sisa pembayaran terakhir untuk setiap jenis pembayaran
            if (!isset($lastSisaPembayaranPerJenis[$jenisPembayaran])) {
                // Menghitung sisa pembayaran untuk jenis pembayaran pertama
                $sisaPembayaran = $biayaPertahun - $jumlahBayar - $diskon;
                $lastSisaPembayaranPerJenis[$jenisPembayaran] = $sisaPembayaran;
            }
        }

        // Menjumlahkan sisa pembayaran terakhir dari setiap jenis pembayaran
        foreach ($lastSisaPembayaranPerJenis as $sisa) {
            $totalSisaPembayaranFooter += $sisa;
        }
    ?>
                <tr>
                    <!-- Menambahkan kotak border pada tulisan 'Total' -->
                    <td colspan="3" style="border: 3px solid #e0e0e0;"><strong>Total</strong></td>

                    <!-- Total Biaya Pertahun: Menampilkan total biaya pertahun yang dijumlahkan -->
                    <td style="border: 3px solid #e0e0e0;">
                        <?php 
        // Array untuk menyimpan total pertahun yang unik
        $uniqueTotalPertahun = [];

        // Mengambil total pertahun dari setiap pembayaran dan menyimpannya hanya yang unik
        foreach ($pembayaranData as $data) {
            // Menghapus pemisah ribuan sebelum melakukan perhitungan
            $biayaPertahun = (int)str_replace('.', '', $data['total_pertahun']);

            if (!in_array($biayaPertahun, $uniqueTotalPertahun)) {
                $uniqueTotalPertahun[] = $biayaPertahun;
            }
        }

        // Menjumlahkan total pertahun yang unik
        $totalBiayaPertahunFooter = array_sum($uniqueTotalPertahun);

        // Menampilkan hasil dengan pemisah ribuan
        echo number_format($totalBiayaPertahunFooter, 0, ',', '.');
        ?>
                    </td>

                    <!-- Total Jumlah Bayar: Menjumlahkan semua jumlah bayar -->
                    <td style="border: 3px solid #e0e0e0;">
                        <?= number_format($totalJumlahBayarFooter, 0, ',', '.'); ?>
                    </td>

                    <!-- Total Diskon: Menjumlahkan semua diskon -->
                    <td style="border: 3px solid #e0e0e0;">
                        <?= number_format($totalDiskonFooter, 0, ',', '.'); ?>
                    </td>

                    <!-- Menambahkan kolom kosong dengan kotak border -->
                    <td style="border: 3px solid #e0e0e0;"></td>

                    <!-- Total Sisa Pembayaran: Mengambil dari tabel sisa pembayaran dengan kondisi jenis pembayaran berbeda -->
                    <td style="border: 3px solid #e0e0e0;">
                        <?php
        // Array untuk menyimpan sisa pembayaran terakhir dari setiap jenis pembayaran
        $todaySisaPembayaranPerJenis = [];
        $yesterdaySisaPembayaranPerJenis = [];
        $yesterdayJenisPembayaran = null;

        // Mendapatkan tanggal hari ini dan kemarin dalam format Y-m-d
        $todayDate = date('Y-m-d');
        $yesterdayDate = date('Y-m-d', strtotime('-1 day')); // Kemarin

        // Variabel untuk memantau jenis pembayaran terakhir dan tanggal pembayaran
        foreach ($pembayaranData as $data) {
            $jenisPembayaran = $data['jenis_pembayaran'];
            $sisaPembayaran = (int)str_replace('.', '', $data['sisa_pembayaran']);  // Menghapus pemisah ribuan untuk perhitungan
            $tanggalBayar = date('Y-m-d', strtotime($data['tanggal_bayar'])); // Mendapatkan tanggal pembayaran dalam format Y-m-d

            // Cek apakah tanggal pembayaran sama dengan hari ini
            if ($tanggalBayar === $todayDate) {
                // Simpan pembayaran terakhir hari ini per jenis pembayaran
                $todaySisaPembayaranPerJenis[$jenisPembayaran] = $sisaPembayaran;
            }

            // Cek apakah tanggal pembayaran kemarin dan jenis pembayaran belum ada untuk hari ini
            if ($tanggalBayar === $yesterdayDate && !isset($todaySisaPembayaranPerJenis[$jenisPembayaran])) {
                // Simpan pembayaran kemarin per jenis pembayaran
                $yesterdaySisaPembayaranPerJenis[$jenisPembayaran] = $sisaPembayaran;
            }
        }

        // Menjumlahkan sisa pembayaran dari hari ini
        $totalSisaPembayaranFooter = array_sum($todaySisaPembayaranPerJenis);

        // Menambahkan sisa pembayaran kemarin jika jenis pembayaran berbeda
        foreach ($yesterdaySisaPembayaranPerJenis as $jenis => $sisa) {
            // Jika jenis pembayaran kemarin berbeda dari hari ini, tambahkan sisa pembayaran kemarin
            if (!isset($todaySisaPembayaranPerJenis[$jenis])) {
                $totalSisaPembayaranFooter += $sisa;
            }
        }

        // Menampilkan hasil dengan pemisah ribuan
        echo number_format($totalSisaPembayaranFooter, 0, ',', '.');
        ?>
                    </td>
                </tr>







                <?php } ?>
            </tfoot>

        </table>
        <?php 
} else {
    echo '<table class="payment-table"><thead><tr><th>No</th><th>Jenis Pembayaran</th><th>Tahun Ajaran</th><th>Total Pertahun</th><th>Jumlah Bayar</th><th>Diskon</th><th>Tanggal Pembayaran</th><th>Sisa Pembayaran</th></tr></thead><tbody></tbody></table>';
}
} else { ?>
        <p>Pilih siswa terlebih dahulu untuk melihat data pembayaran.</p>
        <?php } ?>





        <div style="width: 95%; text-align: right; font-size: 12px;">
            <strong>Tanggal Pembayaran:</strong> <span><?= date('d-m-Y'); ?></span>
        </div>

        <div style="margin-top: 4px; border-top: 3px dashed #ccc; padding-top: -10px;">

            <div class="footer">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <!-- Kiri: Nama Teller -->

                    <div style="width: 85%; ">
                        <strong>Teller:</strong>
                    </div>

                    <!-- Tengah: Penerima -->
                    <div style="width: 700px; ">
                        <strong>(Penerima)</strong>
                    </div>

                    <!-- Kanan: Tanggal -->

                </div>

                <!-- Tanda Tangan -->
                <div style="margin-top: 25px; display: flex; justify-content: space-between;">
                    <!-- Tanda Tangan Teller & Nama Teller sejajar -->
                    <div style="width: 100%; text-align: center; margin-top: 30px;">
                        <p style="font-style: italic; font-size: 8px;">
                            <span><?= htmlspecialchars($row['teller'] ?? ''); ?></span>
                        </p>

                    </div>

                    <!-- Tanda Tangan Penerima (Kosong) -->
                    <div style="width: 1%;"></div>

                    <!-- Tanda Tangan Penerima -->
                    <div style="width: 100%; text-align: center; margin-top: 40px;">
                        <p style="font-style: italic; font-size: 8px;">(Tanda Tangan Penerima)</p>
                    </div>
                </div>

                <script>
                // Inisialisasi Select2 pada elemen select dengan ID "nama_siswa"
                $(document).ready(function() {
                    $('#nama_siswa').select2({
                        placeholder: "Pilih Nama Siswa",
                        allowClear: true
                    });
                });
                </script>
                <!-- Link JS untuk Select2 -->
                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>


</body>

</html>