<?php
session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

if (!isset($_SESSION['email_otp']) || !isset($_SESSION['mobile_otp'])) {
    header("Location: edit-profile.php");
    exit;
}

require_once "../config/config.php";
include "../includes/header.php";

echo "</div>";

$app_id = (int)$_SESSION['applicant_id'];
$verify_email = $_SESSION['verify_email'] ?? '';
$verify_mobile = $_SESSION['verify_mobile'] ?? '';

// Check OTP expiry (10 minutes)
$otp_age = time() - ($_SESSION['otp_generated_at'] ?? 0);
$otp_expired = $otp_age > 600; // 600 seconds = 10 minutes
?>

<style>
.page-wrapper{max-width:600px;margin:40px auto;padding:0 16px}
.verify-card{
  background:#fff;
  padding:30px;
  border-radius:10px;
  border:1px solid #e6edf9;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.verify-card h2{
  color:#004aad;
  margin-bottom:8px;
}
.verify-card p{
  color:#666;
  margin-bottom:20px;
}
.form-group{
  margin-bottom:16px;
}
.form-group label{
  display:block;
  font-weight:600;
  margin-bottom:6px;
  color:#333;
}
.form-control{
  width:100%;
  height:42px;
  padding:8px 12px;
  border:1px solid #ddd;
  border-radius:6px;
  font-size:16px;
}
.btn-verify{
  background:#004aad;
  color:#fff;
  padding:12px 24px;
  border-radius:8px;
  border:0;
  cursor:pointer;
  font-size:16px;
  width:100%;
}
.btn-verify:hover{
  background:#003a8c;
}
.info-box{
  background:#f6f9ff;
  border:1px solid #d7e6fb;
  padding:12px;
  border-radius:6px;
  margin-bottom:20px;
  font-size:14px;
}
.error-box{
  background:#fff3f3;
  border:1px solid #ffcdd2;
  padding:12px;
  border-radius:6px;
  margin-bottom:20px;
  color:#c62828;
}
.success-box{
  background:#e6ffed;
  border:1px solid #28a745;
  padding:12px;
  border-radius:6px;
  margin-bottom:20px;
  color:#28a745;
}
.resend-link{
  text-align:center;
  margin-top:16px;
  color:#666;
}
.resend-link a{
  color:#004aad;
  text-decoration:none;
}
.optional-label{
  color:#999;
  font-size:13px;
  font-weight:normal;
}
</style>

<div class="page-wrapper">
<div class="verify-card">

<h2>Verify Your Contact Details</h2>
<p>We've sent verification codes to your email and mobile number.</p>

<?php if ($otp_expired): ?>
  <div class="error-box">
    ⚠️ Your OTP has expired. Please <a href="resend-otp.php">click here</a> to resend.
  </div>
<?php endif; ?>

<?php if (!empty($_GET['error'])): ?>
  <div class="error-box">
    ❌ <?= htmlspecialchars($_GET['error']) ?>
  </div>
<?php endif; ?>

<div class="info-box">
  <strong>Email:</strong> <?= htmlspecialchars($verify_email) ?><br>
  <strong>Mobile:</strong> <?= htmlspecialchars($verify_mobile) ?>
</div>

<form action="verify-profile-process.php" method="POST">

  <div class="form-group">
    <label>Email OTP <span style="color:#dc3545">*</span></label>
    <input type="text" name="email_otp" class="form-control" 
           placeholder="Enter 6-digit code" maxlength="6" pattern="\d{6}" required>
  </div>

  <div class="form-group">
    <label>Mobile OTP <span class="optional-label">(Optional - if received)</span></label>
    <input type="text" name="mobile_otp" class="form-control" 
           placeholder="Enter 6-digit code (optional)" maxlength="6" pattern="\d{6}">
    <small style="color:#999;font-size:12px;">SMS may take a few minutes to arrive</small>
  </div>

  <button type="submit" class="btn-verify" <?= $otp_expired ? 'disabled' : '' ?>>
    Verify & Continue
  </button>

</form>

<div class="resend-link">
  Didn't receive the code? <a href="resend-otp.php">Resend OTP</a>
</div>

</div>
</div>

<?php include "../includes/footer.php"; ?>