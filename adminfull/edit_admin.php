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

// Cek apakah id_admin ada di URL
if (isset($_GET['id_admin'])) {
    $id_admin = $_GET['id_admin'];

    // Query untuk mengambil data pengguna berdasarkan id_admin
    $query = "SELECT * FROM users WHERE id_admin = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id_admin);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Jika pengguna tidak ditemukan
    if (!$user) {
        die("User tidak ditemukan.");
    }
} else {
    die("ID admin tidak ditemukan.");
}

// Proses ketika form disubmit untuk update data
if (isset($_POST['update_user'])) {
    // Menangkap data dari form
    $new_username = $_POST['username'];
    $new_password = $_POST['password'];
    $new_active_status = $_POST['aktif'];

    // Validasi password jika diubah
    if ($new_password !== '') {
        // Hash password baru jika diubah
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    } else {
        // Jika password kosong, tidak perlu update password
        $hashed_password = $user['password'];
    }

    // Query untuk update data pengguna
    $update_query = "UPDATE users SET username = ?, password = ?, aktif = ? WHERE id_admin = ?";
    $update_stmt = $mysqli->prepare($update_query);
    $update_stmt->bind_param("ssii", $new_username, $hashed_password, $new_active_status, $user['id_admin']);

    if ($update_stmt->execute()) {
        // Jika berhasil, redirect ke halaman perbaikan_admin.php
        $success_message = "Data berhasil diperbarui.";
        header("Location: perbaikan_admin.php"); // Ganti dengan halaman yang sesuai
        exit();
    } else {
        // Jika ada error saat update
        $error_message = "Terjadi kesalahan, coba lagi.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Edit User - Admin</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body>
<?php include "sidebar.php"; ?>
<div class="container mt-5">
    <h2>Update Akun Admin</h2>

    <?php
    if (isset($error_message)) {
        echo '<div class="alert alert-danger">' . $error_message . '</div>';
    }
    if (isset($success_message)) {
        echo '<div class="alert alert-success">' . $success_message . '</div>';
    }
    ?>

    <!-- Form Edit User -->
    <form method="POST">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" class="form-control" name="username" value="<?php echo $user['username']; ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password (kosongkan jika tidak ingin diubah):</label>
            <input type="password" class="form-control" name="password">
        </div>
        <div class="form-group">
            <label for="aktif">Status Aktif:</label>
            <!-- Mengganti dropdown dengan input type number -->
            <input type="number" class="form-control" name="aktif" value="<?php echo $user['aktif']; ?>" required>
        </div>
        <div class="form-group d-flex justify-content-start">
            <button type="submit" name="update_user" class="btn btn-primary mr-2">Update</button>
            <a href="perbaikan_admin.php" class="btn btn-success">Kembali</a>
        </div>
    </form>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/sb-admin-2.min.js"></script>

</body>
</html>
