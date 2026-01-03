<?php
/* ============================================================
   FILE: applicant/upload-resume-process.php
   USE : Save PDF/DOC/DOCX Resume & Update job_seekers table
   ============================================================ */

session_start();
include "../config/config.php";

if (!isset($_SESSION['applicant_id'])) {
    die("Not logged in.");
}

$app_id = $_SESSION['applicant_id'];

/* ============================
   VALIDATE FILE INPUT
   ============================ */
if (!isset($_FILES['resume']) || $_FILES['resume']['error'] != 0) {
    $_SESSION['resume_error'] = "Upload error. Please try again.";
    header("Location: upload-resume.php");
    exit;
}

$file = $_FILES['resume'];
$file_name = $file['name'];
$file_tmp = $file['tmp_name'];
$file_size = $file['size'];
$file_type = $file['type'];

/* ============================
   GET FILE EXTENSION
   ============================ */
$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

/* ============================
   ALLOW ONLY PDF, DOC, DOCX
   ============================ */
$allowed_extensions = ['pdf', 'doc', 'docx'];

if (!in_array($file_ext, $allowed_extensions)) {
    $_SESSION['resume_error'] = "Only PDF, DOC, and DOCX files are allowed.";
    header("Location: upload-resume.php");
    exit;
}

/* ============================
   VALIDATE MIME TYPES (Security)
   ============================ */
$allowed_mime_types = [
    'application/pdf',
    'application/msword',                                                         // .doc
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',    // .docx
];

// Additional check using finfo
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$detected_mime = finfo_file($finfo, $file_tmp);
finfo_close($finfo);

// Accept if either matches
if (!in_array($file_type, $allowed_mime_types) && !in_array($detected_mime, $allowed_mime_types)) {
    $_SESSION['resume_error'] = "Invalid file format. Please upload a valid PDF, DOC, or DOCX file.";
    header("Location: upload-resume.php");
    exit;
}

/* ============================
   CHECK FILE SIZE (5MB MAX)
   ============================ */
$max_size = 5 * 1024 * 1024; // 5MB in bytes

if ($file_size > $max_size) {
    $_SESSION['resume_error'] = "File too large. Maximum allowed size is 5MB.";
    header("Location: upload-resume.php");
    exit;
}

/* ============================
   DELETE OLD RESUME IF EXISTS
   ============================ */
$stmt = $conn->prepare("SELECT resume_file FROM job_seekers WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $app_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$old_resume = $user['resume_file'] ?? '';

if (!empty($old_resume)) {
    $old_path = "../uploads/resume/" . $old_resume;
    if (file_exists($old_path)) {
        unlink($old_path); // Delete old file
    }
}

/* ============================
   ENSURE FOLDER EXISTS
   ============================ */
$upload_dir = "../uploads/resume/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

/* ============================
   UNIQUE FILE NAME WITH EXTENSION
   ============================ */
$new_name = "resume_" . $app_id . "_" . time() . "." . $file_ext;
$target = $upload_dir . $new_name;

/* ============================
   MOVE FILE
   ============================ */
if (!move_uploaded_file($file_tmp, $target)) {
    $_SESSION['resume_error'] = "File upload failed. Please try again.";
    header("Location: upload-resume.php");
    exit;
}

/* ============================
   UPDATE DATABASE
   ============================ */
$stmt = $conn->prepare("UPDATE job_seekers SET resume_file = ? WHERE id = ?");
$stmt->bind_param("si", $new_name, $app_id);

if ($stmt->execute()) {
    $_SESSION['resume_msg'] = "Resume uploaded successfully! (" . strtoupper($file_ext) . " file)";
} else {
    $_SESSION['resume_error'] = "Database update failed: " . $stmt->error;
}

$stmt->close();

/* ============================
   SUCCESS - REDIRECT BACK
   ============================ */
header("Location: upload-resume.php?uploaded=1");
exit;

?>