<?php
/* ============================================================
   FILE: /applicant/dashboard.php
   PURPOSE: Applicant Dashboard (Modern UI + Correct Paths)
   ============================================================ */

session_start();
require_once __DIR__ . '/../config/config.php';  

// ✅ If not logged in → redirect
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/auth/login.php");
    exit;
}

$app_id = $_SESSION['applicant_id'];

// Fetch applicant info
$sql = "SELECT * FROM job_seekers WHERE id = $app_id LIMIT 1";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Update last login timestamp
$conn->query("UPDATE job_seekers SET last_login = NOW() WHERE id = $app_id");

// SIMPLE PROFILE COMPLETENESS CHECK
$profile_complete = 0;
if (!empty($user['full_name'])) $profile_complete += 20;
if (!empty($user['mobile'])) $profile_complete += 20;
if (!empty($user['state'])) $profile_complete += 15;
if (!empty($user['city'])) $profile_complete += 15;
if (!empty($user['resume_file'])) $profile_complete += 20;

// Skills count
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM applicant_skills WHERE applicant_id = ?");
$stmt->bind_param("i", $app_id);
$stmt->execute();
$res = $stmt->get_result();
$skillCnt = ($res->fetch_assoc())['c'] ?? 0;

if ($skillCnt > 0) $profile_complete += 10;

// Include global header (Bootstrap + Navigation)
include __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Applicant Dashboard - JobsToday</title>

    <style>
        body { background:#e8f2ff; }

        .dash-box {
            width: 80%; margin:40px auto; padding:25px;
            background:#fff; border-radius:10px;
            box-shadow:0 0 10px rgba(0,0,0,0.08);
        }
        .welcome { font-size:22px; margin-bottom:10px; font-weight:bold; }
        .section { margin:25px 0; padding:20px;
                   background:#f9f9f9; border-radius:8px; }

        .btn-custom {
            padding:8px 15px; background:#0a4c90;
            color:#fff; border:none; border-radius:6px;
            text-decoration:none; cursor:pointer;
        }
        .btn-custom:hover { background:#083c70; }

        /* PROFILE METER */
        .profile-meter {
            background:#ddd; border-radius:20px; height:20px; width:100%;
            overflow:hidden; margin-top:10px;
        }
        .profile-meter-fill {
            height:100%; background:#28a745; color:white;
            text-align:center; font-size:12px;
            line-height:20px; transition:0.5s;
        }
    </style>
</head>

<body>

<div class="dash-box">

    <!-- Welcome -->
    <div class="welcome">
        Welcome, <b><?= htmlspecialchars($user['full_name']); ?></b> 🎉
    </div>

    <!-- Profile Strength -->
    <div class="section">
        <h3>Profile Strength</h3>

        <div class="profile-meter">
            <div class="profile-meter-fill" style="width: <?= $profile_complete ?>%;">
                <?= $profile_complete ?>%
            </div>
        </div>

        <?php if ($profile_complete < 80): ?>
            <p>Your profile is only <b><?= $profile_complete ?>%</b> complete.</p>
            <a class="btn-custom" href="/jobsweb/applicant/edit-profile.php">Complete Profile</a>
        <?php else: ?>
            <p>Great! Your profile looks strong.</p>
        <?php endif; ?>
    </div>

    <!-- Profile Photo -->
    <div class="section">
        <h3>Profile Photo</h3>

        <?php if (!empty($user['photo'])): ?>
            <p><img src="/jobsweb/uploads/photos/<?= $user['photo'] ?>" 
                    style="width:80px;height:80px;border-radius:50%;object-fit:cover;"></p>
            <a class="btn-custom" href="/jobsweb/applicant/upload-photo.php">Change Photo</a>
        <?php else: ?>
            <p>No photo uploaded.</p>
            <a class="btn-custom" href="/jobsweb/applicant/upload-photo.php">Upload Photo</a>
        <?php endif; ?>
    </div>

    <!-- Resume -->
    <div class="section">
        <h3>Resume Status</h3>

        <?php if (!empty($user['resume_file'])): ?>
            <p><b>Status:</b> <span style="color:green;">Resume Uploaded ✔</span></p>
            <a class="btn-custom" target="_blank" 
               href="/jobsweb/uploads/resumes/<?= $user['resume_file'] ?>">Download Resume</a>
            <br><br>
            <a class="btn-custom" href="/jobsweb/applicant/upload-resume.php">Upload New Resume</a>
        <?php else: ?>
            <p><b>Status:</b> <span style="color:red;">No Resume Uploaded ✖</span></p>
            <a class="btn-custom" href="/jobsweb/applicant/upload-resume.php">Upload Resume</a>
        <?php endif; ?>
    </div>

    <!-- Recommended Jobs -->
    <div class="section">
        <h3>Recommended Jobs</h3>

        <?php
        $city = $user['city'];
        $state = $user['state'];

        $result_city = $conn->query("SELECT * FROM jobs WHERE job_city='$city' AND job_status='active' ORDER BY created_at DESC LIMIT 5");
        $result_state = null;
        $result_recent = null;

        if ($result_city->num_rows < 3) {
            $result_state = $conn->query("SELECT * FROM jobs WHERE job_state='$state' AND job_status='active' ORDER BY created_at DESC LIMIT 5");
        }

        if ($result_city->num_rows == 0 && ($result_state && $result_state->num_rows == 0)) {
            $result_recent = $conn->query("SELECT * FROM jobs WHERE job_status='active' ORDER BY created_at DESC LIMIT 5");
        }
        ?>

        <?php if ($result_city->num_rows > 0): ?>
            <h4>Jobs in Your City (<?= $city ?>)</h4>
            <?php while ($job = $result_city->fetch_assoc()): ?>
                <div style="padding:10px;background:white;border-radius:6px;border-left:4px solid #0a4c90;margin:10px 0;">
                    <b><?= $job['title'] ?></b><br>
                    <?= $job['company'] ?><br>
                    <small><?= $job['job_city'] ?>, <?= $job['job_state'] ?></small><br>
                    <a class="btn-custom" href="/jobsweb/job-detail.php?id=<?= $job['id'] ?>">View Job</a>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>

        <?php if ($result_state && $result_state->num_rows > 0): ?>
            <h4>Jobs in Your State (<?= $state ?>)</h4>
            <?php while ($job = $result_state->fetch_assoc()): ?>
                <div style="padding:10px;background:white;border-radius:6px;border-left:4px solid #28a745;margin:10px 0;">
                    <b><?= $job['title'] ?></b><br>
                    <?= $job['company'] ?><br>
                    <small><?= $job['job_city'] ?>, <?= $job['job_state'] ?></small><br>
                    <a class="btn-custom" href="/jobsweb/job-detail.php?id=<?= $job['id'] ?>">View Job</a>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>

        <?php if ($result_recent && $result_recent->num_rows > 0): ?>
            <h4>Latest Jobs</h4>
            <?php while ($job = $result_recent->fetch_assoc()): ?>
                <div style="padding:10px;background:white;border-radius:6px;border-left:4px solid #ffc107;margin:10px 0;">
                    <b><?= $job['title'] ?></b><br>
                    <?= $job['company'] ?><br>
                    <small><?= $job['job_city'] ?>, <?= $job['job_state'] ?></small><br>
                    <a class="btn-custom" href="/jobsweb/job-detail.php?id=<?= $job['id'] ?>">View Job</a>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>

        <a class="btn-custom" href="/jobsweb/job-list.php">Browse All Jobs</a>
    </div>

    <div class="section">
        <a class="btn-custom" href="/jobsweb/applicant/applications.php">View My Applications</a>
    </div>

    <div class="section">
        <a class="btn-custom" style="background:#d9534f;" href="/jobsweb/public/auth/logout.php">Logout</a>
    </div>

</div>

</body>
</html>
