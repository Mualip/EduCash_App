<?php
// Include koneksi ke database
include 'koneksi.php';

// Proses ketika form disubmit untuk menambah admin
if (isset($_POST['create_account'])) {
    // Menangkap data dari form
    $username = $_POST['username'];  // Menangkap username
    $nama_pengguna = $_POST['nama_pengguna'];  // Menangkap nama pengguna
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi jika password dan confirm password cocok
    if ($password === $confirm_password) {
        // Query untuk mengecek apakah username sudah ada
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Username sudah digunakan
            $error_message = "Username sudah digunakan, coba yang lain.";
        } else {
            // Mengambil nilai aktif tertinggi yang ada
            $query_active = "SELECT MAX(aktif) AS max_active FROM users";
            $result_active = $mysqli->query($query_active);
            $row = $result_active->fetch_assoc();
            
            // Jika belum ada admin, aktif pertama adalah 1
            if ($row['max_active'] == NULL) {
                $new_active_status = 1; // Admin pertama, aktif = 1
            } else {
                $new_active_status = $row['max_active'] + 1; // Admin berikutnya
            }

            // Meng-hash password sebelum disimpan ke database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);  // Jangan tambahkan salt manual

            // Query untuk memasukkan data admin baru ke tabel users
            $insert_query = "INSERT INTO users (username, nama_pengguna, password, aktif) VALUES (?, ?, ?, ?)";
            $insert_stmt = $mysqli->prepare($insert_query);
            $insert_stmt->bind_param("sssi", $username, $nama_pengguna, $hashed_password, $new_active_status);

            if ($insert_stmt->execute()) {
                // Jika query berhasil, tampilkan pesan sukses dan redirect ke halaman login
                $success_message = "Akun admin berhasil dibuat. Silakan login!";
                
                // Redirect ke halaman login setelah sukses
                header('Location: ../login.php');
                exit();  // pastikan script tidak dilanjutkan setelah redirect
            } else {
                // Jika terjadi kesalahan saat memasukkan data ke database
                $error_message = "Terjadi kesalahan, silakan coba lagi.";
            }
        }
    } else {
        // Jika password dan konfirmasi password tidak cocok
        $error_message = "Password dan konfirmasi password tidak cocok.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Create Admin Account</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-primary">

    <div class="container">
        <!-- Outer Row -->
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Create a New Admin Account</h1>
                                    </div>

                                    <!-- Form Create Admin -->
                                    <form class="user" method="POST" action="">
    <?php
    if (isset($error_message)) {
        echo '<div class="alert alert-danger">' . $error_message . '</div>';
    }
    if (isset($success_message)) {
        echo '<div class="alert alert-success">' . $success_message . '</div>';
    }
    ?>
    <div class="form-group">
        <input type="text" class="form-control form-control-user" name="username" placeholder="Enter Username" required>
    </div>
    <div class="form-group">
        <input type="text" class="form-control form-control-user" name="nama_pengguna" placeholder="Enter Name" required> <!-- Kolom Nama -->
    </div>
    <div class="form-group">
        <input type="password" class="form-control form-control-user" name="password" placeholder="Enter Password" required>
    </div>
    <div class="form-group">
        <input type="password" class="form-control form-control-user" name="confirm_password" placeholder="Confirm Password" required>
    </div>

    <button type="submit" name="create_account" class="btn btn-primary btn-user btn-block">Create Account</button>
    <hr>
</form>
                                    <div class="text-center">
                                        <a class="small" href="login.php">Already have an account? Login!</a>
                                    </div>
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