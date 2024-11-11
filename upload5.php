<?php
require __DIR__ . '/vendor/autoload.php';
include_once 'config.php'; // Menghubungkan dengan database

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['submit'])) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file = $_FILES['file'];
    $fileName = basename($file["name"]);
    $target_file = $target_dir . $fileName;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if (!in_array($fileType, ['xlsx', 'csv'])) {
        echo "Invalid file type. Only .xlsx and .csv files are allowed.";
        exit;
    }

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        try {
            $spreadsheet = IOFactory::load($target_file);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $invalidRows = [];  // Menyimpan daftar baris yang tidak lengkap
            $berhasil = $gagal = 0;

            for ($i = 2; $i <= count($sheetData); $i++) {
                $row = $sheetData[$i];

                // Validasi kolom wajib
                if (empty($row['A']) || empty($row['B']) || empty($row['C']) || empty($row['D']) || empty($row['H'])) {
                    $invalidRows[] = $i;
                    continue;
                }

                // Persiapkan data untuk query
                $nim = $row['A'];
                $jenis_keluar = $row['B'];
                $tanggal_keluar = $row['C'];
                $periode_keluar = $row['D'];
                $keterangan = $row['E'];
                $nomor_sk_yudisium = $row['F'];
                $tanggal_sk_yudisium = $row['G'];
                $ipk = $row['H'];
                $nomor_ijazah = $row['I'];
                $jalur_skripsi = $row['J'];
                $judul_skripsi = $row['K'];
                $bulan_awal_bimbingan = $row['L'];
                $bulan_akhir_bimbingan = $row['M'];

                // Query untuk memasukkan data ke tabel insertmahasiswalulusdo
                $query = "INSERT INTO insertmahasiswalulusdo (nim, id_jenis_keluar, tanggal_keluar, keterangan, nomor_sk_yudisium, tanggal_sk_yudisium, ipk, nomor_ijazah, jalur_skripsi, judul_skripsi, bulan_awal_bimbingan, bulan_akhir_bimbingan, err_no, err_desc)
                        VALUES ('$nim', '$jenis_keluar', '$tanggal_keluar', '$keterangan', '$nomor_sk_yudisium', '$tanggal_sk_yudisium', '$ipk', '$nomor_ijazah', '$jalur_skripsi', '$judul_skripsi', '$bulan_awal_bimbingan', '$bulan_akhir_bimbingan', '0', 'Data Inserted Successfully')";


                if (mysqli_query($db, $query)) {
                    $berhasil++;
                } else {
                    $gagal++;
                    $invalidRows[] = $i; // Simpan baris yang gagal di-insert
                }
            }

            // Redirect ke halaman display2.php dengan parameter daftar invalidRows
            header("Location: display3.php?berhasil=$berhasil&gagal=$gagal&invalidRows=" . urlencode(json_encode($invalidRows)));
            exit;

        } catch (Exception $e) {
            echo "Error processing file: " . $e->getMessage();
        }
    } else {
        echo "Failed to upload file.";
    }
}
