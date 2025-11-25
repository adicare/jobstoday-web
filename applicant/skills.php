<?php
/* FILE: /applicant/skills.php
   PURPOSE: Manage applicant skills (modern layout)
*/

session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

include "../config/config.php";
include "../includes/header.php";

$app_id = $_SESSION['applicant_id'];

// handle display of existing skills
$stmt = $conn->prepare("SELECT id, skill_name FROM applicant_skills WHERE applicant_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $app_id);
$stmt->execute();
$res = $stmt->get_result();
$skills = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<style>
.wrapper { display:flex; margin-top:30px; }
.sidebar { width:240px; background:#0a4c90; color:#fff; padding:20px; border-radius:8px; }
.sidebar a{ display:block; padding:12px; color:#fff; text-decoration:none; margin-bottom:8px; border-radius:5px; font-weight:600; }
.sidebar a.active{ background:#06376a; }
.content-box { flex:1; margin-left:20px; background:#fff; padding:25px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.08); }
.skill-chip { display:inline-block; background:#e9f2ff; color:#0a4c90; padding:8px 12px; border-radius:20px; margin:6px; font-weight:600; }
.btn-action { padding:8px 12px; background:#0a4c90; color:#fff; border:none; border-radius:6px; cursor:pointer; }
.form-inline { display:flex; gap:8px; align-items:center; }
.form-inline input { flex:1; padding:8px; border-radius:6px; border:1px solid #ccc; }
</style>

<div class="container">
  <div class="wrapper">
    <div class="sidebar">
      <h4><i class="bi bi-person-lines-fill me-2"></i> My Profile</h4>
      <a href="edit-profile.php">Personal Details</a>
      <a href="upload-photo.php">Profile Photo</a>
      <a href="upload-resume.php">Resume Upload</a>
      <a href="skills.php" class="active">Skills</a>
    </div>

    <div class="content-box">
      <h3 class="text-primary fw-bold mb-3">Manage Skills</h3>

      <p>Add the skills you have — these help us match jobs.</p>

      <form action="skills-process.php" method="POST" class="form-inline mb-3">
        <input type="text" name="skill" placeholder="Add new skill (e.g. Excel, PHP, Sales)" required>
        <button type="submit" class="btn-action">Add</button>
      </form>

      <div>
        <?php if (count($skills) === 0): ?>
          <div class="small-note">No skills added yet.</div>
        <?php else: ?>
          <?php foreach ($skills as $s): ?>
            <div class="skill-chip">
              <?= htmlspecialchars($s['skill_name']) ?>
              <a href="skills-process.php?remove=<?= $s['id'] ?>" style="color:#d33; margin-left:8px; text-decoration:none;">✖</a>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>
