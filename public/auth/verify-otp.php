<!-- ============================================================
     FILE: verify-otp.php
     USE: OTP Input Page for Email (and Mobile if enabled)
     RESPONSE #: 4
     ============================================================ -->

<?php
session_start();

// If someone directly opens this page without registration
if (!isset($_SESSION['reg_data'])) {
    header("Location: ../register-applicant.php");
    exit;
}

$reg = $_SESSION['reg_data'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP - CareerJano</title>

    <style>
        body { background:#f2f2f2; font-family: Arial; }
        .otp-box {
            width: 420px; margin: 50px auto; padding: 25px;
            background: #fff; border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
        input {
            width: 100%; padding: 12px; margin: 10px 0;
            border: 1px solid #ccc; border-radius: 6px;
            font-size: 16px;
            text-align:center;
        }
        button {
            width: 100%; padding: 12px;
            background: #28a745; border:none; color:#fff;
            border-radius: 6px; font-size: 16px; cursor:pointer;
        }
        button:hover { background:#218838; }
    </style>
</head>

<body>

<div class="otp-box">
    <h2>Verify Your OTP</h2>

    <p><b>Email sent to:</b> <?= $reg['email'] ?></p>
    <p><b>Mobile:</b> <?= $reg['mobile'] ?> (if mobile OTP enabled)</p>

    <form action="verify-otp-process.php" method="POST">

        <label>Email OTP</label>
        <input type="text" name="email_otp" maxlength="6" required>

        <label>Mobile OTP (Optional)</label>
        <input type="text" name="mobile_otp" maxlength="6">

        <button type="submit">Verify OTP</button>
    </form>
</div>

</body>
</html>
