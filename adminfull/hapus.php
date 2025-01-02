<?php
include "koneksi.php"; // Pastikan koneksi sudah benar



// Ambil id siswa yang akan dihapus
$id = $_GET['id']; // Pastikan ini sesuai dengan parameter yang dikirim

// Pastikan $id ada dan tidak kosong
if (!empty($id)) {
    // Query untuk menghapus data siswa
    $hapus = mysqli_query($mysqli, "DELETE FROM siswa WHERE nis_siswa = '$id'");

    // Cek jika data berhasil dihapus
    if ($hapus) {
        header("Location: edit_identitas_full.php"); // Arahkan kembali ke halaman daftar siswa
        exit;
    } else {
        echo "Data Gagal Dihapus: " . mysqli_error($koneksi); // Tampilkan pesan error jika gagal
    }
} else {
    echo "ID siswa tidak ditemukan!";
}
?>
