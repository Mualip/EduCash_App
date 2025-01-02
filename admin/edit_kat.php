<?php
require 'function.php';
include 'koneksi.php';


// Validasi apakah parameter 'id_kategori' ada di URL untuk edit
if (isset($_GET['id_kategori'])) {
    $id_kategori = $_GET['id_kategori']; // Ambil parameter id_kategori dari URL

    // Query untuk mengambil data kategori_pembayaran berdasarkan id_kategori
    $query = mysqli_query($mysqli, "SELECT * FROM kategori_pembayaran WHERE id_kategori = '$id_kategori'");

    // Periksa apakah query berhasil dan data ditemukan
    if (!$query) {
        echo "Error: " . mysqli_error($mysqli);  // Tampilkan pesan error jika query gagal
        exit;
    }

    if (mysqli_num_rows($query) === 0) {
        echo "<script>alert('Data kategori pembayaran tidak ditemukan!'); window.location.href = 'kategori_pembayaran.php';</script>";
        exit;
    }

    $data = mysqli_fetch_assoc($query); // Ambil data sebagai array asosiatif

    // Proses edit data jika form disubmit
    if (isset($_POST['editbtn'])) {
        // Ambil data dari form
        $jenis_pembayaran = $_POST['jenis_pembayaran'];
        $tahun_ajaran = $_POST['tahun_ajaran'];
        $jenjang = $_POST['jenjang'];
        $kelas = $_POST['kelas'];

        // Query untuk update data kategori_pembayaran
        $update_query = "UPDATE kategori_pembayaran SET 
            jenis_pembayaran='$jenis_pembayaran', 
            tahun_ajaran='$tahun_ajaran', 
            jenjang='$jenjang', 
            kelas='$kelas'
            WHERE id_kategori='$id_kategori'";

        // Eksekusi query
        if (mysqli_query($mysqli, $update_query)) {
            echo "<script>alert('Data berhasil diupdate!'); window.location.href = 'edit_kategori.php';</script>";
        } else {
            echo "Error: " . mysqli_error($mysqli);
        }
    }
} else {
    echo "<script>alert('ID kategori tidak ditemukan!'); window.location.href = 'edit_kategori.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kategori Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include "sidebar.php"; ?>
<?php include "navbar.php"; ?>

<div class="container-fluid mt-4 " >
    <h6 class="font-weight-bold text-primary">Edit Data Kategori Pembayaran</h6>
    <form action="" method="post "text-center>
        <div class="mb-3">
            <label for="jenis_pembayaran" class="form-label">Jenis Pembayaran</label>
            <input type="text" class="form-control" id="jenis_pembayaran" name="jenis_pembayaran" value="<?= htmlspecialchars($data['jenis_pembayaran']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="tahun_ajaran" class="form-label">Tahun Ajaran</label>
            <input type="text" class="form-control" id="tahun_ajaran" name="tahun_ajaran" value="<?= htmlspecialchars($data['tahun_ajaran']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="jenjang" class="form-label">Jenjang</label>
            <input type="text" class="form-control" id="jenjang" name="jenjang" value="<?= htmlspecialchars($data['jenjang']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="kelas" class="form-label">Kelas</label>
            <input type="text" class="form-control" id="kelas" name="kelas" value="<?= htmlspecialchars($data['kelas']); ?>" required>
        </div>
        <button type="submit" name="editbtn" class="btn btn-primary">Simpan</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
