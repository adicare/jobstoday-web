<?php
session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

include "../config/config.php";
include "../includes/header.php";

$app_id = (int) $_SESSION['applicant_id'];

/* Fetch master industries (active) */
$master = $conn->prepare("SELECT id, industry_name FROM industry_master WHERE status = 1 ORDER BY industry_name ASC");
$master->execute();
$master_res = $master->get_result();
$master_list = $master_res->fetch_all(MYSQLI_ASSOC);
$master->close();

/* Fetch user's custom industries (both approved and unapproved, but only those added by this user) */
$uc = $conn->prepare("SELECT id, industry_name, approved FROM user_custom_industry WHERE applicant_id = ? ORDER BY created_at DESC");
$uc->bind_param("i", $app_id);
$uc->execute();
$uc_res = $uc->get_result();
$user_customs = $uc_res->fetch_all(MYSQLI_ASSOC);
$uc->close();

/* Fetch existing applicant choices (if any) */
$ai = $conn->prepare("SELECT choice_order, industry_master_id, user_custom_id FROM applicant_industries WHERE applicant_id = ?");
$ai->bind_param("i", $app_id);
$ai->execute();
$ai_res = $ai->get_result();
$existing = [];
while ($row = $ai_res->fetch_assoc()) {
    $existing[(int)$row['choice_order']] = $row;
}
$ai->close();

$msg = $_SESSION['pref_msg'] ?? '';
unset($_SESSION['pref_msg']);
?>

<style>
.content-box { background:#fff; padding:25px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.08); margin-top:20px; }
.row-choice { display:flex; gap:16px; margin-bottom:14px; align-items:center; }
.left-col { flex:1; }
.right-col { width:320px; } /* stable width for custom input */
label.small { font-weight:600; font-size:0.95rem; }
.btn-save { padding:10px 18px; background:#0a4c90; color:#fff; border:none; border-radius:6px; }
.note { color:#666; font-size:13px; margin-top:8px; }
.badge-unapproved { background:#f8d7da; color:#842029; padding:6px 8px; border-radius:6px; font-size:12px; margin-left:8px; }
</style>

<div class="container mt-3">
  <div class="row">

    <div class="col-md-3">
      <?php include "profile-sidebar.php"; ?>
    </div>

    <div class="col-md-9">
      <div class="content-box">
        <h4 class="text-primary fw-bold mb-3">Preferred Industries — Select up to 3</h4>

        <?php if ($msg): ?>
          <div class="mb-3 alert alert-info"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="POST" action="preferred-industry-process.php">

          <?php for ($i = 1; $i <= 3; $i++): 
              $sel_master = $existing[$i]['industry_master_id'] ?? null;
              $sel_custom = $existing[$i]['user_custom_id'] ?? null;
          ?>
            <div class="row-choice">
              <div class="left-col">
                <label class="small">Choice <?= $i ?> — Select from list</label>
                <select name="master_id_<?= $i ?>" class="form-select">
                  <option value="">-- Select industry --</option>
                  <?php foreach ($master_list as $m): ?>
                    <option value="<?= (int)$m['id'] ?>" <?= ($sel_master && $sel_master == $m['id']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($m['industry_name']) ?>
                    </option>
                  <?php endforeach; ?>
                  <?php /* Include user's custom choices for this user in dropdown so they can reselect them */ ?>
                  <?php if (!empty($user_customs)): ?>
                    <optgroup label="Your custom entries">
                      <?php foreach ($user_customs as $uc): ?>
                         <option value="uc_<?= (int)$uc['id'] ?>" <?= ($sel_custom && $sel_custom == $uc['id']) ? 'selected' : '' ?>>
                           <?= htmlspecialchars($uc['industry_name']) ?> <?= $uc['approved'] ? '' : ' (pending)' ?>
                         </option>
                      <?php endforeach; ?>
                    </optgroup>
                  <?php endif; ?>
                </select>
              </div>

              <div class="right-col">
                <label class="small">Add custom (optional)</label>
                <div class="d-flex">
                  <input maxlength="80" name="custom_<?= $i ?>" class="form-control" placeholder="Type custom industry (optional)">
                </div>
                <div class="note">If you add a custom industry it will be visible only to you until admin approves it.</div>
              </div>
            </div>
          <?php endfor; ?>

          <div class="mt-2">
            <button type="submit" class="btn-save">Save Preferences</button>
          </div>

        </form>

        <div class="mt-3 note">Tip: choose your primary industry as first choice. Custom entries require admin approval before becoming part of the standard list.</div>
      </div>
    </div>

  </div>
</div>

<?php include "../includes/footer.php"; ?>
