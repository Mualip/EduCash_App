<?php
// Koneksi ke database
include 'koneksi.php'; // Pastikan file koneksi.php sudah ada dan benar

// Query untuk mengambil data dari tabel 'profil_sekolah'
$sql = "SELECT * FROM profil_sekolah";
$result = $mysqli->query($sql);

// Pastikan query berhasil
if (!$result) {
    die("Error: " . $mysqli->error);
}
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

<?php include "sidebar.php"; ?>

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
            while ($profil_sekolah = $result->fetch_assoc()) {
            ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($profil_sekolah['nama_sekolah']); ?></td>
                    <td><?php echo htmlspecialchars($profil_sekolah['alamat']); ?></td>
                    <td>
                        <!-- Menampilkan gambar logo -->
                        <?php
                        // Path yang benar ke folder 'uploads'
                        $logo_path = 'uploads/' . htmlspecialchars($profil_sekolah['logo']);
                        
                        // Memeriksa apakah logo ada dan dapat diakses
                        if (!empty($profil_sekolah['logo']) && file_exists($logo_path)) {
                            // Menampilkan gambar logo
                            echo "<img src='$logo_path' alt='Logo Sekolah' width='50' class='img-fluid'>";
                        } else {
                            echo "Tidak ada logo";
                        }
                        ?>
                    </td>
                    <td class="d-flex justify-content-center">
                        <!-- Tombol aksi Edit dan Hapus -->
                        <a href="edit_profil.php?id=<?php echo $profil_sekolah['id']; ?>" class="btn btn-warning btn-sm mx-1">Edit</a>
                        <a href="hapus_profil.php?id=<?php echo $profil_sekolah['id']; ?>" class="btn btn-danger btn-sm mx-1" onclick="return confirm('Apakah Anda yakin ingin menghapus profil sekolah ini?')">Hapus</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
