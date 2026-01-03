



<?php
// DB connection (XAMPP default)
$conn = new mysqli("localhost", "root", "", "defchitt_jobsweb_db");

if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// Correct file path
$path = __DIR__ . "/public_html/data/bhutan.txt";

// Safety check
if (!file_exists($path)) {
    die("File not found: " . $path);
}

$file = fopen($path, "r");

while (($line = fgets($file)) !== false) {

    $line = trim($line);
    if ($line === '') continue;

    // Split by pipe |
    $row = array_map('trim', explode('|', $line));
    if (count($row) < 5) continue;

    $country = $row[0];
    $state   = $row[1];
    $city    = $row[2];
    $lat     = $row[3];
    $lng     = $row[4];

    $stmt = $conn->prepare(
        "INSERT INTO bhutan_locations (country, state, city, lat, lng)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("sssdd", $country, $state, $city, $lat, $lng);
    $stmt->execute();
}

fclose($file);

echo "✅ Bhutan locations imported successfully";
