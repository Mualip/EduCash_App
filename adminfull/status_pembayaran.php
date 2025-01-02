<?php
require 'function.php';
include 'koneksi.php';

// Start session to get the logged-in admin's id
session_start();
if (!isset($_SESSION['id_admin'])) {
    echo "<script>alert('Anda belum login!'); window.location.href = 'login.php';</script>";
    exit;
}

// Get the logged-in admin's id
$id_admin = $_SESSION['id_admin'];  

// Initialize variables
$siswa = [];
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? max(10, (int)$_GET['per_page']) : 10;
$start = ($page - 1) * $perPage;

$tahunAjaran = isset($_GET['tahun_ajaran']) ? $_GET['tahun_ajaran'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : ''; // New filter for status

$query = "SELECT 
            s.nis_siswa, s.nama_siswa, s.jenis_kelamin, s.kelas,
            t.jenis_pembayaran, t.jenjang, t.total_pertahun, t.status_pembayaran, t.tahun_ajaran,
            t.jumlah_bayar, t.diskon, t.tanggal_bayar
          FROM siswa s
          JOIN total t ON s.nis_siswa = t.nis_siswa
          WHERE t.tanggal_bayar = (SELECT MAX(t2.tanggal_bayar) 
                                    FROM total t2 
                                    WHERE t2.nis_siswa = s.nis_siswa)";

// If the logged-in admin is not admin 1, filter by their own id
if ($id_admin != 1) {
    $query .= " AND s.id_admin = '$id_admin'";  // Add filter based on admin id
}

if ($tahunAjaran) {
    $query .= " AND t.tahun_ajaran = '$tahunAjaran'";
}

if ($status) {
    $query .= " AND t.status_pembayaran = '$status'";
}

$query .= " ORDER BY t.bulan ASC LIMIT $start, $perPage";
$result = mysqli_query($mysqli, $query);

// Array to store student data
$siswa = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Calculate remaining payment based on the last payment
    $row['sisa_pembayaran'] = $row['total_pertahun'] - ($row['jumlah_bayar'] + $row['diskon']);
    $siswa[] = $row;
}

// Calculate total records for pagination
$totalQuery = "SELECT COUNT(DISTINCT s.nis_siswa) FROM siswa s JOIN total t ON s.nis_siswa = t.nis_siswa";

// If the logged-in admin is not admin 1, filter by their own id
if ($id_admin != 1) {
    $totalQuery .= " WHERE s.id_admin = '$id_admin'";
}

$totalResult = mysqli_query($mysqli, $totalQuery);
$totalRows = mysqli_fetch_row($totalResult)[0];
$totalPages = ceil($totalRows / $perPage);

// Get distinct tahun_ajaran for the dropdown filter
$tahunAjaranQuery = "SELECT DISTINCT tahun_ajaran FROM total";
$resultTahunAjaran = mysqli_query($mysqli, $tahunAjaranQuery);

// Get distinct status_pembayaran for the dropdown filter
$statusQuery = "SELECT DISTINCT status_pembayaran FROM total";
$resultStatus = mysqli_query($mysqli, $statusQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .text-nowrap {
        white-space: nowrap;
    }

    .table thead th {
        vertical-align: middle;
    }

    .table-responsive {
        -ms-overflow-style: scrollbar;
        scrollbar-width: thin;
    }

    .table td,
    .table th {
        word-wrap: break-word;
        white-space: nowrap;
    }

    .table td {
        width: auto;
    }
    </style>
</head>

<body>
    <?php include "sidebar.php"; ?>
    <?php include "navbar.php"; ?>

    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <!-- Judul -->
            <h6 class="font-weight-bold text-primary mb-0">Status Pembayaran</h6>
        </div>

        <form method="get" action="" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <label for="tahun_ajaran">Tahun Ajaran:</label>
                    <select id="tahun_ajaran" name="tahun_ajaran" class="form-select">
                        <option value="">Pilih Tahun Ajaran</option>
                        <?php while ($row = mysqli_fetch_assoc($resultTahunAjaran)): ?>
                        <option value="<?= $row['tahun_ajaran']; ?>"
                            <?= $tahunAjaran == $row['tahun_ajaran'] ? 'selected' : '' ?>>
                            <?= $row['tahun_ajaran']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                

                <div class="col-md-4">
                    <label for="status">Status Pembayaran:</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Pilih Status</option>
                        <option value="Lunas" <?= $status == 'Lunas' ? 'selected' : '' ?>>Lunas</option>
                        <option value="Masih Ada Tunggakan" <?= $status == 'Masih Ada Tunggakan' ? 'selected' : '' ?>>
                            Masih Ada Tunggakan</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary mt-4">Filter</button>
                    <!-- Tombol untuk Download CSV -->
                    <a href="download_csv.php?page=<?= $page; ?>&per_page=<?= $perPage ?>&tahun_ajaran=<?= $tahunAjaran ?>&status=<?= $status ?>"
                    class="btn btn-success mt-4 ms-2">Download CSV</a>
                </div>
            </div>
        </form>

        <form id="form-pilih" method="post">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr class="text-center">
                    <th><input type="checkbox" id="select_all"></th>
                    <th class="text-nowrap">No</th>
                    <th class="text-nowrap">NIS</th>
                    <th class="text-nowrap">Nama Siswa</th>
                    <th class="text-nowrap">Jenis Kelamin</th>
                    <th class="text-nowrap">Kelas</th>
                    <th class="text-nowrap">Jenis Pembayaran</th>
                    <th class="text-nowrap">Jenjang</th>
                    <th class="text-nowrap">Tahun Ajaran</th>
                    <th class="text-nowrap">Biaya Pertahun</th>
                    <th class="text-nowrap">Jumlah Bayar</th>
                    <th class="text-nowrap">Diskon</th>
                    <th class="text-nowrap">Sisa Pembayaran</th>
                    <th class="text-nowrap">Status Pembayaran</th>
                    <th class="text-nowrap">Tanggal Bayar</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = $start + 1; ?>
                <?php foreach ($siswa as $row): ?>
                <tr>
                    <td class="text-center">
                        <input type="checkbox" name="siswa[]"
                            value="<?= htmlspecialchars(json_encode($row)); ?>">
                    </td>
                    <td class="text-center"><?= $i; ?></td>
                    <td class="text-center"><?= $row["nis_siswa"]; ?></td>
                    <td class="text-nowrap"><?= $row["nama_siswa"]; ?></td>
                    <td class="text-nowrap"><?= $row["jenis_kelamin"]; ?></td>
                    <td class="text-nowrap"><?= $row["kelas"]; ?></td>
                    <td class="text-nowrap"><?= $row["jenis_pembayaran"]; ?></td>
                    <td class="text-nowrap"><?= $row["jenjang"]; ?></td>
                    <td class="text-nowrap"><?= $row["tahun_ajaran"]; ?></td>
                    <td class="text-nowrap"><?= "Rp. " . number_format($row["total_pertahun"], 0, ',', '.'); ?></td>
                    <td class="text-nowrap"><?= "Rp. " . number_format($row["jumlah_bayar"], 0, ',', '.'); ?></td>
                    <td class="text-nowrap"><?= "Rp. " . number_format($row["diskon"], 0, ',', '.'); ?></td>
                    <td><?= "Rp. " . number_format($row["sisa_pembayaran"], 0, ',', '.'); ?></td>
                    <td><?= $row["status_pembayaran"]; ?></td>
                    <td class="text-nowrap"><?= date('d-m-Y', strtotime($row["tanggal_bayar"])); ?></td>
                </tr>
                <?php $i++; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</form>

        <!-- Pagination Section -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <ul class="pagination mb-7 mt-10 w-100 justify-content-center">
                <?php if ($page > 1): ?>
                <li class="page-item">
                    <a href="?page=<?= $page - 1; ?>&per_page=<?= $perPage ?>" class="page-link">‹</a>
                </li>
                <?php endif; ?>

                <?php 
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);

                for ($p = $startPage; $p <= $endPage; $p++): 
                ?>
                <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                    <a href="?page=<?= $p; ?>&per_page=<?= $perPage ?>" class="page-link"><?= $p; ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a href="?page=<?= $page + 1; ?>&per_page=<?= $perPage ?>" class="page-link">›</a>
                </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center">
                <label for="per_page" class="me-2 mb-0">Rows per page: </label>
                <select id="per_page" class="form-select" style="width: auto;"
                    onchange="window.location='?page=<?= $page; ?>&per_page=' + this.value">
                    <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                    <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100</option>
                </select>
            </div>
        </div>
        <?php include "footer.php"; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

    <script>
    // Select All Checkbox
    document.getElementById('select_all').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('input[name="siswa[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });
    </script>

</body>

</html>