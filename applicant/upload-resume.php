<?php
/* ============================================================
   FILE: applicant/upload-resume.php
   USE : Resume Upload Form (PDF only)
   RESPONSE #: 6
   ============================================================ */

session_start();
include "../config/config.php";

// Redirect if not logged in
if (!isset($_SESSION['applicant_id'])) {
    header("Location: ../public/auth/login.php");
    exit;
}

$app_id = $_SESSION['applicant_id'];

?>
<!DOCTYPE html>
<html>
<head>
<title>Upload Resume - CareerJano</title>
<style>
    body { background:#f2f2f2; font-family:Arial; }
    .box {
        width:450px; margin:50px auto; background:white;
        padding:25px; border-radius:10px;
        box-shadow:0 0 10px rgba(0,0,0,0.1);
    }
    input { width:100%; padding:10px; margin:10px 0; }
    button {
        width:100%; padding:12px; background:#007bff;
        border:none; color:white; border-radius:6px;
        cursor:pointer; font-size:16px;
    }
</style>
</head>
<body>

<div class="box">
    <h2>Upload Your Resume</h2>
    <p>Allowed Format: <b>PDF only</b></p>

    <form action="upload-resume-process.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="resume" accept="application/pdf" required>
        <button type="submit">Upload Resume</button>
    </form>
</div>

</body>
</html>
