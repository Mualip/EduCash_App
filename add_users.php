<?php
// add_user.php

// Termasuk koneksi ke database
$pdo = include "koneksi.php";

// Data untuk pengguna baru
$username = 'admin';
$password = 'admin123'; // Ganti dengan password yang lebih kuat
$salt = bin2hex(random_bytes(16));  // Membuat salt acak
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);  // Hash password menggunakan bcrypt

// Menyisipkan pengguna ke dalam database
$query = $pdo->prepare("INSERT INTO users (username, password, salt, aktif) VALUES (:username, :password, :salt, 1)");
$query->execute(array(
    ':username' => $username,
    ':password' => $hashedPassword,
    ':salt' => $salt
));

echo "Pengguna berhasil ditambahkan!";
?>
