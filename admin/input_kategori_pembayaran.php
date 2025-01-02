<?php
include 'koneksi.php'; // Koneksi ke database
session_start();

// Pastikan admin sudah login
if (!isset($_SESSION['id_admin'])) {
    header('Location: login.php');
    exit;
}

$id_admin = $_SESSION['id_admin']; // Ambil id_admin dari session

if (isset($_POST['simpan'])) {
    // Ambil data dari form
    $tahun_ajaran = mysqli_real_escape_string($mysqli, $_POST['tahun_ajaran']);
    $jenjang = mysqli_real_escape_string($mysqli, $_POST['jenjang_manual']); // Mengambil input manual jenjang
    $kelas = mysqli_real_escape_string($mysqli, $_POST['kelas']); // Mengambil input manual jenjang
    $jenis_pembayaran = mysqli_real_escape_string($mysqli, $_POST['jenis_pembayaran_manual']); // Mengambil input manual jenis pembayaran

    // Insert data pembayaran ke tabel kategori_pembayaran
    $stmt = $mysqli->prepare("INSERT INTO kategori_pembayaran (tahun_ajaran, jenjang, kelas, jenis_pembayaran, id_admin) 
                              VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        // Bind parameters
        $stmt->bind_param("sssss", $tahun_ajaran, $jenjang, $kelas, $jenis_pembayaran, $id_admin);

        // Eksekusi statement
        if ($stmt->execute()) {
            echo "<script>alert('Kategori Pembayaran berhasil ditambahkan!'); window.location.href='input_kategori_pembayaran.php';</script>";
            exit;
        } else {
            echo "Error inserting data: " . $stmt->error;
        }

        // Tutup statement
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $mysqli->error;
    }
}

// Generate dynamic academic year (Tahun Ajaran) starting from 2018-2019
$current_year = 2018;  // Mulai dari tahun 2018
$years = [];
for ($i = 0; $i < 10; $i++) {
    $start_year = $current_year + $i;
    $end_year = $start_year + 1;
    $years[] = "$start_year-$end_year";  // Format as "yyyy-yyyy"
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Kategori Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
    /* Global Style */
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f8f9fa;
        color: #333;
        margin: 0;
        padding: 0;
    }

    /* Kontainer Utama */
    .container {
        background-color: white;
        padding: 10px;
        /* Menambah ruang di dalam kontainer */
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);

    }

    /* Judul Form */
    h2 {
        font-size: 24px;
        margin-bottom: 15px;
        /* Memberikan jarak yang lebih besar antara judul dan form */
        text-align: left;
        color: #333;
    }

    /* Label Form */
    .form-label {
        font-size: 16px;
        margin-bottom: 5px;
        /* Memberikan jarak dengan input field */
        display: inline-block;
    }

    /* Input, Select, dan Textarea */
    .form-control {
        font-size: 14px;

        width: 100%;
        /* Memastikan elemen input memanfaatkan seluruh lebar kontainer */
        /* Menambahkan jarak bawah antara elemen form */
        border: 1px solid #ddd;
        /* Memberikan border ringan */
        border-radius: 4px;
        /* Membulatkan sudut input */
    }

    /* Tombol (Button) */
    .btn {
        font-size: 16px;
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn:hover {
        background-color: #0056b3;
        /* Mengubah warna saat hover */
    }

    /* Textarea */
    textarea.form-control {
        resize: vertical;
        /* Membuat textarea dapat di-resize secara vertikal */
    }

    /* Menata Form agar lebih dekat dengan Navbar */
    .form-group {
        margin-bottom: 20px;
        /* Mengatur jarak antara setiap elemen form */
    }

    /* Responsif untuk tampilan kecil */
    @media (max-width: 768px) {
        .container {
            padding: 1px;
            /* Mengurangi padding pada layar kecil */
        }

        .form-label,
        .form-control {
            font-size: 14px;
            /* Memperkecil ukuran font untuk perangkat kecil */
        }

        .btn {
            font-size: 14px;
            /* Mengurangi ukuran tombol di perangkat kecil */
        }
    }
    </style>
</head>

<body>

    <?php include "sidebar.php"; ?>
    <?php include "navbar.php"; ?>
    <div class="container">
        <h2>Input Kategori Pembayaran</h2>
        <form action="" method="post">
    <!-- Tahun Ajaran -->
    <div class="form-group">
        <label for="tahun_ajaran" class="form-label">Tahun Ajaran:</label>
        <select name="tahun_ajaran" id="tahun_ajaran" class="form-control" required>
            <option value="">Pilih Tahun Ajaran</option>
            <?php foreach ($years as $year): ?>
                <option value="<?= $year ?>"><?= $year ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Jenjang Manual -->
    <div class="mb-3">
        <label for="jenjang_manual" class="form-label">Jenjang :</label>
        <input type="text" name="jenjang_manual" id="jenjang_manual" class="form-control"
               placeholder="Masukkan Jenjang Baru" required>
    </div>
    <div class="mb-3">
        <label for="kelas" class="form-label">Kelas :</label>
        <input type="text" name="kelas" id="kelas" class="form-control" placeholder="Masukkan kelas Baru"
               required>
    </div>

    <!-- Jenis Pembayaran Manual -->
    <div class="mb-3">
        <label for="jenis_pembayaran_manual" class="form-label">Jenis Pembayaran :</label>
        <input type="text" name="jenis_pembayaran_manual" id="jenis_pembayaran_manual" class="form-control"
               placeholder="Masukkan Jenis Pembayaran Baru" required>
    </div>

    <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
</form>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
</body>

</html>