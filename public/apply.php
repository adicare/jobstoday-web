<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/header.php'; // ✅ universal header

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die("<div class='alert alert-danger text-center mt-5'>Invalid Job ID</div>");
}
$job_id = (int)$_GET['id'];

$success = $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply'])) {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $course   = trim($_POST['course'] ?? '');
    $semester = trim($_POST['semester'] ?? '');
    $year     = ($semester === "Passed Out") ? trim($_POST['year'] ?? '') : null;

    // CAPTCHA validation
    $captcha_answer = (int)($_POST['captcha_answer'] ?? 0);
    $captcha_sum    = (int)($_POST['captcha_sum'] ?? 0);
    if ($captcha_answer !== $captcha_sum) {
        $error = "Incorrect CAPTCHA answer. Please try again.";
    }

    // Basic validations
    if (!$error && ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '' || $course === '' || $semester === '')) {
        $error = "Please fill all required fields correctly.";
    }

    // Upload handling
    if (!$error && isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['pdf', 'doc', 'docx'];
        $ext = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $error = "Only PDF, DOC, or DOCX files are allowed.";
        } elseif ($_FILES['resume']['size'] > 3 * 1024 * 1024) {
            $error = "File too large (max 3MB).";
        } else {
            $uploadDir = __DIR__ . '/../uploads/resumes/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $resume_name = time() . '_' . preg_replace('/[^A-Za-z0-9\._-]/', '_', $_FILES['resume']['name']);
            $target = $uploadDir . $resume_name;

            if (!move_uploaded_file($_FILES['resume']['tmp_name'], $target)) {
                $error = "Failed to upload file.";
            }
        }
    } else if (!$error) {
        $error = "Resume is required.";
    }

    if (!$error) {
        $stmt = $conn->prepare("INSERT INTO applications (job_id, name, email, phone, course, semester, year, resume_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $job_id, $name, $email, $phone, $course, $semester, $year, $resume_name);
        $stmt->execute();
        $stmt->close();
        $success = "Application submitted successfully!";
    }
}
?>

<div class="container my-5">
  <div class="card shadow-lg p-4">
    <h2 class="text-primary mb-4">Apply for Job</h2>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
    <?php elseif ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold">Name</label>
          <input type="text" name="name" class="form-control" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold">Phone</label>
          <input type="text" name="phone" class="form-control" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold">Course Name</label>
          <input type="text" name="course" class="form-control" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold">Semester</label>
          <select name="semester" id="semester" class="form-select" onchange="toggleYearField()" required>
            <option value="">--Select--</option>
            <option value="I">I</option><option value="II">II</option>
            <option value="III">III</option><option value="IV">IV</option>
            <option value="V">V</option><option value="VI">VI</option>
            <option value="Passed Out">Passed Out</option>
          </select>
        </div>

        <div class="col-md-6 mb-3" id="yearDiv" style="display:none;">
          <label class="form-label fw-bold">Year of Passing</label>
          <input type="text" name="year" class="form-control">
        </div>

        <div class="col-md-12 mb-3">
          <label class="form-label fw-bold">Upload Resume (PDF/DOC/DOCX, max 3MB)</label>
          <input type="file" name="resume" class="form-control" required>
        </div>
      </div>

      <!-- CAPTCHA -->
      <div class="mt-3">
        <?php
          $num1 = rand(1, 9);
          $num2 = rand(1, 9);
          $sum = $num1 + $num2;
        ?>
        <label><strong>Human Check:</strong> What is <?= $num1; ?> + <?= $num2; ?> ?</label>
        <input type="number" name="captcha_answer" class="form-control mt-2" required>
        <input type="hidden" name="captcha_sum" value="<?= $sum; ?>">
      </div>

      <button type="submit" name="apply" class="btn btn-primary mt-4 w-100">Submit Application</button>
    </form>

    <div class="text-center mt-3">
      <a href="jobs.php" class="text-decoration-none text-primary fw-bold">← Back to Jobs</a>
    </div>
  </div>
</div>

<script>
function toggleYearField() {
  const sem = document.getElementById("semester").value;
  document.getElementById("yearDiv").style.display = (sem === "Passed Out") ? "block" : "none";
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
