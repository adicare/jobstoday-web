<?php
/*
    FINAL CLEAN PROFILE CARD
    - Uses ONLY stored DB value (profile_completed)
    - 100% consistent with sidebar
    - No CURL, No fallback logic, No ajax mismatch
*/

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$applicant_id = intval($_SESSION['applicant_id'] ?? 0);

// default values
$photoPath  = "/jobsweb/assets/user-icon.png";
$name       = "Applicant";
$viewed     = 0;
$completion = 0;

// connection required
if ($applicant_id > 0 && isset($conn) && $conn) {

    $stmt = $conn->prepare("
        SELECT full_name, photo, profile_viewed, profile_completed 
        FROM job_seekers 
        WHERE id = ? LIMIT 1
    ");
    $stmt->bind_param("i", $applicant_id);
    $stmt->execute();
    $stmt->bind_result($db_name, $db_photo, $db_viewed, $db_completion);
    $stmt->fetch();
    $stmt->close();

    if (!empty($db_name)) {
        $name = $db_name;
    }

    $viewed     = intval($db_viewed);
    $completion = intval($db_completion);

    if (!empty($db_photo)) {
        $photoPath = "/jobsweb/uploads/photos/" . $db_photo;
    }
}

?>

<div class="profile-block">

    <div class="profile-photo">
        <img src="<?= htmlspecialchars($photoPath) ?>" 
             alt="Profile" 
             style="width:72px;height:72px;border-radius:8px;object-fit:cover;">
    </div>

    <div class="profile-info">

        <div class="profile-name"><?= htmlspecialchars($name) ?></div>

        <div class="profile-meta">
            <div>Profile Completed: <strong><?= $completion ?>%</strong></div>
            <div>Profile Viewed: <strong><?= $viewed ?></strong></div>
        </div>

        <div class="profile-links">
            <a href="/jobsweb/applicant/view-profile.php">View</a>
            <a href="/jobsweb/applicant/edit-profile.php">Update</a>
        </div>

    </div>

</div>
