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

    // Validasi tipe file
    if (!in_array($fileType, ['xlsx', 'csv'])) {
        echo "Invalid file type. Only .xlsx and .csv files are allowed.";
        exit;
    }

    // Pindahkan file yang diunggah ke direktori target
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        try {
            $spreadsheet = IOFactory::load($target_file);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $jsonArray = []; // Array untuk menampung semua baris data
            $apiUrl = "https://example.com/api/receive-data"; // Ganti dengan URL API yang dituju

            // Loop untuk memproses setiap baris
            for ($i = 2; $i <= count($sheetData); $i++) {
                $row = $sheetData[$i];
                $jsonRow = [
                    $row['A'], // NIM
                    $row['B'], // JENIS_KELUAR
                    $row['C'], // TANGGAL_KELUAR
                    $row['D'], // PERIODE_KELUAR
                    $row['E'], // KETERANGAN
                    $row['F'], // NOMOR_SK_YUDISIUM
                    $row['G'], // TANGGAL_SK_YUDISIUM
                    $row['H'], // IPK
                    $row['I'], // NOMOR_IJAZAH
                    $row['J'], // JALUR_SKRIPSI
                    $row['K'], // JUDUL_SKRIPSI
                    $row['L'], // BULAN_AWAL_BIMBINGAN
                    $row['M']  // BULAN_AKHIR_BIMBINGAN
                ];

                // Menyimpan setiap baris ke jsonArray
                $jsonArray[] = $jsonRow;
            }

            // Mengirim seluruh jsonArray ke API dalam satu kali request
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonArray));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Menangani respons API
            if ($httpCode !== 200) {
                echo "Failed to send data. HTTP Code: " . $httpCode . "<br>";
            } else {
                echo "All data sent successfully.<br>";
            }

            // Menyimpan jsonArray ke dalam file JSON
            $jsonFile = $target_dir . pathinfo($fileName, PATHINFO_FILENAME) . '.json';
            file_put_contents($jsonFile, json_encode($jsonArray, JSON_PRETTY_PRINT));

            // Redirect ke halaman display.php untuk menampilkan file JSON
            header("Location: display.php?file=" . urlencode($jsonFile));
            exit;

        } catch (Exception $e) {
            echo "Error processing file: " . $e->getMessage();
        }
    } else {
        echo "Failed to upload file.";
    }
}
?>