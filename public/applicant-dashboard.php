<?php
/* ============================================================
   FILE: public/applicant-dashboard.php
   USE: Applicant Dashboard with fixed premium sidebar
   RESPONSE #: 13
   NOTE: Replace your current public/applicant-dashboard.php with this file.
   ============================================================ */

session_start();
include "config/config.php";  // DB connection

// If user not logged in → redirect
if (!isset($_SESSION['applicant_id'])) {
    header("Location: public/auth/login.php");
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
if (!empty($user['resume_file'])) $profile_complete += 30;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Applicant Dashboard - CareerJano</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<div class="cj-layout">

    <!-- SIDEBAR (fixed) -->
    <?php include "includes/sidebar.php"; ?>

    <!-- MAIN CONTENT -->
    <main class="cj-main">
        <!-- welcome card -->
        <div class="cj-card">
            <h2>Welcome, <?php echo htmlspecialchars($user['full_name']); ?> 🎉</h2>
            <p style="color:#555;">This is your dashboard. Use the left menu to navigate your profile, resumes, applications and saved jobs.</p>
        </div>

        <!-- profile strength -->
        <div class="cj-card">
            <h3>Profile Strength</h3>
            <div class="profile-meter" style="margin-top:10px;">
                <div class="profile-meter-fill" style="width: <?php echo $profile_complete; ?>%;">
                    <?php echo $profile_complete; ?>%
                </div>
            </div>
            <?php if ($profile_complete < 80): ?>
                <p style="margin-top:10px;">Your profile is only <strong><?php echo $profile_complete; ?>%</strong> complete.</p>
                <a class="btn" href="applicant/edit-profile.php">Complete Profile</a>
            <?php else: ?>
                <p style="margin-top:10px;">Great! Your profile looks strong and ready for job recommendations.</p>
            <?php endif; ?>
        </div>

        <!-- profile photo + resume status row -->
        <div class="cj-card" style="display:flex; gap:18px; flex-wrap:wrap;">
            <div style="flex:1; min-width:220px;">
                <h4>Profile Photo</h4>
                <?php if (!empty($user['photo'])): ?>
                    <img src="../uploads/photos/<?php echo htmlspecialchars($user['photo']); ?>" alt="photo" style="width:120px;height:120px;border-radius:10px;object-fit:cover;">
                    <p><a class="btn" href="applicant/upload-photo.php">Change Photo</a></p>
                <?php else: ?>
                    <p>No photo uploaded.</p>
                    <p><a class="btn" href="applicant/upload-photo.php">Upload Photo</a></p>
                <?php endif; ?>
            </div>

            <div style="flex:2; min-width:240px;">
                <h4>Resume Status</h4>
                <?php if (!empty($user['resume_file'])): ?>
                    <p><b>Status:</b> <span style="color:green;">Resume Uploaded ✔</span></p>
                    <p><a class="btn" href="../uploads/resumes/<?php echo htmlspecialchars($user['resume_file']); ?>" target="_blank">Download Resume</a></p>
                    <p><a class="btn" href="applicant/upload-resume.php">Upload New Resume</a></p>
                <?php else: ?>
                    <p><b>Status:</b> <span style="color:red;">No Resume Uploaded ✖</span></p>
                    <p><a class="btn" href="applicant/upload-resume.php">Upload Resume</a></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recommended jobs -->
        <div class="cj-card">
            <h3>Recommended Jobs</h3>
            <?php
            $city = $user['city'];
            $state = $user['state'];

            $sql_city = "SELECT * FROM jobs WHERE job_city = '".$conn->real_escape_string($city)."' AND job_status = 'active' ORDER BY created_at DESC LIMIT 5";
            $result_city = $conn->query($sql_city);

            if ($result_city->num_rows < 3) {
                $sql_state = "SELECT * FROM jobs WHERE job_state = '".$conn->real_escape_string($state)."' AND job_status = 'active' ORDER BY created_at DESC LIMIT 5";
                $result_state = $conn->query($sql_state);
            }

            if (($result_city->num_rows == 0) && (isset($result_state) && $result_state->num_rows == 0)) {
                $sql_recent = "SELECT * FROM jobs WHERE job_status = 'active' ORDER BY created_at DESC LIMIT 5";
                $result_recent = $conn->query($sql_recent);
            }
            ?>

            <?php if ($result_city->num_rows > 0): ?>
                <h4>Jobs in Your City (<?php echo htmlspecialchars($city); ?>)</h4>
                <?php while ($job = $result_city->fetch_assoc()): ?>
                    <div style="margin:10px 0; padding:10px; background:#fff; border-radius:6px; border-left:4px solid var(--cj-primary);">
                        <b><?php echo htmlspecialchars($job['title']); ?></b><br>
                        <?php echo htmlspecialchars($job['company']); ?><br>
                        <small><?php echo htmlspecialchars($job['job_city']).', '.htmlspecialchars($job['job_state']); ?></small><br>
                        <a class="btn" href="job-detail.php?id=<?php echo $job['id']; ?>">View Job</a>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>

            <?php if (isset($result_state) && $result_state->num_rows > 0): ?>
                <h4>Jobs in Your State (<?php echo htmlspecialchars($state); ?>)</h4>
                <?php while ($job = $result_state->fetch_assoc()): ?>
                    <div style="margin:10px 0; padding:10px; background:#fff; border-radius:6px; border-left:4px solid #28a745;">
                        <b><?php echo htmlspecialchars($job['title']); ?></b><br>
                        <?php echo htmlspecialchars($job['company']); ?><br>
                        <small><?php echo htmlspecialchars($job['job_city']).', '.htmlspecialchars($job['job_state']); ?></small><br>
                        <a class="btn" href="job-detail.php?id=<?php echo $job['id']; ?>">View Job</a>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>

            <?php if (isset($result_recent) && $result_recent->num_rows > 0): ?>
                <h4>Latest Active Jobs</h4>
                <?php while ($job = $result_recent->fetch_assoc()): ?>
                    <div style="margin:10px 0; padding:10px; background:#fff; border-radius:6px; border-left:4px solid #ffc107;">
                        <b><?php echo htmlspecialchars($job['title']); ?></b><br>
                        <?php echo htmlspecialchars($job['company']); ?><br>
                        <small><?php echo htmlspecialchars($job['job_city']).', '.htmlspecialchars($job['job_state']); ?></small><br>
                        <a class="btn" href="job-detail.php?id=<?php echo $job['id']; ?>">View Job</a>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>

            <p style="margin-top:12px;"><a class="btn" href="job-list.php">Browse All Jobs</a></p>
        </div>

        <!-- quick link to applications -->
        <div class="cj-card" style="text-align:center;">
            <a class="btn" href="applicant/applications.php">View My Applications</a>
        </div>

    </main>
</div>

</body>
</html>
