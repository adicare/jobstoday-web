<?php
session_start();
include "../config/config.php";

if (!isset($_SESSION['applicant_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$id = (int)($data['id'] ?? 0);
$applicant_id = (int)$_SESSION['applicant_id'];

if ($id <= 0) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $conn->prepare("
    DELETE FROM applicant_education
    WHERE id = ? AND applicant_id = ?
");
$stmt->bind_param("ii", $id, $applicant_id);
$ok = $stmt->execute();
$stmt->close();

echo json_encode(['success' => $ok]);
