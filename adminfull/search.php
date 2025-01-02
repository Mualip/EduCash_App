<?php
include "koneksi.php";

// Mengambil nilai pencarian dan tahun ajaran dari parameter GET
$pencarian = isset($_GET['cari']) ? $_GET['cari'] : '';
$tahun_ajaran = isset($_GET['tahun_ajaran']) ? $_GET['tahun_ajaran'] : '';

// Menyusun query berdasarkan kondisi pencarian dan tahun ajaran
if ($pencarian && $tahun_ajaran && $tahun_ajaran != 'semua_siswa') {
    // Mencari berdasarkan pencarian dan tahun ajaran
    $query = "SELECT * FROM siswa WHERE tahun_ajaran LIKE '%" . $pencarian . "%' AND tahun_ajaran = '$tahun_ajaran'";
} elseif ($pencarian) {
    // Mencari hanya berdasarkan pencarian (tahun ajaran tidak difilter)
    $query = "SELECT * FROM siswa WHERE tahun_ajaran LIKE '%" . $pencarian . "%'";
} elseif ($tahun_ajaran && $tahun_ajaran != 'semua_siswa') {
    // Mencari hanya berdasarkan tahun ajaran
    $query = "SELECT * FROM siswa WHERE tahun_ajaran = '$tahun_ajaran'";
} else {
    // Menampilkan semua siswa jika tidak ada pencarian
    $query = "SELECT * FROM siswa";
}

// Eksekusi query
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
            max-width: 500px;
            margin: 50px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="tahun_ajaran">
        <form action="daftarsiswa.php" method="get"> <!-- Form action diatur ke halaman yang sama -->
            <div class="mb-3">
                <label for="cari" class="form-label">Pencarian :</label>
                <input type="text" name="cari" id="cari" value="<?php echo isset($_GET['cari']) ? $_GET['cari'] : ''; ?>">
                
                <button type="submit">Cari</button>
            </div>

           
        </form>
    </div>

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
