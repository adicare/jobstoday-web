<?php
session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

require_once "../config/config.php";

$app_id = (int) $_SESSION['applicant_id'];

// Fetch email and mobile
$stmt = $conn->prepare("SELECT email, mobile FROM job_seekers WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $app_id);
$stmt->execute();
$stmt->bind_result($email, $mobile);
$stmt->fetch();
$stmt->close();

// Generate new OTPs
$email_otp = sprintf("%06d", mt_rand(100000, 999999));
// $mobile_otp = sprintf("%06d", mt_rand(100000, 999999)); // DISABLED - Mobile OTP

// Store OTPs in session
$_SESSION['email_otp'] = $email_otp;
// $_SESSION['mobile_otp'] = $mobile_otp; // DISABLED - Mobile OTP
$_SESSION['otp_generated_at'] = time();
$_SESSION['verify_email'] = $email;
$_SESSION['verify_mobile'] = $mobile;

// Send Email OTP
$to = $email;
$subject = "Verify Your Email - New OTP Code";
$message = "Your new OTP for email verification is: $email_otp\n\nThis OTP is valid for 10 minutes.\n\nIf you didn't request this, please ignore this email.";
$headers = "From: noreply@yourdomain.com\r\n";
$headers .= "Reply-To: noreply@yourdomain.com\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

mail($to, $subject, $message, $headers);

// DISABLED - Send Mobile OTP (integrate SMS API here)
// Example: sendSMS($mobile, "Your new OTP is: $mobile_otp");

// Redirect back to verification page
header("Location: verify-profile.php");
exit;