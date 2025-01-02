<?php
require 'function.php'; // Include your functions

// Initialize variables
$siswa = []; // To store student data

// Search functionality
if (isset($_GET["keyword"])) {
    $keyword = $_GET["keyword"];
    $siswa = cari($keyword); // Call search function
} else {
    // Fetch all students if no keyword is provided
    $siswa = cari(''); // Call function to fetch all students
}

// Adding student data
if (isset($_POST['simpan'])) {
    $data = [
        'nis_siswa' => htmlspecialchars($_POST['nis_siswa']),
        'nama_siswa' => htmlspecialchars($_POST['nama_siswa']),
        'jenis_kelamin' => htmlspecialchars($_POST['jenis_kelamin']),
        'nama_orangtua' => htmlspecialchars($_POST['nama_orangtua']),
        'alamat' => htmlspecialchars($_POST['alamat']),
        'no_hp' => htmlspecialchars($_POST['no_hp']),
        'kelas' => htmlspecialchars($_POST['kelas']),
        'keaktifan' => htmlspecialchars($_POST['keaktifan'])
    ];

    if (tambahSiswa($data)) {
        header("Location: index.php"); // Redirect after success
        exit;
    } else {
        echo '<div class="alert alert-danger mt-3" role="alert">Error adding student.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Daftar Siswa</h1>
       
        <a href="tambah.php" class="btn btn-success mb-3">TAMBAH DATA SISWA</a>
        
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr class="text-center">
                        <th>No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Jenis Kelamin</th>
                        <th>Nama Orang Tua</th>
                        <th>Alamat</th>
                        <th>No Hp</th>
                        <th>Kelas</th>
                        <th>Status</th>
                        <th>Aksi</th> <!-- New column for actions -->
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; ?>
                    <?php foreach ($siswa as $row): ?>
                        <tr>
                            <td class="text-center"><?= $i; ?></td>
                            <td class="text-center"><?= $row["nis_siswa"]; ?></td>
                            <td><?= $row["nama_siswa"]; ?></td>
                            <td><?= $row["jenis_kelamin"]; ?></td>
                            <td><?= $row["nama_orangtua"]; ?></td>
                            <td><?= $row["alamat"]; ?></td>
                            <td><?= $row["no_hp"]; ?></td>
                            <td><?= $row["kelas"]; ?></td>
                            <td><?= $row["keaktifan"]; ?></td>
                            <td class="text-center">
                                <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a> <!-- Edit button -->
                            </td>
                        </tr>
                        <?php $i++; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>


        <div class="form-group">
            <label for="kelas" class="form-label">Pilih Kelas :</label>
            <select name="kelas" id="kelas" class="form-control">
                <option value="semua_kelas" <?php echo ($kelas == 'semua_kelas' ? 'selected' : ''); ?>>Semua Kelas</option>
                <option value="Kelas 1" <?php echo ($kelas == 'Kelas 1' ? 'selected' : ''); ?>>Kelas 1</option>
                <option value="Kelas 2" <?php echo ($kelas == 'Kelas 2' ? 'selected' : ''); ?>>Kelas 2</option>
                <option value="Kelas 3" <?php echo ($kelas == 'Kelas 3' ? 'selected' : ''); ?>>Kelas 3</option>
                <option value="Kelas 4" <?php echo ($kelas == 'Kelas 4' ? 'selected' : ''); ?>>Kelas 4</option>
                <option value="Kelas 5" <?php echo ($kelas == 'Kelas 5' ? 'selected' : ''); ?>>Kelas 5</option>
                <option value="Kelas 6" <?php echo ($kelas == 'Kelas 6' ? 'selected' : ''); ?>>Kelas 6</option>
            </select>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
