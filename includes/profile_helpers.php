<?php
/* ==========================================================
   profile_helpers.php
   MASTER CALCULATION — METHOD B (Stored %)
   ========================================================== */

function calculate_profile_completion(mysqli $conn, int $app_id): int 
{
    if ($app_id <= 0) return 0;

    // Fetch main applicant details
    $stmt = $conn->prepare("
        SELECT full_name, mobile, photo, preferred_industry, resume_file 
        FROM job_seekers 
        WHERE id = ? LIMIT 1
    ");
    $stmt->bind_param("i", $app_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $u = $result->fetch_assoc();
    $stmt->close();

    if (!$u) return 0;

    $score = 0;

    /* PERSONAL DETAILS — 15 */
    if (!empty($u['full_name'])) $score += 7;
    if (!empty($u['mobile']))     $score += 8;

    /* PHOTO — 5 */
    if (!empty($u['photo'])) $score += 5;

    /* SKILLS — 15 */
    $skills = $conn->prepare("SELECT COUNT(*) FROM applicant_skills WHERE applicant_id = ?");
    $skills->bind_param("i", $app_id);
    $skills->execute();
    $skills->bind_result($skill_count);
    $skills->fetch();
    $skills->close();

    if ($skill_count > 0) $score += 15;

    /* EDUCATION — 15 */
    $edu = $conn->prepare("SELECT COUNT(*) FROM applicant_education WHERE applicant_id = ?");
    $edu->bind_param("i", $app_id);
    $edu->execute();
    $edu->bind_result($edu_count);
    $edu->fetch();
    $edu->close();

    if ($edu_count > 0) $score += 15;

    /* EXPERIENCE — 10 */
    $exp = $conn->prepare("
        SELECT experience_level 
        FROM applicant_career_info 
        WHERE applicant_id = ? 
        LIMIT 1
    ");
    $exp->bind_param("i", $app_id);
    $exp->execute();
    $exp->bind_result($exp_level);
    $exp->fetch();
    $exp->close();

    // Normalize exp_level (avoid NULL issues)
    $exp_level = $exp_level ?? "Fresher";

    if ($exp_level === "Experienced") {

        // Must have at least 1 experience record
        $e2 = $conn->prepare("SELECT COUNT(*) FROM applicant_experience WHERE applicant_id = ?");
        $e2->bind_param("i", $app_id);
        $e2->execute();
        $e2->bind_result($exp_count);
        $e2->fetch();
        $e2->close();

        if ($exp_count > 0) {
            $score += 15;
        }

    } else {
        // Fresher always gets 10
        $score += 15;
    }

    /* PREFERRED INDUSTRY — 15 */
    if (!empty($u['preferred_industry'])) $score += 15;

    /* RESUME — 20 */
    if (!empty($u['resume_file'])) $score += 20;

    /* Sanity check — Cap between 0 and 100 */
    if ($score < 0) $score = 0;
    if ($score > 100) $score = 100;

    return $score;
}



/* ==========================================================
   SUPPORT FUNCTIONS FOR SIDEBAR, CARDS, DASHBOARD
   ========================================================== */

function get_profile_name(mysqli $conn, int $app_id): string {
    $q = $conn->prepare("SELECT full_name FROM job_seekers WHERE id=? LIMIT 1");
    $q->bind_param("i", $app_id);
    $q->execute();
    $q->bind_result($name);
    $q->fetch();
    $q->close();
    return $name ?: "User";
}

function get_profile_photo(mysqli $conn, int $app_id): string {
    $q = $conn->prepare("SELECT photo FROM job_seekers WHERE id=? LIMIT 1");
    $q->bind_param("i", $app_id);
    $q->execute();
    $q->bind_result($photo);
    $q->fetch();
    $q->close();

    if (empty($photo)) {
        return "/jobsweb/assets/img/default_user.png";
    }

    return "/jobsweb/uploads/photos/" . $photo;
}

function get_profile_percent(mysqli $conn, int $app_id): int {
    $q = $conn->prepare("SELECT profile_completed FROM job_seekers WHERE id=? LIMIT 1");
    $q->bind_param("i", $app_id);
    $q->execute();
    $q->bind_result($percent);
    $q->fetch();
    $q->close();
    return intval($percent);
}
