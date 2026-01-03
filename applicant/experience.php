<?php
/* FILE: /applicant/experience.php
   PURPOSE: Manage applicant job experience + Fresher/Experienced Logic
*/

session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

include "../config/config.php";
include "../includes/header.php";

$app_id = (int) $_SESSION['applicant_id'];

/* =======================================
   FETCH CAREER STATUS: Fresher / Experienced
   ======================================= */
$career_sql = $conn->prepare("SELECT experience_level FROM applicant_career_info WHERE applicant_id = ?");
$career_sql->bind_param("i", $app_id);
$career_sql->execute();
$career_result = $career_sql->get_result();
$career_info = $career_result->fetch_assoc();

$experience_level = isset($career_info['experience_level']) ? $career_info['experience_level'] : null;  // NULL = first-time user

/* =======================================
   FETCH INDUSTRIES
   ======================================= */
$ind_sql = $conn->query("SELECT id, industry_name FROM industry_master WHERE status=1 ORDER BY industry_name ASC");
$industries = $ind_sql ? $ind_sql->fetch_all(MYSQLI_ASSOC) : [];

/* =======================================
   FETCH USER EXPERIENCE LIST
   ======================================= */
$stmt = $conn->prepare("
    SELECT ae.*, im.industry_name 
    FROM applicant_experience ae
    LEFT JOIN industry_master im ON ae.industry_id = im.id
    WHERE ae.applicant_id = ?
    ORDER BY ae.id DESC
");
$stmt->bind_param("i", $app_id);
$stmt->execute();
$exp_result = $stmt->get_result();
$experiences = $exp_result ? $exp_result->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

$msg = isset($_SESSION['exp_msg']) ? $_SESSION['exp_msg'] : '';
unset($_SESSION['exp_msg']);
?>

<style>
.wrapper { display:flex; margin-top:30px; }
.sidebar { width:240px; background:#0a4c90; color:#fff; padding:20px; border-radius:8px; }
.sidebar a{ display:block; padding:12px; color:#fff; text-decoration:none; margin-bottom:8px; border-radius:5px; font-weight:600; }
.sidebar a.active{ background:#06376a; }
.content-box { flex:1; margin-left:20px; background:#fff; padding:25px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.08); }
.msg { padding:10px; border-radius:6px; margin-bottom:12px; }
.msg.success { background:#e6ffed; color:#156c21; }
.msg.error { background:#ffecec; color:#b11; }

.exp-card { border:1px solid #eee; padding:15px; border-radius:8px; margin-bottom:12px; background:#fafafa; }
.exp-title{ font-size:18px; font-weight:700; color:#0a4c90; }
.exp-company{ font-size:15px; font-weight:600; }
.status-box{ background:#eef5ff; border-left:5px solid #0a4c90; padding:12px; border-radius:6px; margin-bottom:20px; }
.fresher-toggle {
    display: flex;
    align-items: center;
    gap: 10px;
    background-color: #047225ff;
    color: #fff;
    padding: 8px 14px;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
}

.fresher-toggle input[type="checkbox"] {
    width: 22px;
    height: 22px;
    cursor: pointer;
}

.current-work-checkbox {
    background-color: #0a4c90;
    color: #fff;
    padding: 10px 15px;
    border-radius: 6px;
    display: inline-block;
}

.current-work-checkbox input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin-right: 8px;
    cursor: pointer;
}

.current-work-checkbox label {
    color: #fff;
    margin: 0;
    cursor: pointer;
}

.page-header-nav {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.page-header-nav h3 {
    margin:0;
    color:#004aad;
    font-weight:bold;
}

.nav-buttons {
    display:flex;
    gap:10px;
}

.btn-back {
    background:#6c757d;
    color:#fff;
    padding:9px 18px;
    border-radius:8px;
    border:0;
    cursor:pointer;
    text-decoration:none;
    display:inline-block;
    font-weight:600;
}

.btn-back:hover {
    background:#5a6268;
    color:#fff;
}

.btn-next {
    background:#28a745;
    color:#fff;
    padding:9px 18px;
    border-radius:8px;
    border:0;
    cursor:pointer;
    text-decoration:none;
    display:inline-block;
    font-weight:600;
}

.btn-next:hover {
    background:#218838;
    color:#fff;
}
</style>

<script>
// Auto-fill industry_name when industry is selected
function updateIndustryName() {
    const select = document.getElementById('industry_id');
    const hiddenInput = document.getElementById('industry_name');
    const selectedOption = select.options[select.selectedIndex];
    hiddenInput.value = selectedOption.text === '-- Select Industry --' ? '' : selectedOption.text;
}

// Disable end date fields when "currently working" is checked
function toggleEndDate() {
    const isCurrentCheckbox = document.getElementById('is_current');
    const endMonth = document.querySelector('select[name="end_month"]');
    const endYear = document.querySelector('select[name="end_year"]');
    
    if (isCurrentCheckbox.checked) {
        endMonth.value = '';
        endYear.value = '';
        endMonth.disabled = true;
        endYear.disabled = true;
        endMonth.removeAttribute('required');
        endYear.removeAttribute('required');
    } else {
        endMonth.disabled = false;
        endYear.disabled = false;
    }
}

// Initialize on page load
window.addEventListener('DOMContentLoaded', function() {
    const isCurrentCheckbox = document.getElementById('is_current');
    if (isCurrentCheckbox) {
        isCurrentCheckbox.addEventListener('change', toggleEndDate);
        toggleEndDate(); // Run on load
    }
});
</script>

<div class="container">
  <div class="wrapper">

    <?php include "../includes/profile_sidebar.php"; ?>

    <div class="content-box">
      
      <!-- ================= PAGE HEADER WITH BACK/NEXT ================= -->
      <div class="page-header-nav">
        <h3>Job Experience</h3>
        <div class="nav-buttons">
          <a href="qualification.php" class="btn-back">← BACK</a>
          <a href="preferred-industry.php" class="btn-next">NEXT →</a>
        </div>
      </div>

      <?php if ($msg): ?>
        <div class="msg <?php echo (strpos($msg,'success') !== false) ? 'success' : 'error'; ?>">
          <?php echo htmlspecialchars($msg); ?>
        </div>
      <?php endif; ?>

      <!-- =====================================================
           CASE 1: FIRST-TIME USER (Ask Fresher/Experienced)
      ====================================================== -->
      <?php if ($experience_level === null): ?>

        <div class="status-box">
          <h5 class="fw-bold mb-3">Tell us about your job status</h5>

          <form method="POST" action="experience-process.php">
            <input type="hidden" name="action" value="set_status">

            <div class="form-check">
              <input class="form-check-input" type="radio" id="exp_fresher" name="experience_level" value="Fresher" required>
              <label class="form-check-label" for="exp_fresher">I am a Fresher (No work experience yet)</label>
            </div>

            <div class="form-check mt-2">
              <input class="form-check-input" type="radio" id="exp_experienced" name="experience_level" value="Experienced" required>
              <label class="form-check-label" for="exp_experienced">I am Experienced</label>
            </div>

            <button class="btn btn-primary mt-3">Save & Continue</button>
          </form>
        </div>

        <?php include "../includes/footer.php"; exit; ?>
      <?php endif; ?>

      <!-- =====================================================
           CASE 2: USER IS FRESHER
      ====================================================== -->
      <?php if ($experience_level === "Fresher"): ?>

        <div class="status-box">
          <h5 class="fw-bold text-success">You are marked as a Fresher</h5>
          <p>You do not need to add work experience. Focus on your skills and qualifications.</p>

          <form method="POST" action="experience-process.php">
            <input type="hidden" name="action" value="set_status">
            <input type="hidden" name="experience_level" value="Experienced">
            <button class="btn btn-outline-primary">Switch to Experienced</button>
          </form>
        </div>

        <?php include "../includes/footer.php"; exit; ?>
      <?php endif; ?>

      <!-- =====================================================
           CASE 3: USER IS EXPERIENCED (Full Module)
      ====================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-3">

      <p class="fw-bold mb-0">
          You are marked as:
          <span class="text-primary">Experienced</span>
      </p>

      <form method="POST" action="experience-process.php" class="mb-0">
          <input type="hidden" name="action" value="set_status">
          <input type="hidden" name="experience_level" value="Fresher">

          <label class="fresher-toggle">
              <input type="checkbox" onchange="this.form.submit()">
              <span> Check it If You are fresher or Not having any Job experience </span>
          </label>
      </form>

    </div>

      <!-- ================= ADD EXPERIENCE FORM ================= -->
      <form action="experience-process.php" method="POST" class="border p-3 rounded mb-4">
        <input type="hidden" name="action" value="add">

        <!-- ROW 1: Job Title, Company Name, Industry -->
        <div class="row mb-3">
          <div class="col-md-4">
            <label class="form-label">Job Title</label>
            <input type="text" name="job_title" class="form-control" required>
          </div>

          <div class="col-md-4">
            <label class="form-label">Company Name</label>
            <input type="text" name="company_name" class="form-control" required>
          </div>

          <div class="col-md-4">
            <label class="form-label">Industry</label>
            <select name="industry_id" id="industry_id" class="form-select" onchange="updateIndustryName()" required>
              <option value="">-- Select Industry --</option>
              <?php
              if (!empty($industries)) {
                  foreach ($industries as $i) {
                      echo '<option value="' . (int)$i['id'] . '">' . htmlspecialchars($i['industry_name']) . '</option>';
                  }
              }
              ?>
            </select>
            <input type="hidden" name="industry_name" id="industry_name">
          </div>
        </div>

        <!-- ROW 2: Job Role, Work Mode, Employment Type -->
        <div class="row mb-3">
          <div class="col-md-4">
            <label class="form-label">Primary Job Role</label>
            <input type="text" name="job_role" class="form-control" placeholder="e.g., Software Developer, Marketing Manager">
          </div>

          <div class="col-md-4">
            <label class="form-label">Work Mode</label>
            <select name="work_mode" class="form-select" required>
              <option value="">-- Select Work Mode --</option>
              <option value="On-site">On-site</option>
              <option value="Remote">Remote</option>
              <option value="Hybrid">Hybrid</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Employment Type</label>
            <select name="employment_type" class="form-select" required>
              <option value="">-- Select Employment Type --</option>
              <option value="Full-time">Full-time</option>
              <option value="Part-time">Part-time</option>
              <option value="Contract">Contract</option>
              <option value="Freelance">Freelance</option>
              <option value="Internship">Internship</option>
            </select>
          </div>
        </div>

        <!-- ROW 3: Period (Start Month, Start Year, End Month, End Year, Job Location) -->
        <div class="row mb-3">
          <div class="col-md-2">
            <label class="form-label">Start Month</label>
            <select name="start_month" class="form-select" required>
              <option value="">Select</option>
              <option>Jan</option><option>Feb</option><option>Mar</option>
              <option>Apr</option><option>May</option><option>Jun</option>
              <option>Jul</option><option>Aug</option><option>Sep</option>
              <option>Oct</option><option>Nov</option><option>Dec</option>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label">Start Year</label>
            <select name="start_year" class="form-select" required>
              <option value="">Select</option>
              <?php for($y = date("Y"); $y >= 1980; $y--){ echo '<option>' . $y . '</option>'; } ?>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label">End Month</label>
            <select name="end_month" class="form-select">
              <option value="">--</option>
              <option>Jan</option><option>Feb</option><option>Mar</option>
              <option>Apr</option><option>May</option><option>Jun</option>
              <option>Jul</option><option>Aug</option><option>Sep</option>
              <option>Oct</option><option>Nov</option><option>Dec</option>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label">End Year</label>
            <select name="end_year" class="form-select">
              <option value="">--</option>
              <?php for($y = date("Y"); $y >= 1980; $y--){ echo '<option>' . $y . '</option>'; } ?>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Job Location (District)</label>
            <input type="text" name="district_location" class="form-control" placeholder="e.g., Mumbai, Pune" required>
          </div>
        </div>

        <!-- Currently Working Checkbox -->
          <div class="row mb-3">
          <div class="col-md-6 d-flex align-items-center">
            <div class="current-work-checkbox">
              <input class="form-check-input" type="checkbox" name="is_current" value="1" id="is_current">
              <label class="form-check-label" for="is_current">I am currently working here</label>
            </div>
          </div>
          
          <div class="col-md-6">
            <label class="form-label">Annual Salary (Approx.) – Optional</label>
            <input type="number" name="annual_salary" class="form-control" placeholder="e.g., 350000" min="0" step="1">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Responsibilities (optional)</label>
          <textarea name="responsibilities" class="form-control" rows="3"></textarea>
        </div>

        <button class="btn btn-success">Add Experience</button>
      </form>

      <!-- ================= LIST EXPERIENCE ================= -->
      <h5 class="fw-bold mb-3">Your Experience</h5>

      <?php if (empty($experiences)): ?>
        <div class="text-muted">No experience added yet.</div>
      <?php else: ?>

        <div class="row">
        <?php foreach ($experiences as $exp): ?>
          <div class="col-md-4 mb-3">
            <div class="border rounded p-3 h-100">
              <strong class="text-primary"><?= htmlspecialchars($exp['job_title']) ?></strong><br>
              <span class="fw-semibold"><?= htmlspecialchars($exp['company_name']) ?></span><br>

              <small class="text-muted">
                <strong>Industry:</strong> <?= htmlspecialchars($exp['industry_name'] ?? 'N/A') ?><br>
                <strong>Role:</strong> <?= htmlspecialchars($exp['job_role'] ?? '-') ?><br>
                <strong>Location:</strong> <?= htmlspecialchars($exp['job_location'] ?? '-') ?><br>
                <strong>Work Mode:</strong> <?= htmlspecialchars($exp['work_mode'] ?? 'N/A') ?><br>
                <strong>Employment:</strong> <?= htmlspecialchars($exp['employment_type'] ?? 'N/A') ?><br>
                
                <strong>Period:</strong> 
                <?= htmlspecialchars($exp['start_month']) ?> <?= htmlspecialchars($exp['start_year']) ?>
                <?php if ($exp['is_current']): ?>
                  – Present
                <?php else: ?>
                  – <?= htmlspecialchars($exp['end_month']) ?> <?= htmlspecialchars($exp['end_year']) ?>
                <?php endif; ?>
                <br>

                <?php if (!empty($exp['annual_salary'])): ?>
                  <strong>Salary:</strong> ₹<?= number_format($exp['annual_salary']) ?><br>
                <?php endif; ?>
              </small>

              <?php if (!empty($exp['responsibilities'])): ?>
                <div class="mt-2">
                  <strong>Responsibilities:</strong><br>
                  <small><?= nl2br(htmlspecialchars($exp['responsibilities'])) ?></small>
                </div>
              <?php endif; ?>

              <form class="mt-2" method="POST" action="experience-process.php" onsubmit="return confirm('Delete this experience?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$exp['id'] ?>">
                <button class="btn btn-sm btn-danger">Delete</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
        </div>

      <?php endif; ?>

    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>