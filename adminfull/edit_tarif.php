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


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tarif Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        th, td {
            text-align: center;
            vertical-align: middle;
        }
        .table thead th {
            background-color: #f8f9fa;
            text-align: center;
        }
        .table td, .table th {
            padding: 10px;
        }
    </style>
</head>
<body id="page-top">

<?php include "sidebar.php"; ?>
<?php include "navbar.php"; ?>

<div class="container">
    <h6 class="font-weight-bold text-primary">Edit Tarif Pembayaran</h6>
    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 20%;">Tahun Ajaran</th>
                <th style="width: 20%;">Jenjang</th>
                <th style="width: 25%;">Jenis Pembayaran</th>
                <th style="width: 20%;">Tarif</th>
                <th style="width: 10%;">Edit</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Ambil data tarif pembayaran
$query = "SELECT * FROM tarif_pembayaran ORDER BY tahun_ajaran DESC";
$result = mysqli_query($mysqli, $query);

            $no = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                // Pastikan tarif adalah angka yang benar tanpa simbol "Rp." dan titik
                $tarif = number_format($row['tarif'], 0, ',', '.'); // Memformat angka dengan titik sebagai pemisah ribuan
                echo "<tr>
                        <td>{$no}</td>
                        <td>{$row['tahun_ajaran']}</td>
                        <td>{$row['jenjang']}</td>
                        <td>{$row['jenis_pembayaran']}</td>
                        <td>Rp. {$tarif}</td>
                        <td><a href='edit_tarif_pembayaran.php?id={$row['id']}' class='btn btn-primary btn-sm'>Edit</a></td>
                      </tr>";
                $no++;
            }
            ?>
        </tbody>
    </table>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
