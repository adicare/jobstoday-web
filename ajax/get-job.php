<?php
session_start();
require_once("../config/config.php");

if (!isset($_POST['id'])) {
    echo "<div class='text-danger'>Invalid request</div>";
    exit;
}

$job_id = intval($_POST['id']);

$q = $conn->prepare("SELECT * FROM jobs WHERE id = ?");
$q->bind_param("i", $job_id);
$q->execute();
$res = $q->get_result();

if ($res->num_rows == 0) {
    echo "<div class='text-danger'>Job not found</div>";
    exit;
}

$job = $res->fetch_assoc();

/* Check saved status (for Save / Unsave button) */
$is_saved = false;
if (isset($_SESSION['applicant_id'])) {
    $pid = intval($_SESSION['applicant_id']);
    $s = $conn->prepare("SELECT id FROM saved_jobs WHERE applicant_id=? AND job_id=? LIMIT 1");
    $s->bind_param("ii", $pid, $job_id);
    $s->execute();
    $r = $s->get_result();
    $is_saved = ($r->num_rows > 0);
}
?>

<div class="job-preview-container">

    <h2><?= htmlspecialchars($job['title']) ?></h2>
    <div class="job-meta">
        <strong><?= htmlspecialchars($job['company']) ?></strong> • 
        <?= htmlspecialchars($job['location']) ?>
    </div>

    <hr>

    <div class="job-description">
        <?= nl2br(htmlspecialchars($job['description'])) ?>
    </div>

    <hr>

    <div class="d-flex" style="gap:14px; margin-top:10px;">

        <!-- APPLY BUTTON (opens popup) -->
        <?php if(isset($_SESSION['applicant_id'])): ?>
            <button 
                class="btn btn-primary"
                onclick="openApplyPopup(
                    <?= $job_id ?>,
                    '<?= htmlspecialchars($job['title'], ENT_QUOTES) ?>',
                    '<?= htmlspecialchars($job['company'], ENT_QUOTES) ?>'
                )">
                Apply
            </button>
        <?php else: ?>
            <a href="/jobsweb/public/login.php" class="btn btn-primary">Login to Apply</a>
        <?php endif; ?>

        <!-- SAVE / UNSAVE TOGGLE -->
        <?php if(isset($_SESSION['applicant_id'])): ?>
            <?php if($is_saved): ?>
                <button class="btn btn-secondary" onclick="unsaveJob(<?= $job_id ?>)">Unsave</button>
            <?php else: ?>
                <button class="btn btn-outline-secondary" onclick="saveJob(<?= $job_id ?>)">Save</button>
            <?php endif; ?>
        <?php endif; ?>

    </div>

</div>
