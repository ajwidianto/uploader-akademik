<?php
require __DIR__ . '/vendor/autoload.php';

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

            $jsonArray = [];
            $headers = $sheetData[1];
            $invalidRows = [];  // Menyimpan daftar baris yang tidak lengkap

            for ($i = 2; $i <= count($sheetData); $i++) {
                $row = $sheetData[$i];

                // Validasi kolom wajib
                if (
                    empty($row['A']) ||  // NIM
                    empty($row['B']) ||  // JENIS KELUAR
                    empty($row['C']) ||  // TANGGAL KELUAR
                    empty($row['D']) ||  // PERIODE KELUAR
                    empty($row['H'])     // IPK
                ) {
                    $invalidRows[] = $i;  // Simpan nomor baris yang tidak lengkap
                    continue; // Lewatkan baris jika ada kolom wajib yang kosong
                }

                $jsonRow = [
                    'NIM' => $row['A'],
                    'JENIS_KELUAR' => $row['B'],
                    'TANGGAL_KELUAR' => $row['C'],
                    'PERIODE_KELUAR' => $row['D'],
                    'KETERANGAN' => $row['E'],
                    'NOMOR_SK_YUDISIUM' => $row['F'],
                    'TANGGAL_SK_YUDISIUM' => $row['G'],
                    'IPK' => $row['H'],
                    'NOMOR_IJAZAH' => $row['I'],
                    'JALUR_SKRIPSI' => $row['J'],
                    'JUDUL_SKRIPSI' => $row['K'],
                    'BULAN_AWAL_BIMBINGAN' => $row['L'],
                    'BULAN_AKHIR_BIMBINGAN' => $row['M']
                ];
                $jsonArray[] = $jsonRow;
            }

            $jsonFile = $target_dir . pathinfo($fileName, PATHINFO_FILENAME) . '.json';
            file_put_contents($jsonFile, json_encode($jsonArray, JSON_PRETTY_PRINT));

            // Redirect ke halaman display.php dengan parameter file JSON dan daftar invalidRows
            header("Location: display2.php?file=" . urlencode($jsonFile) . "&invalidRows=" . urlencode(json_encode($invalidRows)));
            exit;

        } catch (Exception $e) {
            echo "Error processing file: " . $e->getMessage();
        }
    } else {
        echo "Failed to upload file.";
    }
}
?>
