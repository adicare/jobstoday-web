<?php
include "../config/config.php";

$q = trim($_GET['q'] ?? '');

if (strlen($q) < 3) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("
    SELECT provider_id, provider_name
    FROM course_provider_master
    WHERE provider_name LIKE ?
    ORDER BY provider_name
    LIMIT 20
");

$like = "%{$q}%";
$stmt->bind_param("s", $like);
$stmt->execute();

$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
