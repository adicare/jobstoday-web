<?php
session_start();
ob_start(); // Prevent any unwanted output before JSON

header("Content-Type: application/json");

// If not logged in → 0%
if (!isset($_SESSION['applicant_id'])) {
    ob_end_clean();
    echo json_encode(["percent" => 0]);
    exit;
}

$app_id = intval($_SESSION['applicant_id']);

// Correct paths
require_once "../config/config.php";
require_once "../includes/profile_helpers.php";

// 1) Calculate fresh %
$percent = calculate_profile_completion($conn, $app_id);

// Safety check
if ($percent < 0) $percent = 0;
if ($percent > 100) $percent = 100;

// 2) Store in DB
$upd = $conn->prepare("UPDATE job_seekers SET profile_completed = ? WHERE id = ?");
$upd->bind_param("ii", $percent, $app_id);
$upd->execute();
$upd->close();

// Clear unwanted output
ob_end_clean();

// 3) Return JSON response
echo json_encode(["percent" => $percent]);
exit;
