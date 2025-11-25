<?php
/* FILE: /applicant/upload-resume.php
   PURPOSE: Upload PDF resume (modern layout)
*/

session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

include "../config/config.php";
include "../includes/header.php";

$app_id = $_SESSION['applicant_id'];
$res = $conn->query("SELECT resume_file, full_name FROM job_seekers WHERE id = $app_id LIMIT 1");
$user = $res->fetch_assoc();
?>

<style>
.wrapper { display:flex; margin-top:30px; }
.sidebar { width:240px; background:#0a4c90; color:#fff; padding:20px; border-radius:8px; }
.sidebar a{ display:block; padding:12px; color:#fff; text-decoration:none; margin-bottom:8px; border-radius:5px; font-weight:600; }
.sidebar a.active{ background:#06376a; }
.content-box { flex:1; margin-left:20px; background:#fff; padding:25px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.08); }
.btn-upload{ padding:10px 16px; background:#0a4c90; color:#fff; border:none; border-radius:6px; }
.small-note{ color:#666; font-size:13px; margin-top:8px; }
.resume-info{ background:#f8f9fa; padding:12px; border-radius:6px; margin-bottom:12px; }
</style>

<div class="container">
  <div class="wrapper">
    <div class="sidebar">
      <h4><i class="bi bi-person-lines-fill me-2"></i> My Profile</h4>
      <a href="edit-profile.php">Personal Details</a>
      <a href="upload-photo.php">Profile Photo</a>
      <a href="upload-resume.php" class="active">Resume Upload</a>
      <a href="skills.php">Skills</a>
    </div>

    <div class="content-box">
      <h3 class="text-primary fw-bold mb-3">Upload Resume (PDF)</h3>

      <?php if (!empty($user['resume_file'])): ?>
        <div class="resume-info">
          Current Resume: <strong><?= htmlspecialchars($user['resume_file']) ?></strong>
          <div class="mt-2">
            <a class="btn btn-sm btn-outline-primary" href="/jobsweb/uploads/resume/<?= $user['resume_file'] ?>" target="_blank">Download</a>
          </div>
        </div>
      <?php else: ?>
        <div class="resume-info">No resume uploaded yet.</div>
      <?php endif; ?>

      <form action="upload-resume-process.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <label class="form-label">Choose resume (PDF only)</label>
          <input type="file" name="resume" accept="application/pdf" class="form-control" required>
          <div class="small-note">Only PDF allowed. Max 2MB recommended.</div>
        </div>

        <button type="submit" class="btn-upload">Upload Resume</button>
      </form>

    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>
