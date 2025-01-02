<?php
// Include koneksi ke database
include 'koneksi.php';

// Cek jika form sudah disubmit
if (isset($_POST['submit'])) {
    $email = $_POST['email'];  // Ambil email dari form
    $old_password = $_POST['old_password'];  // Ambil password lama dari form
    $new_password = $_POST['new_password'];  // Ambil password baru dari form

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email tidak valid.";
    } else {
        // Query untuk mencari pengguna berdasarkan email
        $query = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $email);  // Menggunakan email untuk pencarian
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Jika email ditemukan, cek password lama
            $user = $result->fetch_assoc();

            // Verifikasi password lama
            if (password_verify($old_password, $user['password'])) {
                // Password lama cocok, update password baru
                $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);

                // Query untuk update password
                $update_query = "UPDATE users SET password = ? WHERE email = ?";
                $update_stmt = $mysqli->prepare($update_query);
                $update_stmt->bind_param("ss", $new_password_hashed, $email);
                if ($update_stmt->execute()) {
                    $message = "Password berhasil diubah.";
                } else {
                    $message = "Terjadi kesalahan saat mengubah password.";
                }
            } else {
                $message = "Password lama yang Anda masukkan salah.";
            }
        } else {
            $message = "Email tidak ditemukan.";
        }
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

    <title>Change Password</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
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
                            <div class="col-lg-6 d-none d-lg-block bg-password-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-2">Change Your Password</h1>
                                        <p class="mb-4">Please enter your email, old password and new password to update your password.</p>
                                    </div>

                                    <!-- Form Change Password -->
                                    <form class="user" method="POST" action="">
                                        <?php
                                        if (isset($message)) {
                                            echo '<div class="alert alert-info">' . $message . '</div>';
                                        }
                                        ?>
                                        <div class="form-group">
                                            <input type="email" class="form-control form-control-user" name="email" placeholder="Enter Email Address" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user" name="old_password" placeholder="Enter Old Password" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user" name="new_password" placeholder="Enter New Password" required>
                                        </div>
                                        <button type="submit" name="submit" class="btn btn-primary btn-user btn-block">Change Password</button>
                                    </form>
                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="login.php">Back to Login</a>
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
