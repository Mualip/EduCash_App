<?php
session_start();

// Hapus session
session_unset();
session_destroy();

// Hapus cookie logged_in
setcookie('logged_in', '', time() - 3600, "/");

// Arahkan pengguna ke halaman login setelah logout
header('Location: login.php');
exit();
?>