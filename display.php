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
                echo '<table>';
                // Header tabel
                echo '<tr>';
                foreach (array_keys($data[0]) as $header) {
                    echo '<th>' . htmlspecialchars($header) . '</th>';
                }
                echo '</tr>';

                // Baris data
                foreach ($data as $row) {
                    echo '<tr>';
                    foreach ($row as $value) {
                        echo '<td>' . htmlspecialchars($value) . '</td>';
                    }
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo "<p>Data not found or invalid JSON format.</p>";
            }
        } else {
            echo "<p>No file specified.</p>";
        }
        ?>
    </div>
</body>
</html>
