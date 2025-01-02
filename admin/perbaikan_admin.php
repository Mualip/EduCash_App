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

// Query untuk mengambil semua pengguna dari tabel users
$query = "SELECT * FROM users";
$result = $mysqli->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Perbaikan Data Pengguna</title>
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
<?php include "sidebar_akses_full.php"; ?>
<div class="container mt-5">
    <h2>Data Pengguna</h2>
    <table class="table table-bordered">
        <thead>
            <tr class="text-nowrap">
                <th>ID Admin</th>
                <th>Username</th>
                <th>Password</th>
                <th>Status Aktif</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $user['id_admin']; ?></td>
                    <td><?php echo $user['username']; ?></td>
                    <td><?php echo $user['password']; ?></td>
                    <td>
                        <?php 
                            // Menampilkan nilai yang ada pada kolom 'aktif' sesuai dengan database
                            echo $user['aktif']; 
                        ?>
                    </td>
                    <td class="text-center">
                        <a href="edit_admin.php?id_admin=<?php echo $user['id_admin']; ?>" class="btn btn-primary btn-sm mr-2">Edit</a>
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
