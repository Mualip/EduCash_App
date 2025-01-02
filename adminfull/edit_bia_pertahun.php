<?php

require 'function.php';
include 'koneksi.php';
session_start();

// Pastikan id_admin ada dalam session, jika tidak, redirect ke halaman login
if (!isset($_SESSION['id_admin'])) {
    header("Location: login.php");
    exit;
}

$id_admin = $_SESSION['id_admin'];  // Ambil id_admin dari session



// Cek apakah ada id yang diterima dari URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Ambil data berdasarkan id
    $query = "SELECT * FROM biaya_pertahun WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
    } else {
        echo "Data tidak ditemukan!";
        exit;
    }
}

// Proses edit data
if (isset($_POST['update'])) {
    $jenis_biaya = mysqli_real_escape_string($mysqli, $_POST['jenis_pembayaran']);
    $total_pertahun = mysqli_real_escape_string($mysqli, $_POST['total_pertahun']);
    $jenjang = mysqli_real_escape_string($mysqli, $_POST['jenjang']);
    $tahun_ajaran = mysqli_real_escape_string($mysqli, $_POST['tahun_ajaran']);

    // Hilangkan simbol 'Rp.' dan titik ribuan dari total_pertahun
    $total_pertahun = str_replace(['Rp. ', '.'], '', $total_pertahun);

    // Update data biaya pertahun
    $update_query = "UPDATE biaya_pertahun SET tahun_ajaran = ?, jenjang = ?, jenis_biaya = ?, total_pertahun = ? WHERE id = ?";
    $stmt = $mysqli->prepare($update_query);
    $stmt->bind_param("ssssi", $tahun_ajaran, $jenjang, $jenis_biaya, $total_pertahun, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Data berhasil diperbarui!'); window.location.href='daftar_biaya_pertahun.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Biaya Pertahun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body id="page-top">

<?php include "sidebar.php"; ?>
<?php include "navbar.php"; ?>
<div id="content-wrapper" class="d-flex flex-column">


    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="login.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

<div class="container">
    <h2>Edit Biaya Pertahun</h2>
    <form action="" method="post">
        <!-- Tahun Ajaran -->
        <div class="mb-3">
            <label for="tahun_ajaran" class="form-label">Tahun Ajaran:</label>
            <input type="text" name="tahun_ajaran" id="tahun_ajaran" class="form-control" value="<?= $data['tahun_ajaran']; ?>" required>
        </div>

        <!-- Jenjang -->
        <div class="mb-3">
            <label for="jenjang" class="form-label">Jenjang:</label>
            <input type="text" name="jenjang" id="jenjang" class="form-control" value="<?= $data['jenjang']; ?>" required>
        </div>

        <!-- Jenis Biaya -->
        <div class="mb-3">
            <label for="jenis_pembayaran" class="form-label">Jenis Biaya:</label>
            <input type="text" name="jenis_pembayaran" id="jenis_pembayaran" class="form-control" value="<?= $data['jenis_biaya']; ?>" required>
        </div>

        <!-- Total Pertahun -->
        <div class="mb-3">
            <label for="total_pertahun" class="form-label">Total Pertahun:</label>
            <input type="text" name="total_pertahun" id="total_pertahun" class="form-control" value="Rp. <?= number_format($data['total_pertahun'], 0, ',', '.'); ?>" required oninput="formatTarif()">
        </div>

        <button type="submit" name="update" class="btn btn-primary">Update</button>
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
