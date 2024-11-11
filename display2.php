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
                echo '<tr>';
                foreach (array_keys($data[0]) as $header) {
                    echo '<th>' . htmlspecialchars($header) . '</th>';
                }
                echo '</tr>';

                foreach ($data as $row) {
                    echo '<tr>';
                    foreach ($row as $value) {
                        echo '<td>' . htmlspecialchars($value) . '</td>';
                    }
                    echo '</tr>';
                }
                echo '</table>';

                echo "<script>const jsonData = " . json_encode($data) . ";</script>";
            } else {
                echo "<p>No valid data to display. Make sure all required fields are filled.</p>";
            }
        } else {
            echo "<p>No file specified.</p>";
        }

        // Menampilkan invalidRows dalam JavaScript untuk ditampilkan dalam popup
        if (isset($_GET['invalidRows'])) {
            $invalidRows = json_decode($_GET['invalidRows']);
            echo "<script>const invalidRows = " . json_encode($invalidRows) . ";</script>";
        }
        ?>
        
        <!-- Tombol Kirim ke API -->
        <?php if (!empty($data)) : ?>
            <button onclick="sendToApi()">Send Data to API</button>
            <p id="status-message"></p>
        <?php endif; ?>
    </div>

    <!-- Popup untuk menampilkan baris tidak lengkap -->
    <div id="popup" class="popup">
        <div class="popup-content">
            <span class="close-btn" onclick="closePopup()">&times;</span>
            <h2>Incomplete Rows</h2>
            <p id="popup-message"></p>
        </div>
    </div>

    <script>
        // Menampilkan popup jika ada invalidRows
        function showPopup(message) {
            const popup = document.getElementById("popup");
            const popupMessage = document.getElementById("popup-message");
            popupMessage.innerHTML = message;
            popup.style.display = "block";
        }

        function closePopup() {
            document.getElementById("popup").style.display = "none";
        }

        if (invalidRows && invalidRows.length > 0) {
            const message = `The following rows are incomplete: ${invalidRows.join(', ')}`;
            showPopup(message);
        }

        function sendToApi() {
            const apiUrl = 'https://example.com/api/receive-data';
            const statusMessage = document.getElementById('status-message');
            statusMessage.textContent = "Sending data to API...";

            jsonData.forEach((row, index) => {
                fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(row)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Failed to send data for row ${index + 1}`);
                    }
                    return response.json();
                })
                .then(data => {
                    statusMessage.textContent = `Data successfully sent for row ${index + 1}`;
                })
                .catch(error => {
                    console.error('Error:', error);
                    statusMessage.textContent = error.message;
                });
            });
        }
    </script>
</body>
</html>
