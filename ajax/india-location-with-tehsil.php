<?php
require_once "../config/config.php";

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';

/* =========================
   1️⃣ STATES
   ========================= */
if ($type === 'states') {

    $sql = "
        SELECT DISTINCT state
        FROM india_location
        WHERE state IS NOT NULL AND state <> ''
        ORDER BY state
    ";

    $res = $conn->query($sql);
    $out = [];

    while ($row = $res->fetch_assoc()) {
        $out[] = $row['state'];
    }

    echo json_encode($out);
    exit;
}

/* =========================
   2️⃣ DISTRICTS
   ========================= */
if ($type === 'districts') {

    $state = $_GET['state'] ?? '';

    $stmt = $conn->prepare("
        SELECT DISTINCT district
        FROM india_location
        WHERE state = ?
          AND district IS NOT NULL AND district <> ''
        ORDER BY district
    ");
    $stmt->bind_param("s", $state);
    $stmt->execute();

    $res = $stmt->get_result();
    $out = [];

    while ($row = $res->fetch_assoc()) {
        $out[] = $row['district'];
    }

    echo json_encode($out);
    exit;
}

/* =========================
   3️⃣ TEHSILS  ✅ (DISTINCT FIX)
   ========================= */
if ($type === 'tehsils') {

    $state    = $_GET['state'] ?? '';
    $district = $_GET['district'] ?? '';

    $stmt = $conn->prepare("
        SELECT DISTINCT tehsil
        FROM india_location
        WHERE state = ?
          AND district = ?
          AND tehsil IS NOT NULL AND tehsil <> ''
        ORDER BY tehsil
    ");
    $stmt->bind_param("ss", $state, $district);
    $stmt->execute();

    $res = $stmt->get_result();
    $out = [];

    while ($row = $res->fetch_assoc()) {
        $out[] = $row['tehsil'];
    }

    echo json_encode($out);
    exit;
}

/* =========================
   4️⃣ VILLAGES  ✅ (FULL ROW)
   ========================= */
if ($type === 'villages') {

    $state    = $_GET['state'] ?? '';
    $district = $_GET['district'] ?? '';
    $tehsil   = $_GET['tehsil'] ?? '';

    $stmt = $conn->prepare("
        SELECT
            village,
            lat,
            lng,
            pincode
        FROM india_location
        WHERE state = ?
          AND district = ?
          AND tehsil = ?
          AND village IS NOT NULL AND village <> ''
        ORDER BY village
    ");
    $stmt->bind_param("sss", $state, $district, $tehsil);
    $stmt->execute();

    $res = $stmt->get_result();
    $out = [];

    while ($row = $res->fetch_assoc()) {
        $out[] = [
            'village'  => $row['village'],
            'latitude' => $row['lat'],
            'longitude'=> $row['lng'],
            'pincode'  => $row['pincode']
        ];
    }

    echo json_encode($out);
    exit;
}

/* =========================
   INVALID REQUEST
   ========================= */
echo json_encode([]);
