<?php
// Koneksi ke database
include 'koneksi.php'; // Pastikan file koneksi.php sudah ada dan benar

// Query untuk mengambil data dari tabel 'profil_sekolah'
$sql = "SELECT * FROM profil_sekolah";
$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Profil Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include "sidebar.php";


?>

<div class="container mt-5">
    <h2>Daftar Profil Sekolah</h2>
    <table class="table table-bordered">
        <thead>
            <tr class="text-nowrap">
                <th>No</th>
                <th>Nama Sekolah</th>
                <th>Alamat</th>
                <th>Logo</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // No urut untuk menampilkan nomor urut
            $no = 1;
            while ($sekolah = $result->fetch_assoc()) {
            ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $sekolah['nama_sekolah']; ?></td>
                    <td><?php echo $sekolah['alamat']; ?></td>
                    <td>
                        <!-- Menampilkan gambar logo -->
                        <?php
                        // Periksa jika kolom logo kosong atau tidak
                        if (!empty($sekolah['logo'])) {
                            // Menampilkan gambar dari folder 'uploads/'
                            echo "<img src='uploads/" . $sekolah['logo'] . "' alt='Logo Sekolah' width='50'>";
                        } else {
                            // Jika tidak ada logo, tampilkan teks
                            echo "Logo Sekolah";
                        }
                        ?>
                    </td>
                    <td class="d-flex justify-content-center">
                        <!-- Tombol aksi Edit dan Hapus -->
                        <a href="edit_profil.php?id=<?php echo $sekolah['id']; ?>" class="btn btn-warning btn-sm mx-1">Edit</a>
                        <a href="hapus_profil.php?id=<?php echo $sekolah['id']; ?>" class="btn btn-danger btn-sm mx-1" onclick="return confirm('Apakah Anda yakin ingin menghapus profil sekolah ini?')">Hapus</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
