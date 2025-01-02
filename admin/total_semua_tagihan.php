<?php
include 'koneksi.php'; // Koneksi ke database


// Menampilkan total_pertahun per jenjang jika jenjang dipilih
$total_semua_tagihan = 0; // Default value for total_semua_tagihan

// Menampilkan total semua tagihan untuk setiap jenjang dan tahun ajaran
$query_all_biaya = "SELECT jenjang, tahun_ajaran, SUM(total_pertahun) AS total_semua_tagihan
                    FROM biaya_pertahun 
                    GROUP BY jenjang, tahun_ajaran";
$result_all_biaya = mysqli_query($mysqli, $query_all_biaya);
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

        .table th, .table td {
            text-align: center;
        }
    </style>
</head>
<body id="page-top">

<?php include "sidebar.php"; ?>
<?php include "navbar.php"; ?>

<div class="container">
 
    <!-- Tabel Biaya Pertahun -->
    <h3 >Total Semua Biaya Pertahun</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Tahun Ajaran</th>
                <th>Jenjang</th>
                <th>Total Semua Tagihan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Menampilkan total semua tagihan untuk setiap jenjang dan tahun ajaran
            $query_all_biaya = "SELECT jenjang, tahun_ajaran, SUM(total_pertahun) AS total_semua_tagihan
                                FROM biaya_pertahun 
                                GROUP BY jenjang, tahun_ajaran";
            $result_all_biaya = mysqli_query($mysqli, $query_all_biaya);
            $no = 1;

            while ($biaya = mysqli_fetch_assoc($result_all_biaya)) {
                echo "<tr>";
                echo "<td>" . $no++ . "</td>";
                echo "<td>" . $biaya['tahun_ajaran'] . "</td>";
                echo "<td>" . $biaya['jenjang'] . "</td>";
                echo "<td>Rp. " . number_format($biaya['total_semua_tagihan'], 0, ',', '.') . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
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
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/sb-admin-2.min.js"></script>

</body>
</html>
