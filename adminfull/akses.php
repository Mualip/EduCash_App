<?php
// koneksi.php
$host = "localhost";           // Host database
$username = "root";            // Username database
$password = "";                // Password database (kosongkan jika tidak ada)
$database = "educash";         // Nama database

try {
    // Membuat koneksi dengan PDO
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password, array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ));
    // Jika koneksi berhasil, kembalikan objek PDO
    return $pdo;
} catch (PDOException $e) {
    // Tangani error koneksi dan tampilkan pesan kesalahan yang lebih jelas
    echo "Database connection failed: " . $e->getMessage();
    exit;  // Hentikan eksekusi jika koneksi gagal
}
?>
