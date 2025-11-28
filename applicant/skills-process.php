<?php
// FILE: applicant/skills-process.php
session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

include __DIR__ . "/../config/config.php";

$app_id = (int) $_SESSION['applicant_id'];
$action = $_POST['action'] ?? '';

/* -----------------------
   ADD NEW SKILL
-------------------------*/
if ($action === 'add') {

    $skill_id = (int) ($_POST['skill_id'] ?? 0);
    $experience_years = trim($_POST['experience_years'] ?? '');
    $proficiency = $_POST['proficiency'] ?? 'Beginner';

    if (!$skill_id || $experience_years === '') {
        $_SESSION['skill_msg'] = "Please select skill and enter experience.";
        header("Location: /jobsweb/applicant/skills.php");
        exit;
    }

    // prevent duplicate skill for same applicant
    $stmt = $conn->prepare("SELECT id FROM applicant_skills WHERE applicant_id = ? AND skill_id = ?");
    if (!$stmt) {
        $_SESSION['skill_msg'] = "Database error (prepare).";
        header("Location: /jobsweb/applicant/skills.php");
        exit;
    }
    $stmt->bind_param("ii", $app_id, $skill_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        $_SESSION['skill_msg'] = "Skill already added.";
        header("Location: /jobsweb/applicant/skills.php");
        exit;
    }
    $stmt->close();

    // insert new skill
    $ins = $conn->prepare("
        INSERT INTO applicant_skills(applicant_id, skill_id, experience_years, proficiency)
        VALUES (?,?,?,?)
    ");
    if (!$ins) {
        $_SESSION['skill_msg'] = "Database error (prepare insert).";
        header("Location: /jobsweb/applicant/skills.php");
        exit;
    }
    $ins->bind_param("iiss", $app_id, $skill_id, $experience_years, $proficiency);

    $_SESSION['skill_msg'] = $ins->execute() ? "Skill added." : "Database error, try again.";
    $ins->close();

    header("Location: /jobsweb/applicant/skills.php");
    exit;
}


/* -----------------------
   DELETE SKILL
-------------------------*/
if ($action === 'delete') {

    $app_skill_id = (int) ($_POST['app_skill_id'] ?? 0);

    if ($app_skill_id) {
        $del = $conn->prepare("DELETE FROM applicant_skills WHERE id = ? AND applicant_id = ?");
        if ($del) {
            $del->bind_param("ii", $app_skill_id, $app_id);
            $del->execute();

            $_SESSION['skill_msg'] = ($del->affected_rows > 0) ? "Skill removed." : "Could not remove.";
            $del->close();
        } else {
            $_SESSION['skill_msg'] = "Database error (prepare delete).";
        }
    } else {
        $_SESSION['skill_msg'] = "Invalid request.";
    }

    header("Location: /jobsweb/applicant/skills.php");
    exit;
}

// default
header("Location: /jobsweb/applicant/skills.php");
exit;
