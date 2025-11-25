<?php
// FILE: applicant/skills.php
// Purpose: Applicant -> Manage Skills (Add / Remove / List)

session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

include __DIR__ . "/../config/config.php";

$app_id = (int) $_SESSION['applicant_id'];

// fetch master skills
$skillsRes = $conn->query("SELECT id, skill_name FROM skills_master WHERE status=1 ORDER BY skill_name ASC");

// fetch applicant skills (joined)
$stmt = $conn->prepare("
    SELECT ask.id as app_skill_id, sm.id AS skill_id, sm.skill_name,
           ask.experience_years, ask.proficiency
    FROM applicant_skills ask
    JOIN skills_master sm ON sm.id = ask.skill_id
    WHERE ask.applicant_id = ?
    ORDER BY ask.created_at DESC
");
$stmt->bind_param("i", $app_id);
$stmt->execute();
$appSkills = $stmt->get_result();
$stmt->close();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>My Skills — Applicant</title>
    <link rel="stylesheet" href="/jobsweb/assets/css/dashboard.css">

    <style>
        .skills-form { max-width:720px; margin:18px auto; }
        .skill-row { display:flex; gap:8px; align-items:center; margin-bottom:8px; }
        .skill-row select, .skill-row input { flex:1; padding:8px; border-radius:6px; border:1px solid #ddd; }

        .skill-list { margin-top:18px; }
        .skill-card {
            background:#fff; padding:12px; border-radius:8px;
            box-shadow:0 4px 12px rgba(0,0,0,0.03);
            margin-bottom:10px; display:flex;
            justify-content:space-between; align-items:center;
        }

        .btn { display:inline-block; padding:8px 12px;
               border-radius:6px; text-decoration:none;
               background:var(--cj-primary); color:#fff; }
        .btn.danger { background:#dc3545; }
        .msg { color:#0a7; margin-bottom:8px; font-weight:600; }
    </style>
</head>
<body>

<?php include __DIR__ . '/../public/includes/sidebar.php'; ?>

<main class="cj-main" style="padding-top:28px;">

    <div class="cj-card skills-form">
        <h3>Manage Skills</h3>
        <p>Add your skills with years of experience and proficiency.</p>

        <?php if (!empty($_SESSION['skill_msg'])): ?>
            <div class="msg"><?php echo $_SESSION['skill_msg']; unset($_SESSION['skill_msg']); ?></div>
        <?php endif; ?>

        <form id="addSkillForm" method="POST" action="/jobsweb/applicant/skills-process.php">
            <div class="skill-row">

                <select name="skill_id" required>
                    <option value="">Select skill...</option>
                    <?php if($skillsRes): while($sk = $skillsRes->fetch_assoc()): ?>
                        <option value="<?= (int)$sk['id']; ?>">
                            <?= htmlspecialchars($sk['skill_name']); ?>
                        </option>
                    <?php endwhile; endif; ?>
                </select>

                <select name="experience_years" required>
                    <option value="">Experience</option>
                    <option value="0-1 years">0-1 years</option>
                    <option value="1-3 years">1-3 years</option>
                    <option value="3-5 years">3-5 years</option>
                    <option value="5+ years">5+ years</option>
                </select>

                <select name="proficiency" required>
                    <option value="Beginner">Beginner</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Expert">Expert</option>
                </select>

                <input type="hidden" name="action" value="add">
                <button class="btn" type="submit">Add Skill</button>

            </div>
        </form>

        <div class="skill-list">
            <h4>Your Skills</h4>

            <?php if($appSkills && $appSkills->num_rows): ?>
                <?php while($r = $appSkills->fetch_assoc()): ?>
                    <div class="skill-card">
                        <div>
                            <strong><?= htmlspecialchars($r['skill_name']); ?></strong>
                            <div style="font-size:13px;color:#666;">
                                <?= htmlspecialchars($r['experience_years']); ?>
                                • <?= htmlspecialchars($r['proficiency']); ?>
                            </div>
                        </div>

                        <form method="POST" action="/jobsweb/applicant/skills-process.php">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="app_skill_id" value="<?= (int)$r['app_skill_id']; ?>">
                            <button class="btn danger"
                                onclick="return confirm('Delete this skill?')">Delete</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No skills added yet.</p>
            <?php endif; ?>

        </div>
    </div>

</main>
</body>
</html>
