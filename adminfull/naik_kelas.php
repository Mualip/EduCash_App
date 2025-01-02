<?php
require 'function.php';
include 'koneksi.php';

// Inisialisasi variabel
$siswa = [];
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? max(10, (int)$_GET['per_page']) : 10;
$start = ($page - 1) * $perPage;

// Hitung total data siswa
$totalQuery = "SELECT COUNT(*) FROM siswa";
$totalResult = mysqli_query($mysqli, $totalQuery);
$totalRows = mysqli_fetch_row($totalResult)[0];
$totalPages = ceil($totalRows / $perPage);

// Ambil data siswa berdasarkan pencarian atau seluruh data
if (isset($_GET["keyword"])) {
    $keyword = $_GET["keyword"];
    $siswa = cari($keyword);
} else {
    $query = "SELECT * FROM siswa LIMIT $start, $perPage";
    $result = mysqli_query($mysqli, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $siswa[] = $row;
    }
}

// Proses "Naik Kelas"
if (isset($_POST['naik_kelas'])) {
    foreach ($_POST['siswa'] as $nis_siswa => $kelas_sekarang) {
        $kelas_baru = naikkanKelas($kelas_sekarang);
        $stmt = $mysqli->prepare("UPDATE siswa SET kelas = ? WHERE nis_siswa = ?");
        $stmt->bind_param("ss", $kelas_baru, $nis_siswa);
        $stmt->execute();
        $stmt->close();
    }
    echo "<script>alert('Kelas berhasil dinaikkan.'); window.location='index.php';</script>";
}

function naikkanKelas($kelas) {
    if (preg_match('/^(\d+)([A-Z]?)$/', $kelas, $matches)) {
        $angka = $matches[1];
        $huruf = $matches[2];
        return $huruf === '' ? (string)((int)$angka + 1) : (string)((int)$angka + 1) . $huruf;
    }
    return $kelas;
}

// Tambah siswa
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

    if (!preg_match('/^\d{4}$/', $data['nis_siswa'])) {
        echo '<div class="alert alert-danger mt-3">NIS harus 4 digit angka.</div>';
        exit;
    }

    if (tambahSiswa($data)) {
        header("Location: index.php");
        exit;
    } else {
        echo '<div class="alert alert-danger mt-3">Gagal menambahkan siswa.</div>';
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Ciomas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .text-nowrap { white-space: nowrap; }
        .table thead th {
            vertical-align: middle;
        }
        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
</head>
<body>
<?php include "sidebar.php"; ?>
<?php include "navbar.php"; ?>

<div class="container-fluid mt-4">
    <h6 class="font-weight-bold text-primary">Daftar Siswa</h6>
    <form method="post" action="">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr class="text-center">
                    <th><input type="checkbox" id="select_all"></th> <!-- Select All Checkbox -->
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Jenis Kelamin</th>
                    <th>Kelas</th>
                    <th>Nama Ayah</th>
                    <th>No Hp Ayah</th>
                    <th>Nama Ibu</th>
                    <th>No Hp Ibu</th>
                    <th>Alamat</th>
                    <th>Tahun Ajaran</th>
                    <th>Status</th>
                    <th>Edit</th>
                    <th>Mutasi siswa</th>
                    <th>Lulus</th>
                    <th>Hapus</th>
                   
                </tr>
            </thead>
            <tbody>
                <?php $i = $start + 1; ?>
                <?php foreach ($siswa as $row): ?>
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" name="siswa[<?= $row['nis_siswa']; ?>]" value="<?= $row['kelas']; ?>"> <!-- Checkbox for each student -->
                        </td>
                        <td class="text-center text-nowrap"><?= $i; ?></td>
                        <td class="text-center text-nowrap"><?= $row["nis_siswa"]; ?></td>
                        <td class="text-nowrap"><?= $row["nama_siswa"]; ?></td>
                        <td class="text-nowrap"><?= $row["jenis_kelamin"]; ?></td>
                        <td class="text-center text-nowrap"><?= $row["kelas"]; ?></td> <!-- Kelas di-center-kan -->
                        <td class="text-nowrap"><?= $row["nama_ayah"]; ?></td>
                        <td class="text-nowrap"><?= $row["no_hp_ayah"]; ?></td>
                        <td class="text-nowrap"><?= $row["nama_ibu"]; ?></td>
                        <td class="text-nowrap"><?= $row["no_hp_ibu"]; ?></td>
                        <td class="text-nowrap"><?= $row["alamat"]; ?></td>
                        <td class="text-nowrap"><?= $row["tahun_ajaran"]; ?></td>
                        <td class="text-nowrap"><?= $row["keaktifan"]; ?></td>
                        <td class="text-center">
                            <a href="edit.php?id=<?= $row['nis_siswa']; ?>" class="btn btn-primary btn-sm mr-2">Edit</a>
                        </td>
                        <td class="text-center">
                            <a href="mutasi_siswa.php?id=<?= $row['nis_siswa']; ?>" class="btn btn-primary btn-sm mr-2">Mutasi</a>
                        </td>
                        <td class="text-center">
                            <a href="lulus_siswa.php?id=<?= $row['nis_siswa']; ?>" class="btn btn-primary btn-sm mr-2">Lulus</a>
                        </td>
                        <td class="text-center">
                            <a href="hapus.php?id=<?= $row['nis_siswa']; ?>" onclick="return confirm('Apakah yakin akan menghapus data ini?')" class="btn btn-danger btn-sm">Hapus</a>
                        </td>
                       
                       
                    </tr>
                    <?php $i++; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
    <!-- Tombol Naik Kelas -->
    <button type="submit" name="naik_kelas" class="btn btn-primary">Naik Kelas</button>
    <ul class="pagination mt-3">
    <!-- Tombol Sebelumnya -->
    <?php if ($page > 1): ?>
        <li class="page-item">
            <a href="?page=<?= $page - 1; ?>&per_page=<?= $perPage ?>" class="page-link">‹</a>
        </li>
    <?php endif; ?>

    <!-- Tautan Halaman -->
    <?php 
    $startPage = max(1, $page - 2);
    $endPage = min($totalPages, $page + 2);

    for ($p = $startPage; $p <= $endPage; $p++): 
    ?>
        <li class="page-item <?= $p == $page ? 'active' : '' ?>">
            <a href="?page=<?= $p; ?>&per_page=<?= $perPage ?>" class="page-link"><?= $p; ?></a>
        </li>
    <?php endfor; ?>

    <!-- Tombol Berikutnya -->
    <?php if ($page < $totalPages): ?>
        <li class="page-item">
            <a href="?page=<?= $page + 1; ?>&per_page=<?= $perPage ?>" class="page-link">›</a>
        </li>
    <?php endif; ?>
    </ul>
    
    <!-- Dropdown Rows Per Page -->
    <div class="d-flex align-items-center">
        <label for="per_page" class="me-2">Rows per page: </label>
        <select id="per_page" class="form-select" style="width: auto;" onchange="window.location='?page=<?= $page; ?>&per_page=' + this.value;">
            <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
            <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
            <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
            <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100</option>
        </select>
        
    </div>
</div>

    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('select_all').addEventListener('click', function () {
        const checkboxes = document.querySelectorAll('input[name^="siswa"]');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });
</script>
</body>

</html>
