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
    background: var(--light-blue-bg);
    padding: 26px;
    border-radius: 12px;
    border: 1px solid var(--soft-divider);
    box-shadow: var(--shadow-sm);
}

/* Rows */
.pref-row {
    display: flex;
    gap: 16px;
    margin-bottom: 18px;
}
.pref-left { flex: 1; }
.pref-right { width: 260px; }

/* Labels */
.small-label {
    font-weight: 700;
    font-size: 14px;
    color: var(--brand-primary);
    margin-bottom: 6px;
}

.note {
    font-size: 13px;
    color: #666;
    margin-top: 4px;
}

/* Save Button */
.btn-save {
    background: var(--brand-secondary);
    color: #fff;
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    transition: 0.15s;
}
.btn-save:hover {
    background: var(--brand-primary);
}
</style>

<div class="page-wrapper">
    <div class="profile-grid">

        <!-- LEFT SIDEBAR (Correct!) -->
      <?php include "../includes/profile_sidebar.php"; ?>

        <!-- RIGHT CONTENT -->
        <section class="content-card">
            <h3 class="page-title">Preferred Industries (Select up to 3)</h3>

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

                    <!-- SELECT BOX -->
                    <div class="pref-left">
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

                    <!-- CUSTOM INPUT -->
                    <div class="pref-right">
                        <label class="small-label">Add Custom (optional)</label>
                        <input type="text" maxlength="80" 
                               class="form-control"
                               name="custom_<?= $i ?>"
                               placeholder="Type custom industry">
                        <div class="note">Custom entries stay private until admin approves.</div>
                    </div>

                </div>
                <?php endfor; ?>

                <button class="btn-save mt-3">Save Preferences</button>

            </form>

            <div class="note mt-3">
                Tip: Put your primary industry as Choice 1.
            </div>
        </section>

    </div>
</div>

<?php include "../includes/footer.php"; ?>
