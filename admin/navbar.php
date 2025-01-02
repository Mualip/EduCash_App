<?php
// Include koneksi.php untuk menghubungkan ke database
include "koneksi.php";


// Periksa apakah 'id_admin' ada di dalam session
if (isset($_SESSION['id_admin'])) {
    $id_admin = $_SESSION['id_admin']; // Ambil id_admin dari session
} else {
    // Jika id_admin tidak ada di session, redirect ke halaman login
    header("Location: login.php");
    exit(); // Pastikan proses berhenti setelah redirect
}

// Query untuk mengambil data pengguna yang aktif dengan kondisi aktif antara 1 dan 100
$query = "SELECT nama_pengguna FROM users WHERE id_admin = ? AND aktif BETWEEN 1 AND 100"; // Memeriksa aktif antara 1 dan 100
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $id_admin); // "i" untuk integer (id_admin)
$stmt->execute();
$result = $stmt->get_result();

// Jika pengguna ditemukan dan aktif
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $nama_admin = $row['nama_pengguna']; // Ambil nama pengguna
} else {
    $nama_admin = "Admin"; // Default jika tidak ditemukan atau pengguna tidak aktif dalam rentang 1 sampai 100
}

$stmt->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Dashboard</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

</head>

<body id="page-top">

<div id="content-wrapper" class="d-flex flex-column">

<!-- Main Content -->
<div id="">

    <!-- Topbar -->
    <nav class="navbar navbar-expand navbar-light bg-white topbar static-top shadow">
        <ul class="navbar-nav ml-auto">
            <!-- Nav Item - User Information -->
            <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $nama_admin; ?></span>
                    <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                </a>
            </li>
        </ul>
    </nav>
            <!-- End of Topbar -->

        </div>
        <!-- End of Page Wrapper -->

        <!-- Scroll to Top Button-->
        <a class="scroll-to-top rounded" href="#page-top">
            <i class="fas fa-angle-up"></i>
        </a>


        <!-- Bootstrap core JavaScript-->
        <script src="vendor/jquery/jquery.min.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

        <!-- Core plugin JavaScript-->
        <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

        <!-- Custom scripts for all pages-->
        <script src="js/sb-admin-2.min.js"></script>

        <!-- Page level plugins -->
        <script src="vendor/chart.js/Chart.min.js"></script>

        <!-- Page level custom scripts -->
        <script src="js/demo/chart-area-demo.js"></script>
        <script src="js/demo/chart-pie-demo.js"></script>

</body>

</html>