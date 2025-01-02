<?php
include 'koneksi.php'; // Koneksi ke database

// Mulai sesi untuk mendapatkan id_admin yang sedang login
session_start();

// Memastikan hanya admin yang bisa mengakses halaman ini
if (!isset($_SESSION['id_admin'])) {
    header('Location: login.php'); // Redirect ke halaman login jika tidak ada id_admin di session
    exit;
}

// Mengambil id_admin dari session
$id_admin = $_SESSION['id_admin'];

if (isset($_POST['simpan'])) {
    // Ambil data dari form
    $jenis_biaya = mysqli_real_escape_string($mysqli, $_POST['jenis_pembayaran']);
    $total_pertahun = mysqli_real_escape_string($mysqli, $_POST['total_pertahun']);
    $jenjang = mysqli_real_escape_string($mysqli, $_POST['jenjang']);
    $tahun_ajaran = mysqli_real_escape_string($mysqli, $_POST['tahun_ajaran']);

    // Hilangkan simbol 'Rp.' dan titik ribuan dari total_pertahun
    $total_pertahun = str_replace(['Rp. ', '.'], '', $total_pertahun); // Menghapus 'Rp.' dan titik ribuan

    // Pastikan total_semua_tagihan mendapat nilai (misalnya 0)
    $total_semua_tagihan = 0; // Menetapkan default 0 pada kolom total_semua_tagihan

    // Insert data pembayaran ke tabel biaya_pertahun
    $stmt = $mysqli->prepare("INSERT INTO biaya_pertahun (tahun_ajaran, jenjang, jenis_biaya, total_pertahun, total_semua_tagihan, id_admin) 
                             VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        // Bind parameters
        $stmt->bind_param("ssssss", $tahun_ajaran, $jenjang, $jenis_biaya, $total_pertahun, $total_semua_tagihan, $id_admin);

        // Eksekusi statement
        if ($stmt->execute()) {
            echo "<script>alert('Pembayaran berhasil dilakukan!'); window.location.href='input_biaya_pertahun.php';</script>";
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

// Ambil data untuk dropdown
$tahun_query = "SELECT DISTINCT tahun_ajaran FROM kategori_pembayaran ORDER BY tahun_ajaran";
$jenjang_query = "SELECT DISTINCT jenjang FROM kategori_pembayaran ORDER BY jenjang";
$jenis_query = "SELECT DISTINCT jenis_pembayaran FROM kategori_pembayaran ORDER BY jenis_pembayaran";

// Ambil data untuk dropdown
$tahun_result = mysqli_query($mysqli, $tahun_query);
$jenjang_result = mysqli_query($mysqli, $jenjang_query);
$jenis_result = mysqli_query($mysqli, $jenis_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Biaya Pertahun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 2px;
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
    </style>
</head>
<body id="page-top">

<?php include "sidebar.php"; ?>
<?php include "navbar.php"; ?>

<div class="container">
    <h2>Input Biaya Pertahun</h2>
    <form action="" method="post">
        <!-- Tahun Ajaran -->
        <div class="mb-3">
            <label for="tahun_ajaran" class="form-label">Tahun Ajaran:</label>
            <select name="tahun_ajaran" id="tahun_ajaran" class="form-control" required>
                <option value="">Pilih Tahun Ajaran</option>
                <?php
                while ($row = mysqli_fetch_assoc($tahun_result)) {
                    echo "<option value='" . $row['tahun_ajaran'] . "'>" . $row['tahun_ajaran'] . "</option>";
                }
                ?>
            </select>
        </div>

        <!-- Jenjang -->
        <div class="mb-3">
            <label for="jenjang" class="form-label">Jenjang:</label>
            <select name="jenjang" id="jenjang" class="form-control" required>
                <option value="">Pilih Jenjang</option>
                <?php
                while ($row = mysqli_fetch_assoc($jenjang_result)) {
                    echo "<option value='" . $row['jenjang'] . "'>" . $row['jenjang'] . "</option>";
                }
                ?>
            </select>
        </div>

        <!-- Jenis Biaya -->
        <div class="mb-3">
            <label for="jenis_pembayaran" class="form-label">Jenis Biaya:</label>
            <select name="jenis_pembayaran" id="jenis_pembayaran" class="form-control" required>
                <option value="">Pilih Jenis Pembayaran</option>
                <?php
                while ($row = mysqli_fetch_assoc($jenis_result)) {
                    echo "<option value='" . $row['jenis_pembayaran'] . "'>" . $row['jenis_pembayaran'] . "</option>";
                }
                ?>
            </select>
        </div>

        <!-- Total Pertahun -->
        <div class="mb-3">
            <label for="total_pertahun" class="form-label">Total Pertahun:</label>
            <input type="text" name="total_pertahun" id="total_pertahun" class="form-control" placeholder="Masukkan tarif" required oninput="formatTarif()">
        </div>

        <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
    </form>
</div>

<script>
// Function to format the total_pertahun value with 'Rp.' and thousand separators (.)
function formatTarif() {
    var input = document.getElementById('total_pertahun');
    var value = input.value.replace(/\D/g, ''); // Remove all non-numeric characters
    var formattedValue = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // Add the thousand separator
    input.value = 'Rp. ' + formattedValue; // Add 'Rp.' at the beginning
}
</script>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>  
</body>
</html>
