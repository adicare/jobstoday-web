<?php
session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}
include "../config/config.php";

$app_id = (int) $_SESSION['applicant_id'];

/* Simple abusive-word list (add to this array) */
$badwords = ['spamword1','abuse1','abuse2']; // expand as needed

function clean_input($s) {
    $s = trim($s);
    $s = preg_replace('/\s+/', ' ', $s);
    $s = mb_substr($s, 0, 80); // max length
    return $s;
}

function contains_badword($s, $badwords) {
    $sl = mb_strtolower($s);
    foreach ($badwords as $b) {
        if ($b === '') continue;
        if (mb_strpos($sl, mb_strtolower($b)) !== false) return true;
    }
    return false;
}

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
        $custom_key = 'custom_'.$i;

        $master_val = $_POST[$master_key] ?? '';
        $custom_val = $_POST[$custom_key] ?? '';

        $master_id = null;
        $custom_id = null;

        // Priority: if custom field filled -> create custom entry and use it
        if (is_string($custom_val) && trim($custom_val) !== '') {
            $c = clean_input($custom_val);

            // Validate (only letters, numbers, spaces, hyphen, ampersand, dot)
            if (!preg_match('/^[\p{L}\p{N}\s\-\&\.\,]{2,80}$/u', $c)) {
                throw new Exception("Invalid characters in custom industry (choice {$i}).");
            }

            if (contains_badword($c, $badwords)) {
                throw new Exception("Custom industry for choice {$i} contains disallowed words.");
            }

            // Insert custom
            $insCustom = $conn->prepare("INSERT INTO user_custom_industry (applicant_id, industry_name, approved) VALUES (?, ?, 0)");
            $insCustom->bind_param("is", $app_id, $c);
            $insCustom->execute();
            $custom_id = $insCustom->insert_id;
            $insCustom->close();
        } else if (is_string($master_val) && $master_val !== '') {
            // If master dropdown used
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
            // Note: bind_param wants scalars; if null, pass null — mysqli will convert
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
