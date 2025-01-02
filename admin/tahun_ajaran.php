<?php
include "koneksi.php";


// Mengambil nilai pencarian, tahun ajaran, dan kelas dari parameter GET
$pencarian = isset($_GET['cari']) ? $_GET['cari'] : '';
$tahun_ajaran = isset($_GET['tahun_ajaran']) ? $_GET['tahun_ajaran'] : '';
$kelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';

// Menyusun query berdasarkan kondisi pencarian, tahun ajaran, dan kelas
$query = "SELECT * FROM siswa WHERE 1"; // Default query yang mengambil semua data siswa

if ($pencarian) {
    // Mencari berdasarkan nama siswa
    $query .= " AND nama_siswa LIKE '%" . mysqli_real_escape_string($mysqli, $pencarian) . "%'";
}

if ($tahun_ajaran && $tahun_ajaran != 'semua_siswa') {
    // Mencari berdasarkan tahun ajaran
    $query .= " AND tahun_ajaran = '$tahun_ajaran'";
}

if ($kelas && $kelas != 'semua_kelas') {
    // Mencari berdasarkan kelas
    $query .= " AND kelas = '$kelas'";
}

// Eksekusi query
$data = mysqli_query($mysqli, $query);
/// Mengambil nilai pencarian, tahun ajaran, dan kelas dari parameter GET
$pencarian = isset($_GET['cari']) ? mysqli_real_escape_string($mysqli, $_GET['cari']) : '';
$tahun_ajaran = isset($_GET['tahun_ajaran']) ? mysqli_real_escape_string($mysqli, $_GET['tahun_ajaran']) : '';
$kelas = isset($_GET['kelas']) ? mysqli_real_escape_string($mysqli, $_GET['kelas']) : '';

// Menyusun query berdasarkan kondisi pencarian dan filter
$query = "SELECT * FROM siswa WHERE 1"; // Memulai query dengan kondisi yang selalu benar

// Jika ada pencarian, tambahkan ke query
if ($pencarian) {
    $query .= " AND (nis_siswa LIKE '%" . $pencarian . "%' 
              OR nama_siswa LIKE '%" . $pencarian . "%' 
              OR jenis_kelamin LIKE '%" . $pencarian . "%' 
              OR jenjang LIKE '%" . $pencarian . "%' 
              OR kelas LIKE '%" . $pencarian . "%' 
              OR nama_ayah LIKE '%" . $pencarian . "%' 
              OR no_hp_ayah LIKE '%" . $pencarian . "%' 
              OR nama_ibu LIKE '%" . $pencarian . "%' 
              OR no_hp_ibu LIKE '%" . $pencarian . "%' 
              OR alamat LIKE '%" . $pencarian . "%' 
              OR tahun_ajaran LIKE '%" . $pencarian . "%' 
              OR keaktifan LIKE '%" . $pencarian . "%')";
}

// Jika ada filter tahun ajaran, tambahkan ke query
if ($tahun_ajaran && $tahun_ajaran != 'semua_siswa') {
    $query .= " AND tahun_ajaran = '$tahun_ajaran'";
}

// Jika ada filter kelas, tambahkan ke query
if ($kelas) {
    $query .= " AND kelas = '$kelas'";
}

// Menjalankan query untuk mengambil data
$data = mysqli_query($mysqli, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Siswa</title>
    <style>
        .tahun_ajaran {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .form-tahun, .form-kelas {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            width: 100%;
            padding: 8px;
            margin-top: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .btn-submit {
            margin-top: 10px;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-submit:hover {
            background-color: #45a049;
        }

        .card {
            margin-top: 30px;
        }

        .table th, .table td {
            text-align: center;
        }
    </style>
</head>
<body>

<!-- Form Pencarian Tahun Ajaran dan Kelas -->
<div class="tahun_ajaran">
    <form action="daftarsiswa.php" method="get" class="form-tahun">
        <!-- Dropdown untuk memilih Tahun Ajaran -->
        <div class="form-group">
            <label for="tahun_ajaran" class="form-label">Pilih Tahun Ajaran :</label>
            <select name="tahun_ajaran" id="tahun_ajaran" class="form-control">
                <option value="semua_siswa" <?php echo ($tahun_ajaran == 'semua_siswa' ? 'selected' : ''); ?>>Semua Siswa</option>
                <option value="2022-2023" <?php echo ($tahun_ajaran == '2022-2023' ? 'selected' : ''); ?>>2022-2023</option>
                <option value="2023-2024" <?php echo ($tahun_ajaran == '2023-2024' ? 'selected' : ''); ?>>2023-2024</option>
                <option value="2024-2025" <?php echo ($tahun_ajaran == '2024-2025' ? 'selected' : ''); ?>>2024-2025</option>
                <option value="2025-2026" <?php echo ($tahun_ajaran == '2025-2026' ? 'selected' : ''); ?>>2025-2026</option>
            </select>
            <button type="submit" name="submit_tahun" class="btn-submit">Cari Tahun Ajaran</button>
        </div>
    </form>

    <form action="daftarsiswa.php" method="get" class="form-kelas">
        <!-- Dropdown untuk memilih Kelas -->
        <div class="form-group">
        <label for="kelas" class="form-label">Pilih Kelas :</label>
            <select name="kelas" id="kelas" class="form-control">
                <option value="1" <?php echo ($kelas == '1' ? 'selected' : ''); ?>>1</option>
                <option value="2" <?php echo ($kelas == '2' ? 'selected' : ''); ?>>2</option>
                <option value="3" <?php echo ($kelas == '3' ? 'selected' : ''); ?>>3</option>
                <option value="4" <?php echo ($kelas == '4' ? 'selected' : ''); ?>>4</option>
                <option value="5" <?php echo ($kelas == '5' ? 'selected' : ''); ?>>5</option>
                <option value="6" <?php echo ($kelas == '6' ? 'selected' : ''); ?>>6</option>
             </select>
            <button type="submit" name="submit_kelas" class="btn-submit">Cari Kelas</button>
        </div>
    </form>
</div>

<!-- Tabel Daftar Siswa -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Siswa</h6>
    </div>
    <div class="card-body">
        <div class="container mt-1">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr class="text-center">
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Jenis Kelamin</th>
                            <th>Jenjang</th>
                            <th>Kelas</th>
                            <th>Nama Ayah</th>
                            <th>No Hp Ayah</th>
                            <th>Nama Ibu</th>
                            <th>No Hp Ibu</th>
                            <th>Alamat</th>
                            <th>Tahun Ajaran</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1; // Inisialisasi nomor urut
                        if (mysqli_num_rows($data) > 0) {
                            while ($row = mysqli_fetch_array($data)) {
                                ?>
                                <tr>
                                    <td class="text-center text-nowrap"><?= $i; ?></td>
                                    <td class="text-center text-nowrap"><?= $row["nis_siswa"]; ?></td>
                                    <td class="text-nowrap"><?= $row["nama_siswa"]; ?></td>
                                    <td class="text-nowrap"><?= $row["jenis_kelamin"]; ?></td>
                                    <td class="text-nowrap"><?= $row["jenjang"]; ?></td>
                                    <td class="text-nowrap"><?= $row["kelas"]; ?></td>
                                    <td class="text-nowrap"><?= $row["nama_ayah"]; ?></td>
                                    <td class="text-nowrap"><?= $row["no_hp_ayah"]; ?></td>
                                    <td class="text-nowrap"><?= $row["nama_ibu"]; ?></td>
                                    <td class="text-nowrap"><?= $row["no_hp_ibu"]; ?></td>
                                    <td class="text-nowrap"><?= $row["alamat"]; ?></td>
                                    <td class="text-nowrap"><?= $row["tahun_ajaran"]; ?></td>
                                    <td class="text-nowrap"><?= $row["keaktifan"]; ?></td>
                                </tr>
                                <?php
                                $i++; // Increment nomor urut
                            }
                        } else {
                            // Jika tidak ada data
                            echo "<tr><td colspan='13' class='text-center'>Tidak ada data siswa</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>
