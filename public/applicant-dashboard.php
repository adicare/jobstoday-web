<?php
/* ============================================================
   FILE: applicant-dashboard.php
   USE: Applicant Dashboard (After OTP login)
   RESPONSE #: 5 + Step-9 + Step-10 + Step-11 + Step-12 Integration
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
    <title>Applicant Dashboard - CareerJano</title>

    <style>
        body { font-family: Arial; background:#f4f4f4; }
        .dash-box {
            width: 75%; margin:50px auto; padding:30px;
            background:#fff; border-radius:10px;
            box-shadow:0 0 12px rgba(0,0,0,0.1);
        }
        .welcome { font-size:22px; margin-bottom:15px; }
        .section { margin:20px 0; padding:15px;
                   background:#f9f9f9; border-radius:8px; }
        .btn {
            padding:10px 15px; background:#007bff;
            color:#fff; border:none; border-radius:6px;
            text-decoration:none; cursor:pointer;
        }
        .btn:hover { background:#0056b3; }

        /* PROFILE STRENGTH METER */
        .profile-meter {
            background: #eaeaea;
            border-radius: 10px;
            height: 18px;
            width: 100%;
            margin-top: 10px;
            overflow: hidden;
        }

        .profile-meter-fill {
            height: 100%;
            background: #28a745;
            text-align: center;
            color: white;
            font-size: 12px;
            line-height: 18px;
            transition: width 0.7s ease;
        }
    </style>
</head>

<body>

<div class="dash-box">

    <!-- ============================================================
         WELCOME HEADER
         ============================================================ -->
    <div class="welcome">
        Welcome, <b><?php echo $user['full_name']; ?></b> 🎉
    </div>

    <!-- ============================================================
         PROFILE STRENGTH METER
         ============================================================ -->
    <div class="section">
        <h3>Profile Strength</h3>

        <div class="profile-meter">
            <div class="profile-meter-fill"
                 style="width: <?php echo $profile_complete; ?>%;">
                 <?php echo $profile_complete; ?>%
            </div>
        </div>

        <?php if ($profile_complete < 80) : ?>
            <p style="margin-top:10px;">
                Your profile is only <b><?php echo $profile_complete; ?>%</b> complete.<br>
                Completing your profile helps us match better job opportunities.
            </p>
            <a class="btn" href="applicant/edit-profile.php">Complete Profile</a>
        <?php else : ?>
            <p style="margin-top:10px;">Great! Your profile looks strong and ready for job recommendations.</p>
        <?php endif; ?>
    </div>


    <!-- ============================================================
         PROFILE PHOTO (STEP-12)
         ============================================================ -->
    <div class="section">
        <h3>Profile Photo</h3>

        <?php if (!empty($user['photo'])): ?>
            <p><img src="uploads/photos/<?php echo $user['photo']; ?>"
                    style="width:80px; height:80px; border-radius:50%; object-fit:cover;"></p>
            <a class="btn" href="applicant/upload-photo.php">Change Photo</a>
        <?php else: ?>
            <p>No photo uploaded.</p>
            <a class="btn" href="applicant/upload-photo.php">Upload Photo</a>
        <?php endif; ?>
    </div>


    <!-- ============================================================
         RESUME STATUS
         ============================================================ -->
    <div class="section">
        <h3>Resume Status</h3>

    <?php if (!empty($user['resume_file'])): ?>
        
        <p><b>Status:</b> <span style="color:green;">Resume Uploaded ✔</span></p>

        <p>
            <a class="btn" href="uploads/resumes/<?php echo $user['resume_file']; ?>" 
               target="_blank">Download Resume</a>
        </p>

        <p>You can replace your resume anytime:</p>
        <a class="btn" href="applicant/upload-resume.php">Upload New Resume</a>

    <?php else: ?>

        <p><b>Status:</b> <span style="color:red;">No Resume Uploaded ✖</span></p>

        <p>Upload your resume to complete your profile.</p>
        <a class="btn" href="applicant/upload-resume.php">Upload Resume</a>

    <?php endif; ?>
    </div>


    <!-- ============================================================
         RECOMMENDED JOBS (STEP-10)
         ============================================================ -->
    <div class="section">
        <h3>Recommended Jobs</h3>

        <?php
        $city = $user['city'];
        $state = $user['state'];

        // City-based jobs
        $sql_city = "SELECT * FROM jobs 
                    WHERE job_city = '$city' 
                    AND job_status = 'active'
                    ORDER BY created_at DESC
                    LIMIT 5";
        $result_city = $conn->query($sql_city);

        // State-based jobs if city < 3
        if ($result_city->num_rows < 3) {
            $sql_state = "SELECT * FROM jobs 
                          WHERE job_state = '$state'
                          AND job_status = 'active'
                          ORDER BY created_at DESC
                          LIMIT 5";
            $result_state = $conn->query($sql_state);
        }

        // Recent jobs backup
        if (($result_city->num_rows == 0) &&
            (isset($result_state) && $result_state->num_rows == 0)) {

            $sql_recent = "SELECT * FROM jobs
                           WHERE job_status = 'active'
                           ORDER BY created_at DESC
                           LIMIT 5";
            $result_recent = $conn->query($sql_recent);
        }
        ?>

        <!-- City Jobs -->
        <?php if ($result_city->num_rows > 0): ?>
            <h4>Jobs in Your City (<?= $city ?>)</h4>
            <?php while ($job = $result_city->fetch_assoc()): ?>
                <div style="margin:10px 0; padding:10px; background:#fff; border-radius:6px; border-left:4px solid #007bff;">
                    <b><?= $job['title'] ?></b><br>
                    <?= $job['company'] ?><br>
                    <small><?= $job['job_city'] ?>, <?= $job['job_state'] ?></small><br>
                    <a class="btn" href="job-detail.php?id=<?= $job['id'] ?>">View Job</a>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>


        <!-- State Jobs -->
        <?php if (isset($result_state) && $result_state->num_rows > 0): ?>
            <h4>Jobs in Your State (<?= $state ?>)</h4>
            <?php while ($job = $result_state->fetch_assoc()): ?>
                <div style="margin:10px 0; padding:10px; background:#fff; border-radius:6px; border-left:4px solid #28a745;">
                    <b><?= $job['title'] ?></b><br>
                    <?= $job['company'] ?><br>
                    <small><?= $job['job_city'] ?>, <?= $job['job_state'] ?></small><br>
                    <a class="btn" href="job-detail.php?id=<?= $job['id'] ?>">View Job</a>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>


        <!-- Recent Jobs -->
        <?php if (isset($result_recent) && $result_recent->num_rows > 0): ?>
            <h4>Latest Active Jobs</h4>
            <?php while ($job = $result_recent->fetch_assoc()): ?>
                <div style="margin:10px 0; padding:10px; background:#fff; border-radius:6px; border-left:4px solid #ffc107;">
                    <b><?= $job['title'] ?></b><br>
                    <?= $job['company'] ?><br>
                    <small><?= $job['job_city'] ?>, <?= $job['job_state'] ?></small><br>
                    <a class="btn" href="job-detail.php?id=<?= $job['id'] ?>">View Job</a>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>


        <p style="margin-top:10px;">
            <a class="btn" href="job-list.php">Browse All Jobs</a>
        </p>

    </div>


    <!-- ============================================================
         VIEW APPLICATIONS (STEP-11)
         ============================================================ -->
    <div class="section">
        <a class="btn" href="applicant/applications.php">View My Applications</a>
    </div>


    <!-- ============================================================
         LOGOUT BUTTON
         ============================================================ -->
    <div class="section">
        <a class="btn" style="background:#dc3545;" href="public/auth/logout.php">Logout</a>
    </div>

</div>

</body>
</html>
