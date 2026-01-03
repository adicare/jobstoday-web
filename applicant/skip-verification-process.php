<?php
/* ======================================================
   skip-verification-process.php
   Allows user to skip verification and continue
   ====================================================== */

session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: edit-profile.php");
    exit;
}

// Clear OTP session data
unset($_SESSION['email_otp']);
// unset($_SESSION['mobile_otp']); // DISABLED
unset($_SESSION['otp_generated_at']);
unset($_SESSION['verify_email']);
unset($_SESSION['verify_mobile']);

// Redirect to edit profile page with success message
// User data is already saved, just skipping verification
header("Location: edit-profile.php?success=1&skipped=1");
exit;