<?php
session_start();
include 'koneksi.php';

// Cek apakah token ada di URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Cek token di database
    $query = "SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW() LIMIT 1";
    $stmt = $mysqli->prepare($query);
    if ($stmt === false) {
        die("Gagal mempersiapkan query: " . $mysqli->error);
    }
    $stmt->bind_param('s', $token);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Token valid dan belum kadaluarsa, tampilkan form reset password
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);  // Hash password baru

            // Update password di database
            $query_update = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id_admin = ?";
            $stmt_update = $mysqli->prepare($query_update);
            if ($stmt_update === false) {
                die("Gagal mempersiapkan query: " . $mysqli->error);
            }
            $stmt_update->bind_param('si', $new_password, $user['id_admin']);
            $stmt_update->execute();

            // Redirect atau pesan sukses
            $success_msg = "Password berhasil direset. Anda dapat login dengan password baru.";
        }
    } else {
        $error_msg = "Token tidak valid atau sudah kadaluarsa.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body>

<?php
if (isset($success_msg)) {
    echo "<div>$success_msg</div>";
} elseif (isset($error_msg)) {
    echo "<div>$error_msg</div>";
}
?>

<?php if (isset($user)) { ?>
    <h2>Reset Password</h2>
    <form method="POST">
        <div>
            <label for="new_password">New Password</label>
            <input type="password" name="new_password" required>
        </div>
        <div>
            <button type="submit">Reset Password</button>
        </div>
    </form>
<?php } ?>

</body>
</html>
