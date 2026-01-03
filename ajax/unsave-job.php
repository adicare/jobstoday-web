<?php
session_start();
require_once("../config/config.php");

header("Content-Type: application/json");

if (!isset($_SESSION['applicant_id'])) {
    echo json_encode(["success" => false, "message" => "Login required"]);
    exit;
}

if (!isset($_POST['job_id'])) {
    echo json_encode(["success" => false, "message" => "Invalid job"]);
    exit;
}

$pid  = intval($_SESSION['applicant_id']);
$job  = intval($_POST['job_id']);

$q = $conn->prepare("DELETE FROM saved_jobs WHERE applicant_id=? AND job_id=?");
$q->bind_param("ii", $pid, $job);
$q->execute();

/* Updated count */
$count = $conn->query("SELECT COUNT(*) AS c FROM saved_jobs WHERE applicant_id=$pid")
              ->fetch_assoc()['c'];

echo json_encode([
    "success" => true,
    "saved_count" => $count
]);
