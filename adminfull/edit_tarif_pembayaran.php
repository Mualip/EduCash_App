<?php
include 'koneksi.php'; // Koneksi ke database

// Mengecek apakah parameter 'id' ada di URL untuk edit
if (isset($_GET['id'])) {
    // Ambil ID dari URL
    $id = $_GET['id'];

    // Ambil data berdasarkan ID
    $query = "SELECT * FROM tarif_pembayaran WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Cek jika data ditemukan
    if (!$row) {
        echo "Data tidak ditemukan.";
        exit;
    }
}

// Proses simpan data baru atau update data
if (isset($_POST['simpan'])) {
    // Ambil data dari form
    $jenis_pembayaran = mysqli_real_escape_string($mysqli, $_POST['jenis_pembayaran']);
    $tarif = mysqli_real_escape_string($mysqli, $_POST['tarif']);
    $jenjang = mysqli_real_escape_string($mysqli, $_POST['jenjang']);
    $tahun_ajaran = mysqli_real_escape_string($mysqli, $_POST['tahun_ajaran']);

    // Format tarif untuk menghilangkan 'Rp.' dan titik sebelum simpan
    $tarif = str_replace(['Rp. ', '.'], '', $tarif); // Menghapus 'Rp.' dan titik ribuan

    // Update data jika id ada
    if (isset($_GET['id'])) {
        $update_query = "UPDATE tarif_pembayaran SET tahun_ajaran = ?, jenjang = ?, jenis_pembayaran = ?, tarif = ? WHERE id = ?";
        $stmt = $mysqli->prepare($update_query);
        $stmt->bind_param("ssssi", $tahun_ajaran, $jenjang, $jenis_pembayaran, $tarif, $id);

        if ($stmt->execute()) {
            echo "<script>alert('Data berhasil diperbarui!'); window.location.href='edit_tarif_pembayaran.php?id=$id';</script>";
            exit;
        } else {
            echo "Error updating data: " . $stmt->error;
        }

        // Tutup statement
        $stmt->close();
    }
}

// Ambil data untuk dropdown
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
    <title>Edit Tarif Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <h2>Edit Tarif Pembayaran</h2>
    <form action="" method="post">
        <!-- Tahun Ajaran -->
        <div class="mb-3">
            <label for="tahun_ajaran" class="form-label">Tahun Ajaran:</label>
            <select name="tahun_ajaran" id="tahun_ajaran" class="form-control" required>
                <option value="">Pilih Tahun Ajaran</option>
                <?php
                // Loop through the tahun_ajaran array and populate the dropdown
                while ($t_row = mysqli_fetch_assoc($tahun_result)) {
                    $selected = ($t_row['tahun_ajaran'] == $row['tahun_ajaran']) ? 'selected' : '';
                    echo "<option value='" . $t_row['tahun_ajaran'] . "' $selected>" . $t_row['tahun_ajaran'] . "</option>";
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
                while ($j_row = mysqli_fetch_assoc($jenjang_result)) {
                    $selected = ($j_row['jenjang'] == $row['jenjang']) ? 'selected' : '';
                    echo "<option value='" . $j_row['jenjang'] . "' $selected>" . $j_row['jenjang'] . "</option>";
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
                while ($p_row = mysqli_fetch_assoc($jenis_result)) {
                    $selected = ($p_row['jenis_pembayaran'] == $row['jenis_pembayaran']) ? 'selected' : '';
                    echo "<option value='" . $p_row['jenis_pembayaran'] . "' $selected>" . $p_row['jenis_pembayaran'] . "</option>";
                }
                ?>
            </select>
        </div>

        <!-- Tarif -->
        <div class="mb-3">
            <label for="tarif" class="form-label">Tarif:</label>
            <input type="text" name="tarif" id="tarif" class="form-control" value="<?= number_format($row['tarif'], 0, ',', '.') ?>" required oninput="formatTarif()">
        </div>

        <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
    </form>
</div>

<script>
    // Format tarif input (remove 'Rp.' and format with commas)
    function formatTarif() {
        var input = document.getElementById('tarif');
        var value = input.value.replace(/\D/g, ''); // Remove non-numeric characters
        var formattedValue = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // Add thousand separator
        input.value = formattedValue; // Only format the value without 'Rp.'
    }
</script>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
