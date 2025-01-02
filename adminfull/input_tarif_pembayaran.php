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
    $jenis_pembayaran = mysqli_real_escape_string($mysqli, $_POST['jenis_pembayaran']);
    $tarif = mysqli_real_escape_string($mysqli, $_POST['tarif']);  // Tarif sudah bersih
    $jenjang = mysqli_real_escape_string($mysqli, $_POST['jenjang']);
    $tahun_ajaran = mysqli_real_escape_string($mysqli, $_POST['tahun_ajaran']); // Added for year input

    // Insert data pembayaran ke tabel tarif_pembayaran sesuai dengan urutan kolom
    $stmt = $mysqli->prepare("INSERT INTO tarif_pembayaran (tahun_ajaran, jenjang, jenis_pembayaran, tarif, id_admin) 
                              VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        // Bind parameters
        $stmt->bind_param("sssss", $tahun_ajaran, $jenjang, $jenis_pembayaran, $tarif, $id_admin);

        // Eksekusi statement
        if ($stmt->execute()) {
            echo "<script>alert('Pembayaran berhasil dilakukan!'); window.location.href='input_tarif_pembayaran.php';</script>";
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

// Ambil data dari tabel kategori_pembayaran untuk tahun_ajaran, jenjang, dan jenis_pembayaran
$tahun_query = "SELECT DISTINCT tahun_ajaran FROM kategori_pembayaran ORDER BY tahun_ajaran";
$jenjang_query = "SELECT DISTINCT jenjang FROM kategori_pembayaran ORDER BY jenjang";
$jenis_query = "SELECT DISTINCT jenis_pembayaran FROM kategori_pembayaran ORDER BY jenis_pembayaran";

$tahun_result = mysqli_query($mysqli, $tahun_query);
$jenjang_result = mysqli_query($mysqli, $jenjang_query);
$jenis_result = mysqli_query($mysqli, $jenis_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom fonts for this template-->
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
    <h2>Input Biaya Perbulan</h2>
    <form action="" method="post" onsubmit="cleanTarif()">
        <!-- Tahun Ajaran -->
        <div class="mb-3">
            <label for="tahun_ajaran" class="form-label">Tahun Ajaran:</label>
            <select name="tahun_ajaran" id="tahun_ajaran" class="form-control" required>
                <option value="">Pilih Tahun Ajaran</option>
                <?php
                // Loop through the tahun_ajaran array and populate the dropdown
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
                // Loop through the jenjang array and populate the dropdown
                while ($row = mysqli_fetch_assoc($jenjang_result)) {
                    echo "<option value='" . $row['jenjang'] . "'>" . $row['jenjang'] . "</option>";
                }
                ?>
            </select>
        </div>

        <!-- Jenis Pembayaran -->
        <div class="mb-3">
            <label for="jenis_pembayaran" class="form-label">Jenis Pembayaran:</label>
            <select name="jenis_pembayaran" id="jenis_pembayaran" class="form-control" required>
                <option value="">Pilih Jenis Pembayaran</option>
                <?php
                // Loop through the jenis_pembayaran array and populate the dropdown
                while ($row = mysqli_fetch_assoc($jenis_result)) {
                    echo "<option value='" . $row['jenis_pembayaran'] . "'>" . $row['jenis_pembayaran'] . "</option>";
                }
                ?>
            </select>
        </div>

        <!-- Tarif -->
        <div class="mb-3">
            <label for="tarif" class="form-label">Tarif:</label>
            <input type="text" name="tarif" id="tarif" class="form-control" placeholder="Masukkan tarif" required oninput="formatTarif()">
        </div>

        <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
    </form>
</div>
<?php include "footer.php"; ?>
<script>
    // Function to format the tarif value with 'Rp.' and thousand separators (.)
    function formatTarif() {
        var input = document.getElementById('tarif');
        var value = input.value.replace(/\D/g, ''); // Remove all non-numeric characters
        var formattedValue = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // Add the thousand separator
        input.value = 'Rp. ' + formattedValue; // Add 'Rp.' at the beginning
    }

    // Function to clean 'Rp.' and '.' before sending the data to the server
    function cleanTarif() {
        var input = document.getElementById('tarif');
        var value = input.value.replace(/\D/g, ''); // Remove 'Rp.' and '.' characters
        input.value = value; // Update the input field with cleaned value
    }
</script>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/sb-admin-2.min.js"></script>
</body>
</html>
