<?php
/* ============================================================
   FILE: applicant/upload-photo-process.php
   PURPOSE: Handle profile photo upload
   ============================================================ */

session_start();
include "../config/config.php";

// Auth check
if (!isset($_SESSION['applicant_id'])) {
    header("Location: ../public/auth/login.php");
    exit;
}

$app_id = $_SESSION['applicant_id'];

// Upload folder
$upload_dir = "../uploads/photos/";

// Validate file
if (!isset($_FILES['photo']) || $_FILES['photo']['error'] != 0) {
    die("File upload failed.");
}

$allowed = ['jpg', 'jpeg', 'png'];
$ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
    die("Invalid file type. Only JPG and PNG allowed.");
}

// Create unique filename
$new_name = "PHOTO_" . time() . "_" . rand(1000,9999) . "." . $ext;

// Move file
if (!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $new_name)) {
    die("Unable to save file.");
}

// Save filename in DB
$stmt = $conn->prepare("UPDATE job_seekers SET photo = ? WHERE id = ?");
$stmt->bind_param("si", $new_name, $app_id);
$stmt->execute();

/* =========================================
   FIXED REDIRECT — Stay on same photo page
   ========================================= */
header("Location: upload-photo.php?success=1");
exit;

?>
