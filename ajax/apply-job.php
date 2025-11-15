<?php
/* ================================================================
   FILE: /ajax/apply-job.php
   PURPOSE:
     - Handle AJAX apply requests
     - Check login (session)
     - Prevent duplicate applications
     - Insert into job_applications table
     - Return simple text responses:
         * "login_required"
         * "already_applied"
         * "success"
         * "error"
   ================================================================ */

session_start();
include("../config/config.php"); // adjust path if needed

// Check login
if (!isset($_SESSION['applicant_id']) || empty($_SESSION['applicant_id'])) {
    echo "login_required";
    exit;
}
$applicant_id = intval($_SESSION['applicant_id']);

// Validate input
if (!isset($_POST['job_id']) || empty($_POST['job_id'])) {
    echo "error";
    exit;
}
$job_id = intval($_POST['job_id']);

// Prevent duplicate
$stmt = $conn->prepare("SELECT id FROM job_applications WHERE job_id = ? AND applicant_id = ? LIMIT 1");
$stmt->bind_param("ii", $job_id, $applicant_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    echo "already_applied";
    $stmt->close();
    exit;
}
$stmt->close();

// Insert application
$stmt2 = $conn->prepare("INSERT INTO job_applications (job_id, applicant_id, applied_at) VALUES (?, ?, NOW())");
$stmt2->bind_param("ii", $job_id, $applicant_id);
$ok = $stmt2->execute();
$stmt2->close();

if ($ok) {
    echo "success";
} else {
    echo "error";
}
