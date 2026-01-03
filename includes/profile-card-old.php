<?php
/*
  profile-card.php
  - Lightweight, safe profile card for left panel
  - DOES NOT call remote/local HTTP endpoints
  - Uses helper function get_profile_percent($conn, $id) if available
  - Does NOT start session (parent is expected to have started it)
*/

if (!isset($_SESSION)) {
    // parent should have started session; if not, try to start quietly
    @session_start();
}

// require applicant id
$applicant_id = intval($_SESSION['applicant_id'] ?? 0);

// defaults
$photoPath = "/jobsweb/assets/user-icon.png";
$name = "Applicant";
$viewed = 0;
$completion = 0;

// DB connection ($conn) is expected to be present from parent include
if (empty($applicant_id) || !isset($conn) || !$conn) {
    // Render a minimal card when not logged in or no db
    ?>
    <div class="profile-block">
        <div class="profile-photo">
            <img src="<?= htmlspecialchars($photoPath) ?>" alt="Profile">
        </div>
        <div class="profile-info">
            <div class="profile-name"><?= htmlspecialchars($name) ?></div>
            <div class="profile-meta">
                <div>Profile Completed: <strong><?= $completion ?>%</strong></div>
                <div>Profile Viewed: <strong><?= $viewed ?></strong></div>
            </div>
            <div class="profile-links">
                <a href="/jobsweb/public/login.php">Login</a>
            </div>
        </div>
    </div>
    <?php
    return;
}

// Fetch basic user details (prepared statement)
$stmt = $conn->prepare("SELECT full_name, photo, profile_viewed FROM job_seekers WHERE id = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param("i", $applicant_id);
    $stmt->execute();
    $stmt->bind_result($db_full_name, $db_photo, $db_viewed);
    $stmt->fetch();
    $stmt->close();

    if (!empty($db_full_name)) $name = $db_full_name;
    $viewed = intval($db_viewed);

    if (!empty($db_photo)) {
        $photoPath = "/jobsweb/uploads/photos/" . $db_photo;
    }
}

// PROFILE %: Prefer helper function if present
if (function_exists('get_profile_percent')) {
    // helper expects ($conn, $id)
    try {
        $completion = intval(get_profile_percent($conn, $applicant_id));
    } catch (\Throwable $e) {
        $completion = 0;
    }
} else {
    // fallback: try to compute quickly inline similar to your logic (safe)
    $completion = 5; // baseline

    // personal details (quick check)
    $stmt2 = $conn->prepare("SELECT full_name, mobile, photo, preferred_industry, resume_file FROM job_seekers WHERE id = ? LIMIT 1");
    if ($stmt2) {
        $stmt2->bind_param("i", $applicant_id);
        $stmt2->execute();
        $res = $stmt2->get_result();
        $u = $res->fetch_assoc();
        $stmt2->close();

        if ($u) {
            if (!empty($u['full_name']) && !empty($u['mobile'])) $completion += 15;
            if (!empty($u['photo'])) $completion += 5;

            // skills
            $s = $conn->prepare("SELECT COUNT(*) FROM applicant_skills WHERE applicant_id = ?");
            if ($s) { $s->bind_param("i",$applicant_id); $s->execute(); $s->bind_result($sc); $s->fetch(); $s->close(); if (intval($sc)>0) $completion+=15; }

            // education
            $e = $conn->prepare("SELECT COUNT(*) FROM applicant_education WHERE applicant_id = ?");
            if ($e) { $e->bind_param("i",$applicant_id); $e->execute(); $e->bind_result($ec); $e->fetch(); $e->close(); if (intval($ec)>0) $completion+=15; }

            // experience (simple)
            $ex = $conn->prepare("SELECT COUNT(*) FROM applicant_experience WHERE applicant_id = ?");
            if ($ex) { $ex->bind_param("i",$applicant_id); $ex->execute(); $ex->bind_result($exc); $ex->fetch(); $ex->close(); if (intval($exc)>0) $completion+=10; else $completion+=10; }

            if (!empty($u['preferred_industry'])) $completion += 15;
            if (!empty($u['resume_file'])) $completion += 20;
        }
    }
    $completion = min(100, $completion);
}

?>
<div class="profile-block">

    <div class="profile-photo">
        <img src="<?= htmlspecialchars($photoPath) ?>" alt="Profile" style="width:72px;height:72px;border-radius:8px;object-fit:cover;">
    </div>

    <div class="profile-info">

        <div class="profile-name"><?= htmlspecialchars($name) ?></div>

        <div class="profile-meta">
            <div>Profile Completed: <strong><?= htmlspecialchars($completion) ?>%</strong></div>
            <div>Profile Viewed: <strong><?= htmlspecialchars($viewed) ?></strong></div>
        </div>

        <div class="profile-links">
            <a href="/jobsweb/applicant/view-profile.php">View</a>
            <a href="/jobsweb/applicant/edit-profile.php">Update</a>
        </div>

    </div>

</div>
