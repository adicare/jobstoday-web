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
        full_name, email, mobile, gender, dob, birth_time,
        state, district, village, present_pincode,
        birth_state, birth_district, birth_village, birth_pincode,
        email_verified, mobile_verified, photo, resume_file
    FROM job_seekers
    WHERE id = $app_id
")->fetch_assoc();

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
    SELECT 
        company_name, 
        job_title, 
        duration_years, 
        is_current
    FROM applicant_experience
    WHERE applicant_id = $app_id
    ORDER BY is_current DESC, start_year DESC
")->fetch_all(MYSQLI_ASSOC);

/* ================= SKILLS ================= */
$skills = $conn->query("
    SELECT 
        s.skill_name,
        aps.proficiency
    FROM applicant_skills aps
    LEFT JOIN skills_master s ON aps.skill_id = s.id
    WHERE aps.applicant_id = $app_id
")->fetch_all(MYSQLI_ASSOC);

/* ================= PREFERRED INDUSTRIES - CORRECTED ================= */
$industries = $conn->query("
    SELECT 
        ai.choice_order,
        im.industry_name
    FROM applicant_industries ai
    LEFT JOIN industry_master im ON ai.industry_master_id = im.id
    WHERE ai.applicant_id = $app_id
    ORDER BY ai.choice_order
")->fetch_all(MYSQLI_ASSOC);

/* ================= RESUME & PHOTO ================= */
$resume_file = $profile['resume_file'] ?? '';
$photo_file = $profile['photo'] ?? '';
?>

<style>
.page-wrapper{max-width:1200px;margin:22px auto;padding:0 16px}
.profile-layout{display:flex;gap:20px;align-items:flex-start}
.profile-sidebar{width:240px;flex-shrink:0}
.profile-main{flex:1}
.profile-card{background:#fff;padding:20px;border-radius:10px;border:1px solid #e6edf9;margin-bottom:20px}
.profile-card h3{margin-bottom:14px;font-size:18px;font-weight:600;color:#004aad;border-bottom:2px solid #e6edf9;padding-bottom:8px}
.profile-card h4{margin-bottom:10px;font-size:16px;font-weight:600;color:#333;margin-top:16px}
.profile-section{margin-bottom:20px}
.info-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f0f0f0}
.info-row:last-child{border:0}
.info-label{font-weight:600;color:#555;min-width:140px}
.info-value{color:#333;text-align:right}
.qualification-item,.experience-item{background:#f9fbff;padding:12px;border-radius:6px;margin-bottom:10px;border-left:3px solid #0066cc}
.skill-badge{display:inline-block;background:#e8f4ff;padding:6px 12px;border-radius:16px;margin:4px;font-size:13px;color:#0066cc}
.industry-badge{display:inline-block;background:#fff3e6;padding:6px 12px;border-radius:16px;margin:4px;font-size:13px;color:#cc6600;border:1px solid #ffcc99}
.priority-number{
    display:inline-block;
    background:#004aad;
    color:#fff;
    width:20px;
    height:20px;
    line-height:20px;
    text-align:center;
    border-radius:50%;
    font-size:11px;
    font-weight:bold;
    margin-right:6px;
}
.resume-link{display:inline-block;background:#28a745;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;margin-top:10px}
.resume-link:hover{background:#218838;color:#fff}
.no-data{color:#999;font-style:italic;padding:10px 0}
.verified-badge{
    display:inline-block;
    background:#28a745;
    color:#fff;
    padding:3px 8px;
    border-radius:4px;
    font-size:11px;
    margin-left:6px;
}
.pending-badge{
    display:inline-block;
    background:#ffc107;
    color:#333;
    padding:3px 8px;
    border-radius:4px;
    font-size:11px;
    margin-left:6px;
}
.profile-photo{
    width:150px;
    height:150px;
    border-radius:10px;
    object-fit:cover;
    border:3px solid #e6edf9;
    margin-bottom:16px;
}
@media (max-width:880px){
    .profile-layout{flex-direction:column}
    .profile-sidebar{width:100%}
}
</style>

<div class="page-wrapper">
    <div class="profile-layout">

        <!-- SIDEBAR -->
        <div class="profile-sidebar">
            <div class="profile-card">
                <?php if ($photo_file): ?>
                    <img src="/jobsweb/uploads/photos/<?= htmlspecialchars($photo_file) ?>" 
                         alt="Profile Photo" 
                         class="profile-photo">
                <?php endif; ?>
                
                <h3>Quick Actions</h3>
                <a href="edit-profile.php" class="btn btn-primary btn-sm w-100 mb-2">Edit Profile</a>
                <a href="qualification.php" class="btn btn-secondary btn-sm w-100 mb-2">Education</a>
                <a href="experience.php" class="btn btn-secondary btn-sm w-100 mb-2">Experience</a>
                <a href="skills.php" class="btn btn-secondary btn-sm w-100 mb-2">Skills</a>
                <a href="preferred-industry.php" class="btn btn-secondary btn-sm w-100 mb-2">Industries</a>
                <a href="upload-resume.php" class="btn btn-secondary btn-sm w-100 mb-2">Upload Resume</a>
                <a href="dashboard.php" class="btn btn-info btn-sm w-100">Dashboard</a>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="profile-main">

            <!-- PERSONAL INFO -->
            <div class="profile-card">
                <h3>Personal Information</h3>
                <div class="info-row">
                    <span class="info-label">Full Name:</span>
                    <span class="info-value"><?= htmlspecialchars($profile['full_name'] ?? 'Not provided') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">
                        <?= htmlspecialchars($profile['email'] ?? 'Not provided') ?>
                        <?php if ($profile['email_verified']): ?>
                            <span class="verified-badge">✓ Verified</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Mobile:</span>
                    <span class="info-value">
                        <?= htmlspecialchars($profile['mobile'] ?? 'Not provided') ?>
                        <?php if ($profile['mobile_verified']): ?>
                            <span class="verified-badge">✓ Verified</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Gender:</span>
                    <span class="info-value"><?= htmlspecialchars($profile['gender'] ?? 'Not provided') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date of Birth:</span>
                    <span class="info-value"><?= htmlspecialchars($profile['dob'] ?? 'Not provided') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Birth Time:</span>
                    <span class="info-value"><?= htmlspecialchars($profile['birth_time'] ?? 'Not provided') ?></span>
                </div>
            </div>

            <!-- LOCATION INFO -->
            <div class="profile-card">
                <h3>Location Details</h3>
                <h4>Present Location</h4>
                <div class="info-row">
                    <span class="info-label">State:</span>
                    <span class="info-value"><?= htmlspecialchars($profile['state'] ?? 'Not provided') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">District:</span>
                    <span class="info-value"><?= htmlspecialchars($profile['district'] ?? 'Not provided') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Village/City:</span>
                    <span class="info-value"><?= htmlspecialchars($profile['village'] ?? 'Not provided') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Pincode:</span>
                    <span class="info-value"><?= htmlspecialchars($profile['present_pincode'] ?? 'Not provided') ?></span>
                </div>

                <h4>Birth Place</h4>
                <div class="info-row">
                    <span class="info-label">State:</span>
                    <span class="info-value"><?= htmlspecialchars($profile['birth_state'] ?? 'Not provided') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">District:</span>
                    <span class="info-value"><?= htmlspecialchars($profile['birth_district'] ?? 'Not provided') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Village/City:</span>
                    <span class="info-value"><?= htmlspecialchars($profile['birth_village'] ?? 'Not provided') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Pincode:</span>
                    <span class="info-value"><?= htmlspecialchars($profile['birth_pincode'] ?? 'Not provided') ?></span>
                </div>
            </div>

            <!-- PREFERRED INDUSTRIES -->
            <div class="profile-card">
                <h3>Preferred Industries</h3>
                <?php if (!empty($industries)): ?>
                    <?php foreach($industries as $ind): ?>
                        <div class="industry-badge">
                            <span class="priority-number"><?= $ind['choice_order'] ?></span>
                            <?= htmlspecialchars($ind['industry_name']) ?>
                            <?php if (isset($ind['approved']) && !$ind['approved']): ?>
                                <span class="pending-badge">Pending Approval</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <p style="margin-top:12px;font-size:13px;color:#666;">
                        <strong>Tip:</strong> Priority 1 is your primary preference. 
                        <a href="preferred-industry.php">Update preferences</a>
                    </p>
                <?php else: ?>
                    <p class="no-data">No preferred industries selected yet.</p>
                    <a href="preferred-industry.php" class="btn btn-primary btn-sm">Select Industries</a>
                <?php endif; ?>
            </div>

            <!-- QUALIFICATIONS -->
            <div class="profile-card">
                <h3>Educational Qualifications</h3>
                <?php if (!empty($qualifications)): ?>
                    <?php foreach($qualifications as $qual): ?>
                        <div class="qualification-item">
                            <strong><?= htmlspecialchars($qual['qualification_level']) ?></strong>
                            <?php if ($qual['course_name']): ?>
                                 - <?= htmlspecialchars($qual['course_name']) ?>
                            <?php endif; ?>
                            <?php if ($qual['specialization']): ?>
                                (<?= htmlspecialchars($qual['specialization']) ?>)
                            <?php endif; ?>
                            <br>
                            <small style="color:#666;">
                                <?php if ($qual['university']): ?>
                                    <?= htmlspecialchars($qual['university']) ?> | 
                                <?php endif; ?>
                                <?= $qual['is_pursuing'] ? 'Pursuing' : htmlspecialchars($qual['year_of_passing']) ?> | 
                                <?= htmlspecialchars($qual['study_mode']) ?>
                                <?php if ($qual['percentage']): ?>
                                    | <?= htmlspecialchars($qual['percentage']) ?>%
                                <?php endif; ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-data">No qualifications added yet.</p>
                    <a href="qualification.php" class="btn btn-primary btn-sm">Add Education</a>
                <?php endif; ?>
            </div>

            <!-- EXPERIENCE -->
                <div class="profile-card">
                <h3>Work Experience</h3>
                <?php if (!empty($experiences)): ?>
                    <?php foreach($experiences as $exp): ?>
                        <div class="experience-item">
                            <strong><?= htmlspecialchars($exp['job_title'] ?? '') ?></strong>
                            <?php if (!empty($exp['job_role'])): ?>
                                (<?= htmlspecialchars($exp['job_role']) ?>)
                            <?php endif; ?>
                            <?php if (!empty($exp['company_name'])): ?>
                                at <?= htmlspecialchars($exp['company_name']) ?>
                            <?php endif; ?>
                            <?php if (!empty($exp['is_current'])): ?>
                                <span class="verified-badge">Current</span>
                            <?php endif; ?>
                            <br>
                            <small style="color:#666;">
                                <?php if (!empty($exp['start_month']) && !empty($exp['start_year'])): ?>
                                    From: <?= htmlspecialchars($exp['start_month']) ?> <?= htmlspecialchars($exp['start_year']) ?>
                                <?php endif; ?>
                                <?php if (!empty($exp['end_month']) && !empty($exp['end_year']) && empty($exp['is_current'])): ?>
                                    To: <?= htmlspecialchars($exp['end_month']) ?> <?= htmlspecialchars($exp['end_year']) ?>
                                <?php endif; ?>
                                <?php if (!empty($exp['duration_years']) || !empty($exp['duration_months'])): ?>
                                    <br>Duration: 
                                    <?php if (!empty($exp['duration_years'])): ?>
                                        <?= $exp['duration_years'] ?> year<?= $exp['duration_years'] > 1 ? 's' : '' ?>
                                    <?php endif; ?>
                                    <?php if (!empty($exp['duration_months'])): ?>
                                        <?= $exp['duration_months'] ?> month<?= $exp['duration_months'] > 1 ? 's' : '' ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if (!empty($exp['district_location'])): ?>
                                    <br>Location: <?= htmlspecialchars($exp['district_location']) ?>
                                <?php endif; ?>
                                <?php if (!empty($exp['employment_type'])): ?>
                                    <br>Type: <?= htmlspecialchars($exp['employment_type']) ?>
                                    <?php if (!empty($exp['work_mode'])): ?>
                                        | <?= htmlspecialchars($exp['work_mode']) ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-data">No work experience added yet.</p>
                    <a href="experience.php" class="btn btn-primary btn-sm">Add Experience</a>
                <?php endif; ?>
            </div>
            <!-- SKILLS -->
            <div class="profile-card">
                <h3>Skills</h3>
                <?php if (!empty($skills)): ?>
                    <?php foreach($skills as $skill): ?>
                        <span class="skill-badge">
                            <?= htmlspecialchars($skill['skill_name'] ?? 'Skill #'.$skill['skill_id']) ?>
                            <?php if (!empty($skill['proficiency_level'])): ?>
                                - <?= htmlspecialchars($skill['proficiency_level']) ?>
                            <?php endif; ?>
                        </span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-data">No skills added yet.</p>
                    <a href="skills.php" class="btn btn-primary btn-sm">Add Skills</a>
                <?php endif; ?>
            </div>

            <!-- RESUME -->
            <div class="profile-card">
                <h3>Resume</h3>
                <?php if (!empty($resume_file)): ?>
                    <p>Resume uploaded: <strong><?= htmlspecialchars($resume_file) ?></strong></p>
                    <a href="/jobsweb/uploads/resumes/<?= htmlspecialchars($resume_file) ?>" 
                       target="_blank" 
                       class="resume-link">
                        📄 View Resume
                    </a>
                    <a href="upload-resume.php" 
                       class="btn btn-secondary btn-sm" 
                       style="margin-left:10px;">
                        Update Resume
                    </a>
                <?php else: ?>
                    <p class="no-data">No resume uploaded yet.</p>
                    <a href="upload-resume.php" class="btn btn-primary">Upload Resume</a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>