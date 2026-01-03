<?php
session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

include "../config/config.php";
include "../includes/header.php";

$app_id = (int) $_SESSION['applicant_id'];

/* ------------------------------------------------------
   Fetch MASTER industries
------------------------------------------------------ */
$q1 = $conn->prepare("
    SELECT id, industry_name 
    FROM industry_master 
    WHERE status = 1 
    ORDER BY industry_name ASC
");
$q1->execute();
$r1 = $q1->get_result();
$master_list = $r1->fetch_all(MYSQLI_ASSOC);
$q1->close();

/* ------------------------------------------------------
   Fetch user's custom industries
------------------------------------------------------ */
$q2 = $conn->prepare("
    SELECT id, industry_name, approved
    FROM user_custom_industry
    WHERE applicant_id = ?
    ORDER BY created_at DESC
");
$q2->bind_param("i", $app_id);
$q2->execute();
$r2 = $q2->get_result();
$user_customs = $r2->fetch_all(MYSQLI_ASSOC);
$q2->close();

/* ------------------------------------------------------
   Fetch existing choices
------------------------------------------------------ */
$q3 = $conn->prepare("
    SELECT choice_order, industry_master_id, user_custom_id
    FROM applicant_industries
    WHERE applicant_id = ?
");
$q3->bind_param("i", $app_id);
$q3->execute();
$res3 = $q3->get_result();

$existing = [];
while ($row = $res3->fetch_assoc()) {
    $existing[(int)$row['choice_order']] = $row;
}
$q3->close();

$msg = $_SESSION['pref_msg'] ?? '';
unset($_SESSION['pref_msg']);
?>

<style>
.page-wrapper {
    max-width: 1100px;
    margin: 28px auto;
    padding: 0 16px;
}

.profile-grid {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 22px;
}
@media (max-width:880px){
    .profile-grid { grid-template-columns: 1fr; }
}

.content-card {
    background: #fff;
    padding: 26px;
    border-radius: 12px;
    border: 1px solid #e1e5ee;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.page-header-nav {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.page-header-nav h3 {
    margin:0;
    color:#004aad;
    font-weight:bold;
}

.nav-buttons {
    display:flex;
    gap:10px;
}

.btn-back {
    background:#6c757d;
    color:#fff;
    padding:9px 18px;
    border-radius:8px;
    border:0;
    cursor:pointer;
    text-decoration:none;
    display:inline-block;
    font-weight:600;
}

.btn-back:hover {
    background:#5a6268;
    color:#fff;
}

.btn-next {
    background:#28a745;
    color:#fff;
    padding:9px 18px;
    border-radius:8px;
    border:0;
    cursor:pointer;
    text-decoration:none;
    display:inline-block;
    font-weight:600;
}

.btn-next:hover {
    background:#218838;
    color:#fff;
}

/* Rows */
.pref-row {
    margin-bottom: 18px;
}

/* Labels */
.small-label {
    font-weight: 700;
    font-size: 14px;
    color: #004aad;
    margin-bottom: 6px;
    display: block;
}

.note {
    font-size: 13px;
    color: #666;
    margin-top: 4px;
}

.msg {
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 16px;
}

.msg.success {
    background: #e6ffed;
    color: #156c21;
    border: 1px solid #28a745;
}

/* Save Button */
.btn-save {
    background: #004aad;
    color: #fff;
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    transition: 0.15s;
}
.btn-save:hover {
    background: #003a8c;
}
</style>

<div class="page-wrapper">
    <div class="profile-grid">

        <!-- LEFT SIDEBAR -->
        <?php include "../includes/profile_sidebar.php"; ?>

        <!-- RIGHT CONTENT -->
        <section class="content-card">
            
            <!-- PAGE HEADER WITH BACK/NEXT -->
            <div class="page-header-nav">
                <h3>Preferred Industries (Select up to 3)</h3>
                <div class="nav-buttons">
                    <a href="experience.php" class="btn-back">← BACK</a>
                    <a href="upload-resume.php" class="btn-next">NEXT →</a>
                </div>
            </div>

            <?php if ($msg): ?>
                <div class="msg success"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>

            <form method="POST" action="preferred-industry-process.php">

                <?php 
                for ($i = 1; $i <= 3; $i++):
                    $sel_master = $existing[$i]['industry_master_id'] ?? null;
                    $sel_custom = $existing[$i]['user_custom_id'] ?? null;
                ?>
                <div class="pref-row">
                    <label class="small-label">Choice <?= $i ?></label>
                    <select name="master_id_<?= $i ?>" class="form-select">
                        <option value="">-- Select industry --</option>

                        <?php foreach ($master_list as $m): ?>
                            <option value="<?= $m['id'] ?>" 
                                <?= ($sel_master == $m['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['industry_name']) ?>
                            </option>
                        <?php endforeach; ?>

                        <?php if (!empty($user_customs)): ?>
                        <optgroup label="Your Custom Entries">
                            <?php foreach ($user_customs as $u): ?>
                                <option value="uc_<?= $u['id'] ?>"
                                    <?= ($sel_custom == $u['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['industry_name']) ?>
                                    <?= $u['approved'] ? '' : ' (pending)' ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <?php endif; ?>
                    </select>
                </div>
                <?php endfor; ?>

                <button class="btn-save mt-3">Save Preferences</button>

            </form>

            <div class="note mt-3">
                <strong>Tip:</strong> Put your primary industry as Choice 1.
            </div>
        </section>

    </div>
</div>

<?php include "../includes/footer.php"; ?>