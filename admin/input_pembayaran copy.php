<?php
include 'koneksi.php';



if (isset($_POST['simpan'])) {
    // Ambil data dari form
    $nis_siswa = mysqli_real_escape_string($mysqli, $_POST['nis_siswa']);
    $jenis_pembayaran = mysqli_real_escape_string($mysqli, $_POST['jenis_pembayaran']);
    $tahun_ajaran = mysqli_real_escape_string($mysqli, $_POST['tahun_ajaran']);
    $jenjang = mysqli_real_escape_string($mysqli, $_POST['jenjang']);
    $tanggal_pembayaran = mysqli_real_escape_string($mysqli, $_POST['tanggal_pembayaran']);
    $jumlah_bayar = mysqli_real_escape_string($mysqli, $_POST['jumlah_bayar']);
    $teler = mysqli_real_escape_string($mysqli, $_POST['teler']);
    $tarif = mysqli_real_escape_string($mysqli, $_POST['tarif']);
    $bulan = mysqli_real_escape_string($mysqli, $_POST['bulan']);
    $metode_pembayaran = mysqli_real_escape_string($mysqli, $_POST['metode_pembayaran']); // Ambil nilai metode pembayaran

    // Ambil total pertahun dari tabel biaya_pertahun
    $query_total_pertahun = "SELECT total_pertahun FROM biaya_pertahun 
                                WHERE tahun_ajaran = '$tahun_ajaran' 
                                AND jenjang = '$jenjang' 
                                AND jenis_biaya = '$jenis_pembayaran' LIMIT 1";

    $result_total_pertahun = mysqli_query($mysqli, $query_total_pertahun);

    if ($row_total = mysqli_fetch_assoc($result_total_pertahun)) {
        $total_pertahun = $row_total['total_pertahun'];

        // Pastikan total_pertahun dan jumlah_bayar adalah angka
        $total_pertahun = floatval(str_replace(['Rp', '.', ' '], '', $total_pertahun)); // Menghapus simbol Rp dan titik
        $jumlah_bayar = floatval(str_replace(['Rp', '.', ' '], '', $jumlah_bayar)); // Menghapus simbol Rp dan titik

        // Hitung sisa pembayaran
        $sisa_pembayaran = $total_pertahun - $jumlah_bayar;

        // Validasi apakah NIS siswa ada
        $query_check_nis = "SELECT * FROM siswa WHERE nis_siswa = '$nis_siswa'";
        $result_check_nis = mysqli_query($mysqli, $query_check_nis);

        if (mysqli_num_rows($result_check_nis) == 0) {
            echo "<script>alert('NIS tidak ditemukan!');</script>";
        } else {
            // Proses penyimpanan ke tabel total
            $stmt = $mysqli->prepare("INSERT INTO total 
            (nis_siswa, tahun_ajaran, jenis_pembayaran, jenjang, total_pertahun, jumlah_bayar, tanggal_bayar, teler, sisa_pembayaran, tarif, bulan, metode_pembayaran) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            if ($stmt) {
                $stmt->bind_param(
                    "ssssdissidss", // Menambahkan 's' untuk metode_pembayaran
                    $nis_siswa,
                    $tahun_ajaran,
                    $jenis_pembayaran,
                    $jenjang,
                    $total_pertahun,
                    $jumlah_bayar,
                    $tanggal_pembayaran,
                    $teler,
                    $sisa_pembayaran,
                    $tarif,
                    $bulan,
                    $metode_pembayaran // Tambahkan metode_pembayaran di sini
                );

                if ($stmt->execute()) {
                    echo "<script>alert('Pembayaran berhasil disimpan!'); window.location.href='input_pembayaran.php';</script>";
                    exit;
                } else {
                    echo "Error inserting data: " . $stmt->error;
                }
                $stmt->close();
            } else {
                echo "Error preparing statement: " . $mysqli->error;
            }
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
                $query_siswa = "SELECT nis_siswa, nama_siswa FROM siswa";
                $result_siswa = mysqli_query($mysqli, $query_siswa);
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
                $query_tahun = "SELECT DISTINCT tahun_ajaran FROM tarif_pembayaran ORDER BY tahun_ajaran";
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
                $query_jenis = "SELECT DISTINCT jenis_pembayaran FROM tarif_pembayaran";
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
            <label for="tanggal_pembayaran" class="form-label">Tanggal Pembayaran:</label>
            <input type="date" name="tanggal_pembayaran" id="tanggal_pembayaran" class="form-control" required>
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
            <label for="teler" class="form-label">Nama Teller:</label>
            <input type="text" name="teler" id="teler" class="form-control" required>
        </div>

        <button type="submit" name="simpan" class="btn btn-primary">Simpan Pembayaran</button>
    </form>
</div>

<script>
    // Fungsi untuk format angka menjadi mata uang Rp. xx.xxx
    function formatRupiah(angka, prefix) {
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        // Jika masih ada angka ribuan, gabungkan dengan titik
        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        // Menambahkan bagian desimal jika ada
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
    }

    $(document).ready(function () {
        // Format input saat mengetik pada 'tarif' dan 'jumlah_bayar'
        $('#tarif, #jumlah_bayar').on('input', function () {
            var inputValue = $(this).val();
            $(this).val(formatRupiah(inputValue)); // Format setiap inputan
        });

        // Ketika dropdown 'jenjang', 'jenis_pembayaran', atau 'tahun_ajaran' berubah
        $('#jenjang, #jenis_pembayaran, #tahun_ajaran').change(function () {
            const jenjang = $('#jenjang').val();
            const jenisPembayaran = $('#jenis_pembayaran').val();
            const tahunAjaran = $('#tahun_ajaran').val();

            if (jenjang && jenisPembayaran && tahunAjaran) {
                // Ambil total pertahun dan tarif menggunakan AJAX
                $.post('get_tarif.php', { jenjang, jenis_pembayaran: jenisPembayaran, tahun_ajaran: tahunAjaran }, function (response) {
                    if (response) {
                        // Format hasil dari response dan tampilkan di input
                        $('#total_pertahun').val(formatRupiah(response.total_pertahun)); // Format total pertahun
                        $('#tarif').val(formatRupiah(response.tarif)); // Format tarif
                    } else {
                        $('#total_pertahun').val('');
                        $('#tarif').val('');
                    }
                }, 'json').fail(function () {
                    alert('Gagal mengambil tarif.');
                });
            }
        });
    });
</script>

</body>
</html>