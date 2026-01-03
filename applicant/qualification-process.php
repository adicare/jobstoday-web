<?php
// applicant/qualification-process.php

session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

include "../config/config.php";

$applicant_id = (int)$_SESSION['applicant_id'];
$action = $_POST['action'] ?? '';

if ($action === 'bulk_add') {

    /* ---------- BASIC VALIDATION ---------- */
    if (!isset($_POST['qualifications']) || !is_array($_POST['qualifications'])) {
        $_SESSION['edu_success'] = "No qualification data received.";
        header("Location: qualification.php");
        exit;
    }

    /* ---------- MAX 6 QUALIFICATIONS ---------- */
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM applicant_education 
        WHERE applicant_id = ?
    ");
    $stmt->bind_param("i", $applicant_id);
    $stmt->execute();
    $stmt->bind_result($cnt);
    $stmt->fetch();
    $stmt->close();

    if ($cnt + count($_POST['qualifications']) > 6) {
        $_SESSION['edu_success'] = " Qualification Updated Successfully.";
        header("Location: qualification.php");
        exit;
    }

    /* ---------- INSERT WITH STATE COLUMN ---------- */
    $insert = $conn->prepare("
        INSERT INTO applicant_education
        (applicant_id, qualification_level, course_name, specialization,
         university, state, year_of_passing, is_pursuing, study_mode, percentage)
        VALUES (?,?,?,?,?,?,?,?,?,?)
    ");

    foreach ($_POST['qualifications'] as $row) {

        $q = json_decode($row['data'], true);
        if (!$q) continue;

        $level      = trim($q['level']);
        $course     = trim($q['course']);
        $spec       = trim($q['specialization']);
        $university = trim($q['university']);
        $state      = trim($q['state']);          // ✅ NEW
        $study_mode = trim($q['study_mode']);
        $status     = $q['status'] ?? '';
        $percentage = trim($q['result']);

        if ($status === 'pursuing') {
            $is_pursuing = 1;
            $year = NULL;
        } else {
            $is_pursuing = 0;
            $year = !empty($q['year']) ? (int)$q['year'] : NULL;
        }

        if ($level === '' || $course === '' || $university === '' || $state === '') {
            continue;
        }

        $insert->bind_param(
            "isssssisss",
            $applicant_id,
            $level,
            $course,
            $spec,
            $university,
            $state,        // ✅ STATE BOUND
            $year,
            $is_pursuing,
            $study_mode,
            $percentage
        );

        $insert->execute();
    }

    $insert->close();

    /* ---------- SUCCESS MESSAGE (GREEN) ---------- */
    $_SESSION['edu_success'] = "Your qualification updated successfully";

    header("Location: qualification.php");
    exit;
}

/* ---------- FALLBACK ---------- */
header("Location: qualification.php");
exit;
