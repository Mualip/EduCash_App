<?php
include "koneksi.php";  // Pastikan koneksi ke database sudah benar

// Hash password menggunakan password_hash()
$hashed_password_full_admin = password_hash('password_saya', PASSWORD_DEFAULT);
$hashed_password_admin = password_hash('password_saya', PASSWORD_DEFAULT);

// Query untuk menambahkan data
$sql = "
INSERT INTO users (username, password, email, role) VALUES
('admin_full', '$hashed_password_full_admin', 'admin_full@example.com', 'full_admin'),
('admin_1', '$hashed_password_admin', 'admin_1@example.com', 'admin'),
('admin_2', '$hashed_password_admin', 'admin_2@example.com', 'admin'),
('admin_3', '$hashed_password_admin', 'admin_3@example.com', 'admin'),
('admin_4', '$hashed_password_admin', 'admin_4@example.com', 'admin');
";

// Menjalankan query
if ($mysqli->query($sql) === TRUE) {
    echo "Admin berhasil ditambahkan!";
} else {
    echo "Error: " . $sql . "<br>" . $mysqli->error;
}

$mysqli->close();
?>
