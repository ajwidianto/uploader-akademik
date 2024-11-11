<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Uploaded</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="table-container">
        <h1>Uploaded Data</h1>
        <?php
        if (isset($_GET['file'])) {
            $jsonFile = $_GET['file'];
            $data = json_decode(file_get_contents($jsonFile), true);

            if ($data && is_array($data)) {
                echo "<table class='table table-striped' border='1'>";
                echo "<tr>
                        <th>Baris</th>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th>ID Keluar</th>
                        <th>Tanggal Keluar</th>
                        <th>No Ijazah</th>
                        <th>Error</th>
                        <th>Description</th>
                      </tr>";

                $rowIndex = 1;
                foreach ($data as $row) {
                    echo "<tr>";
                    echo "<td>" . $rowIndex++ . "</td>";
                    echo "<td>" . htmlspecialchars($row['NIM']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Nama'] ?? '-') . "</td>";
                    echo "<td>" . htmlspecialchars($row['JENIS_KELUAR']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['TANGGAL_KELUAR']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['NOMOR_IJAZAH'] ?? '-') . "</td>";
                    echo "<td>" . (isset($row['Error']) ? htmlspecialchars($row['Error']) : '0') . "</td>";
                    echo "<td>" . (isset($row['Description']) ? htmlspecialchars($row['Description']) : '-') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No valid data to display. Make sure all required fields are filled.</p>";
            }
        } else {
            echo "<p>No file specified.</p>";
        }

        if (isset($_GET['invalidRows'])) {
            $invalidRows = json_decode($_GET['invalidRows']);
            echo "<script>const invalidRows = " . json_encode($invalidRows) . ";</script>";
        }
        ?>
    </div>
</body>
</html>
