<?php
/* FILE: /applicant/upload-resume.php
   PURPOSE: Upload resume using standardized layout
*/

session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

include "../config/config.php";
include "../includes/header.php";

$app_id = $_SESSION['applicant_id'];
$res = $conn->query("SELECT resume_file, full_name FROM job_seekers WHERE id=$app_id LIMIT 1");
$user = $res->fetch_assoc();
?>

<style>
.content-box {
    background:#fff;
    padding:25px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
    margin-top:20px;
}

.btn-upload {
    padding:10px 16px;
    background:#0a4c90;
    color:#fff;
    border:none;
    border-radius:6px;
}

.small-note {
    color:#666;
    font-size:13px;
    margin-top:8px;
}

.resume-info {
    background:#f8f9fa;
    padding:12px;
    border-radius:6px;
    margin-bottom:12px;
}
</style>

<div class="container mt-3">
    <div class="row">

        <!-- LEFT SIDEBAR (standard layout) -->
        <div class="col-md-3">
            <?php include "profile-sidebar.php"; ?>
        </div>

        <!-- RIGHT CONTENT -->
        <div class="col-md-9">
            <div class="content-box">

                <h3 class="text-primary fw-bold mb-3">Upload Resume (PDF)</h3>

                <!-- Existing resume -->
                <?php if (!empty($user['resume_file'])): ?>
                    <div class="resume-info">
                        Current Resume: 
                        <strong><?= htmlspecialchars($user['resume_file']) ?></strong>
                        <div class="mt-2">
                            <a class="btn btn-sm btn-outline-primary" 
                               href="/jobsweb/uploads/resume/<?= $user['resume_file'] ?>" 
                               target="_blank">
                               Download
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="resume-info">
                        No resume uploaded yet.
                    </div>
                <?php endif; ?>

                <!-- Upload Form -->
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
</div>

<?php include "../includes/footer.php"; ?>
