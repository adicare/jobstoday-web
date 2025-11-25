<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/config/config.php');

$isLoggedIn = isset($_SESSION['applicant_id']);

$skills = $conn->query("SELECT id, skill_name FROM skills_master ORDER BY skill_name ASC");
$states = $conn->query("SELECT id, state_name FROM state_master ORDER BY state_name ASC");
$jobs   = $conn->query("SELECT id, title, company, location FROM jobs ORDER BY id DESC");

$latestJobRow = $conn->query("SELECT id FROM jobs ORDER BY id DESC LIMIT 1");
$latestJobId  = ($latestJobRow && $latestJobRow->num_rows > 0)
    ? intval($latestJobRow->fetch_assoc()['id'])
    : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>JobsToday</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>

/* GLOBAL */
body {
    margin:0;
    background:#f4f7fc;
    font-family:Arial, sans-serif;
}

.container-fluid {
    width:100% !important;
    max-width:100% !important;
}

/* SEARCH BAR */
.search-bar-full {
    background:white;
    padding:12px;
    border-radius:8px;
    display:flex;
    gap:10px;
    flex-wrap:nowrap;
    box-shadow:0 0 8px rgba(0,0,0,0.08);
    width:100%;
}
.search-bar-full input,
.search-bar-full select {
    padding:8px;
    border:1px solid #c3d4e6;
    border-radius:6px;
    min-width:120px;
}
.btn-search {
    background:#0a4aa1;
    color:white;
    border:none;
    padding:9px 14px;
    border-radius:6px;
    cursor:pointer;
    font-weight:600;
}

/* ===================== SCROLLER (2-BOX OPTION C1) ===================== */

.scroller-wrapper {
    margin:25px 0;
    padding:10px 0;
    width:100%;
}

.scroller-row {
    display:flex;
    justify-content:space-between;
    gap:20px;
}

/* Each box */
.scroller-box {
    width:50%;
    background:white;
    border-radius:12px;
    padding:16px;
    box-shadow:0 0 8px rgba(0,0,0,0.08);
    overflow:hidden;
}

.scroller-title {
    font-size:16px;
    font-weight:700;
    margin-bottom:8px;
}

/* Slides */
.scroller-slides {
    display:flex;
    transition:transform 0.5s ease-in-out;
}
.slide-card {
    min-width:100%;
    background:#f8fbff;
    border-radius:10px;
    padding:10px;
    box-shadow:0 0 6px rgba(0,0,0,0.06);
    cursor:pointer;
}
.slide-card img {
    width:100%;
    border-radius:8px;
    margin-bottom:8px;
}
.slide-title { font-size:15px; font-weight:600; }
.slide-desc  { font-size:13px; color:#555; }

/* Dots */
.dots { text-align:center; margin-top:6px; }
.dot {
    height:8px; width:8px;
    display:inline-block;
    background:#bbb;
    border-radius:50%;
    margin:0 3px;
}
.dot.active { background:#0a4aa1; }

/* GRID LAYOUT */
.grid {
    display:flex;
    gap:16px;
    margin-top:14px;
    width:100%;
}
.col-left  { width:25%; }
.col-mid   { width:50%; }
.col-right { width:25%; }

.left-box {
    background:#dff1ff;
    padding:12px;
    border-radius:10px;
    height:72vh;
    overflow:auto;
}

.job-row {
    padding:10px;
    border-bottom:1px solid #bcd9f3;
    cursor:pointer;
    border-radius:6px;
}
.job-row:hover { background:#c8e7ff; }
.job-active { background:#8ecaff; }

.middle-box {
    background:#edfff3;
    padding:18px;
    border-radius:10px;
    height:72vh;
    overflow:auto;
}

.right-box {
    background:#084a83;
    color:white;
    padding:18px;
    border-radius:10px;
    height:72vh;
    overflow:auto;
}

.profile-block {
    background:white;
    color:black;
    padding:12px;
    border-radius:8px;
    display:flex;
    gap:14px;
    margin-bottom:14px;
}
.profile-block img {
    width:50px;
    height:50px;
    border-radius:50%;
    object-fit:cover;
}

.login-btn {
    background:white;
    color:#084a83;
    padding:10px 12px;
    display:block;
    text-align:center;
    border-radius:6px;
    text-decoration:none;
    font-weight:700;
    margin-top:10px;
}

.small-list {
    background:rgba(255,255,255,0.08);
    padding:8px;
    border-radius:6px;
}
.small-list a {
    color:#cfe8ff;
    text-decoration:none;
    display:block;
    margin:4px 0;
}
hr.soft {
    border:none;
    border-top:1px solid rgba(255,255,255,0.15);
    margin:10px 0;
}
</style>
</head>

<body>

<div class="container-fluid px-4">

<!-- SEARCH BAR -->
<form class="search-bar-full" id="jobSearchForm" onsubmit="event.preventDefault();">
    <input type="text" name="keywords" placeholder="Keywords">
    <select name="job_type"><option value="">Type</option></select>
    <input type="text" name="role" placeholder="Job Role">

    <select name="skill">
        <option value="">Skill</option>
        <?php while($sk = $skills->fetch_assoc()): ?>
            <option value="<?= $sk['id'] ?>"><?= htmlspecialchars($sk['skill_name']) ?></option>
        <?php endwhile; ?>
    </select>

    <select name="state" id="stateSelect">
        <option value="">State</option>
        <?php while($st = $states->fetch_assoc()): ?>
            <option value="<?= $st['id'] ?>"><?= htmlspecialchars($st['state_name']) ?></option>
        <?php endwhile; ?>
    </select>

    <select name="city" id="citySelect"><option value="">City</option></select>

    <select name="experience"><option value="">Exp</option></select>
    <select name="salary"><option value="">Salary</option></select>

    <button type="submit" class="btn-search">Search</button>
</form>


<!-- ===================== 2-SCROLLERS SECTION ===================== -->

<div class="scroller-wrapper">

    <div class="scroller-row">

        <!-- COURSES -->
        <div class="scroller-box">
            <div class="scroller-title">Courses</div>

            <div class="scroller-slides" id="coursesSlides">
                <div class="slide-card">
                    <img src="/jobsweb/assets/sample1.jpg">
                    <div class="slide-title">Course Title 1</div>
                    <div class="slide-desc">Short description...</div>
                </div>

                <div class="slide-card">
                    <img src="/jobsweb/assets/sample2.jpg">
                    <div class="slide-title">Course Title 2</div>
                    <div class="slide-desc">Short description...</div>
                </div>
            </div>

            <div class="dots" id="coursesDots">
                <span class="dot active"></span>
                <span class="dot"></span>
            </div>
        </div>

        <!-- EXPERTS -->
        <div class="scroller-box">
            <div class="scroller-title">Experts</div>

            <div class="scroller-slides" id="expertsSlides">
                <div class="slide-card">
                    <img src="/jobsweb/assets/sample3.jpg">
                    <div class="slide-title">Expert Name 1</div>
                    <div class="slide-desc">Short info...</div>
                </div>

                <div class="slide-card">
                    <img src="/jobsweb/assets/sample4.jpg">
                    <div class="slide-title">Expert Name 2</div>
                    <div class="slide-desc">Short info...</div>
                </div>
            </div>

            <div class="dots" id="expertsDots">
                <span class="dot active"></span>
                <span class="dot"></span>
            </div>
        </div>

    </div>
</div>


<script>
// Auto Slider Function
function autoSlide(slideId, dotId) {
    let index = 0;
    const slides = document.getElementById(slideId);
    const dots   = document.getElementById(dotId).children;

    setInterval(() => {
        index = (index + 1) % 2;
        slides.style.transform = `translateX(-${index * 100}%)`;

        for (let d of dots) d.classList.remove("active");
        dots[index].classList.add("active");

    }, 3000);
}

autoSlide("coursesSlides", "coursesDots");
autoSlide("expertsSlides", "expertsDots");
</script>


<!-- ===================== MAIN 3-COLUMN GRID ===================== -->

<div class="grid">

    <!-- LEFT LIST -->
    <div class="col-left">
        <div class="left-box" id="jobList">
            <?php while($job = $jobs->fetch_assoc()): ?>
                <div class="job-row" data-id="<?= $job['id'] ?>">
                    <strong><?= htmlspecialchars($job['title']) ?></strong><br>
                    <small><?= htmlspecialchars($job['company']) ?> • <?= htmlspecialchars($job['location']) ?></small>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- MIDDLE PREVIEW -->
    <div class="col-mid">
        <div class="middle-box" id="jobPreview">
            <h3>Select a job to see details.</h3>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="col-right">
        <div class="right-box">

<?php if(!$isLoggedIn): ?>

            <h3>Welcome to JobsToday</h3>
            <p>
                Discover opportunities that match your skills.<br>
                Build your career with the right job.<br>
                Connect with top employers hiring now.
            </p>
            <a class="login-btn" href="/jobsweb/public/login.php">LOGIN</a>

<?php else: ?>

            <div class="profile-block">
                <img src="/jobsweb/assets/user-icon.png">
                <div>
                    <strong><?= htmlspecialchars($_SESSION['applicant_name']) ?></strong><br>
                    <small><?= htmlspecialchars($_SESSION['applicant_skill']) ?></small>
                </div>
            </div>

            <a class="login-btn" href="/jobsweb/applicant/profile.php">Update Profile</a>
            <hr class="soft">

            <strong>Recommended Jobs</strong>
            <div class="small-list">
                <?php
                $rec = $conn->query("SELECT id, title FROM jobs ORDER BY id DESC LIMIT 3");
                while($r = $rec->fetch_assoc()):
                ?>
                    <a href="#" class="rec-job" data-id="<?= $r['id'] ?>">
                        <?= htmlspecialchars($r['title']) ?>
                    </a>
                <?php endwhile; ?>
            </div>

            <br>

            <strong>Recommended Courses</strong>
            <div class="small-list">
                <a href="#">Training Name 1</a>
                <a href="#">Training Name 2</a>
            </div>

<?php endif; ?>

        </div>
    </div>

</div>

</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// Auto load latest job
$(document).ready(function() {
    var latestId = <?= $latestJobId ?>;
    if(latestId>0){
        $('.job-row[data-id="'+latestId+'"]').addClass('job-active');
        $.post('/jobsweb/ajax/get-job.php', { id: latestId }, function(data){
            $('#jobPreview').html(data);
        });
    }
});

// Click job to load preview
$(document).on('click', '.job-row', function(){
    var id = $(this).data('id');
    $('.job-row').removeClass('job-active');
    $(this).addClass('job-active');
    $.post('/jobsweb/ajax/get-job.php', { id }, function(data){
        $('#jobPreview').html(data);
    });
});
</script>

<?php include(__DIR__ . '/includes/footer.php'); ?>
</body>
</html>
