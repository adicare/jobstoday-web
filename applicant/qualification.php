<?php
// FILE: applicant/qualification.php
session_start();
if (!isset($_SESSION['applicant_id'])) {
  header("Location: /jobsweb/public/login.php");
  exit;
}

include "../config/config.php";
include "../includes/header.php";

$app_id = (int)$_SESSION['applicant_id'];

/* fetch existing education entries */
$stmt = $conn->prepare("SELECT * FROM applicant_education WHERE applicant_id = ? ORDER BY id DESC");
$stmt->bind_param("i",$app_id);
$stmt->execute();
$res = $stmt->get_result();
$education = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* fetch masters */
$cq = $conn->query("SELECT name FROM courses_master ORDER BY name");
$courses = $cq->fetch_all(MYSQLI_NUM);
$sq = $conn->query("SELECT name FROM specializations_master ORDER BY name");
$specials = $sq->fetch_all(MYSQLI_NUM);
$uq = $conn->query("SELECT name FROM universities_master ORDER BY name");
$unis = $uq->fetch_all(MYSQLI_NUM);
?>

<style>
.card { background:#fff; padding:14px; border-radius:10px; box-shadow:0 0 8px rgba(0,0,0,0.06); margin-bottom:10px;}
.skill-chip { display:inline-block; margin:6px; padding:8px 12px; border-radius:20px; background:#eef6ff; color:#0a4c90; }
.small-note { color:#666; padding:10px 0; }
</style>

<div class="container mt-3">
  <div class="row">
    <div class="col-md-3">
      <?php include "profile-sidebar.php"; // or replicate sidebar ?>
    </div>

    <div class="col-md-9">
      <div class="card">
        <h4 class="text-primary">Manage Education</h4>
        <div class="small-note">Add the qualifications you have. Class 12 is recommended (it improves your profile) but not mandatory.</div>

        <!-- ADD FORM -->
        <form action="qualification-process.php" method="POST" id="eduForm" class="row g-2 align-items-end">
          <input type="hidden" name="action" value="add">
          <div class="col-md-3">
            <label class="form-label">Level</label>
            <select name="level" id="level" class="form-select" required>
              <option value="">-- Select level --</option>
              <option value="Class 10">Class 10</option>
              <option value="Class 12">Class 12</option>
              <option value="Diploma">Diploma</option>
              <option value="Graduation">Graduation</option>
              <option value="Post Graduation">Post Graduation</option>
              <option value="Doctorate">Doctorate</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Course</label>
            <select name="course_select" id="course_select" class="form-select">
              <option value="">-- Select course --</option>
              <?php foreach($courses as $c): ?>
                <option value="<?= htmlspecialchars($c[0]) ?>"><?= htmlspecialchars($c[0]) ?></option>
              <?php endforeach; ?>
              <option value="__other__">Other (add)</option>
            </select>
            <input type="text" name="course_text" id="course_text" class="form-control mt-2 d-none" placeholder="Type course name">
          </div>

          <div class="col-md-3">
            <label class="form-label">Specialization</label>
            <select name="spec_select" id="spec_select" class="form-select">
              <option value="">-- Select specialization --</option>
              <?php foreach($specials as $s): ?>
                <option value="<?= htmlspecialchars($s[0]) ?>"><?= htmlspecialchars($s[0]) ?></option>
              <?php endforeach; ?>
              <option value="__other__">Other (add)</option>
            </select>
            <input type="text" name="spec_text" id="spec_text" class="form-control mt-2 d-none" placeholder="Type specialization">
          </div>

          <div class="col-md-3">
            <label class="form-label">University / Board</label>
            <select name="uni_select" id="uni_select" class="form-select">
              <option value="">-- Select university --</option>
              <?php foreach($unis as $u): ?>
                <option value="<?= htmlspecialchars($u[0]) ?>"><?= htmlspecialchars($u[0]) ?></option>
              <?php endforeach; ?>
              <option value="__other__">Other (add)</option>
            </select>
            <input type="text" name="uni_text" id="uni_text" class="form-control mt-2 d-none" placeholder="Type university/board">
          </div>

          <div class="col-md-3">
            <label class="form-label">Year / Status</label>
            <select name="year_or_pursuing" id="year_or_pursuing" class="form-select">
              <option value="">-- Select --</option>
              <option value="pursuing">Pursuing</option>
              <?php
                $current = (int)date("Y");
                for($y=$current;$y>=1985;$y--){ echo "<option value=\"$y\">$y</option>"; }
              ?>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label">Study mode</label>
            <select name="study_mode" class="form-select">
              <option>Regular</option>
              <option>Online</option>
              <option>Distance</option>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label">Percentage / CGPA</label>
            <input type="text" name="percentage" class="form-control" placeholder="e.g. 72% or 8.2">
          </div>

          <div class="col-md-2">
            <button class="btn btn-primary w-100" type="submit">Add</button>
          </div>
        </form>

        <!-- LIST / CARDS -->
        <hr>
        <?php if(empty($education)): ?>
          <div class="small-note">No education records yet.</div>
        <?php else: ?>
          <div class="row">
            <?php foreach($education as $ed): ?>
              <div class="col-md-6 mb-2">
                <div class="card">
                  <strong><?= htmlspecialchars($ed['qualification_level']) ?></strong>
                  <?php if($ed['course_name']): ?>
                    <div class="small-note"><?= htmlspecialchars($ed['course_name']) ?> <?= $ed['specialization'] ? ' • '.htmlspecialchars($ed['specialization']) : '' ?></div>
                  <?php endif; ?>

                  <div class="small-note">
                    <?php if($ed['is_pursuing']): ?>
                      Status: <span class="badge bg-warning text-dark">Pursuing</span>
                    <?php else: ?>
                      <?= $ed['year_of_passing'] ? "Year: ".htmlspecialchars($ed['year_of_passing']) : '' ?>
                    <?php endif; ?>
                     &nbsp; | &nbsp; <?= htmlspecialchars($ed['study_mode']) ?>
                     <?= $ed['percentage'] ? " • ".htmlspecialchars($ed['percentage']) : "" ?>
                  </div>

                  <form method="POST" action="qualification-process.php" class="mt-2">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $ed['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Remove ✖</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<!-- small JS for 'Other' fields and 'pursuing' behaviour -->
<script>
document.getElementById('course_select')?.addEventListener('change', function(){
  document.getElementById('course_text').classList.toggle('d-none', this.value !== '__other__');
});
document.getElementById('spec_select')?.addEventListener('change', function(){
  document.getElementById('spec_text').classList.toggle('d-none', this.value !== '__other__');
});
document.getElementById('uni_select')?.addEventListener('change', function(){
  document.getElementById('uni_text').classList.toggle('d-none', this.value !== '__other__');
});
document.getElementById('year_or_pursuing')?.addEventListener('change', function(){
  // if 'pursuing' selected, no year_of_passing; we use 'is_pursuing' flag in backend
});
</script>

<?php include "../includes/footer.php"; ?>
