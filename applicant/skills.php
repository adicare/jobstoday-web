<?php
/* FILE: /applicant/skills.php
   PURPOSE: Manage applicant skills (standardized layout)
*/

session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

include "../config/config.php";
include "../includes/header.php";

$app_id = (int) $_SESSION['applicant_id'];

/* Fetch master skills */
$stmt = $conn->prepare("SELECT id, skill_name FROM skills_master WHERE status = 1 ORDER BY skill_name ASC");
$stmt->execute();
$master_res = $stmt->get_result();
$master_skills = $master_res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* Fetch applicant skills */
$stmt2 = $conn->prepare("
    SELECT asn.id AS app_skill_id, s.skill_name, asn.experience_years, asn.proficiency
    FROM applicant_skills AS asn
    JOIN skills_master AS s ON asn.skill_id = s.id
    WHERE asn.applicant_id = ?
    ORDER BY asn.id DESC
");
$stmt2->bind_param("i", $app_id);
$stmt2->execute();
$res2 = $stmt2->get_result();
$skills = $res2->fetch_all(MYSQLI_ASSOC);
$stmt2->close();

$msg = $_SESSION['skill_msg'] ?? '';
unset($_SESSION['skill_msg']);
?>

<style>
.content-box {
    background:#fff;
    padding:25px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
    margin-top:20px;
}

.skill-chip {
    display:inline-block;
    background:#e9f2ff;
    color:#0a4c90;
    padding:8px 12px;
    border-radius:20px;
    margin:6px;
    font-weight:600;
}

.btn-action {
    padding:8px 12px;
    background:#0a4c90;
    color:#fff;
    border:none;
    border-radius:6px;
    cursor:pointer;
}

.form-inline {
    display:flex;
    gap:8px;
    align-items:center;
}

.form-inline input, 
.form-inline select {
    flex:1;
    padding:8px;
    border-radius:6px;
    border:1px solid #ccc;
}

.small-note {
    color:#666;
    padding:8px 0;
}

.msg {
    padding:10px;
    border-radius:6px;
    margin-bottom:12px;
}

.msg.success {
    background:#e6ffed;
    color:#2b7a34;
}

.msg.error {
    background:#ffecec;
    color:#a33;
}

.del-form { 
    display:inline-block; 
    margin-left:8px; 
}

.del-btn {
    background:transparent;
    border:none;
    color:#d33;
    font-weight:700;
    cursor:pointer;
}

.chip-meta {
    font-size:0.85rem;
    color:#0a4c90;
    margin-left:8px;
    font-weight:500;
    opacity:0.85;
}
</style>

<div class="container mt-3">
    <div class="row">

        <!-- LEFT SIDEBAR (STANDARD) -->
        <div class="col-md-3">
            <?php include "profile-sidebar.php"; ?>
        </div>

        <!-- RIGHT CONTENT -->
        <div class="col-md-9">
            <div class="content-box">

                <h3 class="text-primary fw-bold mb-3">Manage Skills</h3>

                <?php if ($msg): ?>
                    <div class="msg <?= (strpos($msg, 'added') !== false || strpos($msg, 'removed') !== false) ? 'success' : 'error' ?>">
                        <?= htmlspecialchars($msg) ?>
                    </div>
                <?php endif; ?>

                <p>Add the skills you have — these help us match jobs.</p>

                <!-- ADD SKILL FORM -->
                <form action="skills-process.php" method="POST" class="form-inline mb-3">
                    <input type="hidden" name="action" value="add">

                    <select name="skill_id" required>
                        <option value="">-- Select skill --</option>
                        <?php foreach ($master_skills as $ms): ?>
                            <option value="<?= (int)$ms['id'] ?>">
                                <?= htmlspecialchars($ms['skill_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input name="experience_years" type="text" placeholder="Experience (years) e.g. 2" required>

                    <select name="proficiency" required>
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Expert">Expert</option>
                    </select>

                    <button type="submit" class="btn-action">Add</button>
                </form>

                <!-- SKILL LIST -->
                <div>
                    <?php if (count($skills) === 0): ?>
                        <div class="small-note">No skills added yet.</div>
                    <?php else: ?>
                        <?php foreach ($skills as $s): ?>
                            <div class="skill-chip">
                                <?= htmlspecialchars($s['skill_name']) ?>
                                <span class="chip-meta">
                                    <?= htmlspecialchars($s['proficiency']) ?> • <?= htmlspecialchars($s['experience_years']) ?>
                                </span>

                                <form method="POST" action="skills-process.php" class="del-form"
                                      onsubmit="return confirm('Remove this skill?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="app_skill_id" value="<?= (int)$s['app_skill_id'] ?>">
                                    <button type="submit" class="del-btn">✖</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    </div>
</div>

<?php include "../includes/footer.php"; ?>
