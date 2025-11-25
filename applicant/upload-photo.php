<?php
/* FILE: /applicant/upload-photo.php
   PURPOSE: Upload profile photo (modern sidebar layout)
*/

session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

include "../config/config.php";
include "../includes/header.php";

$app_id = $_SESSION['applicant_id'];
// fetch user for info (optional)
$res = $conn->query("SELECT photo, full_name FROM job_seekers WHERE id = $app_id LIMIT 1");
$user = $res->fetch_assoc();
?>

<style>
.wrapper { display:flex; margin-top:30px; }
.sidebar { width:240px; background:#0a4c90; color:#fff; padding:20px; border-radius:8px; }
.sidebar h4{ font-size:20px; margin-bottom:18px; }
.sidebar a{ display:block; padding:12px; color:#fff; text-decoration:none; margin-bottom:8px; border-radius:5px; font-weight:600; }
.sidebar a.active{ background:#06376a; }
.sidebar a:hover{ background:#094983; }

.content-box { flex:1; margin-left:20px; background:#fff; padding:25px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.08); }
.photo-preview { width:110px; height:110px; border-radius:50%; object-fit:cover; border:2px solid #eee; }
.btn-upload{ padding:10px 16px; background:#0a4c90; color:#fff; border:none; border-radius:6px; }
.small-note{ color:#666; font-size:13px; margin-top:8px; }
</style>

<div class="container">
  <div class="wrapper">
    <div class="sidebar">
      <h4><i class="bi bi-person-lines-fill me-2"></i> My Profile</h4>
      <a href="edit-profile.php">Personal Details</a>
      <a href="upload-photo.php" class="active">Profile Photo</a>
      <a href="upload-resume.php">Resume Upload</a>
      <a href="skills.php">Skills</a>
    </div>

    <div class="content-box">
      <h3 class="text-primary fw-bold mb-3">Profile Photo</h3>

      <div class="mb-3">
        <?php if (!empty($user['photo'])): ?>
          <img src="/jobsweb/uploads/photos/<?= htmlspecialchars($user['photo']) ?>" alt="photo" class="photo-preview mb-2">
        <?php else: ?>
          <img src="/jobsweb/assets/img/default-avatar.png" alt="no-photo" class="photo-preview mb-2">
        <?php endif; ?>
      </div>

      <form action="upload-photo-process.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <label class="form-label">Choose photo (JPG / PNG)</label>
          <input type="file" name="photo" accept=".jpg,.jpeg,.png" class="form-control" required>
          <div class="small-note">Recommended size: 250x250px. Max 2MB.</div>
        </div>

        <button type="submit" class="btn-upload">Upload Photo</button>
      </form>

    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>
