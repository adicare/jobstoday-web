<?php
session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

include "../config/config.php";

$app_id = (int) $_SESSION['applicant_id'];
$action = $_POST['action'] ?? '';

/* ==========================================================
   FUNCTION: Calculate Duration (years, months)
   ========================================================== */
function calc_duration($sm, $sy, $em, $ey)
{
    if ($sm == "" || $sy == "") return [0, 0];

    // If currently working → end = today
    if ($em == "" || $ey == "") {
        $end = new DateTime();
    } else {
        $end = DateTime::createFromFormat("M Y", "$em $ey");
    }

    $start = DateTime::createFromFormat("M Y", "$sm $sy");
    if (!$start) return [0, 0];

    $diff = $start->diff($end);
    return [$diff->y, $diff->m];
}


/* ==========================================================
   1) SET EXPERIENCE STATUS (Fresher / Experienced)
   ========================================================== */
if ($action === "set_status") {

    $experience_level = $_POST['experience_level'] ?? 'Fresher';

    $stmt = $conn->prepare("
        INSERT INTO applicant_career_info (applicant_id, experience_level)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE experience_level = VALUES(experience_level)
    ");
    $stmt->bind_param("is", $app_id, $experience_level);

    if ($stmt->execute()) {
        $_SESSION['exp_msg'] = "Experience status updated. (success)";
    } else {
        $_SESSION['exp_msg'] = "Error updating status.";
    }
    $stmt->close();

    header("Location: experience.php");
    exit;
}



/* ==========================================================
   2) ADD EXPERIENCE  (Only for Experienced users)
   ========================================================== */
if ($action == "add") {

    $job_title      = trim($_POST['job_title']);
    $company_name   = trim($_POST['company_name']);
    $industry_id    = $_POST['industry_id'];
    $job_role       = $_POST['job_role'] ?? null;

    $start_month    = $_POST['start_month'];
    $start_year     = $_POST['start_year'];

    $end_month      = $_POST['end_month'] ?: null;
    $end_year       = $_POST['end_year'] ?: null;

    $is_current     = isset($_POST['is_current']) ? 1 : 0;

    $employment_type = $_POST['employment_type'];
    $work_mode      = $_POST['work_mode'];

    $annual_salary  = !empty($_POST['annual_salary']) ? (int)$_POST['annual_salary'] : null;

    $responsibilities = trim($_POST['responsibilities']);

    // If currently working → ignore end date
    if ($is_current == 1) {
        $end_month = null;
        $end_year = null;
    }

    /* Calculate duration */
    list($duration_years, $duration_months) = calc_duration(
        $start_month,
        $start_year,
        $end_month,
        $end_year
    );

    /* Insert record */
    $stmt = $conn->prepare("
        INSERT INTO applicant_experience 
        (applicant_id, job_title, company_name, industry_id, job_role,
         start_month, start_year, end_month, end_year, is_current,
         duration_years, duration_months, employment_type, work_mode,
         annual_salary, responsibilities)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
    "ississsissiissis",
        $app_id,
        $job_title,
        $company_name,
        $industry_id,
        $job_role,
        $start_month,
        $start_year,
        $end_month,
        $end_year,
        $is_current,
        $duration_years,
        $duration_months,
        $employment_type,
        $work_mode,
        $annual_salary,
        $responsibilities
    );

    if ($stmt->execute()) {
        $_SESSION['exp_msg'] = "Experience added successfully. (success)";
    } else {
        $_SESSION['exp_msg'] = "Error adding experience: " . $stmt->error;
    }
    $stmt->close();

    header("Location: experience.php");
    exit;
}



/* ==========================================================
   3) DELETE EXPERIENCE
   ========================================================== */
if ($action == "delete") {

    $id = (int) $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM applicant_experience WHERE id=? AND applicant_id=? LIMIT 1");
    $stmt->bind_param("ii", $id, $app_id);

    if ($stmt->execute()) {
        $_SESSION['exp_msg'] = "Experience removed successfully. (success)";
    } else {
        $_SESSION['exp_msg'] = "Error deleting experience.";
    }
    $stmt->close();

    header("Location: experience.php");
    exit;
}

?>
