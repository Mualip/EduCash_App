<?php
session_start(); // Mulai sesi untuk mengambil id_admin
include 'koneksi.php'; // Koneksi ke database

// Pastikan admin sudah login
if (!isset($_SESSION['id_admin'])) {
    echo "Anda harus login terlebih dahulu.";
    exit;
}

// Ambil id_admin dari sesi
$id_admin = $_SESSION['id_admin'];

// Direktori tempat gambar disimpan, gunakan path absolut
$upload_dir = __DIR__ . '/uploads/';

// Pastikan direktori uploads ada dan bisa diakses
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);  // Membuat direktori jika belum ada
}

// Jika tombol simpan diklik
if (isset($_POST['simpan'])) {
    // Ambil data dari form
    $nama_sekolah = mysqli_real_escape_string($mysqli, $_POST['nama_sekolah']);
    $alamat = mysqli_real_escape_string($mysqli, $_POST['alamat']);

    // Inisialisasi variabel logo
    $logo = NULL;

    // Jika logo di-upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        // Membaca ekstensi file gambar
        $file_tmp = $_FILES['logo']['tmp_name'];
        $file_name = $_FILES['logo']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Membuat nama file unik
        $new_file_name = uniqid() . '.' . $file_ext;

        // Tentukan path untuk menyimpan file
        $target_file = $upload_dir . $new_file_name;

        // Validasi ekstensi file
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_ext, $allowed_types)) {
            echo "Hanya file gambar yang diperbolehkan (JPG, JPEG, PNG, GIF).";
            exit;
        }

        // Validasi ukuran file (misalnya 2MB maksimal)
        if ($_FILES['logo']['size'] > 2 * 1024 * 1024) {  // 2MB
            echo "Ukuran file logo terlalu besar. Maksimal 2MB.";
            exit;
        }

        // Pindahkan file ke direktori yang telah ditentukan
        if (move_uploaded_file($file_tmp, $target_file)) {
            $logo = 'uploads/' . $new_file_name; // Menyimpan path file logo relatif
        } else {
            echo "Terjadi kesalahan saat meng-upload logo.";
            exit;
        }
    }

    // Query SQL untuk insert data ke tabel profil_sekolah
    $sql = "INSERT INTO profil_sekolah (id_admin, nama_sekolah, alamat, logo) VALUES (?, ?, ?, ?)";

    if ($stmt = $mysqli->prepare($sql)) {
        // Binding parameter
        $stmt->bind_param("isss", $id_admin, $nama_sekolah, $alamat, $logo);  // Menggunakan kolom yang ada

        // Eksekusi query
        if ($stmt->execute()) {
            echo "<script>alert('Profil sekolah berhasil disimpan!'); window.location.href='input_profil_sekolah.php';</script>";
        } else {
            echo "Error inserting data: " . $stmt->error;
        }

        // Tutup prepared statement
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $mysqli->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Profil Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include "sidebar.php"; ?>
    <?php include "navbar.php"; ?>
    
    <div class="container mt-5">
        <h2>Input Profil Sekolah</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nama_sekolah" class="form-label">Nama Sekolah:</label>
                <input type="text" name="nama_sekolah" id="nama_sekolah" class="form-control" placeholder="Masukkan nama sekolah" required>
            </div>
            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat:</label>
                <input type="text" name="alamat" id="alamat" class="form-control" placeholder="Masukkan alamat sekolah" required>
            </div>
            <div class="mb-3">
                <label for="logo" class="form-label">Logo Sekolah:</label>
                <input type="file" name="logo" id="logo" class="form-control" accept=".jpg, .jpeg, .png, .gif" required>
            </div>

            <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
        </form>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
