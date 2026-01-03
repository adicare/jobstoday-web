<?php
/* ============================================================
   FILE: applicant/skills.php
   PURPOSE: Manage applicant skills (MAX 12, correct sidebar)
   ============================================================ */

session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

include "../config/config.php";
include "../includes/header.php";

$app_id = (int) $_SESSION['applicant_id'];
$max_skills = 12;

/* -------- Fetch master skills -------- */
$stmt = $conn->prepare("
    SELECT id, skill_name
    FROM skills_master
    WHERE status = 1
    ORDER BY skill_name ASC
");
$stmt->execute();
$master_skills = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* -------- Fetch applicant skills -------- */
$stmt2 = $conn->prepare("
    SELECT
        asn.id AS app_skill_id,
        s.skill_name,
        asn.experience_years,
        asn.proficiency
    FROM applicant_skills as asn
    JOIN skills_master s ON asn.skill_id = s.id
    WHERE asn.applicant_id = ?
    ORDER BY asn.id DESC
");
$stmt2->bind_param("i", $app_id);
$stmt2->execute();
$app_skills = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt2->close();

$skill_count = count($app_skills);
$can_add = ($skill_count < $max_skills);
?>

<style>
.skill-page-grid{
    display:grid;
    grid-template-columns:260px 1fr;
    gap:22px;
    margin-top:20px;
}
@media(max-width:900px){
    .skill-page-grid{ grid-template-columns:1fr; }
}
</style>

<!-- ================= PAGE LAYOUT ================= -->
<div class="container mt-3">
    <div class="skill-page-grid">

        <!-- LEFT SIDEBAR (CORRECT ONE) -->
        <aside>
            <?php include "../includes/profile_sidebar.php"; ?>
        </aside>

        <!-- RIGHT CONTENT -->
        <section>

            <h4 class="mb-3">My Skills</h4>

            <div class="alert <?= $can_add ? 'alert-info' : 'alert-warning' ?>">
                Skills added: <strong><?= $skill_count ?> / <?= $max_skills ?></strong>
                <?php if (!$can_add): ?>
                    <br>Maximum skill limit reached. Remove a skill to add another.
                <?php endif; ?>
            </div>

            <!-- ADD SKILL FORM -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="post" action="skills-process.php">
                        <input type="hidden" name="action" value="add">

                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Skill</label>
                                <select name="skill_id" class="form-select" required <?= !$can_add ? 'disabled' : '' ?>>
                                    <option value="">Select skill</option>
                                    <?php foreach ($master_skills as $ms): ?>
                                        <option value="<?= $ms['id'] ?>">
                                            <?= htmlspecialchars($ms['skill_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Experience (Years)</label>
                                <input type="number" name="experience_years"
                                       class="form-control"
                                       min="0" max="50"
                                       required <?= !$can_add ? 'disabled' : '' ?>>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Proficiency</label>
                                <select name="proficiency" class="form-select" required <?= !$can_add ? 'disabled' : '' ?>>
                                    <option value="Beginner">Beginner</option>
                                    <option value="Intermediate">Intermediate</option>
                                    <option value="Expert">Expert</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100"
                                    <?= !$can_add ? 'disabled' : '' ?>>
                                    Add Skill
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SKILLS GRID -->
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($app_skills as $s): ?>
                            <div class="col-md-3 col-sm-6">
                                <div class="border rounded p-2 bg-light position-relative h-100">

                                    <form method="post" action="skills-process.php"
                                          onsubmit="return confirm('Remove this skill?')"
                                          class="position-absolute top-0 end-0 m-1">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="app_skill_id" value="<?= $s['app_skill_id'] ?>">
                                        <button class="btn btn-sm btn-danger px-2 py-0">×</button>
                                    </form>

                                    <strong><?= htmlspecialchars($s['skill_name']) ?></strong>
                                    <div class="small text-muted">
                                        <?= (int)$s['experience_years'] ?> yrs ·
                                        <?= htmlspecialchars($s['proficiency']) ?>
                                    </div>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </section>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
