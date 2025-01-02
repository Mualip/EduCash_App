<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['siswa'])) {
    $selectedData = $_POST['siswa'];
    $isSuccessful = true; // Menambahkan variabel untuk status keberhasilan

    foreach ($selectedData as $data) {
        $decodedData = json_decode($data, true);

        // Ambil data dari JSON
        $nis_siswa = $decodedData['nis_siswa'];
        $nama_siswa = $decodedData['nama_siswa'];
        $jenis_kelamin = $decodedData['jenis_kelamin'];
        $kelas = $decodedData['kelas'];
        $jenis_pembayaran = $decodedData['jenis_pembayaran'];
        $jenjang = $decodedData['jenjang'];
        $tahun_ajaran = $decodedData['tahun_ajaran'];
        $total_pertahun = $decodedData['total_pertahun'];
        $jumlah_bayar = $decodedData['jumlah_bayar'];
        $diskon = $decodedData['diskon'];
        $total_bayar = $jumlah_bayar + $diskon;
        $bulan = $decodedData['bulan'];
        $tanggal_bayar = $decodedData['tanggal_bayar'];
        $sisa_pembayaran = $decodedData['sisa_pembayaran'];
        $teller = $decodedData['teller'];

        // Mendapatkan waktu saat ini
        $waktu_simpan = date('Y-m-d H:i:s'); // Format: YYYY-MM-DD HH:MM:SS

        // Masukkan data ke tabel `cetak_kuitansi`
        $query = "INSERT INTO cetak_kuitansi (
                    nis_siswa, nama_siswa, jenis_kelamin, kelas, jenis_pembayaran,
                    jenjang, tahun_ajaran, total_pertahun, jumlah_bayar, diskon, total_bayar,
                    bulan, tanggal_bayar, sisa_pembayaran, teller, waktu_simpan
                  ) VALUES (
                    '$nis_siswa', '$nama_siswa', '$jenis_kelamin', '$kelas', '$jenis_pembayaran',
                    '$jenjang', '$tahun_ajaran', '$total_pertahun', '$jumlah_bayar', '$diskon', '$total_bayar',
                    '$bulan', '$tanggal_bayar', '$sisa_pembayaran', '$teller', '$waktu_simpan'
                  )";

        // Mengeksekusi query dan menangani kesalahan jika ada
        if (!mysqli_query($mysqli, $query)) {
            $isSuccessful = false;
            // Menangani error query
            die("Error: " . mysqli_error($mysqli));
        }
    }

    // Redirect berdasarkan status sukses atau error
    if ($isSuccessful) {
        header('Location: cetak_kuitansi.php?success=1');
    } else {
        header('Location: cetak_kuitansi.php?error=1');
    }
    exit;
} else {
    // Jika tidak ada data siswa yang dipilih atau request method bukan POST
    header('Location: cetak_kuitansi.php?error=1');
    exit;
}
?>

