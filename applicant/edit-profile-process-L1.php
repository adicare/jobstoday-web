<?php
/* ======================================================
   FINAL INDIA VERSION — edit-profile-process.php
   SAFE AUTO-BIND | TEHSIL FREE | COUNT-PROOF
   ====================================================== */

session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

require_once "../config/config.php";

$app_id = (int) $_SESSION['applicant_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method");
}

/* ======================================================
   1) COLLECT INPUTS
   ====================================================== */

/* -------- PERSONAL -------- */
$full_name  = trim($_POST['full_name'] ?? '');
$gender     = trim($_POST['gender'] ?? '');
$dob        = trim($_POST['dob'] ?? '');
$birth_time = trim($_POST['birth_time'] ?? '');

/* -------- PRESENT LOCATION -------- */
$country   = 'India';
$state     = trim($_POST['state'] ?? '');
$district  = trim($_POST['district'] ?? '');
$village   = trim($_POST['village'] ?? '');
$city      = trim($_POST['city'] ?? '');

$present_pin = trim($_POST['present_pincode'] ?? '');
$present_lat = ($_POST['present_lat'] === '' ? null : (float) $_POST['present_lat']);
$present_lng = ($_POST['present_lng'] === '' ? null : (float) $_POST['present_lng']);

/* -------- BIRTH LOCATION -------- */
$birth_same = ($_POST['birth_same_as_present'] ?? 'yes');

$birth_country  = 'India';
$birth_state    = trim($_POST['birth_state'] ?? '');
$birth_district = trim($_POST['birth_district'] ?? '');
$birth_village  = trim($_POST['birth_village'] ?? '');
$birth_pin      = trim($_POST['birth_pincode'] ?? '');

$birth_lat = ($_POST['birth_lat'] === '' ? null : (float) $_POST['birth_lat']);
$birth_lng = ($_POST['birth_lng'] === '' ? null : (float) $_POST['birth_lng']);

/* -------- CONTACT -------- */
$country_code = '+91';
$mobile       = trim($_POST['mobile'] ?? '');

/* ======================================================
   2) ENFORCE BIRTH = PRESENT IF SAME
   ====================================================== */
if ($birth_same === 'yes') {
    $birth_state    = $state;
    $birth_district = $district;
    $birth_village  = $village;
    $birth_pin      = $present_pin;
    $birth_lat      = $present_lat;
    $birth_lng      = $present_lng;
}

/* ======================================================
   3) SQL (22 PLACEHOLDERS)
   ====================================================== */

$sql = "
UPDATE job_seekers SET
    full_name = ?, 
    gender = ?, 
    dob = ?, 
    birth_time = ?,

    country = ?, 
    state = ?, 
    district = ?, 
    village = ?, 
    city = ?, 
    present_pincode = ?, 
    present_lat = ?, 
    present_lng = ?,

    birth_country = ?, 
    birth_state = ?, 
    birth_district = ?, 
    birth_village = ?, 
    birth_pincode = ?, 
    birth_lat = ?, 
    birth_lng = ?,

    country_code = ?, 
    mobile = ?
WHERE id = ?
LIMIT 1
";

/* ======================================================
   4) PARAM ARRAY (ORDER = SQL ?)
   ====================================================== */
$params = [
    $full_name,
    $gender,
    $dob,
    $birth_time,

    $country,
    $state,
    $district,
    $village,
    $city,
    $present_pin,
    $present_lat,
    $present_lng,

    $birth_country,
    $birth_state,
    $birth_district,
    $birth_village,
    $birth_pin,
    $birth_lat,
    $birth_lng,

    $country_code,
    $mobile,

    $app_id
];

/* ======================================================
   5) AUTO BUILD TYPE STRING
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
   6) PREPARE, BIND, EXECUTE
   ====================================================== */
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    header("Location: edit-profile.php?success=1");
    exit;
} else {
    die("Update failed: " . $stmt->error);
}
