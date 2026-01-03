<?php
header("Content-Type: application/json");

// Path to pincode database
require_once __DIR__ . "/../data/pincode_db.php";  

$pincode = $_POST['pincode'] ?? '';

if (!$pincode || !isset($pincodeDB[$pincode])) {
    echo json_encode([
        "status" => "error",
        "message" => "Pincode not found",
        "lat" => null,
        "lng" => null
    ]);
    exit;
}

// Return lat/lng
echo json_encode([
    "status" => "success",
    "pincode" => $pincode,
    "lat" => $pincodeDB[$pincode]['lat'],
    "lng" => $pincodeDB[$pincode]['lng']
]);
exit;
?>
