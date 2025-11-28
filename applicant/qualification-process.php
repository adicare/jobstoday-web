<?php
// FILE: applicant/qualification-process.php
session_start();
if (!isset($_SESSION['applicant_id'])) { header("Location: /jobsweb/public/login.php"); exit; }
include "../config/config.php";

$app_id = (int)$_SESSION['applicant_id'];
$action = $_POST['action'] ?? '';

if ($action === 'add') {
  // sanitize & collect
  $level = trim($_POST['level'] ?? '');
  $course = trim($_POST['course_select'] ?? '');
  if ($course === '__other__') $course = trim($_POST['course_text'] ?? '');
  $spec = trim($_POST['spec_select'] ?? '');
  if ($spec === '__other__') $spec = trim($_POST['spec_text'] ?? '');
  $uni = trim($_POST['uni_select'] ?? '');
  if ($uni === '__other__') $uni = trim($_POST['uni_text'] ?? '');
  $year_sel = $_POST['year_or_pursuing'] ?? '';
  $is_pursuing = ($year_sel === 'pursuing') ? 1 : 0;
  $year = $is_pursuing ? NULL : (($year_sel && ctype_digit($year_sel)) ? (int)$year_sel : NULL);
  $study_mode = $_POST['study_mode'] ?? 'Regular';
  $percentage = trim($_POST['percentage'] ?? '');

  // basic validation
  if (!$level) { $_SESSION['edu_msg']="Select qualification level."; header("Location: qualification.php"); exit; }

  // max 6 check
  $stmt = $conn->prepare("SELECT COUNT(*) FROM applicant_education WHERE applicant_id = ?");
  $stmt->bind_param("i",$app_id); $stmt->execute(); $stmt->bind_result($cnt); $stmt->fetch(); $stmt->close();
  if ($cnt >= 6) { $_SESSION['edu_msg']="Maximum 6 education entries allowed."; header("Location: qualification.php"); exit; }

  // prevent exact duplicate (same level+course+year for same applicant)
  $dup = $conn->prepare("SELECT id FROM applicant_education WHERE applicant_id=? AND qualification_level=? AND course_name=? LIMIT 1");
  $dup->bind_param("iss",$app_id, $level, $course);
  $dup->execute(); $dup->store_result();
  if ($dup->num_rows > 0) { $dup->close(); $_SESSION['edu_msg']="This education entry already exists."; header("Location: qualification.php"); exit; }
  $dup->close();

  // insert record
  $ins = $conn->prepare("INSERT INTO applicant_education (applicant_id, qualification_level, course_name, specialization, university, year_of_passing, is_pursuing, study_mode, percentage) VALUES (?,?,?,?,?,?,?,?,?)");
  $ins->bind_param("isssisis s", $app_id, $level, $course, $spec, $uni, $year, $is_pursuing, $study_mode, $percentage);

  // If any master item selected as 'other', insert into masters (safe unique insert)
  if ($course) {
    $stmtc = $conn->prepare("INSERT IGNORE INTO courses_master (name) VALUES (?)"); $stmtc->bind_param("s",$course); $stmtc->execute(); $stmtc->close();
  }
  if ($spec) {
    $stmts = $conn->prepare("INSERT IGNORE INTO specializations_master (name) VALUES (?)"); $stmts->bind_param("s",$spec); $stmts->execute(); $stmts->close();
  }
  if ($uni) {
    $stmtu = $conn->prepare("INSERT IGNORE INTO universities_master (name) VALUES (?)"); $stmtu->bind_param("s",$uni); $stmtu->execute(); $stmtu->close();
  }

  if ($ins->execute()) {
    $_SESSION['edu_msg'] = "Education added.";
  } else {
    $_SESSION['edu_msg'] = "Database error, try again.";
  }
  $ins->close();
  header("Location: qualification.php");
  exit;
}

/* DELETE */
if ($action === 'delete') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id) {
    $del = $conn->prepare("DELETE FROM applicant_education WHERE id=? AND applicant_id=?");
    $del->bind_param("ii",$id,$app_id);
    $del->execute();
    $_SESSION['edu_msg'] = ($del->affected_rows>0) ? "Removed." : "Could not remove.";
    $del->close();
  }
  header("Location: qualification.php");
  exit;
}

// default
header("Location: qualification.php");
exit;
