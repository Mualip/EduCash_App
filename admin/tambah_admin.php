<?php
$pdo = include "koneksi.php";  // Koneksi database

// Contoh data pengguna
$username = 'admin';
$password = 'admin123';
$salt = bin2hex(random_bytes(16));  // Membuat salt acak
$hashedPassword = sha1($password . $salt);  // Meng-hash password dengan salt

// Menyisipkan pengguna ke dalam database
$query = $pdo->prepare("INSERT INTO users (username, password, salt, aktif) VALUES (:username, :password, :salt, 1)");
$query->execute(array(
    ':username' => $username,
    ':password' => $hashedPassword,
    ':salt' => $salt
));

echo "Pengguna berhasil ditambahkan!";
?>
