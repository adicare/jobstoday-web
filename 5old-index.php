<?php
/* ===============================================================
   FILE: index.php
   PURPOSE: Main job search page with:
            - Always auto-select the latest job (Option A)
            - Left panel shows full job list (L1)
            - Job preview (middle) always shows latest job initially
            - Apply button + AJAX + toast notifications
   =============================================================== */

// HEADER INCLUDE (assumed to load Bootstrap CSS & JS)
include(__DIR__ . '/includes/header.php');

// DB Connection
include(__DIR__ . '/config/config.php');


/* ===============================================================
   FETCH MASTER DATA FOR SEARCH BAR and latest job id
   =============================================================== */

// Fetch Skills
$skills = $conn->query("SELECT id, skill_name FROM skills_master ORDER BY skill_name ASC");

// Fetch States
$states = $conn->query("SELECT id, state_name FROM state_master ORDER BY state_name ASC");

// Fetch jobs for left panel (full list, latest first)
$jobs = $conn->query("
    SELECT id, title, company, location
    FROM jobs
    ORDER BY id DESC
");

// Get the latest job id (Option A default)
$latestJobRow = $conn->query("SELECT id FROM jobs ORDER BY id DESC LIMIT 1");
$latestJobId = ($latestJobRow && $latestJobRow->num_rows > 0) ? intval($latestJobRow->fetch_assoc()['id']) : 0;
?>

<!-- ===============================================================
     PAGE STYLES — Layout, Search Bar, Panels
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

/* Left box */
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

/* Middle preview */
.middle-box {
    background: #eaffed;
    border-radius: 10px;
    padding: 18px;
    height: 82vh;
    overflow-y: auto;
}

/* Right box */
.right-box {
    background: #084a83;
    color: white;
    border-radius: 12px;
    padding: 25px;
    height: 82vh;
}
.right-box h3 { font-size: 1.8rem; font-weight: 700; }
.right-box .welcome-text { font-size: 1.1rem; line-height: 1.5rem; margin-top: 12px; margin-bottom: 25px; }

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

    <!-- SEARCH BAR (L1: this search will NOT change left list; we prevent default submit) -->
    <form id="jobSearchForm" class="search-bar-full row g-2 align-items-center" action="#" method="get">
        <div class="col-md-2">
            <input type="text" name="keywords" class="form-control" placeholder="Keywords (title, skill)">
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
            <input type="text" name="role" class="form-control" placeholder="Job Role (e.g., Accountant)">
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
                <option value="5">&lt; 5L</option>
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

    <!-- 3-column layout -->
    <div class="row g-3 mt-2">

        <!-- LEFT: FULL JOB LIST (unfiltered, latest-first) -->
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

            <div class="text-center mt-2">
                <button class="btn btn-sm btn-primary" id="prevBtn">Previous</button>
                <button class="btn btn-sm btn-primary" id="nextBtn">Next</button>
            </div>
        </div>

        <!-- MIDDLE: JOB PREVIEW (will auto-load latest job always) -->
        <div class="col-md-5">
            <div class="middle-box" id="jobPreview">
                <h4>Select a job to see its description.</h4>
                <p>Click any job from the left list to view full details here.</p>
            </div>
        </div>

        <!-- RIGHT: WELCOME -->
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

    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php include(__DIR__ . '/includes/footer.php'); ?>


<!-- ===============================================================
     TOAST CONTAINER (PART A)
     =============================================================== -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
    <div id="toastMessage" class="toast align-items-center text-white bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div id="toastText" class="toast-body">Sample Message</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>


<!-- ===============================================================
     JAVASCRIPT — jQuery + Toasts + Apply handler + Default-load latest job
     =============================================================== -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
/* PART B: Toast function */
function showToast(message, color = "bg-primary") {
    var toastEl = $("#toastMessage");
    toastEl.removeClass("bg-primary bg-success bg-danger bg-warning");
    toastEl.addClass(color);
    $("#toastText").html(message);

    var toastElement = document.getElementById('toastMessage');
    if (toastElement && typeof bootstrap !== 'undefined' && bootstrap.Toast) {
        var bsToast = new bootstrap.Toast(toastElement);
        bsToast.show();
    } else {
        // fallback
        alert(message);
    }
}

/* Prevent the search form from reloading the page (L1 behavior) */
$("#jobSearchForm").on("submit", function(e) {
    e.preventDefault();
    // Optionally, you can perform AJAX search here — currently per L1 we keep left list unchanged.
    showToast("Search submitted (L1 behaviour): left list remains full. Results do not replace the left panel.", "bg-primary");
});

/* AUTO-LOAD: always load the latest job (Option A) */
$(document).ready(function() {
    var latestId = <?= json_encode($latestJobId); ?>;

    if (latestId && latestId > 0) {
        // highlight in left list (if present)
        $('.job-row').removeClass('job-active');
        $('.job-row[data-id="' + latestId + '"]').addClass('job-active');

        // load description via AJAX
        $.post("/jobsweb/ajax/get-job.php", { id: latestId }, function(data) {
            $("#jobPreview").html(data);
        }, "html");
    } else {
        // no jobs in DB
        $("#jobPreview").html("<p>No jobs available.</p>");
    }
});

/* CLICK TO LOAD JOB DETAILS (user can still click any job) */
$(document).on("click", ".job-row", function() {
    var jobId = $(this).data("id");
    $(".job-row").removeClass("job-active");
    $(this).addClass("job-active");

    $.post("/jobsweb/ajax/get-job.php", { id: jobId }, function(data) {
        $("#jobPreview").html(data);
    }, "html");
});

/* PAGINATION (uses existing ajax/get-jobs.php — left list replaced on page change) */
var page = 1;
$("#nextBtn").click(function() { page++; loadJobs(); });
$("#prevBtn").click(function() { if (page > 1) { page--; loadJobs(); } });

function loadJobs() {
    $.post("/jobsweb/ajax/get-jobs.php", { page: page }, function(data) {
        $("#jobList").html(data);
        // after loadJobs, re-highlight latest job if present (Option A)
        var latestId = <?= json_encode($latestJobId); ?>;
        $('.job-row').removeClass('job-active');
        $('.job-row[data-id="' + latestId + '"]').addClass('job-active');
    }, "html");
}

/* PART C: Apply button handler (uses toasts) */
$(document).on("click", ".applyJobBtn", function() {
    var $btn = $(this);
    var jobId = $btn.data("jobid");

    $.post("/jobsweb/ajax/apply-job.php", { job_id: jobId }, function(response) {
        if (response && response.toLowerCase().indexOf("successfully") !== -1) {
            showToast(response, "bg-success");
            // Optionally change button to Applied and disable
            $btn.prop("disabled", true).text("Applied ✓").removeClass("btn-success").addClass("btn-secondary");
        } else if (response && response.toLowerCase().indexOf("already") !== -1) {
            showToast(response, "bg-warning");
            $btn.prop("disabled", true).text("Applied ✓").removeClass("btn-success").addClass("btn-secondary");
        } else if (response && response.toLowerCase().indexOf("login") !== -1) {
            showToast(response, "bg-danger");
            // redirect to login after short delay
            setTimeout(function() {
                window.location.href = "/jobsweb/public/login.php";
            }, 900);
        } else {
            showToast(response, "bg-danger");
        }
    }, "text");
});
</script>
