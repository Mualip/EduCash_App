<?php
session_start();  // Memulai session

// Cek apakah file koneksi.php sudah ter-include dengan benar
include 'koneksi.php';  // Menghubungkan dengan file koneksi.php

// Debugging untuk memastikan koneksi MySQLi berhasil
if (!$mysqli || $mysqli->connect_error) {
    die("Koneksi MySQLi gagal atau tidak tersedia: " . $mysqli->connect_error);
} 

// Periksa jika form login sudah disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = htmlspecialchars($_POST['username']);  // NIS atau username
    $password = $_POST['password'];  // Password

    // Cek jika username (nis_siswa) ada di tabel siswa terlebih dahulu
    $query_siswa = "SELECT * FROM siswa WHERE nis_siswa = ? LIMIT 1";
    if ($mysqli) {
        // Periksa apakah username ada di tabel siswa
        $stmt = $mysqli->prepare($query_siswa);
        if ($stmt === false) {
            die("Gagal mempersiapkan query: " . $mysqli->error);
        }

        $stmt->bind_param('s', $username);  // 's' menunjukkan parameter berupa string
        $stmt->execute();

        // Ambil data siswa
        $result_siswa = $stmt->get_result();
        $siswa = $result_siswa->fetch_assoc();

        // Jika ditemukan data siswa
        if ($siswa) {
            // Verifikasi password menggunakan password_verify (menggunakan hash password)
            if (password_verify($password, $siswa['password'])) {
                // Login berhasil untuk siswa
                $_SESSION['browser'] = md5($_SERVER['HTTP_USER_AGENT']);
                $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['id_siswa'] = $siswa['id_siswa'];  // Menyimpan id_siswa di session
                $_SESSION['siswa'] = array(
                    'id' => $siswa['id_siswa'],
                    'nis' => $siswa['nis_siswa'],
                    'nama' => $siswa['nama_siswa']
                );

                // Redirect ke halaman siswa
                header("Location: index.php");
                exit;
            } else {
                $gagal = true;
                $error_msg = "Password salah.";
            }
        } else {
            // Jika tidak ditemukan siswa, cek ke tabel users (admin)
            $query_admin = "SELECT * FROM users WHERE username = ? LIMIT 1";
            $stmt = $mysqli->prepare($query_admin);
            if ($stmt === false) {
                die("Gagal mempersiapkan query: " . $mysqli->error);
            }

            // Cek username di tabel users (admin)
            $stmt->bind_param('s', $username);  // 's' menunjukkan parameter berupa string
            $stmt->execute();

            // Ambil data admin
            $result_admin = $stmt->get_result();
            $admin = $result_admin->fetch_assoc();

            // Verifikasi login admin
            if ($admin) {
                if ($admin['aktif'] < 1 || $admin['aktif'] > 100) {
                    $gagal = true;
                    $error_msg = "Akun Anda tidak aktif. Silakan hubungi admin.";
                } elseif (password_verify($password, $admin['password'])) {
                    // Login berhasil untuk admin
                    $_SESSION['browser'] = md5($_SERVER['HTTP_USER_AGENT']);
                    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
                    $_SESSION['id_admin'] = $admin['id_admin'];  // Menyimpan id_admin di session
                    $_SESSION['user'] = array(
                        'id' => $admin['id_admin'],
                        'username' => $admin['username']
                    );

                    // Redirect berdasarkan id_admin
                    if ($_SESSION['id_admin'] == 1) {
                        // Jika id_admin = 1, arahkan ke index_akses_full.php
                        header("Location: adminfull/index.php");
                    } else {
                        // Jika id_admin selain 1, arahkan ke index.php
                        header("Location: admin/index.php");
                    }
                    exit;
                } else {
                    $gagal = true;
                    $error_msg = "Password salah.";
                }
            } else {
                // Jika username tidak ditemukan di kedua tabel
                $gagal = true;
                $error_msg = "Username tidak ditemukan.";
            }
        }
    } else {
        die("Koneksi MySQLi gagal.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login Admin & Siswa</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-primary">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="row">
                            <!-- Kolom Gambar (sebelah kiri) -->
                            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                                <img src="img/yadin.jpg" alt="Logo" class="img-fluid">
                            </div>
                            <!-- Kolom Teks (sebelah kanan) -->
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                    </div>

                                    <!-- Form Login -->
                                    <form class="user" method="POST" action="">
                                        <?php
                                        if (isset($gagal)) {
                                            echo '<div class="alert alert-danger">' . $error_msg . '</div>';
                                        }

                                        // Menampilkan pesan jika sudah login
                                        if (isset($_SESSION['siswa'])) {
                                            echo '<div class="alert alert-success mt-4">Sudah login dengan NIS: ' . $_SESSION['siswa']['nis'] . ' - ' . $_SESSION['siswa']['nama'] . '</div>';
                                        }
                                        ?>
                                        <div class="form-group">
                                            <input type="text" class="form-control form-control-user" name="username" placeholder="Enter Username / NIS" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user" name="password" placeholder="Enter Password" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">Login</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

</body>
</html>

