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
            $apiUrl = "https://example.com/api/receive-data"; // Ganti dengan URL API yang dituju

            for ($i = 2; $i <= count($sheetData); $i++) {
                $row = $sheetData[$i];
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
                
                // Kirim jsonRow ke API
                $ch = curl_init($apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json'
                ]);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonRow));

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                // Menangani error jika respons API bukan 200
                if ($httpCode !== 200) {
                    echo "Failed to send data for NIM: " . $jsonRow['NIM'] . "<br>";
                } else {
                    echo "Data sent for NIM: " . $jsonRow['NIM'] . "<br>";
                }

                // Simpan ke dalam array untuk disimpan dalam file JSON
                $jsonArray[] = $jsonRow;
            }

            $jsonFile = $target_dir . pathinfo($fileName, PATHINFO_FILENAME) . '.json';
            file_put_contents($jsonFile, json_encode($jsonArray, JSON_PRETTY_PRINT));

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
