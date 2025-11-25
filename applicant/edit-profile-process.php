<?php
/* ============================================================
   FILE: applicant/edit-profile-process.php
   USE : Save updated profile data into job_seekers table
   RESPONSE #: 8
   ============================================================ */

session_start();
include "../config/config.php";

if (!isset($_SESSION['applicant_id'])) {
    die("Not logged in.");
}

$app_id = $_SESSION['applicant_id'];

// Collect POST data
$full_name = trim($_POST['full_name']);
$mobile    = trim($_POST['mobile']);
$gender    = trim($_POST['gender']);
$dob       = trim($_POST['dob']);
$state     = trim($_POST['state']);
$city      = trim($_POST['city']);

// Update query
$stmt = $conn->prepare("
    UPDATE job_seekers SET 
        full_name = ?, 
        mobile = ?, 
        gender = ?, 
        dob = ?, 
        state = ?, 
        city = ?
    WHERE id = ?
");

$stmt->bind_param("ssssssi",
    $full_name, $mobile, $gender, $dob, $state, $city, $app_id
);

$stmt->execute();

// Redirect back to dashboard
header("Location: ../public/applicant-dashboard.php?profile=updated");
exit;

?>
