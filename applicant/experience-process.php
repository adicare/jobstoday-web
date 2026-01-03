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
    $industry_id    = (int)$_POST['industry_id'];
    $industry_name  = trim($_POST['industry_name']);
    $job_role       = trim($_POST['job_role']) ?: null;

    $start_month    = $_POST['start_month'];
    $start_year     = $_POST['start_year'];

    $is_current     = isset($_POST['is_current']) ? 1 : 0;

    // Handle end_month and end_year
    // If currently working, set end date to today's date
    if ($is_current == 1) {
        $end_month = date('M');  // Current month (e.g., "Jan", "Feb")
        $end_year  = date('Y');  // Current year (e.g., "2024")
    } else {
        $end_month = (isset($_POST['end_month']) && $_POST['end_month'] !== '') ? $_POST['end_month'] : null;
        $end_year  = (isset($_POST['end_year']) && $_POST['end_year'] !== '') ? $_POST['end_year'] : null;
    }

    $employment_type   = $_POST['employment_type'];
    $work_mode         = $_POST['work_mode'];
    $district_location = trim($_POST['district_location']);

    $annual_salary  = !empty($_POST['annual_salary']) ? (int)$_POST['annual_salary'] : null;

    $responsibilities = trim($_POST['responsibilities']);

    /* Calculate duration */
    list($duration_years, $duration_months) = calc_duration(
        $start_month,
        $start_year,
        $end_month,
        $end_year
    );

    /* ======================================================
       Build SQL with all 18 fields
       ====================================================== */
    $sql = "
        INSERT INTO applicant_experience 
        (applicant_id, job_title, company_name, industry_id, industry_name, job_role,
         start_month, start_year, end_month, end_year, is_current,
         duration_years, duration_months, employment_type, work_mode,
         district_location, annual_salary, responsibilities)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    /* ======================================================
       Build params array (18 values in exact order)
       ====================================================== */
    $params = [
        $app_id,              // i
        $job_title,           // s
        $company_name,        // s
        $industry_id,         // i
        $industry_name,       // s
        $job_role,            // s (nullable)
        $start_month,         // s
        $start_year,          // s
        $end_month,           // s (now always has value - either user input or today's date)
        $end_year,            // s (now always has value - either user input or today's year)
        $is_current,          // i
        $duration_years,      // i
        $duration_months,     // i
        $employment_type,     // s
        $work_mode,           // s
        $district_location,   // s
        $annual_salary,       // i (nullable)
        $responsibilities     // s
    ];

    /* ======================================================
       Auto-build type string
       ====================================================== */
    $types = '';
    foreach ($params as $p) {
        if (is_int($p)) {
            $types .= 'i';
        } elseif (is_float($p)) {
            $types .= 'd';
        } else {
            $types .= 's';
        }
    }

    /* ======================================================
       Prepare, Bind, Execute
       ====================================================== */
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['exp_msg'] = "Prepare failed: " . $conn->error;
        header("Location: experience.php");
        exit;
    }

    $stmt->bind_param($types, ...$params);

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