<?php
/* ============================================================
   FILE: send-email-otp.php
   USE: Handles Email OTP for Applicant Registration
   RESPONSE #: 3
   ============================================================ */

include "../config.php";  // database connection

// Collect form data
$full_name = $_POST['full_name'];
$email     = $_POST['email'];
$mobile    = $_POST['mobile'];
$gender    = $_POST['gender'];
$dob       = $_POST['dob'];
$state     = $_POST['state'];
$city      = $_POST['city'];

// Generate 6-digit OTP
$email_otp = rand(100000, 999999);

// OTP expiry time (5 minutes)
$expires_at = date("Y-m-d H:i:s", strtotime("+5 minutes"));

// Insert OTP into logs
$sql = "INSERT INTO applicant_otp_logs (email, mobile, email_otp, expires_at)
        VALUES ('$email', '$mobile', '$email_otp', '$expires_at')";

if (!$conn->query($sql)) {
    die("OTP log error: " . $conn->error);
}

// Store registration details temporarily in session
session_start();
$_SESSION['reg_data'] = [
    'full_name' => $full_name,
    'email'     => $email,
    'mobile'    => $mobile,
    'gender'    => $gender,
    'dob'       => $dob,
    'state'     => $state,
    'city'      => $city
];

// SEND EMAIL OTP (PHPMailer recommended later)
// For now: simple PHP mail()

$subject = "CareerJano - Email Verification OTP";
$message = "Dear $full_name,\n\nYour OTP is: $email_otp\nValid for 5 minutes.\n\nRegards,\nCareerJano";

$headers = "From: no-reply@careerjano.com";

// Mail function
mail($email, $subject, $message, $headers);

// Redirect to OTP verification page
header("Location: verify-otp.php");
exit;

?>
