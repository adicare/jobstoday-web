<?php
/* ============================================================
   FILE: send-mobile-otp.php
   USE: OPTIONAL - Sends Mobile OTP (If admin enabled)
   RESPONSE #: 3
   ============================================================ */

include "../config.php";

$mobile = $_POST['mobile'];
$mobile_otp = rand(100000, 999999);

// Your SMS API (EDUMARC or other)
$sms_api_url = "https://your-sms-api.com/send?to=$mobile&msg=Your OTP is $mobile_otp";

// Call SMS API
file_get_contents($sms_api_url);

// Save mobile OTP into logs
$conn->query("UPDATE applicant_otp_logs 
              SET mobile_otp='$mobile_otp' 
              WHERE mobile='$mobile' 
              ORDER BY id DESC LIMIT 1");

?>
