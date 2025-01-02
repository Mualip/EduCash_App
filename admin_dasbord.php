<?php
session_start();

// Pastikan hanya admin biasa yang bisa mengakses
if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php'); // Redirect ke halaman login jika bukan admin
    exit();
}

// Konten untuk admin biasa
echo "Welcome Admin!";
?>
