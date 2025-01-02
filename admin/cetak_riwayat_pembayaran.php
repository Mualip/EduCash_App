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

// Tutup statement untuk kategori pembayaran setelah mengambil hasil
//$kategoriStmt->free_result();
//$kategoriStmt->close();




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
    
    // Query pembayaran berdasarkan nis_siswa yang dipilih
    // Query pembayaran berdasarkan nis_siswa yang dipilih
    $queryPembayaran = "
        SELECT 
            kategori_pembayaran.jenis_pembayaran,
            kategori_pembayaran.tahun_ajaran,
            biaya_pertahun.total_pertahun,
            total.jumlah_bayar,
            total.diskon,
            total.tanggal_bayar
        FROM kategori_pembayaran
        LEFT JOIN biaya_pertahun ON kategori_pembayaran.jenis_pembayaran = biaya_pertahun.jenis_biaya AND kategori_pembayaran.tahun_ajaran = biaya_pertahun.tahun_ajaran
        LEFT JOIN total ON kategori_pembayaran.jenis_pembayaran = total.jenis_pembayaran
        WHERE total.nis_siswa = ? AND total.id_admin = ?
        ORDER BY kategori_pembayaran.jenis_pembayaran ASC
    ";
    $stmtPembayaran = $mysqli->prepare($queryPembayaran);
    $stmtPembayaran->bind_param("ss", $nisSiswa, $idAdmin);
    $stmtPembayaran->execute();
    $resultPembayaran = $stmtPembayaran->get_result();
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
        width: 700px;
        margin: auto;
        background-color: #fff;
        padding: 2px;
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
    border-collapse: collapse; /* Menggabungkan border agar tidak ada celah antar sel */
    width: 100%; /* Memastikan tabel memenuhi lebar kontainer */
}
    /* Tabel Info Siswa tanpa border */
    .info-table {
        width: 100%;
        margin-bottom: 10px;
        border-collapse: collapse;
    }

    .info-table td {
        padding: 5px;
        border: none;
        /* Menghapus border pada tabel info */
    }

    .info-table td {
        text-align: left;
    }

    /* Tabel Pembayaran dengan border */
    .payment-table {
        width: 100%;
        margin-bottom: 5px;
        border-collapse: collapse;
    }
    th, td {
        border: 3px solid #e0e0e0; /* Border hitam dan tipis di sekitar setiap sel */
    padding: 5px; /* Ruang dalam sel agar konten tidak terlalu rapat dengan border */
    text-align: center; /* Menyusun teks ke tengah */
    font-size: 12px; /* Ukuran font untuk teks di dalam sel */
}
    .payment-table th, .payment-table td {
        border: 3px solid #e0e0e0;
    padding: 2px;
    text-align: center;
}

    .payment-table th {
    background-color: #f2f2f2; /* Warna latar belakang header tabel */
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

    /* CSS untuk mencetak */
    @media print {

        /* Mengatur margin saat mencetak */
        @page {
            margin: 5mm;
            /* Memberikan margin 20mm di sekitar halaman */
        }

        /* Sembunyikan elemen yang tidak diinginkan */
        body * {
            visibility: hidden;
        }

        .container,
        .container * {
            visibility: visible;
        }

        .container {
            position: absolute;
            top: 0;
            left: 0;
            width: 1200px;
        }

        /* Hapus header browser dan footer browser */
        header,
        footer {
            display: none;
        }
           /* Pastikan elemen .small-font tetap visible dan border terlihat */
    .small-font {
    border: 3px solid #e0e0e0;
    padding: 2px 5px 4px 10px; /* Padding atas, kanan, bawah, kiri */
    text-align: center; /* Menyusun teks di tengah */
    font-size: 12px;
    visibility: visible; /* Pastikan elemen tetap terlihat saat dicetak */
    box-sizing: border-box; /* Memastikan padding dan border dihitung dalam ukuran elemen */
    align-items: center;
    justify-content: center;
}
    }

    /* Media Queries untuk Responsivitas */
    @media (max-width: 768px) {
        .container {
            width: 100%;
            padding: 5px;
        }

        .header h1 {
            font-size: 12px;
        }

        .header p {
            font-size: 12px;
        }

        .info-table,
        .payment-table {
            font-size: 12px;
        }

        .form-group label {
            width: 80px;
        }

        .form-group input,
        .form-group select {
            width: 80%;
        }

        .footer p {
            font-size: 12px;
        }
    }

    .custom-select {
        width: 100%;
        max-width: 900px;
        /* Tentukan lebar maksimal */
        padding: 5px;
        /* Memberikan padding untuk kenyamanan pengguna */
    }
    </style>
</head>

<body>
<?php
include "sidebar.php";
?>
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
        <label for="nama_siswa" style="display: inline-block; margin-right: 1px;"><strong>Nama Siswa</strong></label> 
        <span>:</span>
        <select name="nama_siswa" id="nama_siswa" onchange="this.form.submit()" class="custom-select"
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
        <table class="payment-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Jenis Pembayaran</th>
                    <th>Tahun Ajaran</th>
                    <th>Total Pertahun</th>
                    <th>Jumlah Bayar</th>
                    <th>Diskon</th>
                    <th>Tanggal Pembayaran</th>
                    <th>Sisa Pembayaran</th>
                </tr>
            </thead>
            <tbody>
                <?php
        // Cek apakah ada data pembayaran
        if ($resultPembayaran && $resultPembayaran->num_rows > 0) {
            $totalJumlahBayar = 0;
            $totalDiskon = 0;
            $totalSisaPembayaran = 0;
            $totalBiayaPertahun = 0;
            $i = 1;

            // Variabel untuk menyimpan nilai jenis pembayaran sebelumnya
            $lastJenisPembayaran = '';
            $lastSisaPembayaran = 0; // Variabel untuk menyimpan sisa pembayaran terakhir berdasarkan jenis pembayaran
            $sisaPembayaranSebelumnya = 0; // Variabel untuk menyimpan sisa pembayaran sebelumnya berdasarkan jenis pembayaran

            // Array untuk menyimpan semua data pembayaran
            $pembayaranData = [];

            // Menampilkan baris pembayaran berdasarkan kategori_pembayaran yang sudah ada
            while ($pembayaran = $resultPembayaran->fetch_assoc()) {
                $biaya_pertahun = $pembayaran['total_pertahun'];
                $jumlahBayar = $pembayaran['jumlah_bayar'] ?? 0;
                $diskon = $pembayaran['diskon'] ?? 0;
                $tanggalBayar = $pembayaran['tanggal_bayar'] ?? '-';

                // Menghitung sisa pembayaran berdasarkan jenis pembayaran
                if ($lastJenisPembayaran != $pembayaran['jenis_pembayaran']) {
                    // Jika jenis pembayaran berbeda, hitung ulang sisa pembayaran dari awal
                    $sisaPembayaran = $biaya_pertahun - $jumlahBayar - $diskon;
                } else {
                    // Jika jenis pembayaran sama dengan sebelumnya, hitung sisa berdasarkan pembayaran sebelumnya
                    $sisaPembayaran = $lastSisaPembayaran - $jumlahBayar - $diskon;
                }

                // Update sisa pembayaran terakhir
                $lastSisaPembayaran = $sisaPembayaran;

                // Menjumlahkan total pembayaran, diskon, dan sisa pembayaran
                $totalJumlahBayar += $jumlahBayar;
                $totalDiskon += $diskon;

                // Tambahkan biaya pertahun hanya untuk jenis pembayaran pertama yang berbeda
                if ($lastJenisPembayaran != $pembayaran['jenis_pembayaran']) {
                    $totalBiayaPertahun += $biaya_pertahun;
                }

                // Simpan data pembayaran dalam array
                $pembayaranData[] = [
                    'no' => $i++,
                    'jenis_pembayaran' => htmlspecialchars($pembayaran['jenis_pembayaran']),
                    'tahun_ajaran' => htmlspecialchars($pembayaran['tahun_ajaran']),
                    'total_pertahun' => number_format($biaya_pertahun, 0, ',', '.'),
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
            foreach ($pembayaranData as $data) {
        ?>
                <tr>
                    <td><?= $data['no']; ?></td>
                    <!-- Tampilkan Jenis Pembayaran hanya jika jenis pembayaran berbeda dari sebelumnya -->
                    <td><?= ($data['is_same_jenis']) ? '' : $data['jenis_pembayaran']; ?></td>
                    <td><?= $data['tahun_ajaran']; ?></td>
                    <!-- Tampilkan Total Pertahun hanya jika jenis pembayaran berbeda dari sebelumnya -->
                    <td><?= ($data['is_same_jenis']) ? '' : $data['total_pertahun']; ?></td>
                    <td><?= $data['jumlah_bayar']; ?></td>
                    <td><?= $data['diskon']; ?></td>
                    <td><?= $data['tanggal_bayar']; ?></td>
                    <td><?= $data['sisa_pembayaran']; ?></td>
                </tr>
                <?php 
            }
        } else {
            // Jika tidak ada data pembayaran, tampilkan baris dengan data default
        ?>
                <tr>
                    <td colspan="3">Total</td>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                    <td></td>
                    <td>0</td>
                </tr>
                <?php
        }
        ?>
            </tbody>
            <tfoot>
                <?php 
    if ($resultPembayaran && $resultPembayaran->num_rows > 0) {
        $totalSisaPembayaranFooter = 0; // Variabel untuk total sisa pembayaran di footer
        $lastSisaPembayaranPerJenis = []; // Array untuk menyimpan sisa pembayaran terakhir per jenis pembayaran

        // Iterasi untuk menghitung sisa pembayaran terakhir per jenis pembayaran
        foreach ($pembayaranData as $data) {
            $jenisPembayaran = $data['jenis_pembayaran'];
            $sisaPembayaran = (int)str_replace('.', '', $data['sisa_pembayaran']);
            
            // Simpan sisa pembayaran terakhir per jenis pembayaran
            $lastSisaPembayaranPerJenis[$jenisPembayaran] = $sisaPembayaran;
        }

        // Jumlahkan sisa pembayaran terakhir untuk setiap jenis pembayaran
        foreach ($lastSisaPembayaranPerJenis as $sisa) {
            $totalSisaPembayaranFooter += $sisa;
        }
    ?>
                <tr>
                    <td colspan="3"><strong>Total</strong></td>
                    <!-- Total Biaya Pertahun: Menampilkan total biaya pertahun berdasarkan jenis pembayaran -->
                    <td><?= number_format($totalBiayaPertahun, 0, ',', '.'); ?></td>
                    <!-- Total Jumlah Bayar: Jumlahkan jumlah bayar dari semua jenis pembayaran -->
                    <td><?= number_format($totalJumlahBayar, 0, ',', '.'); ?></td>
                    <!-- Total Diskon: Jumlahkan diskon dari semua jenis pembayaran -->
                    <td><?= number_format($totalDiskon, 0, ',', '.'); ?></td>
                    <td></td>
                    <!-- Total Sisa Pembayaran: Menjumlahkan sisa pembayaran terakhir dari setiap jenis pembayaran -->
                    <td><?= number_format($totalSisaPembayaranFooter, 0, ',', '.'); ?></td>
                </tr>
                <?php } ?>
            </tfoot>

        </table>
        <div style="width: 97%; text-align: right;">
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
                    <div style="width: 98%; text-align: center; margin-top: 30px;">
                        <p style="font-style: italic; font-size: 12px;">
                            <span><?= htmlspecialchars($row['teller'] ?? ''); ?></span>
                        </p>

                    </div>

                    <!-- Tanda Tangan Penerima (Kosong) -->
                    <div style="width: 1%;"></div>

                    <!-- Tanda Tangan Penerima -->
                    <div style="width: 100%; text-align: center; margin-top: 40px;">
                        <p style="font-style: italic; font-size: 14px;">(Tanda Tangan Penerima)</p>
                    </div>
                </div>

                <script>
    $(document).ready(function() {
        // Inisialisasi Select2 pada elemen select
        $('#nama_siswa').select2({
            placeholder: "Pilih Nama Siswa", // Placeholder text
            allowClear: true, // Menambahkan tombol untuk menghapus pilihan
            width: 'resolve' // Menyesuaikan lebar elemen select
        });
    });
</script>
                <!-- Skrip JavaScript untuk Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
           

</body>

</html>