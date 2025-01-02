<?php
include 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
    body {
        font-family: 'Arial', sans-serif;
        /* Mengubah font menjadi Arial */
        background-color: #f8f9fa;
        /* Memberikan latar belakang yang lebih terang */
        color: #333;
        /* Warna teks lebih gelap agar lebih jelas */
    }

    .container {
        background-color: white;
        padding: 50px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    h2 {
        font-size: 24px;
        margin-bottom: 2px;
        text-align: center;
    }

    .form-label {
        font-size: 16px;
        font-weight: 300px;
    }

    .form-control {
        font-size: 14px;
        padding: 10px;
    }

    .btn {
        font-size: 16px;
        padding: 5px 20px;
    }

    textarea.form-control {
        resize: vertical;
        /* Membuat textarea bisa diubah ukurannya secara vertikal */
    }

    .sidebar {
        width: 300;
        /* Perlebar sidebar menjadi 300px */
    }

    #content-wrapper {
        margin-left: 3px;
        /* Sesuaikan margin konten utama */
    }

    .sidebar .nav-item .nav-link {
        font-size: 14px;
        /* Ukuran font navigasi jika perlu diubah */
    }
    </style>

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-text mx-20"> Aplikasi EduCash</div>
            </a>


            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item active">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Interface
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
            <!-- Manajemen Siswa -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseManajemenSiswa"
                    aria-expanded="false" aria-controls="collapseManajemenSiswa">
                    <i class="fas fa-fw fa-user-graduate"></i> <!-- Ikon Manajemen Siswa -->
                    <span>Manajemen Siswa</span>
                </a>
                <div id="collapseManajemenSiswa" class="collapse" aria-labelledby="headingManajemenSiswa"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Kelola Siswa:</h6>
                        <a class="collapse-item" href="tambah.php">Tambah Siswa</a>
                        <a class="collapse-item" href="edit_identitas.php">Edit Identitas</a>

                    </div>
                </div>
            </li>

            <!-- Data Mutasi dan Status -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseMutasiStatus"
                    aria-expanded="false" aria-controls="collapseMutasiStatus">
                    <i class="fas fa-fw fa-exchange-alt"></i> <!-- Ikon Mutasi dan Status -->
                    <span>Daftar & Status</span>
                </a>
                <div id="collapseMutasiStatus" class="collapse" aria-labelledby="headingMutasiStatus"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Data Siswa:</h6>
                        <a class="collapse-item" href="daftarsiswa.php">Daftar Siswa Aktif</a>
                        <a class="collapse-item" href="siswa_keluar.php">Daftar Siswa Keluar</a>
                        <a class="collapse-item" href="siswa_lulus.php">Daftar Siswa Lulus</a>
                        <a class="collapse-item" href="status_pembayaran.php">Status Pembayaran</a>
                    </div>
                </div>
            </li>

            <!-- Input Data Pembayaran -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseInputData"
                    aria-expanded="false" aria-controls="collapseInputData">
                    <i class="fas fa-fw fa-database"></i> <!-- Ikon Input Data -->
                    <span>Input Data Pembayaran</span>
                </a>
                <div id="collapseInputData" class="collapse" aria-labelledby="headingInputData"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Kelola Data Pembayaran:</h6>
                        <a class="collapse-item" href="input_pembayaran.php">Input Pembayaran</a>

                    </div>
                </div>
            </li>

            <!-- Manajemen Pembayaran -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseManajemenPembayaran"
                    aria-expanded="false" aria-controls="collapseManajemenPembayaran">
                    <i class="fas fa-fw fa-wallet"></i> <!-- Ikon Manajemen Pembayaran -->
                    <span>Manajemen Pembayaran</span>
                </a>
                <div id="collapseManajemenPembayaran" class="collapse" aria-labelledby="headingManajemenPembayaran"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Pengelolaan Pembayaran:</h6>
                        <a class="collapse-item" href="uang_masuk.php">Uang Masuk</a>
                        <a class="collapse-item" href="daftar_ulang.php">Daftar Ulang</a>
                        <a class="collapse-item" href="spp.php">SPP</a>
                    </div>
                </div>
            </li>


            <!-- Laporan Keuangan -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseLaporanKeuangan"
                    aria-expanded="false" aria-controls="collapseLaporanKeuangan">
                    <i class="fas fa-fw fa-file-alt"></i> <!-- Ikon Laporan Keuangan -->
                    <span>Laporan Keuangan</span>
                </a>
                <div id="collapseLaporanKeuangan" class="collapse" aria-labelledby="headingLaporanKeuangan"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Jenis Laporan:</h6>
                        <a class="collapse-item" href="laporan_harian.php">Laporan Harian</a>
                        <a class="collapse-item" href="laporan_bulanan.php">Laporan Bulanan</a>
                        <a class="collapse-item" href="laporan_tahunan.php">Laporan Tahunan</a>
                    </div>
                </div>
            </li>
            <!-- Cetak Pembayaran -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCetakPembayaran"
                    aria-expanded="false" aria-controls="collapseCetakPembayaran">
                    <i class="fas fa-fw fa-print"></i> <!-- Ikon Cetak Pembayaran (Printer) -->
                    <span>Cetak Pembayaran</span>
                </a>
                <div id="collapseCetakPembayaran" class="collapse" aria-labelledby="headingCetakPembayaran"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Cetak Pembayaran:</h6>
                        <a class="collapse-item" href="cetak_kuitansi.php">Cetak Kuitansi</a>
                        <a class="collapse-item" href="cetak_riwayat_pembayaran.php">Cetak Riwayat Pembayaran</a>
                    </div>
                </div>
            </li>
            <!--engaturan -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePengaturan"
                    aria-expanded="false" aria-controls="collapsePengaturan">
                    <i class="fas fa-fw fa-cogs"></i> <!-- Ikon Pengaturan -->
                    <span>Pengaturan</span>
                </a>
                <div id="collapsePengaturan" class="collapse" aria-labelledby="headingPengaturan"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Pengaturan Sistem:</h6>
                      
                      <!--  <a class="collapse-item" href="input_profil_sekolah.php">Input Profil Sekolah</a> -->
                        <a class="collapse-item" href="backup_data.php">Backup Data</a>
                        <a class="collapse-item" href="perbaikan_siswa.php">Forgot Siswa</a>
                       
                       
                      <!--  <a class="collapse-item" href="tampilan_profil_sekolah.php">Profil sekolah</a>  -->
                    </div>
                </div>
                <li class="nav-item">
    <a class="nav-link" href="javascript:void(0);" id="logoutButton">
        <i class="fas fa-fw fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>
</li>
        </ul>

        </ul>

        </nav>

      

<script>
// Event listener untuk tombol logout
document.getElementById("logoutButton").addEventListener("click", function() {
    // Tampilkan konfirmasi dengan modal
    if (confirm("Apakah Anda yakin ingin logout?")) {
        // Jika user memilih "OK", arahkan ke halaman logout
        window.location.href = "../logout.php"; // Arahkan ke logout.php
    }
});
</script>
        
        <!-- Bootstrap core JavaScript-->
        <script src="vendor/jquery/jquery.min.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

        <!-- Core plugin JavaScript-->
        <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

        <!-- Custom scripts for all pages-->
        <script src="js/sb-admin-2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>