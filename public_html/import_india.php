<?php
// =======================================
// Import Nepal locations (JOB-ONLY MODE)
// FINAL – NEPAL SAFE VERSION
// =======================================

set_time_limit(0);
ini_set('memory_limit', '1024M');

require_once __DIR__ . '/../config/config.php';

$filePath = __DIR__ . '/data/nepal_clean.txt';

if (!file_exists($filePath)) {
    die("❌ File not found: $filePath");
}

$file = fopen($filePath, 'r');
if (!$file) {
    die("❌ Cannot open nepal_clean.txt");
}

$stmt = $conn->prepare(
    "INSERT INTO nepal_location
     (country, state, district, tehsil, village, pincode, lat, lng)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    die("❌ Prepare failed: " . $conn->error);
}

$inserted = 0;
$skipped  = 0;

$conn->autocommit(false);

while (($line = fgets($file)) !== false) {

    $line = rtrim($line, "\r\n");
    if ($line === '') {
        continue;
    }

    // split tab-delimited
    $row = explode("\t", $line);
    $row = array_map('trim', $row);
    $row = array_pad($row, 8, null);

    [
        $country,
        $state,
        $district,
        $tehsil,
        $village,
        $pincode,
        $lat,
        $lng
    ] = $row;

    // country must be NP
    if ($country !== 'NP') {
        $skipped++;
        continue;
    }

    // minimum job-level fields required
    if ($state === '' || $district === '' || $tehsil === '') {
        $skipped++;
        continue;
    }

    // normalize optional fields
    if (!is_numeric($lat))  $lat = null;
    if (!is_numeric($lng))  $lng = null;
    if (!is_numeric($pincode)) $pincode = null;
    if ($village === '') $village = null;

    $stmt->bind_param(
        "ssssssss",
        $country,
        $state,
        $district,
        $tehsil,
        $village,
        $pincode,
        $lat,
        $lng
    );

    if ($stmt->execute()) {
        $inserted++;
    } else {
        $skipped++;
    }
}

$conn->commit();
$conn->autocommit(true);

fclose($file);
$stmt->close();

echo "✅ Nepal import completed<br>";
echo "Inserted: {$inserted}<br>";
echo "Skipped: {$skipped}<br>";
