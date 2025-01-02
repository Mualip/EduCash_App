<?php
// function.php
require 'koneksi.php'; // Include database connection

// Function to search for students based on a keyword
function cari($keyword) {
    global $mysqli; // Use global mysqli connection
    $keyword = $mysqli->real_escape_string($keyword);
    $query = "SELECT * FROM siswa WHERE nama_siswa LIKE '%$keyword%' OR nis_siswa LIKE '%$keyword%'";
    $result = $mysqli->query($query);
    
    $siswa = [];
    while ($row = $result->fetch_assoc()) {
        $siswa[] = $row;
    }
    return $siswa;
}

// Function to add a student
function tambahSiswa($data) {
    global $mysqli; // Use the global $mysqli variable

    $sql = "INSERT INTO siswa (nis_siswa, nama_siswa, jenis_kelamin, nama_ayah, no_hp_ayah, nama_ibu, no_hp_ibu, alamat, kelas, keaktifan)
            VALUES ('{$data['nis_siswa']}', '{$data['nama_siswa']}', '{$data['jenis_kelamin']}', '{$data['nama_ayah']}', '{$data['no_hp_ayah']}', '{$data['nama_ibu']}', '{$data['no_hp_ibu']}', '{$data['alamat']}', '{$data['kelas']}', '{$data['keaktifan']}')";
    
    return $mysqli->query($sql); // Use $mysqli instead of $koneksi
}
?>