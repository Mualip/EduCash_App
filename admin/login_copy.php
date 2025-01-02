<?php
session_start();
include('koneksi.php'); // Menyertakan file konfigurasi untuk koneksi database

// Menangani login
if (isset($_POST['loginbtn'])) {
    // Mendapatkan data dari form login
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query untuk mendapatkan user berdasarkan username
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Cek apakah username ditemukan
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Jika password cocok, set session dan alihkan ke halaman dashboard
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id'] = $user['id']; // atau field lainnya

            // Mengecek apakah pengguna memilih "Remember Me"
            if (isset($_POST['remember_me']) && $_POST['remember_me'] == 'on') {
                // Set cookie untuk "Remember Me" selama 2 menit (120 detik)
                setcookie('username', $user['username'], time() + 60, "/"); // Cookie selama 2 menit
                setcookie('user_id', $user['id'], time() + 60, "/"); // Cookie selama 2 menit
            } else {
                // Jika "Remember Me" tidak dipilih, pastikan cookie tidak ada
                if (isset($_COOKIE['username'])) {
                    setcookie('username', '', time() - 3600, "/");
                    setcookie('user_id', '', time() - 3600, "/");
                }
            }

            // Redirect ke halaman index.php
            header('Location: index.php');
            exit();
        } else {
            // Jika password tidak cocok
            $error_msg = "Invalid username or password.";
        }
    } else {
        // Jika username tidak ditemukan
        $error_msg = "Invalid username or password.";
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
    <title>Admin Login</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        
        .bg-login-image {
            background-image: url('../img/yadin.jpg');
            background-size: cover;
            background-position: center;
            height: 55vh;
        }
    </style>
</head>

<body class="bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-15 col-md-15">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                    </div>
                                    <!-- Form Login -->
                                    <form class="user" method="POST" action="login.php">
                                        <?php
                                        // Menampilkan pesan error jika ada
                                        if (isset($error_msg)) {
                                            echo '<div class="alert alert-danger">' . $error_msg . '</div>';
                                        }
                                        ?>
                                        <div class="form-group">
                                            <input type="text" class="form-control form-control-user" id="username" name="username"
                                                placeholder="Enter Username..." required>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user" id="password" name="password"
                                                placeholder="Password" required>
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="customCheck" name="remember_me">
                                                <label class="custom-control-label" for="customCheck">Remember Me</label>
                                            </div>
                                        </div>
                                        <button type="submit" name="loginbtn" class="btn btn-primary btn-user btn-block">Login</button>
                                        <hr>
                                    </form>
                                    <div class="text-center">
                                        <a class="small" href="forgot_password.php">Forgot Password?</a>
                                    </div>
                                    <div class="text-center">
                                        <a class="small" href="register.php">Create an Account!</a>
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
