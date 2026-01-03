<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/* Load DB + helper */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/profile_helpers.php';

$app_id = intval($_SESSION['applicant_id'] ?? 0);

if ($app_id <= 0) {
    echo "<div style='color:white'>Login required</div>";
    return;
}

/* Fetch name + photo */
$name  = htmlspecialchars(get_profile_name($conn, $app_id));
$photo = htmlspecialchars(get_profile_photo($conn, $app_id));

/* Load stored percent FIRST (fallback) */
$percent = intval(get_profile_percent($conn, $app_id));
?>
<div class="profile-sidebar" style="
    width:200px;
    padding:14px 12px;
    background: linear-gradient(180deg, var(--brand-primary), #0367d9);
    color:white;
    border-radius:10px;
">

    <!-- TOP SECTION -->
    <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:10px;">

        <!-- PHOTO -->
        <img src="<?= $photo ?>"
             style="width:80px; height:80px; border-radius:50%; object-fit:cover; border:2px solid rgba(255,255,255,0.25);">

        <!-- METER -->
        <div style="width:70px; height:70px; position:relative;">
            <svg viewBox="0 0 100 100" style="width:70px; height:70px; transform:rotate(-90deg);">
                <circle cx="50" cy="50" r="40"
                        stroke="rgba(255,255,255,0.25)" stroke-width="7" fill="none"/>
                <circle id="meterArc" cx="50" cy="50" r="40"
                        stroke="#ff3b30" stroke-width="7"
                        stroke-linecap="round"
                        stroke-dasharray="251"
                        stroke-dashoffset="251"
                        style="transition:.6s;">
                </circle>
            </svg>

            <div id="meterPercent" style="
                position:absolute;
                top:50%; left:50%;
                transform:translate(-50%, -50%);
                font-size:14px;
                font-weight:700;
            "><?= $percent ?>%</div>
        </div>

    </div>

    <!-- NAME -->
    <div style="text-align:center; font-size:15px; font-weight:700; margin-bottom:12px;">
        <?= $name ?>
    </div>

    <hr style="border-color:rgba(255,255,255,0.25); margin:8px 0;">

    <!-- MENU LINKS -->
    <div style="display:flex; flex-direction:column; gap:4px;">
        <?php
        function active($file){
            return basename($_SERVER['PHP_SELF']) == $file ? "background:rgba(255,255,255,0.18);" : "";
        }
        ?>

        <a href="/jobsweb/applicant/edit-profile.php"       style="padding:6px 8px; border-radius:5px; <?=active('edit-profile.php')?>; color:white;">Edit Profile</a>
        <a href="/jobsweb/applicant/upload-photo.php"       style="padding:6px 8px; border-radius:5px; <?=active('upload-photo.php')?>; color:white;">Profile Photo</a>
        <a href="/jobsweb/applicant/skills.php"             style="padding:6px 8px; border-radius:5px; <?=active('skills.php')?>; color:white;">Skills</a>
        <a href="/jobsweb/applicant/qualification.php"      style="padding:6px 8px; border-radius:5px; <?=active('qualification.php')?>; color:white;">Qualification</a>
        <a href="/jobsweb/applicant/experience.php"         style="padding:6px 8px; border-radius:5px; <?=active('experience.php')?>; color:white;">Experience</a>
        <a href="/jobsweb/applicant/preferred-industry.php" style="padding:6px 8px; border-radius:5px; <?=active('preferred-industry.php')?>; color:white;">Preferred Industry</a>
        <a href="/jobsweb/applicant/upload-resume.php"      style="padding:6px 8px; border-radius:5px; <?=active('upload-resume.php')?>; color:white;">Resume Upload</a>

        <hr style="border-color:rgba(255,255,255,0.25); margin:6px 0;">

        <a href="/jobsweb/index.php"
           style="padding:6px 8px; font-size:14px; font-weight:600; opacity:0.9; color:white;">
           ← Back to Home
        </a>

    </div>

</div>

<!-- FETCH UPDATED % via AJAX -->
<script>
document.addEventListener("DOMContentLoaded", () => {

    fetch("/jobsweb/applicant/get_profile_completion.php")
        .then(r => r.json())
        .then(data => {
            if (!data.percent) return;

            const p = data.percent;
            updateMeter(p);
        })
        .catch(err => console.error("Meter AJAX error", err));
});

function updateMeter(p) {

    const arc = document.getElementById("meterArc");
    const label = document.getElementById("meterPercent");

    if (!arc || !label) return;

    const r = 40;
    const circ = 2 * Math.PI * r;

    arc.style.strokeDasharray = circ;
    arc.style.strokeDashoffset = circ * (1 - p/100);

    label.textContent = p + "%";

    arc.style.stroke =
        p >= 70 ? "#28a745" :
        p >= 40 ? "#ffbf00" :
                  "#ff3b30";
}
</script>
