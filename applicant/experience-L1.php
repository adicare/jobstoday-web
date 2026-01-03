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
   FETCH JOB ROLES (Optional)
   ======================================= */
$role_sql = $conn->query("SELECT id, role_name FROM job_role_master ORDER BY role_name ASC");
$job_roles = $role_sql ? $role_sql->fetch_all(MYSQLI_ASSOC) : [];

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
    background-color: #0d1b4c; /* dark blue */
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

</style>


<div class="container">
  <div class="wrapper">

    <?php include "../includes/profile_sidebar.php"; ?>


    <div class="content-box">
      <h3 class="text-primary fw-bold mb-3">Job Experience</h3>

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
              <input type="checkbox"
                    onchange="this.form.submit()">
              <span>If You are fresher or Not having any Job experienec Please Check this box to  be treated as FRESHER</span>
          </label>

      </form>

    </div>


      <!-- ================= ADD EXPERIENCE FORM ================= -->
      <form action="experience-process.php" method="POST" class="border p-3 rounded mb-4">
        <input type="hidden" name="action" value="add">

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Job Title</label>
            <input type="text" name="job_title" class="form-control" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Company Name</label>
            <input type="text" name="company_name" class="form-control" required>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Industry</label>
            <select name="industry_id" class="form-select" required>
              <option value="">-- Select Industry --</option>
              <?php
              if (!empty($industries)) {
                  foreach ($industries as $i) {
                      echo '<option value="' . (int)$i['id'] . '">' . htmlspecialchars($i['industry_name']) . '</option>';
                  }
              }
              ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Primary Job Role</label>
            <select name="job_role" class="form-select">
              <option value="">-- Select Role (optional) --</option>
              <?php
              if (!empty($job_roles)) {
                  foreach ($job_roles as $r) {
                      echo '<option value="' . htmlspecialchars($r['role_name']) . '">' . htmlspecialchars($r['role_name']) . '</option>';
                  }
              }
              ?>
            </select>
          </div>
        </div>


        <!-- Dates -->
        <div class="row mb-3">
          <div class="col-md-3">
            <label class="form-label">Start Month</label>
            <select name="start_month" class="form-select" required>
              <option value="">Select</option>
              <option>Jan</option><option>Feb</option><option>Mar</option>
              <option>Apr</option><option>May</option><option>Jun</option>
              <option>Jul</option><option>Aug</option><option>Sep</option>
              <option>Oct</option><option>Nov</option><option>Dec</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Start Year</label>
            <select name="start_year" class="form-select" required>
              <?php for($y = date("Y"); $y >= 1980; $y--){ echo '<option>' . $y . '</option>'; } ?>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">End Month</label>
            <select name="end_month" class="form-select">
              <option value="">--</option>
              <option>Jan</option><option>Feb</option><option>Mar</option>
              <option>Apr</option><option>May</option><option>Jun</option>
              <option>Jul</option><option>Aug</option><option>Sep</option>
              <option>Oct</option><option>Nov</option><option>Dec</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">End Year</label>
            <select name="end_year" class="form-select">
              <option value="">--</option>
              <?php for($y = date("Y"); $y >= 1980; $y--){ echo '<option>' . $y . '</option>'; } ?>
            </select>
          </div>
        </div>

        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="is_current" value="1" id="is_current">
          <label class="form-check-label" for="is_current">I am currently working here</label>
        </div>


        <!-- Annual Salary -->
        <div class="row mb-3">

        <div class="col-md-4">
            <label class="form-label">Annual Salary (Approx.) — Optional</label>
            <input type="number" name="annual_salary" class="form-control" placeholder="e.g., 350000" min="0" step="1">
        </div>

        <div class="col-md-4">
            <label class="form-label">Employment Type</label>
            <select name="employment_type" class="form-select">
            <option>Full-time</option>
            <option>Part-time</option>
            <option>Internship</option>
            <option>Contract</option>
            <option>Freelance</option>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Work Mode</label>
            <select name="work_mode" class="form-select">
            <option>On-site</option>
            <option>Hybrid</option>
            <option>Remote</option>
            </select>
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

        <?php
        foreach ($experiences as $e) {
            echo '<div class="exp-card">';
            echo '<div class="exp-title">' . htmlspecialchars($e['job_title']) . '</div>';
            echo '<div class="exp-company">' . htmlspecialchars($e['company_name']) . '</div>';

            echo '<div class="mt-2">';
            echo '<strong>Industry:</strong> ' . htmlspecialchars($e['industry_name'] ?? 'N/A') . '<br>';
            echo '<strong>Role:</strong> ' . htmlspecialchars($e['job_role'] ?? '-') . '<br>';
            echo '<strong>Work Mode:</strong> ' . htmlspecialchars($e['work_mode']) . '<br>';
            echo '<strong>Employment:</strong> ' . htmlspecialchars($e['employment_type']) . '<br>';
            if (!empty($e['annual_salary'])) {
                echo '<strong>Annual Salary:</strong> ₹' . number_format($e['annual_salary']) . '<br>';
            }
            echo '</div>';

            echo '<div class="mt-2"><strong>Period:</strong> ' . htmlspecialchars($e['start_month']) . ' ' . htmlspecialchars($e['start_year']);
            if ($e['is_current']) {
                echo ' – Present';
            } else {
                echo ' – ' . htmlspecialchars($e['end_month']) . ' ' . htmlspecialchars($e['end_year']);
            }
            echo '</div>';

            if (!empty($e['responsibilities'])) {
                echo '<div class="mt-2"><strong>Responsibilities:</strong><br>' . nl2br(htmlspecialchars($e['responsibilities'])) . '</div>';
            }

            echo '<form class="mt-2" method="POST" action="experience-process.php" onsubmit="return confirm(\'Delete this experience?\')">';
            echo '<input type="hidden" name="action" value="delete">';
            echo '<input type="hidden" name="id" value="' . (int)$e['id'] . '">';
            echo '<button class="btn btn-sm btn-danger">Delete</button>';
            echo '</form>';

            echo '</div>';
        }
        ?>

      <?php endif; ?>

    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>
