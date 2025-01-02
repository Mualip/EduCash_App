<?php
// Include koneksi.php untuk menghubungkan ke database
include "koneksi.php";

// Ambil id_admin dari session (misalnya setelah login)
session_start();
$id_admin = $_SESSION['id_admin']; // Pastikan id_admin ada di session

// Query untuk mengambil data pengguna yang aktif dengan kondisi aktif antara 1 dan 100
$query = "SELECT nama_pengguna FROM users WHERE id_admin = ? AND aktif BETWEEN 1 AND 100"; 
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

// Inisialisasi variabel id_admin yang ingin dihitung (id_admin = 1)
$id_admin = 1;

// Inisialisasi variabel untuk menghitung jumlah siswa
$query_siswa = "SELECT COUNT(*) AS jumlah_siswa FROM siswa";
$stmt_siswa = $mysqli->prepare($query_siswa);
$stmt_siswa->execute();
$result_siswa = $stmt_siswa->get_result();

// Ambil jumlah siswa
if ($result_siswa->num_rows > 0) {
    $row_siswa = $result_siswa->fetch_assoc();
    $jumlah_siswa = $row_siswa['jumlah_siswa']; // Ambil jumlah siswa
} else {
    $jumlah_siswa = 0; // Default jika tidak ada siswa ditemukan
}

$stmt_siswa->close();

// Inisialisasi variabel untuk menghitung total uang masuk
$total_uang_masuk = 0;
$query_uang_masuk = "SELECT SUM(jumlah_bayar) AS total_uang_masuk 
                     FROM total 
                     WHERE jenis_pembayaran = 'UANG MASUK'";
$stmt_uang_masuk = $mysqli->prepare($query_uang_masuk);
$stmt_uang_masuk->execute();
$result_uang_masuk = $stmt_uang_masuk->get_result();

if ($result_uang_masuk->num_rows > 0) {
    $row_uang_masuk = $result_uang_masuk->fetch_assoc();
    $total_uang_masuk = $row_uang_masuk['total_uang_masuk']; // Ambil total uang masuk
}

// Pastikan jika total uang masuk kosong, di-set ke 0
if ($total_uang_masuk === null || $total_uang_masuk === "") {
    $total_uang_masuk = 0;
}

$stmt_uang_masuk->close();



// Inisialisasi variabel untuk menghitung total SPP
$total_spp = 0;
$query_spp = "SELECT SUM(jumlah_bayar) AS total_spp 
              FROM total 
              WHERE jenis_pembayaran = 'SPP'";
$stmt_spp = $mysqli->prepare($query_spp);
$stmt_spp->execute();
$result_spp = $stmt_spp->get_result();

if ($result_spp->num_rows > 0) {
    $row_spp = $result_spp->fetch_assoc();
    $total_spp = $row_spp['total_spp']; // Ambil total SPP
}

// Pastikan jika total SPP kosong, di-set ke 0
if ($total_spp === null || $total_spp === "") {
    $total_spp = 0;
}

$stmt_spp->close();



// Inisialisasi variabel untuk menghitung total diskon
$total_diskon = 0;
$query_diskon = "SELECT SUM(diskon) AS total_diskon 
                 FROM total 
                 WHERE diskon IS NOT NULL";
$stmt_diskon = $mysqli->prepare($query_diskon);
$stmt_diskon->execute();
$result_diskon = $stmt_diskon->get_result();

if ($result_diskon->num_rows > 0) {
    $row_diskon = $result_diskon->fetch_assoc();
    $total_diskon = $row_diskon['total_diskon']; // Ambil total diskon
}

// Pastikan jika total diskon kosong, di-set ke 0
if ($total_diskon === null || $total_diskon === "") {
    $total_diskon = 0;
}

$stmt_diskon->close();


// Menentukan rentang tanggal berdasarkan periode 1 Juli - 30 Juni setiap tahunnya
$today = date('Y-m-d');  // Tanggal hari ini
$current_year = date('Y');  // Tahun ini

// Cek apakah sudah melewati 1 Juli atau belum
if ($today >= date('Y-07-01')) {
    $start_date = date('Y-07-01', strtotime($current_year . '-07-01'));
    $end_date = date('Y-06-30', strtotime(($current_year + 1) . '-06-30')); // 30 Juni tahun depan
} else {
    $start_date = date('Y-07-01', strtotime(($current_year - 1) . '-07-01'));
    $end_date = date('Y-06-30', strtotime($current_year . '-06-30'));  // 30 Juni tahun ini
}

// Ambil id_admin dari session

// Query untuk menghitung jumlah siswa lunas dan menunggak berdasarkan status pembayaran
// Tanpa filter berdasarkan id_admin (menghapus bagian id_admin dalam query)
$query_pembayaran = "
    SELECT status_pembayaran, COUNT(*) as jumlah 
    FROM total 
    WHERE status_pembayaran IN ('lunas', 'menunggak')
    AND tanggal_bayar BETWEEN ? AND ?
    GROUP BY status_pembayaran
";

$stmt_pembayaran = $mysqli->prepare($query_pembayaran);
$stmt_pembayaran->bind_param("ss", $start_date, $end_date); // Menghapus id_admin dari parameter
$stmt_pembayaran->execute();
$result_pembayaran = $stmt_pembayaran->get_result();

$jumlah_lunas = 0;
$jumlah_menunggak = 0;
$jumlah_siswa_pembayaran = 0;

// Mengambil data status pembayaran dan menghitung jumlah siswa
while ($row = $result_pembayaran->fetch_assoc()) {
    if ($row['status_pembayaran'] == 'lunas') {
        $jumlah_lunas = $row['jumlah'];
    } elseif ($row['status_pembayaran'] == 'menunggak') {
        $jumlah_menunggak = $row['jumlah'];
    }
}

// Total siswa yang melakukan pembayaran (lunas + menunggak)
$jumlah_siswa_pembayaran = $jumlah_lunas + $jumlah_menunggak;

// Menghitung persentase pembayaran lunas dan menunggak
$persen_lunas = 0;
$persen_menunggak = 0;

if ($jumlah_siswa_pembayaran > 0) {
    // Menghitung persentase lunas
    $persen_lunas = ($jumlah_lunas / $jumlah_siswa_pembayaran) * 100;
    // Menghitung persentase menunggak
    $persen_menunggak = ($jumlah_menunggak / $jumlah_siswa_pembayaran) * 100;
}

// Pastikan jika persentase kosong, di-set ke 0
if ($persen_lunas === null || $persen_lunas === "") {
    $persen_lunas = 0;
}

if ($persen_menunggak === null || $persen_menunggak === "") {
    $persen_menunggak = 0;
}

$stmt_pembayaran->close();

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
    <style>
    .circle-progress-container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
}

.circle-progress {
    position: relative;
    width: 150px; /* Ukuran lingkaran */
    height: 150px; /* Ukuran lingkaran */
    border-radius: 50%;
    background-color: black; /* Warna latar belakang lingkaran */
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: background 0.3s ease-in-out, transform 0.3s ease-in-out;
    cursor: pointer; /* Menambahkan pointer untuk interaksi */
}

/* Mengaktifkan hover pada lingkaran */
.circle-progress:hover {
    transform: scale(1.1); /* Sedikit memperbesar lingkaran saat hover */
    color : white ;
}

.circle-progress:hover .circle-text-lunas,
.circle-progress:hover .circle-text-tunggakan {
    opacity: 1;
    transform: translate(-50%, -50%); /* Menjaga posisi teks agar tetap di tengah */
    color : white;
}

.circle-text-lunas,
.circle-text-tunggakan {
    position: absolute;
    font-weight: bold;
    font-size: 16px; /* Ukuran font untuk teks */
    color: #333; /* Warna teks default */
    opacity: 0; /* Mulai dengan teks tersembunyi */
    transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
    text-align: center;
}

/* Teks Lunas */
.circle-text-lunas {
    color: #1E90FF; /* Warna biru untuk Lunas */
    font-size: 18px; /* Ukuran font lebih besar untuk Lunas */
}

/* Teks Tunggakan */
.circle-text-tunggakan {
    color: rgb(0, 255, 8); /* Warna hijau untuk Tunggakan */
    font-size: 18px; /* Ukuran font lebih besar untuk Tunggakan */
}

/* Menambahkan legend untuk Lunas dan Tunggakan */
.circle-legends {
    font-size: 14px; /* Ukuran font legenda */
    margin-top: 10px;
    color: #333;
    display: flex;
    justify-content: space-between;
    width: 100%;
}

/* Legend untuk Lunas */
.legend-lunas {
    color: #1E90FF; /* Warna biru untuk Lunas */
    font-weight: bold;
}

/* Legend untuk Tunggakan */
.legend-tunggakan {
    color: rgb(0, 255, 8); /* Warna hijau untuk Tunggakan */
    font-weight: bold;
}

/* Responsif untuk ukuran kecil */
@media (max-width: 768px) {
    .circle-progress {
        width: 120px; /* Ukuran lingkaran lebih kecil pada layar kecil */
        height: 120px;
    }

    .circle-text-lunas,
    .circle-text-tunggakan {
        font-size: 14px; /* Ukuran font lebih kecil */
    }

    .circle-legends {
        font-size: 12px; /* Ukuran font legenda lebih kecil */
    }
}

/* Responsif untuk ukuran lebih besar */
@media (min-width: 768px) {
    .circle-progress {
        width: 150px; /* Ukuran lingkaran standar */
        height: 150px;
    }

    .circle-text-lunas,
    .circle-text-tunggakan {
        font-size: 16px; /* Ukuran font lebih besar */
    }

    .circle-legends {
        font-size: 14px; /* Ukuran font legenda lebih besar */
    }
}
/* Mengatur agar body memenuhi seluruh tinggi layar */
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
}

/* Membuat halaman konten mengisi ruang yang tersedia */
#page-content {
    min-height: 100%; 
    display: flex;
    flex-direction: column;
}

/* Konten utama halaman */
#content-wrapper {
    flex-grow: 1;
}

/* Styling untuk footer */
footer {
   
    color: black;
    padding: 20px;
    text-align: center;
    margin-top: auto; /* Ini membuat footer berada di bawah konten jika konten sedikit */
}

/* Jika Anda ingin memastikan tinggi footer responsif */
footer .container {
    max-width: 1140px;
    margin: 0 auto;
}
    </style>

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <?php include "sidebar.php"; ?>
    
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
                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                        
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                       <!-- Daftar siswa -->
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Jumlah Siswa</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($jumlah_siswa); ?></div>
                </div>
                <div class="col-auto">
                    <!-- Ganti ikon menjadi fa-users yang lebih cocok untuk jumlah siswa -->
                    <i class="fas fa-users fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-success shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Uang Masuk
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        Rp. <?php echo number_format($total_uang_masuk, 0, ',', '.'); ?>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-success shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        SPP
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        Rp. <?php echo number_format($total_spp, 0, ',', '.'); ?>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Diskon (Di bawah Uang Masuk dan SPP) -->
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-warning shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                        Diskon
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        Rp. <?php echo number_format($total_diskon, 0, ',', '.'); ?>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<!--

<div class="col-xl-3 col-lg-4 mb-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Presentase Pembayaran</h6>
        </div>
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col-auto">
                    <div class="circle-progress-container text-center">
                        <div class="circle-progress"
                             style="background: conic-gradient(
                                #1E90FF <?php echo round($persen_lunas); ?>%, 
                                rgb(0, 255, 8) <?php echo round($persen_lunas); ?>% <?php echo round($persen_lunas + $persen_menunggak); ?>%)">
                            <div class="circle-text-lunas">
                                <?php echo round($persen_lunas); ?>%
                            </div>
                            <div class="circle-text-tunggakan">
                                <?php echo round($persen_menunggak); ?>%
                            </div>
                        </div>
                        <div class="circle-legends">
                            <span class="legend-lunas">Lunas (<?php echo round($persen_lunas); ?>%)</span> |
                            <span class="legend-tunggakan">Menunggak (<?php echo round($persen_menunggak); ?>%)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
-->
                    <!-- Content Row -->
                    <div class="row">

    <!-- ini navbar -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="login.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <footer class="sticky-footer bg-white" style="margin-top: 310px;">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Educash 2024</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
</footer>
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