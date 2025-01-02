<?php
include "koneksi.php";  // Koneksi database




// Ambil ID siswa (NIS) dari parameter URL
$nis_siswa = $_GET['id'];

// Query untuk mengambil data siswa berdasarkan nis_siswa
$query = mysqli_query($mysqli, "SELECT * FROM siswa WHERE nis_siswa = '$nis_siswa'");
$data = mysqli_fetch_array($query);

// Jika tidak ada data siswa yang ditemukan, tampilkan pesan error
if (!$data) {
    echo "<script>alert('Student not found'); window.location.href = 'siswa.php';</script>";
    exit;
}

// Proses edit data siswa jika form disubmit
if (isset($_POST['editbtn'])) {
    // Ambil data dari form
    $nis_siswa = $_POST['nis_siswa'];
    $nama_siswa = $_POST['nama_siswa'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $nama_ayah = $_POST['nama_ayah'];
    $no_hp_ayah = $_POST['no_hp_ayah'];
    $nama_ibu = $_POST['nama_ibu'];
    $no_hp_ibu = $_POST['no_hp_ibu'];
    $alamat = $_POST['alamat'];
    $tahun_ajaran = $_POST['tahun_ajaran'];
    $kelas = $_POST['kelas'];
    $keaktifan = $_POST['keaktifan'];

    // Query untuk update data siswa
    $query = "UPDATE siswa SET 
        nis_siswa='$nis_siswa', 
        nama_siswa='$nama_siswa', 
        jenis_kelamin='$jenis_kelamin',
        kelas='$kelas', 
        nama_ayah='$nama_ayah', 
        no_hp_ayah='$no_hp_ayah', 
        nama_ibu='$nama_ibu', 
        no_hp_ibu='$no_hp_ibu', 
        alamat='$alamat', 
        tahun_ajaran='$tahun_ajaran', 
        keaktifan='$keaktifan' 
        WHERE nis_siswa='$nis_siswa'";

    // Eksekusi query

    if (mysqli_query($mysqli, $query)) {
        echo "<script>alert('Data berhasil diupdate!'); window.location='edit_identitas.php';</script>";
    }
     else {
        echo "Error: " . mysqli_error($mysqli);
    }
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
     <!-- Custom fonts for this template-->
     <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        
        body {
            font-family: 'Arial', sans-serif; /* Mengubah font menjadi Arial */
            background-color: #f8f9fa; /* Memberikan latar belakang yang lebih terang */
            color: #333; /* Warna teks lebih gelap agar lebih jelas */
        }

        .container {
            
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-label {
            font-size: 16px;
            font-weight: 600;
        }

        .form-control {
            font-size: 14px;
            padding: 10px;
        }

        .btn {
            font-size: 16px;
            padding: 10px 20px;
        }

        textarea.form-control {
            resize: vertical; /* Membuat textarea bisa diubah ukurannya secara vertikal */
        }

        /* Optional: Adding some margin at the bottom of the form */
        .mb-3 {
            margin-bottom:2px;
        }

    
    .text-nowrap {
        white-space: nowrap; /* Prevent text from wrapping */
    }
</style>


</head>
<body>
    <?php
    include "sidebar.php";
    

    ?>

<div class="container mt-5">
        <h2>Edit Siswa</h2>
        <form action="" method="post">
            <div class="mb-3">
                <label for="nis_siswa" class="form-label">NIS Siswa</label>
                <input type="text" class="form-control" id="nis_siswa" name="nis_siswa" value="<?= $data['nis_siswa']; ?>" required readonly>
            </div>
            <div class="mb-3">
                <label for="nama_siswa" class="form-label">Nama Siswa</label>
                <input type="text" class="form-control" id="nama_siswa" name="nama_siswa" value="<?= $data['nama_siswa']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                <select class="form-control" id="jenis_kelamin" name="jenis_kelamin" required>
                    <option value="Laki-laki" <?= $data['jenis_kelamin'] == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                    <option value="Perempuan" <?= $data['jenis_kelamin'] == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="kelas" class="form-label">Kelas</label>
                <input type="text" class="form-control" id="kelas" name="kelas" value="<?= $data['kelas']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="nama_ayah" class="form-label">Nama Ayah</label>
                <input type="text" class="form-control" id="nama_ayah" name="nama_ayah" value="<?= $data['nama_ayah']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="no_hp_ayah" class="form-label">No HP Ayah</label>
                <input type="text" class="form-control" id="no_hp_ayah" name="no_hp_ayah" value="<?= $data['no_hp_ayah']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="nama_ibu" class="form-label">Nama Ibu</label>
                <input type="text" class="form-control" id="nama_ibu" name="nama_ibu" value="<?= $data['nama_ibu']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="no_hp_ibu" class="form-label">No HP Ibu</label>
                <input type="text" class="form-control" id="no_hp_ibu" name="no_hp_ibu" value="<?= $data['no_hp_ibu']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <textarea class="form-control" id="alamat" name="alamat" required><?= $data['alamat']; ?></textarea>
            </div>
            <div class="mb-3">
               <div class="mb-3">
                <label for="tahun_ajaran" class="form-label">Tahun Ajaran</label>
                <select class="form-control" id="tahun_ajaran" name="tahun_ajaran" required>
                    <option value="tahun_ajaran" <?= $data['tahun_ajaran'] == 'L' ? 'selected' : ''; ?>>2022-2023</option>
                    <option value="tahun_ajaran" <?= $data['tahun_ajaran'] == 'P' ? 'selected' : ''; ?>>2023-2024</option>
                    <option value="tahun_ajaran" <?= $data['tahun_ajaran'] == 'L' ? 'selected' : ''; ?>>2024-2025</option>
                    <option value="tahun_ajaran" <?= $data['tahun_ajaran'] == 'P' ? 'selected' : ''; ?>>2025-2026</option>
                </select>
            </div>
            </div>
            <div class="mb-3">
                <label for="keaktifan" class="form-label">Keaktifan</label>
                <select class="form-control" id="keaktifan" name="keaktifan" required>
                    <option value="Aktif" <?= $data['keaktifan'] == 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="Tidak Aktif" <?= $data['keaktifan'] == 'Tidak Aktif' ? 'selected' : ''; ?>>Tidak Aktif</option>
                </select>
            </div>
            <button type="submit" name="editbtn" class="btn btn-primary">Simpan</button>
        </form>
    </div>
    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
