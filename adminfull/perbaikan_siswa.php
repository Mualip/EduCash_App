<?php
// Memulai session
session_start();

// Include koneksi ke database
include 'koneksi.php';

// Pastikan hanya admin yang terautentikasi yang bisa mengakses halaman ini
if (!isset($_SESSION['id_admin'])) {
    header("Location: login.php");
    exit;
}

// Ambil nilai pencarian jika ada, atau default ke string kosong
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Query untuk mengambil semua data siswa dari tabel siswa dengan pencarian
$query = "SELECT * FROM siswa";
if ($searchTerm) {
    $query .= " WHERE nis_siswa LIKE '%$searchTerm%' OR nama_siswa LIKE '%$searchTerm%' OR keaktifan LIKE '%$searchTerm%'";
}

// Menjalankan query dan memeriksa apakah ada data yang ditemukan
$result = $mysqli->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Perbaikan Data Siswa</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .table th, .table td {
            text-align: center;
        }
        .btn-edit {
            background-color: #28a745; /* Warna hijau muda */
            color: white; /* Warna teks putih */
            border-color: #28a745; /* Border hijau muda */
        }
        .search-container {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<?php include "sidebar.php"; ?>


<div class="container mt-5">
    <h2>Data Siswa</h2>

    <!-- Form Pencarian -->
    <form action="" method="GET" class="form-inline mb-3">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari..." value="<?php echo htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>" style="width: auto; max-width: 300px;">
        <button type="submit" class="btn btn-primary btn-sm ml-2">Cari</button>
    </form>

    <!-- Tabel Data Siswa -->
    <table class="table table-bordered">
        <thead>
            <tr class="text-nowrap">
                <th>No</th>
                <th>NIS Siswa</th>
                <th>Nama Siswa</th>
                <th>Password</th>
                <th>Keaktifan</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            <?php 
            // Menambahkan variabel counter untuk nomor urut
            $no = 1;
            // Cek apakah ada data
            if ($result->num_rows > 0) {
                while ($siswa = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $no++; ?></td>  <!-- Menampilkan nomor urut -->
                        <td><?php echo $siswa['nis_siswa']; ?></td>
                        <td><?php echo $siswa['nama_siswa']; ?></td>
                        <td><?php echo $siswa['password']; ?></td>
                        <td><?php echo $siswa['keaktifan']; ?></td>
                        <td class="text-center">
                            <a href="edit_siswa.php?nis_siswa=<?php echo $siswa['nis_siswa']; ?>" class="btn btn-primary btn-sm mr-2">Edit</a>
                        </td>
                    </tr>
                <?php }
            } else {
                echo "<tr><td colspan='6' class='text-center'>Data tidak ditemukan</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/sb-admin-2.min.js"></script>

</body>
</html>
