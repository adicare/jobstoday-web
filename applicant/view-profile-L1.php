<?php
session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

require_once "../config/config.php";
include "../includes/header.php";

/* close header container if header opens one */
echo "</div>";

$app_id = (int)$_SESSION['applicant_id'];

/* ================= PERSONAL PROFILE ================= */
$profile = $conn->query("
    SELECT 
        full_name, gender, dob, birth_time, mobile,
        state, district, village, present_pincode,
        birth_state, birth_district, birth_village, birth_pincode
    FROM job_seekers
    WHERE id = $app_id
")->fetch_assoc();

/* ================= QUALIFICATIONS ================= */
/* ================= QUALIFICATIONS ================= */
$qualifications = $conn->query("
    SELECT 
        qualification_level,
        course_name,
        specialization,
        university,
        year_of_passing,
        is_pursuing,
        study_mode,
        percentage
    FROM applicant_education
    WHERE applicant_id = $app_id
    ORDER BY year_of_passing DESC
")->fetch_all(MYSQLI_ASSOC);


/* ================= EXPERIENCE ================= */
$experiences = $conn->query("
    SELECT company_name, job_role, duration_years, is_current
    FROM applicant_experience
    WHERE applicant_id = $app_id
    ORDER BY start_year DESC
")->fetch_all(MYSQLI_ASSOC);

/* ================= SKILLS ================= */
$skills = $conn->query("
    SELECT skill_id
    FROM applicant_skills
    WHERE applicant_id = $app_id
")->fetch_all(MYSQLI_ASSOC);

/* ================= PREFERRED INDUSTRIES ================= */
$industries = $conn->query("
    SELECT industry_name
    FROM applicant_preferred_industries
    WHERE applicant_id = $app_id
")->fetch_all(MYSQLI_ASSOC);

/* ================= RESUME ================= */
$resume = $conn->query("
    SELECT resume_file
    FROM applicant_resume
    WHERE applicant_id = $app_id
    LIMIT 1
")->fetch_assoc();
?>

<style>
.page-wrapper{max-width:1200px;margin:22px auto;padding:0 16px}
.profile-layout{display:flex;gap:20px;align-items:flex-start}
.profile-sidebar{width:240px;flex-shrink:0}
.profile-main{flex:1}
.profile-card{background:#fff;padding:20px;border-radius:10px;border:1px solid #e6edf9}
.section-title{margin:18px 0 8px;font-weight:700;color:#004aad}
.profile-row{margin-bottom:6px}
.badge{display:inline-block;padding:5px 10px;border-radius:6px;font-size:13px;margin:3px}
.bg-skill{background:#e6ffed;border:1px solid #28a745}
.bg-industry{background:#e6f0ff;border:1px solid #0d6efd}
hr{margin:16px 0}
@media(max-width:980px){
  .profile-layout{flex-direction:column}
  .profile-sidebar{width:100%}
}
</style>

<div class="page-wrapper">
<div class="profile-layout">

<!-- SIDEBAR -->
<aside class="profile-sidebar">
    <?php include "../includes/profile_sidebar.php"; ?>
</aside>

<!-- MAIN CONTENT -->
<main class="profile-card profile-main">

<h2>View Profile</h2>

<!-- ================= PERSONAL DETAILS ================= -->
<h6 class="section-title">Personal Details</h6>

<div class="profile-row"><strong>Name:</strong> <?= $profile['full_name'] ?? '-' ?></div>
<div class="profile-row"><strong>Gender:</strong> <?= $profile['gender'] ?? '-' ?></div>
<div class="profile-row"><strong>DOB:</strong> <?= $profile['dob'] ?? '-' ?></div>
<div class="profile-row"><strong>Birth Time:</strong> <?= $profile['birth_time'] ?? '-' ?></div>
<div class="profile-row"><strong>Mobile:</strong> <?= $profile['mobile'] ?? '-' ?></div>

<div class="profile-row">
<strong>Present Location:</strong>
<?= $profile['village'] ?>,
<?= $profile['district'] ?>,
<?= $profile['state'] ?> –
<?= $profile['present_pincode'] ?>
</div>

<div class="profile-row">
<strong>Birth Location:</strong>
<?= $profile['birth_village'] ?>,
<?= $profile['birth_district'] ?>,
<?= $profile['birth_state'] ?> –
<?= $profile['birth_pincode'] ?>
</div>

<hr>

<!-- ================= QUALIFICATIONS ================= -->
<h6 class="section-title">Qualifications</h6>
<?php if ($qualifications): ?>
    <?php foreach ($qualifications as $q): ?>
        <div class="profile-row">
            <strong><?= $q['level'] ?></strong> –
            <?= $q['course'] ?> (<?= $q['specialization'] ?>),
            <?= $q['university'] ?>,
            <?= $q['passing_year'] ?> |
            <?= $q['percentage'] ?>%
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="text-danger">No qualification added</div>
<?php endif; ?>

<hr>

<!-- ================= EXPERIENCE ================= -->
<h6 class="section-title">Experience</h6>
<?php if ($experiences): ?>
    <?php foreach ($experiences as $e): ?>
        <div class="profile-row">
            <strong><?= $e['designation'] ?></strong> –
            <?= $e['company_name'] ?>
            (<?= $e['start_date'] ?> to <?= $e['is_current'] ? 'Present' : $e['end_date'] ?>)
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="text-warning">No experience added</div>
<?php endif; ?>

<hr>

<!-- ================= SKILLS ================= -->
<h6 class="section-title">Skills</h6>
<?php if ($skills): ?>
    <?php foreach ($skills as $s): ?>
        <span class="badge bg-skill"><?= $s['skill_name'] ?></span>
    <?php endforeach; ?>
<?php else: ?>
    <div class="text-danger">Skills not added</div>
<?php endif; ?>

<hr>

<!-- ================= PREFERRED INDUSTRIES ================= -->
<h6 class="section-title">Preferred Industries</h6>
<?php if ($industries): ?>
    <?php foreach ($industries as $i): ?>
        <span class="badge bg-industry"><?= $i['industry_name'] ?></span>
    <?php endforeach; ?>
<?php else: ?>
    <div class="text-danger">Industries not selected</div>
<?php endif; ?>

<hr>

<!-- ================= RESUME ================= -->
<h6 class="section-title">Resume</h6>
<?php if (!empty($resume['resume_file'])): ?>
    <a href="../uploads/resume/<?= $resume['resume_file'] ?>" target="_blank">
        View Resume
    </a>
<?php else: ?>
    <div class="text-danger">Resume not uploaded</div>
<?php endif; ?>

</main>
</div>
</div>

<?php include "../includes/footer.php"; ?>
