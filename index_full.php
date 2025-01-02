<?php
session_start();

// Pastikan hanya full_admin yang bisa mengakses
if ($_SESSION['role'] !== 'full_admin') {
    header('Location: login.php'); // Redirect ke halaman login jika bukan full_admin
    exit();
}

// Konten untuk full_admin
echo "Welcome Full Admin!";
?>
