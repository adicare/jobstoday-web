<?php
/* ============================================================
   FILE: /ajax/get-job.php
   PURPOSE:
   - Fetch full job details by job ID
   - Detect if applicant already applied
   - Return HTML to load inside jobPreview box
   ============================================================ */

session_start();
include("../config/config.php");

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo "<p>Invalid job ID.</p>";
    exit;
}

$job_id = intval($_POST['id']);

/* ------------------------------------------------------------
   1. Fetch job details
   ------------------------------------------------------------ */
$stmt = $conn->prepare("
    SELECT 
        id, 
        title, 
        company, 
        location,
        state_id,
        city_id,
        experience,
        skill_id,
        job_type,
        salary,
        description,
        created_at
    FROM jobs 
    WHERE id = ?
");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();

if (!$job) {
    echo "<p>Job not found.</p>";
    exit;
}


/* ------------------------------------------------------------
   2. Check if applicant already applied
   ------------------------------------------------------------ */
$already_applied = false;

if (isset($_SESSION['applicant_id'])) {
    $applicant_id = intval($_SESSION['applicant_id']);

    $check = $conn->prepare("
        SELECT id FROM job_applications
        WHERE applicant_id = ? AND job_id = ?
        LIMIT 1
    ");
    $check->bind_param("ii", $applicant_id, $job_id);
    $check->execute();
    $res = $check->get_result();

    if ($res && $res->num_rows > 0) {
        $already_applied = true;
    }
}


/* ------------------------------------------------------------
   3. Prepare Apply / Applied button
   ------------------------------------------------------------ */

if ($already_applied) {
    // Green APPLIED ✔ button (disabled)
    $applyBtn = '
        <button class="btn applyJobBtn w-100" 
                style="background:#28a745;color:#fff;border:none;"
                disabled>
            APPLIED ✔
        </button>
    ';
} else {
    // Blue Apply button (active)
    $applyBtn = '
        <button class="btn btn-primary applyJobBtn w-100" 
                data-jobid="'.$job_id.'">
            Apply
        </button>
    ';
}


/* ------------------------------------------------------------
   4. Output Job Details in HTML
   ------------------------------------------------------------ */
?>

<div>
    <h3><?= htmlspecialchars($job['title']); ?></h3>

    <p><strong>Company:</strong> <?= htmlspecialchars($job['company']); ?></p>

    <?php if (!empty($job['location'])): ?>
        <p><strong>Location:</strong> <?= htmlspecialchars($job['location']); ?></p>
    <?php endif; ?>

    <?php if (!empty($job['experience'])): ?>
        <p><strong>Experience:</strong> <?= htmlspecialchars($job['experience']); ?></p>
    <?php endif; ?>

    <?php if (!empty($job['salary'])): ?>
        <p><strong>Salary:</strong> <?= htmlspecialchars($job['salary']); ?></p>
    <?php endif; ?>

    <?php if (!empty($job['job_type'])): ?>
        <p><strong>Job Type:</strong> <?= htmlspecialchars($job['job_type']); ?></p>
    <?php endif; ?>

    <hr>

    <h5>Description</h5>
    <p><?= nl2br(htmlspecialchars($job['description'])); ?></p>

    <hr>

    <!-- APPLY BUTTON -->
    <div class="mt-3">
        <?= $applyBtn; ?>
    </div>
</div>
