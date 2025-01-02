<?php
include 'koneksi.php';

// Menambahkan pengecekan id_admin pada sesi
session_start();
if (!isset($_SESSION['id_admin'])) {
    header('Location: login.php'); // Redirect jika admin tidak login
    exit;
}

$id_admin = $_SESSION['id_admin']; // Ambil id_admin dari session

if (isset($_POST['simpan'])) {
    // Ambil data dari form
    $nis_siswa = mysqli_real_escape_string($mysqli, $_POST['nis_siswa']);
    $jenis_pembayaran = mysqli_real_escape_string($mysqli, $_POST['jenis_pembayaran']);
    $tahun_ajaran = mysqli_real_escape_string($mysqli, $_POST['tahun_ajaran']);
    $jenjang = mysqli_real_escape_string($mysqli, $_POST['jenjang']);
    $tanggal_pembayaran = mysqli_real_escape_string($mysqli, $_POST['tanggal_pembayaran']); // Format: YYYY-MM-DDTHH:MM

    // Mengubah format tanggal menjadi format MySQL DATETIME (YYYY-MM-DD HH:MM:SS)
    $tanggal_pembayaran = str_replace("T", " ", $tanggal_pembayaran); // Mengganti T dengan spasi
    $tanggal_pembayaran = $tanggal_pembayaran . ":00"; // Menambahkan detik menjadi :00
    $tanggal_pembayaran = date("Y-m-d H:i:s", strtotime($tanggal_pembayaran)); // Format MySQL DATETIME

    // Mengambil jumlah bayar, tarif, diskon, dan lainnya
    $jumlah_bayar = floatval(str_replace(['Rp', '.', ' '], '', $_POST['jumlah_bayar']));
    $tarif = floatval(str_replace(['Rp', '.', ' '], '', $_POST['tarif']));
    $diskon = floatval(str_replace(['Rp', '.', ' '], '', $_POST['diskon']));
    $bulan = mysqli_real_escape_string($mysqli, $_POST['bulan']);
    $metode_pembayaran = mysqli_real_escape_string($mysqli, $_POST['metode_pembayaran']);
    $teller = mysqli_real_escape_string($mysqli, $_POST['teller']);

    // Ambil total biaya pertahun dari tabel biaya_pertahun
    // Query untuk mengambil biaya pertahun
    $query_total_pertahun = "
    SELECT total_pertahun 
    FROM biaya_pertahun
    WHERE tahun_ajaran = '$tahun_ajaran'
    AND jenjang = '$jenjang'
    AND jenis_biaya = '$jenis_pembayaran' LIMIT 1";
    $result_total_pertahun = mysqli_query($mysqli, $query_total_pertahun);

    if ($row_total = mysqli_fetch_assoc($result_total_pertahun)) {
        $total_pertahun = floatval(str_replace(['Rp', '.', ' '], '', $row_total['total_pertahun']));

        // Periksa apakah ada pembayaran sebelumnya
        $query_sisa = "SELECT sisa_pembayaran FROM total
                        WHERE nis_siswa = '$nis_siswa'
                        AND tahun_ajaran = '$tahun_ajaran'
                        AND jenis_pembayaran = '$jenis_pembayaran'
                        AND jenjang = '$jenjang'
                        ORDER BY id DESC LIMIT 1";
        $result_sisa = mysqli_query($mysqli, $query_sisa);

        if ($row_sisa = mysqli_fetch_assoc($result_sisa)) {
            $sisa_pembayaran = floatval($row_sisa['sisa_pembayaran']);
        } else {
            $sisa_pembayaran = $total_pertahun; // Jika tidak ada pembayaran sebelumnya
        }

        // Hitung sisa pembayaran setelah pembayaran saat ini (memasukkan diskon)
        $sisa_pembayaran = $sisa_pembayaran - $jumlah_bayar - $diskon;

        // Validasi apakah pembayaran melebihi total biaya
        if ($sisa_pembayaran < 0) {
            echo "<script>alert('Jumlah pembayaran melebihi total biaya! Pembayaran tidak dapat diproses.'); window.location.href='input_pembayaran.php';</script>";
            exit;
        }

        // Tentukan status pembayaran
        $status_pembayaran = ($sisa_pembayaran == 0) ? 'Lunas' : 'Menunggak';

        // Persiapkan statement untuk insert data
        $stmt = $mysqli->prepare("INSERT INTO total
    (nis_siswa, tahun_ajaran, jenis_pembayaran, jenjang, total_pertahun, jumlah_bayar, diskon, tanggal_bayar, metode_pembayaran, sisa_pembayaran, tarif, bulan, teller, status_pembayaran, id_admin)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if ($stmt) {
            // Pastikan urutan bind_param sesuai dengan urutan kolom
            $stmt->bind_param(
                "ssssddsssssssss",  // Tipe data untuk setiap parameter
                $nis_siswa,
                $tahun_ajaran,
                $jenis_pembayaran,
                $jenjang,
                $total_pertahun,
                $jumlah_bayar,
                $diskon,
                $tanggal_pembayaran,  // Gunakan format YYYY-MM-DD HH:MM:SS
                $metode_pembayaran,
                $sisa_pembayaran,
                $tarif,
                $bulan,
                $teller,
                $status_pembayaran,
                $id_admin // Tambahkan id_admin ke dalam query
            );

            if ($stmt->execute()) {
                if ($sisa_pembayaran == 0) {
                    echo "<script>alert('Pembayaran lunas!'); window.location.href='cetak_kuitansi.php';</script>";
                } else {
                    echo "<script>alert('Pembayaran berhasil disimpan! Sisa pembayaran: Rp " . number_format($sisa_pembayaran, 0, ',', '.') . "'); window.location.href='cetak_kuitansi.php';</script>";
                }
                exit;
            } else {
                echo "Error inserting data: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $mysqli->error;
        }
    } else {
        echo "<script>alert('Data biaya pertahun tidak ditemukan!');</script>";
    }
}
?>









<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f8f9fa;
    }

    .container {
        background-color: white;
        padding: 30px;
        border-radius: 2px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    h2 {
        text-align: center;
    }
    </style>
</head>

<body id="page-top">
    <?php include "sidebar.php"; ?>
    <?php include "navbar.php"; ?>
    <div class="container">
        <h2>Input Pembayaran Siswa</h2>
        <form action="" method="post">
            <div class="mb-3">
                <label for="nis_siswa" class="form-label">NIS Siswa:</label>
                <select name="nis_siswa" id="nis_siswa" class="form-control" required>
                    <option value="">Pilih NIS</option>
                    <?php
                // Query untuk mengambil siswa berdasarkan id_admin
                $query_siswa = "SELECT nis_siswa, nama_siswa FROM siswa WHERE id_admin = '$id_admin'";
                $result_siswa = mysqli_query($mysqli, $query_siswa);
                
                // Menampilkan siswa yang sesuai dengan id_admin yang login
                while ($row = mysqli_fetch_assoc($result_siswa)) {
                    echo "<option value='{$row['nis_siswa']}'>{$row['nis_siswa']} - {$row['nama_siswa']}</option>";
                }
                ?>
                </select>
            </div>


            <div class="mb-3">
                <label for="tahun_ajaran" class="form-label">Tahun Ajaran:</label>
                <select name="tahun_ajaran" id="tahun_ajaran" class="form-control" required>
                    <option value="">Pilih Tahun Ajaran</option>
                    <?php
        // Query untuk mengambil tahun ajaran berdasarkan id_admin
        $query_tahun = "SELECT DISTINCT tahun_ajaran FROM tarif_pembayaran";
        $result_tahun = mysqli_query($mysqli, $query_tahun);
        while ($row = mysqli_fetch_assoc($result_tahun)) {
            echo "<option value='{$row['tahun_ajaran']}'>{$row['tahun_ajaran']}</option>";
        }
        ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="jenis_pembayaran" class="form-label">Jenis Pembayaran:</label>
                <select name="jenis_pembayaran" id="jenis_pembayaran" class="form-control" required>
                    <option value="">Pilih Jenis Pembayaran</option>
                    <?php
        // Query untuk mengambil jenis pembayaran berdasarkan id_admin
        $query_jenis = "SELECT DISTINCT jenis_pembayaran FROM tarif_pembayaran ";
        $result_jenis = mysqli_query($mysqli, $query_jenis);
        while ($row = mysqli_fetch_assoc($result_jenis)) {
            echo "<option value='{$row['jenis_pembayaran']}'>{$row['jenis_pembayaran']}</option>";
        }
        ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="jenjang" class="form-label">Jenjang:</label>
                <select name="jenjang" id="jenjang" class="form-control" required>
                    <option value="">Pilih Jenjang</option>
                    <?php
        // Query untuk mengambil jenjang berdasarkan id_admin
        $query_jenjang = "SELECT DISTINCT jenjang FROM tarif_pembayaran";
        $result_jenjang = mysqli_query($mysqli, $query_jenjang);
        while ($row = mysqli_fetch_assoc($result_jenjang)) {
            echo "<option value='{$row['jenjang']}'>{$row['jenjang']}</option>";
        }
        ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="total_pertahun" class="form-label">Biaya Tahun:</label>
                <input type="text" name="total_pertahun" id="total_pertahun" class="form-control" readonly>
            </div>

            <div class="mb-3">
                <label for="tarif" class="form-label">Biaya Perbulan:</label>
                <input type="text" name="tarif" id="tarif" class="form-control" readonly>
            </div>

            <div class="mb-3">
                <label for="bulan" class="form-label">Pembayaran Bulan:</label>
                <select name="bulan" id="bulan" class="form-control" required>
                    <option value="">Pilih Bulan</option>
                    <option value="Juli">Juli</option>
                    <option value="Agustus">Agustus</option>
                    <option value="September">September</option>
                    <option value="Oktober">Oktober</option>
                    <option value="November">November</option>
                    <option value="Desember">Desember</option>
                    <option value="Januari">Januari</option>
                    <option value="Februari">Februari</option>
                    <option value="Maret">Maret</option>
                    <option value="April">April</option>
                    <option value="Mei">Mei</option>
                    <option value="Juni">Juni</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="jumlah_bayar" class="form-label">Jumlah Pembayaran:</label>
                <input type="number" name="jumlah_bayar" id="jumlah_bayar" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="diskon" class="form-label">Diskon</label>
                <input type="number" name="diskon" id="diskon" class="form-control" required>
            </div>
            <div class="mb-3">
    <label for="tanggal_pembayaran" class="form-label">Tanggal Pembayaran:</label>
    <input type="datetime-local" name="tanggal_pembayaran" id="tanggal_pembayaran" class="form-control" value="2024-12-19T14:30" required>
</div>

            <div class="mb-3">
                <label for="metode_pembayaran" class="form-label">Metode Pembayaran:</label>
                <select name="metode_pembayaran" id="metode_pembayaran" class="form-control" required>
                    <option value="">Pilih Metode Pembayaran</option>
                    <option value="Cash">Cash</option>
                    <option value="Transfer">Transfer</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="teller" class="form-label">Teller:</label>
                <input type="text" class="form-control" id="teller" name="teller" required>
            </div>
            <button type="submit" name="simpan" class="btn btn-primary">Simpan Pembayaran</button>
        </form>
    </div>

    <script>
    function formatRupiah(angka, prefix) {
        // Menghapus karakter selain angka dan koma
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','), // Memisahkan bagian sebelum dan sesudah koma
            sisa = split[0].length % 3, // Menghitung sisa bagi 3 untuk bagian ribuan pertama
            rupiah = split[0].substr(0, sisa), // Bagian awal angka sebelum ribuan
            ribuan = split[0].substr(sisa).match(/\d{3}/gi); // Menangkap ribuan yang tersisa

        // Jika ada angka ribuan, tambahkan titik sebagai pemisah
        if (ribuan) {
            separator = sisa ? '.' : ''; // Menambahkan titik jika ada sisa
            rupiah += separator + ribuan.join('.'); // Menggabungkan ribuan dengan titik
        }

        // Menambahkan bagian desimal jika ada (setelah koma)
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;

        // Mengembalikan hasil dengan prefix 'Rp.' jika diperlukan
        return prefix == undefined ? rupiah : 'Rp. ' + rupiah;
    }

    $(document).ready(function() {
        // Format input saat mengetik pada 'tarif' dan 'total_pertahun'
        $('#tarif, #total_pertahun').on('input', function() {
            var inputValue = $(this).val();
            // Menghapus simbol 'Rp.' agar hanya angka yang diambil
            var numericValue = inputValue.replace(/[^0-9]/g, '');
            $(this).val(formatRupiah(numericValue,
                'Rp.')); // Format setiap inputan dengan 'Rp.' dan titik
        });

        // Ketika dropdown 'jenjang', 'jenis_pembayaran', atau 'tahun_ajaran' berubah
        $('#jenjang, #jenis_pembayaran, #tahun_ajaran').change(function() {
            const jenjang = $('#jenjang').val();
            const jenisPembayaran = $('#jenis_pembayaran').val();
            const tahunAjaran = $('#tahun_ajaran').val();

            if (jenjang && jenisPembayaran && tahunAjaran) {
                // Ambil total pertahun dan tarif menggunakan AJAX
                $.post('get_tarif.php', {
                    jenjang,
                    jenis_pembayaran: jenisPembayaran,
                    tahun_ajaran: tahunAjaran
                }, function(response) {
                    if (response) {
                        // Format hasil dari response dan tampilkan di input
                        $('#total_pertahun').val(formatRupiah(response.total_pertahun,
                            'Rp.')); // Format total pertahun dengan 'Rp.'
                        $('#tarif').val(formatRupiah(response.tarif,
                            'Rp.')); // Format tarif dengan 'Rp.'
                    } else {
                        $('#total_pertahun').val('');
                        $('#tarif').val('');
                    }
                }, 'json').fail(function() {
                    alert('Gagal mengambil tarif.');
                });
            }
        });
    });
    </script>

<script>
   // Set nilai default waktu di input ke waktu sekarang di Jakarta
   window.onload = function () {
            // Set default time to local time (WIB - UTC+7)
            const now = new Date();
            const offset = 7 * 60; // WIB (UTC+7)
            const jakartaTime = new Date(now.getTime() + offset * 60000);

            // Format waktu sesuai dengan input datetime-local (YYYY-MM-DDTHH:mm)
            const formattedTime = jakartaTime.toISOString().slice(0, 16);

            // Set value di input
            document.getElementById('tanggal_pembayaran').value = formattedTime;
        };
    </script>

</body>

</html>