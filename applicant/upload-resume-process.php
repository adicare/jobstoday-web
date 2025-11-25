<?php
/* ============================================================
   FILE: applicant/upload-resume-process.php
   USE : Save PDF Resume & Update job_seekers table
   RESPONSE #: 6
   ============================================================ */

session_start();
include "../config/config.php";

if (!isset($_SESSION['applicant_id'])) {
    die("Not logged in.");
}

$app_id = $_SESSION['applicant_id'];

// Check if file uploaded
if (!isset($_FILES['resume']) || $_FILES['resume']['error'] != 0) {
    die("Upload error. Please try again.");
}

$file = $_FILES['resume'];

// Validate PDF
$allowed = ['application/pdf'];

if (!in_array($file['type'], $allowed)) {
    die("Only PDF files are allowed.");
}

// Create uploads folder if not exist
$upload_dir = "../uploads/resume/";

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Final filename
$new_name = "resume_" . $app_id . "_" . time() . ".pdf";
$target = $upload_dir . $new_name;

// Save file
if (!move_uploaded_file($file['tmp_name'], $target)) {
    die("File upload failed.");
}

// Update DB
$stmt = $conn->prepare("UPDATE job_seekers SET resume_file = ? WHERE id = ?");
$stmt->bind_param("si", $new_name, $app_id);
$stmt->execute();

// Redirect back
header("Location: ../public/applicant-dashboard.php?resume=uploaded");
exit;

?>
