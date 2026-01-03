<?php
session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}
include "../config/config.php";

$app_id = (int) $_SESSION['applicant_id'];

/* Begin transaction */
$conn->begin_transaction();

try {
    // Remove existing choices for this applicant (we will re-insert up to 3)
    $del = $conn->prepare("DELETE FROM applicant_industries WHERE applicant_id = ?");
    $del->bind_param("i", $app_id);
    $del->execute();
    $del->close();

    // We'll insert up to 3 choices
    $ins = $conn->prepare("INSERT INTO applicant_industries (applicant_id, choice_order, industry_master_id, user_custom_id) VALUES (?, ?, ?, ?)");
    
    for ($i = 1; $i <= 3; $i++) {
        $master_key = 'master_id_'.$i;
        $master_val = $_POST[$master_key] ?? '';

        $master_id = null;
        $custom_id = null;

        if (is_string($master_val) && $master_val !== '') {
            // Supporting user custom selection from optgroup values like "uc_12"
            if (strpos($master_val, 'uc_') === 0) {
                $cid = (int) substr($master_val, 3);
                // ensure this custom belongs to this user
                $chk = $conn->prepare("SELECT id FROM user_custom_industry WHERE id = ? AND applicant_id = ?");
                $chk->bind_param("ii", $cid, $app_id);
                $chk->execute();
                $res = $chk->get_result();
                if ($res->num_rows === 1) {
                    $custom_id = $cid;
                }
                $chk->close();
            } else {
                // numeric master id
                $mid = (int)$master_val;
                // validate master exists and active
                $chk2 = $conn->prepare("SELECT id FROM industry_master WHERE id = ? AND status = 1");
                $chk2->bind_param("i", $mid);
                $chk2->execute();
                $res2 = $chk2->get_result();
                if ($res2->num_rows === 1) {
                    $master_id = $mid;
                }
                $chk2->close();
            }
        }

        // Only insert if either master_id or custom_id set
        if ($master_id !== null || $custom_id !== null) {
            $mval = $master_id ?? null;
            $cval = $custom_id ?? null;
            $ins->bind_param("iiii", $app_id, $i, $mval, $cval);
            $ins->execute();
        }
    }
    $ins->close();

    $conn->commit();
    $_SESSION['pref_msg'] = "Preferences saved successfully.";
    header("Location: preferred-industry.php");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['pref_msg'] = "Error: " . $e->getMessage();
    header("Location: preferred-industry.php");
    exit;
}