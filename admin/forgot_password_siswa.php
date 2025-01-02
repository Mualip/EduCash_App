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

// Query untuk mengambil semua data siswa dari tabel siswa
$query = "SELECT * FROM siswa";
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

    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
<div class="container mt-5">
    <h2>Data Siswa</h2>
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
            while ($siswa = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $no++; ?></td>  <!-- Menampilkan nomor urut -->
                    <td><?php echo $siswa['nis_siswa']; ?></td>
                    <td><?php echo $siswa['nama_siswa']; ?></td>
                    <td><?php echo $siswa['password']; ?></td>
                    <td>
                        <?php 
                            // Menampilkan nilai yang ada pada kolom 'keaktifan' sesuai dengan database
                            echo $siswa['keaktifan']; 
                        ?>
                    </td>
                    <td class="text-center">
                        <a href="edit_siswa.php?no=<?php echo $siswa['no']; ?>" class="btn btn-primary btn-sm mr-2">Edit</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/sb-admin-2.min.js"></script>

</body>
</html>
