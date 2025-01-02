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


// Ambil data biaya pertahun


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Biaya Pertahun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Menambahkan gaya untuk memperkecil kolom agar lebih rapi */
        th, td {
            text-align: center;
            vertical-align: middle;
        }
        .table thead th {
            background-color: #f8f9fa;
            text-align: center;
        }
        .table td, .table th {
            padding: 3px;
        }
    </style>
</head>
<body id="page-top">

<?php include "sidebar.php"; ?>
<?php include "navbar.php"; ?>
<div id="content-wrapper" class="d-flex flex-column">

    
<div class="container">
    <h6 class="font-weight-bold text-primary">Edit Kategori Pembayaran</h6>
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">No</th>
                    <th class="text-center" style="width: 15%;">Tahun Ajaran</th>
                    <th class="text-center" style="width: 15%;">Jenjang</th>
                    <th class="text-center" style="width: 25%;">Jenis Pembayaran</th>
                    <th class="text-center" style="width: 25%;">Total Pertahun</th>
                    <th class="text-center" style="width: 10%;">Edit</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM biaya_pertahun ORDER BY tahun_ajaran DESC";
                $result = mysqli_query($mysqli, $query);
                $no = 1;
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td class='text-center'>{$no}</td>
                            <td class='text-center'>{$row['tahun_ajaran']}</td>
                            <td class='text-center'>{$row['jenjang']}</td>
                            <td class='text-center'>{$row['jenis_biaya']}</td>
                            <td class='text-center'>Rp. " . number_format($row['total_pertahun'], 0, ',', '.') . "</td>
                            <td class='text-center'><a href='edit_bia_pertahun.php?id={$row['id']}' class='btn btn-primary btn-sm'>Edit</a></td>
                          </tr>";
                    $no++;
                }
                ?>
            </tbody>
        </table>
    </div>
</div>


<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
