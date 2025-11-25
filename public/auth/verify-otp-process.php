<?php
/* ============================================================
   FILE: verify-otp-process.php
   USE: Verifies OTP and registers applicant
   RESPONSE #: 4
   ============================================================ */

session_start();
include "../../config/config.php"; // your DB file

if (!isset($_SESSION['reg_data'])) {
    die("Invalid access.");
}

$reg = $_SESSION['reg_data'];

$email       = $reg['email'];
$mobile      = $reg['mobile'];
$full_name   = $reg['full_name'];
$gender      = $reg['gender'];
$dob         = $reg['dob'];
$state       = $reg['state'];
$city        = $reg['city'];

$input_email_otp  = $_POST['email_otp'];
$input_mobile_otp = $_POST['mobile_otp'];

// Fetch latest OTP log for this email
$sql = "SELECT * FROM applicant_otp_logs 
        WHERE email='$email' 
        ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("OTP not found.");
}

$otp = $result->fetch_assoc();

// Check email OTP
if ($input_email_otp != $otp['email_otp']) {
    die("Invalid Email OTP");
}

// Check expiry
if (strtotime($otp['expires_at']) < time()) {
    die("OTP expired. Please register again.");
}

// OPTIONAL → Check mobile OTP only if admin enabled it later
if ($otp['mobile_otp'] != "" && $input_mobile_otp != $otp['mobile_otp']) {
    die("Invalid Mobile OTP");
}

// Insert applicant into job_seekers
$stmt = $conn->prepare("
    INSERT INTO job_seekers 
    (full_name, email, mobile, gender, dob, state, city, email_verified, mobile_verified)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$email_verified  = 1;
$mobile_verified = ($otp['mobile_otp'] ? 1 : 0);

$stmt->bind_param(
    "sssssssii",
    $full_name, $email, $mobile, $gender, $dob, $state, $city,
    $email_verified, $mobile_verified
);

$stmt->execute();

// Get inserted user ID
$new_user_id = $stmt->insert_id;

// Start login session
$_SESSION['applicant_id'] = $new_user_id;

// Clean temporary session
unset($_SESSION['reg_data']);

// Redirect to applicant dashboard
header("Location: ../../applicant.php");
exit;
?>
