<?php
require('koneksi.php'); // Menghubungkan ke file koneksi database
require('fpdf.php');    // Pastikan FPDF library sudah di-include

// Mengecek apakah checkbox dipilih
if (isset($_POST['checkbox']) && is_array($_POST['checkbox'])) {
    $selected_nis = $_POST['checkbox']; // Menyimpan array NIS yang dipilih

    // Koneksi database
    $conn = new mysqli('localhost', 'root', '', 'db_sekolah');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error); // Jika gagal koneksi
    }

    // Array untuk menampung data siswa
    $data_siswa = [];

    // Loop untuk mengambil data siswa berdasarkan NIS yang dipilih
    foreach ($selected_nis as $nis) {
        $nis = intval($nis); // Pastikan NIS berupa integer
        $query = "SELECT * FROM siswa s JOIN total t ON s.nis_siswa = t.nis_siswa WHERE s.nis_siswa = $nis";
        
        // Debugging untuk melihat query yang dijalankan
        echo $query . "<br>";

        $result = $conn->query($query);
        
        // Cek apakah query menghasilkan data
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data_siswa[] = $row; // Menambahkan data siswa ke array
            }
        } else {
            // Jika tidak ada data yang ditemukan
            echo "Tidak ada data untuk NIS: $nis<br>";
        }
    }

    // Tutup koneksi database
    $conn->close();

    // Jika ada data siswa
    if (!empty($data_siswa)) {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'Bukti Pembayaran SPP', 0, 1, 'C');
        $pdf->Ln(10);

        // Header tabel PDF
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(10, 10, 'No', 1, 0, 'C');
        $pdf->Cell(50, 10, 'Nama Siswa', 1, 0, 'C');
        $pdf->Cell(30, 10, 'Kelas', 1, 0, 'C');
        $pdf->Cell(50, 10, 'NIS', 1, 0, 'C');
        $pdf->Cell(30, 10, 'Jumlah Bayar', 1, 0, 'C');
        $pdf->Ln();

        // Loop untuk mengisi data siswa ke dalam tabel PDF
        $no = 1;
        foreach ($data_siswa as $row) {
            // Menambahkan data ke dalam sel tabel PDF
            $pdf->Cell(10, 10, $no, 1, 0, 'C');
            $pdf->Cell(50, 10, $row['nama_siswa'], 1, 0, 'C');
            $pdf->Cell(30, 10, $row['kelas'], 1, 0, 'C');
            $pdf->Cell(50, 10, $row['nis_siswa'], 1, 0, 'C');
            $pdf->Cell(30, 10, "Rp. " . number_format($row['jumlah_bayar'], 0, ',', '.'), 1, 0, 'C');
            $pdf->Ln();
            $no++;
        }

        // Output PDF di browser
        $pdf->Output();
    } else {
        echo "Tidak ada data yang dipilih atau tidak ditemukan data yang sesuai.";
    }
}
?>
