<?php
// FILE: public/includes/sidebar.php
// USE: Fixed left sidebar (included into applicant-dashboard.php)
// RESPONSE #: 13

// session must already be started in parent page
$app_id = $_SESSION['applicant_id'] ?? null;
$photo = '';
$name  = 'Applicant';

if ($app_id) {
    // Safe fetch (assumes $conn is available in parent scope)
    if (isset($conn)) {
        $stmt = $conn->prepare("SELECT full_name, photo FROM job_seekers WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $app_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            $name = $row['full_name'] ?: $name;
            $photo = $row['photo'];
        }
        $stmt->close();
    }
}

// detect active route for highlight
$cur = basename($_SERVER['PHP_SELF']);
?>
<aside class="cj-sidebar">
    <div class="cj-sidebar-top">
        <div class="cj-profile">
            <?php if (!empty($photo)): ?>
                <img src="../uploads/photos/<?php echo htmlspecialchars($photo); ?>" alt="photo" class="cj-avatar">
            <?php else: ?>
                <div class="cj-avatar cj-avatar-placeholder"><?php echo strtoupper(substr($name,0,1)); ?></div>
            <?php endif; ?>
            <div class="cj-name"><?php echo htmlspecialchars($name); ?></div>
        </div>
    </div>

    <nav class="cj-menu">
        <a class="cj-menu-item <?php echo ($cur=='applicant-dashboard.php')?'active':''; ?>" href="applicant-dashboard.php">
            <span class="cj-icon">🏠</span><span class="cj-text">Dashboard</span>
        </a>

        <a class="cj-menu-item <?php echo ($cur=='edit-profile.php')?'active':''; ?>" href="applicant/edit-profile.php">
            <span class="cj-icon">👤</span><span class="cj-text">My Profile</span>
        </a>

         <a class="cj-menu-item" href="/jobsweb/applicant/skills.php">
        <span class="cj-icon">⚙️</span>
        <span class="cj-text">My Skills</span>
        </a>
        <a class="cj-menu-item <?php echo ($cur=='upload-resume.php')?'active':''; ?>" href="applicant/upload-resume.php">
            <span class="cj-icon">📄</span><span class="cj-text">Upload Resume</span>
        </a>

        <a class="cj-menu-item <?php echo ($cur=='upload-photo.php')?'active':''; ?>" href="applicant/upload-photo.php">
            <span class="cj-icon">📸</span><span class="cj-text">Upload Photo</span>
        </a>

        <a class="cj-menu-item <?php echo ($cur=='applications.php')?'active':''; ?>" href="applicant/applications.php">
            <span class="cj-icon">🧾</span><span class="cj-text">My Applications</span>
        </a>

        <a class="cj-menu-item" href="job-list.php">
            <span class="cj-icon">🔎</span><span class="cj-text">Browse Jobs</span>
        </a>

        <a class="cj-menu-item" href="applicant/saved-jobs.php">
            <span class="cj-icon">⭐</span><span class="cj-text">Saved Jobs</span>
        </a>

        <div class="cj-menu-sep"></div>

        <a class="cj-menu-item" href="public/auth/logout.php" style="margin-top:8px;">
            <span class="cj-icon">🚪</span><span class="cj-text">Logout</span>
        </a>
    </nav>

    <div class="cj-footer">
        <small>CareerJano • v1</small>
    </div>
</aside>
