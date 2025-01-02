<?php
include 'koneksi.php';  // Menggunakan koneksi MySQLi

// Mulai sesi dan ambil id_admin
session_start();
$id_admin = $_SESSION['id_admin'];  // Ambil ID admin dari sesi

// Inisialisasi variabel
$siswa = [];
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? max(10, (int)$_GET['per_page']) : 10;
$start = ($page - 1) * $perPage;

// Hitung total data siswa dengan kondisi keaktifan "AKTIF"
if ($id_admin == 1) {
    // Admin dengan id_admin = 1 dapat melihat semua data
    $totalQuery = "SELECT COUNT(*) FROM siswa WHERE keaktifan = 'AKTIF'";
    $stmt = $mysqli->prepare($totalQuery);
    $stmt->execute();
    $stmt->bind_result($totalRows);
    $stmt->fetch();
    $stmt->close();
} else {
    // Admin selain id_admin = 1 hanya bisa melihat data siswa mereka sendiri
    $totalQuery = "SELECT COUNT(*) FROM siswa WHERE keaktifan = 'AKTIF' AND id_admin = ?";
    $stmt = $mysqli->prepare($totalQuery);
    $stmt->bind_param("i", $id_admin);
    $stmt->execute();
    $stmt->bind_result($totalRows);
    $stmt->fetch();
    $stmt->close();
}

$totalPages = ceil($totalRows / $perPage);

// Ambil data siswa berdasarkan pencarian atau seluruh data
if (isset($_GET["keyword"])) {
    $keyword = $_GET["keyword"];
    // Cari siswa aktif berdasarkan keyword dan id_admin dengan paginasi
    $siswa = cari($keyword, 'AKTIF', $id_admin, $start, $perPage);
} else {
    // Query siswa aktif dengan paginasi
    if ($id_admin == 1) {
        // Admin dengan id_admin = 1 dapat melihat semua data siswa
        $query = "SELECT * FROM siswa WHERE keaktifan = 'AKTIF' LIMIT ?, ?";
    } else {
        // Admin selain id_admin = 1 hanya bisa melihat data siswa mereka sendiri
        $query = "SELECT * FROM siswa WHERE keaktifan = 'AKTIF' AND id_admin = ? LIMIT ?, ?";
    }
    $stmt = $mysqli->prepare($query);
    if ($id_admin == 1) {
        $stmt->bind_param("ii", $start, $perPage);  // Admin 1 tidak perlu filter id_admin
    } else {
        $stmt->bind_param("iii", $id_admin, $start, $perPage);  // Admin lain butuh filter id_admin
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $siswa = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();  // Tutup statement setelah query selesai
}

// Fungsi pencarian siswa berdasarkan kata kunci
function cari($keyword, $keaktifan, $id_admin, $start, $perPage) {
    global $mysqli;
    // Query cari siswa aktif berdasarkan keyword dan id_admin dengan limit dan offset untuk paginasi
    if ($id_admin == 1) {
        // Admin 1 dapat mencari semua data siswa
        $query = "SELECT * FROM siswa WHERE keaktifan = ? AND (
            nis_siswa LIKE ? OR
            nama_siswa LIKE ? OR
            jenis_kelamin LIKE ? OR
            kelas LIKE ? OR
            nama_ayah LIKE ? OR
            no_hp_ayah LIKE ? OR
            nama_ibu LIKE ? OR
            no_hp_ibu LIKE ? OR
            alamat LIKE ? OR
            tahun_ajaran LIKE ?
        ) LIMIT ?, ?";
    } else {
        // Admin lain hanya dapat mencari siswa miliknya sendiri
        $query = "SELECT * FROM siswa WHERE keaktifan = ? AND id_admin = ? AND (
            nis_siswa LIKE ? OR
            nama_siswa LIKE ? OR
            jenis_kelamin LIKE ? OR
            kelas LIKE ? OR
            nama_ayah LIKE ? OR
            no_hp_ayah LIKE ? OR
            nama_ibu LIKE ? OR
            no_hp_ibu LIKE ? OR
            alamat LIKE ? OR
            tahun_ajaran LIKE ?
        ) LIMIT ?, ?";
    }

    $stmt = $mysqli->prepare($query);
    $keyword = '%' . $keyword . '%';
    if ($id_admin == 1) {
        $stmt->bind_param("siissssssssii", $keaktifan, $keyword, $keyword, $keyword, $keyword, $keyword, $keyword, $keyword, $keyword, $keyword, $keyword, $start, $perPage);
    } else {
        $stmt->bind_param("siissssssssiii", $keaktifan, $id_admin, $keyword, $keyword, $keyword, $keyword, $keyword, $keyword, $keyword, $keyword, $keyword, $start, $perPage);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $siswa = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();  // Tutup statement setelah query selesai
    return $siswa;
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
        .text-nowrap, th, td {
            white-space: nowrap; /* Tidak terputus ke baris baru */
        }
        .table thead th {
            vertical-align: middle; /* Vertikal rata tengah */
        }
        .table .filters input {
            width: 100%;
            box-sizing: border-box;
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
    <h6 class="font-weight-bold text-primary">Daftar Siswa Aktif</h6>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <!-- Baris Header Data -->
                <tr class="text-center">
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Jenis Kelamin</th>
                    <th>Kelas</th>
                    <th>Nama Ayah</th>
                    <th>No HP Ayah</th>
                    <th>Nama Ibu</th>
                    <th>No HP Ibu</th>
                    <th>Alamat</th>
                    <th>Tahun Ajaran</th>
                    <th>Status</th>
                </tr>
            </thead>
            <thead>
                <!-- Baris Pencarian -->
                <tr class="filters">
                    <th></th> <!-- Kolom kosong untuk nomor -->
                    <th><input type="text" class="form-control" placeholder="Cari NIS" onkeyup="filterTable('nis_siswa', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari Nama" onkeyup="filterTable('nama_siswa', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari Jenis Kelamin" onkeyup="filterTable('jenis_kelamin', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari Kelas" onkeyup="filterTable('kelas', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari Nama Ayah" onkeyup="filterTable('nama_ayah', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari No HP Ayah" onkeyup="filterTable('no_hp_ayah', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari Nama Ibu" onkeyup="filterTable('nama_ibu', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari No HP Ibu" onkeyup="filterTable('no_hp_ibu', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari Alamat" onkeyup="filterTable('alamat', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari Tahun Ajaran" onkeyup="filterTable('tahun_ajaran', this.value)"></th>
                    <th><input type="text" class="form-control" placeholder="Cari Status" onkeyup="filterTable('keaktifan', this.value)"></th>
                </tr>
            </thead>
            <tbody>
                <?php $i = $start + 1; ?>
                <?php foreach ($siswa as $row): ?>
                    <tr>
                        <td class="text-center"><?= $i; ?></td>
                        <td class="text-nowrap" data-column="nis_siswa"><?= $row["nis_siswa"]; ?></td>
                        <td class="text-nowrap" data-column="nama_siswa"><?= $row["nama_siswa"]; ?></td>
                        <td class="text-nowrap" data-column="jenis_kelamin"><?= $row["jenis_kelamin"]; ?></td>
                        <td class="text-center text-nowrap"><?= $row["kelas"]; ?></td> <!-- Kelas di-center-kan -->
                        <td class="text-nowrap" data-column="nama_ayah"><?= $row["nama_ayah"]; ?></td>
                        <td class="text-nowrap" data-column="no_hp_ayah"><?= $row["no_hp_ayah"]; ?></td>
                        <td class="text-nowrap" data-column="nama_ibu"><?= $row["nama_ibu"]; ?></td>
                        <td class="text-nowrap" data-column="no_hp_ibu"><?= $row["no_hp_ibu"]; ?></td>
                        <td class="text-nowrap" data-column="alamat"><?= $row["alamat"]; ?></td>
                        <td class="text-nowrap" data-column="tahun_ajaran"><?= $row["tahun_ajaran"]; ?></td>
                        <td class="text-nowrap" data-column="keaktifan"><?= $row["keaktifan"]; ?></td>
                    </tr>
                    <?php $i++; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination Section -->
<div class="d-flex justify-content-between align-items-center mt-3">
    <!-- Pagination -->
    <ul class="pagination mb-7 mt-10 w-100 justify-content-center">
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
        <label for="per_page" class="me-2 mb-0">Rows per page: </label>
        <select id="per_page" class="form-select" style="width: auto;" onchange="window.location='?page=<?= $page; ?>&per_page=' + this.value;">
            <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
            <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
            <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
            <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100</option>
        </select>
    </div>
</div>

<!-- Javascript and filter function -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Fungsi untuk filter tabel berdasarkan kolom tertentu
    function filterTable(column, value) {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cell = row.querySelector(`[data-column="${column}"]`);
            if (cell) {
                row.style.display = cell.innerText.toLowerCase().includes(value.toLowerCase()) ? '' : 'none';
            }
        });
    }
</script>

</body>
</html>
