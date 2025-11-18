<?php
/* ===============================================================
   FILE: index.php
   PURPOSE:
     - Main job search page
     - Always auto-select latest job (Option A)
     - Left panel always shows full job list (L1)
     - Middle panel loads job preview via AJAX
     - Apply button system with:
         ✔ Blue Apply (before applying)
         ✔ Green APPLIED ✔ (after applying)
         ✔ Login detection with toast + redirect
   =============================================================== */

// HEADER INCLUDE
include(__DIR__ . '/includes/header.php');

// DB CONNECTION
include(__DIR__ . '/config/config.php');


/* ===============================================================
   FETCH MASTER DATA
   =============================================================== */

// Skills list
$skills = $conn->query("SELECT id, skill_name FROM skills_master ORDER BY skill_name ASC");

// States list
$states = $conn->query("SELECT id, state_name FROM state_master ORDER BY state_name ASC");

// Full job list (left panel)
$jobs = $conn->query("SELECT id, title, company, location FROM jobs ORDER BY id DESC");

// Latest job ID for auto-selection (Option A)
$latestJobRow = $conn->query("SELECT id FROM jobs ORDER BY id DESC LIMIT 1");
$latestJobId = ($latestJobRow && $latestJobRow->num_rows > 0)
    ? intval($latestJobRow->fetch_assoc()['id'])
    : 0;
?>


<!-- ===============================================================
     PAGE STYLES
     =============================================================== -->
<style>
.container { max-width: 98% !important; }

/* Search bar */
.search-bar-full {
    margin-top: 8px !important;
    background: #ffffff;
    padding: 12px;
    border-radius: 12px;
    box-shadow: 0 0 8px rgba(0,0,0,0.08);
}

/* Left panel */
.left-box {
    background: #dff3ff;
    border-radius: 10px;
    padding: 15px;
    height: 82vh;
    overflow-y: auto;
}
.left-box::-webkit-scrollbar { width: 7px; }
.left-box::-webkit-scrollbar-thumb { background: #78a8d8; border-radius: 10px; }

/* Job row */
.job-row {
    padding: 8px;
    border-bottom: 1px solid #b7d4ed;
    cursor: pointer;
    border-radius: 5px;
}
.job-row:hover { background: #cde9ff; }
.job-active { background: #9cd1ff !important; }

/* Middle panel */
.middle-box {
    background: #eaffed;
    border-radius: 10px;
    padding: 18px;
    height: 82vh;
    overflow-y: auto;
}

/* Right panel */
.right-box {
    background: #084a83;
    color: white;
    border-radius: 12px;
    padding: 25px;
    height: 82vh;
}
.right-box h3 { font-size: 1.8rem; font-weight: 700; }
.right-box .welcome-text {
    font-size: 1.1rem;
    line-height: 1.5rem;
    margin-top: 12px;
    margin-bottom: 25px;
}

/* Login button */
.login-btn {
    margin-top: 10px;
    background: #ffffff;
    color: #084a83;
    padding: 14px;
    border-radius: 8px;
    display: block;
    text-align: center;
    font-weight: bold;
    text-decoration: none;
    font-size: 1.1rem;
}
</style>


<!-- ===============================================================
     PAGE CONTENT
     =============================================================== -->
<div class="container mt-2">

    <!-- SEARCH BAR (L1: does NOT change left list) -->
    <form id="jobSearchForm" class="search-bar-full row g-2 align-items-center" action="#" method="get">

        <div class="col-md-2">
            <input type="text" name="keywords" class="form-control" placeholder="Keywords">
        </div>

        <div class="col-md-1">
            <select name="job_type" class="form-select">
                <option value="">Type</option>
                <option value="office">Office</option>
                <option value="wfh">WFH</option>
                <option value="hybrid">Hybrid</option>
            </select>
        </div>

        <div class="col-md-2">
            <input type="text" name="role" class="form-control" placeholder="Job Role">
        </div>

        <div class="col-md-2">
            <select name="skill" class="form-select">
                <option value="">Skill</option>
                <?php if ($skills && $skills->num_rows > 0): ?>
                    <?php while ($sk = $skills->fetch_assoc()): ?>
                        <option value="<?= intval($sk['id']) ?>"><?= htmlspecialchars($sk['skill_name']) ?></option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="col-md-1">
            <select name="state" id="stateSelect" class="form-select">
                <option value="">State</option>
                <?php if ($states && $states->num_rows > 0): ?>
                    <?php while ($st = $states->fetch_assoc()): ?>
                        <option value="<?= intval($st['id']) ?>"><?= htmlspecialchars($st['state_name']) ?></option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="col-md-1">
            <select name="city" id="citySelect" class="form-select">
                <option value="">City</option>
            </select>
        </div>

        <div class="col-md-1">
            <select name="experience" class="form-select">
                <option value="">Exp</option>
                <option value="0">0</option>
                <option value="1">1</option>
                <option value="2">2–3</option>
                <option value="5">5+</option>
                <option value="10">10+</option>
            </select>
        </div>

        <div class="col-md-1">
            <select name="salary" class="form-select">
                <option value="">Salary</option>
                <option value="5">&lt;5L</option>
                <option value="10">5–10L</option>
                <option value="15">10–15L</option>
                <option value="20">15–20L</option>
                <option value="25">20L+</option>
            </select>
        </div>

        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>

    </form>


    <!-- MAIN 3 COLUMN LAYOUT -->
    <div class="row g-3 mt-2">

        <!-- LEFT JOB LIST -->
        <div class="col-md-3">
            <div class="left-box" id="jobList">

                <?php if ($jobs && $jobs->num_rows > 0): ?>
                    <?php while ($job = $jobs->fetch_assoc()): ?>
                        <div class="job-row" data-id="<?= intval($job['id']) ?>">
                            <div class="fw-bold"><?= htmlspecialchars($job['title']) ?></div>
                            <small><?= htmlspecialchars($job['company']) ?> • <?= htmlspecialchars($job['location']) ?></small>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No jobs posted.</p>
                <?php endif; ?>

            </div>
        </div>

        <!-- MIDDLE JOB PREVIEW -->
        <div class="col-md-5">
            <div class="middle-box" id="jobPreview">
                <h4>Select a job to see its description.</h4>
            </div>
        </div>

        <!-- RIGHT WELCOME PANEL -->
        <div class="col-md-4">
            <div class="right-box">
                <h3>Welcome to JobsToday</h3>
                <div class="welcome-text">
                    Discover opportunities that match your skills.<br>
                    Build your career with the right job today.<br>
                    Connect with top employers hiring now.
                </div>
                <a href="/jobsweb/public/login.php" class="login-btn">LOGIN</a>
            </div>
        </div>

    </div><!-- row -->
</div><!-- container -->


<?php include(__DIR__ . '/includes/footer.php'); ?>


<!-- ===============================================================
     TOAST CONTAINER
     =============================================================== -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
    <div id="toastMessage" class="toast text-white bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div id="toastText" class="toast-body">Message</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto"
                data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>


<!-- ===============================================================
     JAVASCRIPT SECTION
     =============================================================== -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
/* ---------------------------------------------------------------
   TOAST FUNCTION
   --------------------------------------------------------------- */
function showToast(message, color = "bg-primary") {
    var box = $("#toastMessage");
    box.removeClass("bg-primary bg-success bg-danger bg-warning");
    box.addClass(color);
    $("#toastText").html(message);

    if (typeof bootstrap !== "undefined") {
        new bootstrap.Toast(box[0]).show();
    } else {
        alert(message);
    }
}


/* ---------------------------------------------------------------
   L1: SEARCH FORM DOES NOT FILTER LEFT LIST
   --------------------------------------------------------------- */
$("#jobSearchForm").on("submit", function(e) {
    e.preventDefault();
    showToast("Search received (L1 mode). Left job list unchanged.", "bg-primary");
});


/* ---------------------------------------------------------------
   AUTO LOAD LATEST JOB (OPTION A)
   --------------------------------------------------------------- */
$(document).ready(function() {
    var latestId = <?= json_encode($latestJobId); ?>;

    if (latestId > 0) {
        $('.job-row[data-id="' + latestId + '"]').addClass("job-active");

        $.post("/jobsweb/ajax/get-job.php",
            { id: latestId },
            function(data) {
                $("#jobPreview").html(data);
            },
            "html"
        );
    }
});


/* ---------------------------------------------------------------
   CLICK TO LOAD ANY JOB
   --------------------------------------------------------------- */
$(document).on("click", ".job-row", function() {
    var id = $(this).data("id");

    $(".job-row").removeClass("job-active");
    $(this).addClass("job-active");

    $.post("/jobsweb/ajax/get-job.php",
        { id: id },
        function(data) {
            $("#jobPreview").html(data);
        },
        "html"
    );
});


/* ---------------------------------------------------------------
   APPLY BUTTON: FINAL VERSION
   --------------------------------------------------------------- */
$(document).on("click", ".applyJobBtn", function(e) {
    e.preventDefault();

    var $btn = $(this);
    var jobId = $btn.data("jobid");

    $btn.prop("disabled", true).text("Applying...");

    $.post("/jobsweb/ajax/apply-job.php",
        { job_id: jobId },
        function(response) {

            response = (response || "").trim().toLowerCase();

            if (response === "success") {

                showToast("Application submitted successfully.", "bg-success");

                $btn
                    .removeClass("btn-primary")
                    .css({ "background": "#28a745", "color": "#fff", "border": "none" })
                    .text("APPLIED ✔")
                    .prop("disabled", true);

            } else if (response === "already_applied") {

                showToast("You have already applied for this job.", "bg-warning");

                $btn
                    .removeClass("btn-primary")
                    .css({ "background": "#28a745", "color": "#fff", "border": "none" })
                    .text("APPLIED ✔")
                    .prop("disabled", true);

            } else if (response === "login_required") {

                showToast("Please login to apply. Redirecting...", "bg-danger");

                setTimeout(function() {
                    window.location.href = "/jobsweb/public/login.php";
                }, 900);

                $btn.prop("disabled", false).text("Apply");

            } else {

                showToast("Error applying. Please try again.", "bg-danger");
                $btn.prop("disabled", false).text("Apply");
            }
        }
    ).fail(function() {
        showToast("Server error. Try again.", "bg-danger");
        $btn.prop("disabled", false).text("Apply");
    });
});
</script>

