<?php
include 'koneksi.php';
session_start();

// Pastikan admin sudah login
if (!isset($_SESSION['id_admin'])) {
    echo json_encode(['error' => 'User belum login']);
    exit;
}

// Cek apakah data yang diperlukan ada dalam request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jenjang'], $_POST['jenis_pembayaran'], $_POST['tahun_ajaran'])) {

    // Ambil data dari POST dan sanitasi input
    $jenjang = mysqli_real_escape_string($mysqli, $_POST['jenjang']);
    $jenis_pembayaran = mysqli_real_escape_string($mysqli, $_POST['jenis_pembayaran']);
    $tahun_ajaran = mysqli_real_escape_string($mysqli, $_POST['tahun_ajaran']);

    // Query untuk mengambil total_pertahun dari tabel biaya_pertahun
    $query_total = "
        SELECT total_pertahun 
        FROM biaya_pertahun 
        WHERE tahun_ajaran = '$tahun_ajaran' 
        AND jenjang = '$jenjang' 
        AND jenis_biaya = '$jenis_pembayaran' 
        LIMIT 1
    ";

    // Eksekusi query dan ambil hasilnya
    $result_total = mysqli_query($mysqli, $query_total);

    // Cek apakah hasil query valid
    if ($result_total && mysqli_num_rows($result_total) > 0) {
        $row_total = mysqli_fetch_assoc($result_total);
        $total_pertahun = $row_total['total_pertahun'];
    } else {
        $total_pertahun = null; // Jika tidak ditemukan
    }

    // Query untuk mengambil tarif dari tabel tarif_pembayaran
    $query_tarif = "
        SELECT tarif 
        FROM tarif_pembayaran 
        WHERE tahun_ajaran = '$tahun_ajaran' 
        AND jenjang = '$jenjang' 
        AND jenis_pembayaran = '$jenis_pembayaran' 
        LIMIT 1
    ";

    // Eksekusi query dan ambil hasilnya
    $result_tarif = mysqli_query($mysqli, $query_tarif);

    // Cek apakah hasil query valid
    if ($result_tarif && mysqli_num_rows($result_tarif) > 0) {
        $row_tarif = mysqli_fetch_assoc($result_tarif);
        $tarif = $row_tarif['tarif'];
    } else {
        $tarif = null; // Jika tidak ditemukan
    }

    // Kembalikan hasil dalam format JSON
    echo json_encode([
        'total_pertahun' => $total_pertahun,
        'tarif' => $tarif
    ]);

} else {
    // Jika request POST tidak lengkap atau tidak valid
    echo json_encode(['error' => 'Data tidak lengkap']);
}
?>
