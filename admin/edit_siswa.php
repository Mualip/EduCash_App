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

// Cek apakah nis_siswa ada di URL
if (isset($_GET['nis_siswa'])) {
    $nis_siswa = $_GET['nis_siswa'];

    // Query untuk mengambil data siswa berdasarkan nis_siswa
    $query = "SELECT * FROM siswa WHERE nis_siswa = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $nis_siswa);
    $stmt->execute();
    $result = $stmt->get_result();
    $siswa = $result->fetch_assoc();

    // Jika siswa tidak ditemukan
    if (!$siswa) {
        die("Siswa tidak ditemukan.");
    }
} else {
    die("NIS Siswa tidak ditemukan.");
}

// Proses ketika form disubmit untuk update data
if (isset($_POST['update_siswa'])) {
    // Menangkap data dari form
    $new_nama = $_POST['nama_siswa'];
    $new_password = $_POST['password'];

    // Validasi password jika diubah
    if ($new_password !== '') {
        // Hash password baru jika diubah
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    } else {
        // Jika password kosong, tidak perlu update password
        $hashed_password = $siswa['password'];
    }

    // Query untuk update data siswa
    $update_query = "UPDATE siswa SET nama_siswa = ?, password = ? WHERE nis_siswa = ?";
    $update_stmt = $mysqli->prepare($update_query);
    $update_stmt->bind_param("ssi", $new_nama, $hashed_password, $siswa['nis_siswa']);

    if ($update_stmt->execute()) {
        // Jika berhasil, redirect ke halaman perbaikan_siswa.php
        $success_message = "Data siswa berhasil diperbarui.";
        header("Location: perbaikan_siswa.php"); // Ganti dengan halaman yang sesuai
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
    <title>Edit Data Siswa</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body>
<?php include "sidebar.php"; ?>
<div class="container mt-5">
    <h2>Edit Data Siswa</h2>

    <?php
    if (isset($error_message)) {
        echo '<div class="alert alert-danger">' . $error_message . '</div>';
    }
    if (isset($success_message)) {
        echo '<div class="alert alert-success">' . $success_message . '</div>';
    }
    ?>

    <!-- Form Edit Siswa -->
    <form method="POST">
        <div class="form-group">
            <label for="nama_siswa">Nama Siswa:</label>
            <!-- Tambahkan readonly agar nama tidak bisa diubah -->
            <input type="text" class="form-control" name="nama_siswa" value="<?php echo $siswa['nama_siswa']; ?>" required readonly>
        </div>
        <div class="form-group">
            <label for="password">Password (kosongkan jika tidak ingin diubah):</label>
            <input type="password" class="form-control" name="password">
        </div>
        <div class="form-group d-flex justify-content-start">
            <button type="submit" name="update_siswa" class="btn btn-primary mr-2">Update</button>
            <a href="perbaikan_siswa.php" class="btn btn-success">Kembali</a>
        </div>
    </form>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/sb-admin-2.min.js"></script>

</body>
</html>
