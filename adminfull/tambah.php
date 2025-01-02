
<?php
session_start();  // Memulai session

// Periksa apakah session id_admin ada
if (!isset($_SESSION['id_admin'])) {
    // Jika session id_admin tidak ada, arahkan ke halaman login
    header("Location: login.php");
    exit;
}

$id_admin = $_SESSION['id_admin'];  // Mengambil id_admin dari session

// Koneksi ke database
include "koneksi.php";  // Menghubungkan ke file koneksi.php yang menggunakan MySQLi

// Cek jika koneksi berhasil
if (!$mysqli) {
    die("Koneksi database gagal!");
}

// Mengambil data jenjang dan kelas dari database
$query_kelas_by_jenjang = "SELECT jenjang, kelas FROM kategori_pembayaran ORDER BY jenjang, kelas ASC";
$stmt_kelas_by_jenjang = $mysqli->query($query_kelas_by_jenjang);

// Cek jika query berhasil
if (!$stmt_kelas_by_jenjang) {
    die("Query gagal dieksekusi: " . $mysqli->error);
}

// Mengelompokkan kelas berdasarkan jenjang
$kelas_by_jenjang = [];
while ($row = $stmt_kelas_by_jenjang->fetch_assoc()) {
    $kelas_by_jenjang[$row['jenjang']][] = $row['kelas'];
}

// Query untuk mengambil data tahun ajaran
$query_tahun_ajaran = "SELECT DISTINCT tahun_ajaran FROM kategori_pembayaran ORDER BY tahun_ajaran ASC";
$stmt_tahun_ajaran = $mysqli->query($query_tahun_ajaran);
$tahun_ajaran_data = $stmt_tahun_ajaran->fetch_all(MYSQLI_ASSOC);

// Query untuk mengambil data jenjang
$query_jenjang = "SELECT DISTINCT jenjang FROM kategori_pembayaran ORDER BY jenjang ASC";
$stmt_jenjang = $mysqli->query($query_jenjang);
$jenjang_data = $stmt_jenjang->fetch_all(MYSQLI_ASSOC);

// Query untuk mengambil data ID Admin berdasarkan kolom 'aktif' yang berisi angka (1, 2, 3, 4)
$query_aktif = "SELECT id_admin FROM users WHERE aktif IN (1, 2, 3, 4) ORDER BY id_admin ASC";
$stmt_aktif = $mysqli->query($query_aktif);

// Cek apakah form sudah disubmit
if (isset($_POST['simpan'])) {
    // Ambil data dari form dan lakukan sanitasi
    $nis_siswa = htmlspecialchars($_POST['nis_siswa']);
    $nama_siswa = htmlspecialchars($_POST['nama_siswa']);
    $jenis_kelamin = htmlspecialchars($_POST['jenis_kelamin']);
    $jenjang = htmlspecialchars($_POST['jenjang']);
    $kelas = htmlspecialchars($_POST['kelas']);
    $nama_ayah = htmlspecialchars($_POST['nama_ayah']);
    $no_hp_ayah = htmlspecialchars($_POST['no_hp_ayah']);
    $nama_ibu = htmlspecialchars($_POST['nama_ibu']);
    $no_hp_ibu = htmlspecialchars($_POST['no_hp_ibu']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $tahun_ajaran = htmlspecialchars($_POST['tahun_ajaran']);
    $keaktifan = htmlspecialchars($_POST['keaktifan']);
    $id_admin = htmlspecialchars($_POST['aktif']);  // Mengambil ID Admin dari form

    // Cek apakah NIS sudah ada di database
    $query_check_nis = "SELECT * FROM siswa WHERE nis_siswa = ?";
    $stmt_check_nis = $mysqli->prepare($query_check_nis);
    $stmt_check_nis->bind_param('s', $nis_siswa);
    $stmt_check_nis->execute();
    $result = $stmt_check_nis->get_result();

    if ($result->num_rows > 0) {
        // Jika NIS sudah ada, tampilkan pesan error
        echo "<script>alert('Maaf, NIS ini sudah terdaftar!');</script>";
    } else {
        // Hash password default
        $password = '12345678'; // Password default
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash password

        // Jika NIS belum ada, masukkan data baru
        $query_insert_siswa = "INSERT INTO siswa (nis_siswa, nama_siswa, jenis_kelamin, jenjang, kelas, nama_ayah, no_hp_ayah, nama_ibu, no_hp_ibu, alamat, tahun_ajaran, keaktifan, password, id_admin) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert_siswa = $mysqli->prepare($query_insert_siswa);

        // Bind parameters termasuk id_admin yang dipilih
        $stmt_insert_siswa->bind_param('ssssssssssssss', 
            $nis_siswa, $nama_siswa, $jenis_kelamin, $jenjang, $kelas, 
            $nama_ayah, $no_hp_ayah, $nama_ibu, $no_hp_ibu, $alamat, 
            $tahun_ajaran, $keaktifan, $hashed_password, $id_admin);

        // Eksekusi statement
        if ($stmt_insert_siswa->execute()) {
            echo "<script>alert('Data berhasil disimpan!'); window.location.href = 'tambah.php';</script>";
            exit;  // Pastikan keluar setelah redirect
        } else {
            echo "Error inserting data: " . $stmt_insert_siswa->error;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f8f9fa;
        color: #333;
    }

    .container {
        background-color: white;
        padding: 30px;
        border-radius: 2px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    h2 {
        font-size: 24px;
        margin-bottom: 20px;
        text-align: center;
    }

    .form-label {
        font-size: 16px;
    }

    .form-control {
        font-size: 14px;
        padding: 10px;
    }

    .btn {
        font-size: 16px;
        padding: 5px 20px;
    }

    .text-nowrap {
        white-space: nowrap;
    }
    </style>
</head>

<body>
    <?php include "sidebar.php"; ?>
    <?php include "navbar.php"; ?>
    <div id="content">
        <div class="container">
            <h2>Tambah Data Siswa</h2>
            <form action="" method="post">
                <div class="mb-3">
                    <label for="nis_siswa" class="form-label">NIS:</label>
                    <input type="number" name="nis_siswa" id="nis_siswa" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="nama_siswa" class="form-label">Nama:</label>
                    <input type="text" name="nama_siswa" id="nama_siswa" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="jenis_kelamin" class="form-label">Jenis Kelamin:</label>
                    <select name="jenis_kelamin" id="jenis_kelamin" class="form-control" required>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="jenjang" class="form-label">Jenjang:</label>
                    <select name="jenjang" id="jenjang" class="form-control" required>
                        <option value="">-- Pilih Jenjang --</option>
                        <?php foreach ($jenjang_data as $row): ?>
                        <option value="<?= htmlspecialchars($row['jenjang']) ?>">
                            <?= htmlspecialchars($row['jenjang']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="kelas" class="form-label">Kelas:</label>
                    <select name="kelas" id="kelas" class="form-control" required>
                        <option value="">-- Pilih Kelas --</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="nama_ayah" class="form-label">Nama Ayah:</label>
                    <input type="text" name="nama_ayah" id="nama_ayah" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="no_hp_ayah" class="form-label">No HP Ayah:</label>
                    <input type="number" name="no_hp_ayah" id="no_hp_ayah" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="nama_ibu" class="form-label">Nama Ibu:</label>
                    <input type="text" name="nama_ibu" id="nama_ibu" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="no_hp_ibu" class="form-label">No HP Ibu:</label>
                    <input type="number" name="no_hp_ibu" id="no_hp_ibu" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat:</label>
                    <input type="text" name="alamat" id="alamat" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="tahun_ajaran" class="form-label">Tahun Ajaran:</label>
                    <select name="tahun_ajaran" id="tahun_ajaran" class="form-control" required>
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        <?php foreach ($tahun_ajaran_data as $row): ?>
                        <option value="<?= htmlspecialchars($row['tahun_ajaran']) ?>">
                            <?= htmlspecialchars($row['tahun_ajaran']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="keaktifan" class="form-label">Status:</label>
                    <select name="keaktifan" id="keaktifan" class="form-control" required>
                        <option value="aktif">AKTIF</option>

                    </select>
                </div>
                <div class="mb-3">
    <label for="aktif" class="form-label">Pilih Admin</label>
    <select name="aktif" id="aktif" class="form-control" required>
        <option value="">-- Pilih Admin --</option>
        <?php 
        // Loop untuk menampilkan data ID Admin
        while ($row = $stmt_aktif->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($row['id_admin']) ?>">
                <?= htmlspecialchars($row['id_admin']) ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>
<button type="submit" name="simpan" class="btn btn-primary">Tambah Data</button>
        </div>
        
        </form>
    </div>
    <?php include "footer.php"; ?>
    </div>
    <script>
    const kelasByJenjang = <?= json_encode($kelas_by_jenjang) ?>;
    document.getElementById('jenjang').addEventListener('change', function() {
        const jenjang = this.value;
        const kelasDropdown = document.getElementById('kelas');
        kelasDropdown.innerHTML = '<option value="">-- Pilih Kelas --</option>';
        if (kelasByJenjang[jenjang]) {
            kelasByJenjang[jenjang].forEach(kelas => {
                const option = document.createElement('option');
                option.value = kelas;
                option.textContent = kelas;
                kelasDropdown.appendChild(option);
            });
        }
    });
    </script>
</body>

</html>