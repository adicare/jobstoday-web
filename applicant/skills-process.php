<?php
// ============================================================
// FILE: applicant/skills-process.php
// PURPOSE: Add / Delete applicant skills (MAX 12 enforced)
// ============================================================

session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

include __DIR__ . "/../config/config.php";

$app_id = (int) $_SESSION['applicant_id'];
$action = $_POST['action'] ?? '';
$MAX_SKILLS = 12;

/* ============================================================
   ADD NEW SKILL
   ============================================================ */
if ($action === 'add') {

    $skill_id = (int) ($_POST['skill_id'] ?? 0);
    $experience_years = trim($_POST['experience_years'] ?? '');
    $proficiency = $_POST['proficiency'] ?? 'Beginner';

    /* ---- Basic validation ---- */
    if ($skill_id <= 0 || $experience_years === '') {
        $_SESSION['skill_msg'] = "Please select skill and enter experience.";
        header("Location: /jobsweb/applicant/skills.php");
        exit;
    }

    /* ---- Enforce MAX skill limit (BACKEND GUARD) ---- */
    $cntStmt = $conn->prepare(
        "SELECT COUNT(*) FROM applicant_skills WHERE applicant_id = ?"
    );
    $cntStmt->bind_param("i", $app_id);
    $cntStmt->execute();
    $cntStmt->bind_result($current_count);
    $cntStmt->fetch();
    $cntStmt->close();

    if ($current_count >= $MAX_SKILLS) {
        $_SESSION['skill_msg'] = "Maximum skill limit reached.";
        header("Location: /jobsweb/applicant/skills.php");
        exit;
    }

    /* ---- Prevent duplicate skill ---- */
    $dup = $conn->prepare(
        "SELECT id FROM applicant_skills WHERE applicant_id = ? AND skill_id = ?"
    );
    $dup->bind_param("ii", $app_id, $skill_id);
    $dup->execute();
    $dup->store_result();

    if ($dup->num_rows > 0) {
        $dup->close();
        $_SESSION['skill_msg'] = "Skill already added.";
        header("Location: /jobsweb/applicant/skills.php");
        exit;
    }
    $dup->close();

    /* ---- Insert skill ---- */
    $ins = $conn->prepare(
        "INSERT INTO applicant_skills
         (applicant_id, skill_id, experience_years, proficiency)
         VALUES (?, ?, ?, ?)"
    );

    if (!$ins) {
        $_SESSION['skill_msg'] = "Database error. Please try again.";
        header("Location: /jobsweb/applicant/skills.php");
        exit;
    }

    $ins->bind_param(
        "iiss",
        $app_id,
        $skill_id,
        $experience_years,
        $proficiency
    );

    $_SESSION['skill_msg'] = $ins->execute()
        ? "Skill added successfully."
        : "Could not add skill. Try again.";

    $ins->close();
    header("Location: /jobsweb/applicant/skills.php");
    exit;
}

/* ============================================================
   DELETE SKILL
   ============================================================ */
if ($action === 'delete') {

    $app_skill_id = (int) ($_POST['app_skill_id'] ?? 0);

    if ($app_skill_id > 0) {
        $del = $conn->prepare(
            "DELETE FROM applicant_skills WHERE id = ? AND applicant_id = ?"
        );
        if ($del) {
            $del->bind_param("ii", $app_skill_id, $app_id);
            $del->execute();

            $_SESSION['skill_msg'] =
                ($del->affected_rows > 0)
                ? "Skill removed."
                : "Unable to remove skill.";

            $del->close();
        } else {
            $_SESSION['skill_msg'] = "Database error.";
        }
    } else {
        $_SESSION['skill_msg'] = "Invalid request.";
    }

    header("Location: /jobsweb/applicant/skills.php");
    exit;
}

/* ============================================================
   DEFAULT FALLBACK
   ============================================================ */
header("Location: /jobsweb/applicant/skills.php");
exit;
