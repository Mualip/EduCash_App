<?php
// koneksi.php
$host = "localhost";           // Host database
$username = "root";            // Username database
$password = "";                // Password database (kosongkan jika tidak ada)
$database = "educash";         // Nama database

// Membuat koneksi MySQLi
$mysqli = new mysqli($host, $username, $password, $database);

// Cek koneksi
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);  // Jika koneksi gagal, tampilkan pesan error
}
?>
