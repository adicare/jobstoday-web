<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include(__DIR__ . '/config/config.php');
include(__DIR__ . '/includes/header.php');

$isLoggedIn = isset($_SESSION['applicant_id']);

/* Fetch applicant photo */
$photoPath = "/jobsweb/assets/user-icon.png";
if ($isLoggedIn) {
    $pid = intval($_SESSION['applicant_id']);
    $q = $conn->query("SELECT photo FROM job_seekers WHERE id=$pid LIMIT 1");
    if ($q && $q->num_rows > 0) {
        $row = $q->fetch_assoc();
        if (!empty($row['photo'])) {
            $photoPath = "/jobsweb/uploads/photos/" . $row['photo'];
        }
    }
}

/* Body class */
$bodyClass = $isLoggedIn ? "logged-in" : "";
?>
<body class="<?= $bodyClass ?>">

<!-- SEARCH BAR -->
<?php include(__DIR__ . "/includes/search-bar.php"); ?>

<link rel="stylesheet" href="/jobsweb/assets/css/home.css?v=<?= time() ?>">

<!-- MAIN GRID -->
<div class="page-grid">

    <!-- LEFT PANEL -->
    <div class="left-panel">
        <div class="left-box">

            <?php if (!$isLoggedIn): ?>
                <?php include(__DIR__ . '/includes/welcome-card.php'); ?>
            <?php else: ?>
                <?php include(__DIR__ . '/includes/profile-card.php'); ?>
            <?php endif; ?>

        </div>
    </div>

    <!-- JOB LIST PANEL -->
    <div class="mid-left-panel">
        <div class="middle-left-box">

            <div class="panel-heading">Jobs</div>

            <?php if ($isLoggedIn): ?>
            <div class="left-tabs">
                <button class="tab-btn" onclick="loadLeftList('all')">All</button>
                <button class="tab-btn" onclick="loadLeftList('recommended')">Recommended</button>
                <button class="tab-btn" onclick="loadLeftList('saved')">Saved</button>
                <button class="tab-btn" onclick="loadLeftList('applied')">Applied</button>
            </div>
            <?php endif; ?>

            <div id="jobList">
                <?php include(__DIR__ . '/includes/job-list.php'); ?>
            </div>

        </div>
    </div>

    <!-- JOB PREVIEW PANEL -->
    <div class="mid-right-panel">
        <div class="middle-right-box" id="jobPreview">
            <h3>Select a job to view details</h3>
        </div>
    </div>

    <!-- RIGHT PANEL (AFTER LOGIN) -->
    <?php if ($isLoggedIn): ?>
    <div class="right-panel">
        <div class="right-box">
            <?php include(__DIR__ . '/includes/widgets-panel.php'); ?>
            <?php include(__DIR__ . '/includes/mini-courses.php'); ?>
        </div>
    </div>
    <?php endif; ?>

</div><!-- END GRID -->

<!-- APPLY POPUP -->
<?php include(__DIR__ . '/includes/apply-popup.php'); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
/* SELECT JOB */
$(document).on("click", ".job-row", function() {
    $(".job-row").removeClass("job-active");
    $(this).addClass("job-active");

    let id = $(this).data("id");

    $.post("/jobsweb/ajax/get-job.php", { id: id }, function(data) {
        $("#jobPreview").html(data);
    });
});

/* LOAD JOB LIST */
function loadLeftList(type) {
    $("#jobList").html("<div class='loading'>Loading...</div>");
    $.post("/jobsweb/ajax/get-jobs.php", { type: type }, function(data) {
        $("#jobList").html(data);

        // auto-select first job after list refresh
        let firstJob = $("#jobList .job-row").first().data("id");
        if (firstJob) {
            $.post("/jobsweb/ajax/get-job.php", { id: firstJob }, function(html) {
                $("#jobPreview").html(html);
            });
        }
    });
}

/* AUTO-LOAD LATEST JOB (BEFORE + AFTER LOGIN) */
$(document).ready(function() {

    $.post("/jobsweb/ajax/get-jobs.php", { type: "all" }, function(list) {
        $("#jobList").html(list);

        let firstJob = $("#jobList .job-row").first().data("id");

        if (firstJob) {
            $.post("/jobsweb/ajax/get-job.php", { id: firstJob }, function(html) {
                $("#jobPreview").html(html);
            });
        }
    });

});
</script>

<?php include(__DIR__ . "/includes/footer.php"); ?>
</body>
</html>
