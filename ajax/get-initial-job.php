<?php
session_start();
include(__DIR__ . '/../config/config.php');

// If applicant logged in
if (!empty($_SESSION['user_id']) && $_SESSION['role'] === 'applicant') {

    $uid = $_SESSION['user_id'];

    // Fetch applicant skills
    $skillQ = $conn->query("SELECT skills FROM applicants WHERE id = $uid");

    if ($skillQ && $skillQ->num_rows > 0) {
        $skillRow = $skillQ->fetch_assoc();
        $skills = explode(",", strtolower($skillRow['skills']));

        if (!empty($skills)) {

            // Find first matching skill job
            foreach ($skills as $skill) {
                $skill = trim($skill);
                if ($skill === "") continue;

                $jobQ = $conn->query("
                    SELECT id FROM jobs 
                    WHERE LOWER(description) LIKE '%$skill%' 
                       OR LOWER(title) LIKE '%$skill%'
                    ORDER BY id DESC LIMIT 1
                ");

                if ($jobQ && $jobQ->num_rows > 0) {
                    $job = $jobQ->fetch_assoc();
                    echo $job['id'];
                    exit;
                }
            }
        }
    }
}

// If no skill match OR no applicant logged in → return latest job
$latest = $conn->query("SELECT id FROM jobs ORDER BY id DESC LIMIT 1");

if ($latest->num_rows > 0) {
    echo $latest->fetch_assoc()['id'];
    exit;
}

echo 0;  // No jobs
