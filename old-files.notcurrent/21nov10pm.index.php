<?php
// FINAL — index.php (Fully Commented Developer Edition)
// Layout: Option A (scrollers under Left + Middle only)
// Left/Middle height: 68vh, Slider thumb height: ~160px, Gap above slider: 12px
// Right panel spans both rows and has its own scrollbar (independent)

// start session if not started
if (session_status() === PHP_SESSION_NONE) session_start();

// header includes top nav and opens the container wrapper (you confirmed this)
include(__DIR__ . '/includes/header.php');

// db connection
include(__DIR__ . '/config/config.php');

// detect applicant login (session name you use)
$isLoggedIn = isset($_SESSION['applicant_id']);

// fetch dropdowns
$skills = $conn->query("SELECT id, skill_name FROM skills_master ORDER BY skill_name ASC");
$states = $conn->query("SELECT id, state_name FROM state_master ORDER BY state_name ASC");

// fetch jobs for left panel
$jobs = $conn->query("SELECT id, title, company, location FROM jobs ORDER BY id DESC");

// latest job id for auto-load preview
$latestJobRow = $conn->query("SELECT id FROM jobs ORDER BY id DESC LIMIT 1");
$latestJobId  = ($latestJobRow && $latestJobRow->num_rows > 0)
    ? intval($latestJobRow->fetch_assoc()['id'])
    : 0;

/*
  slider data queries — using your existing tables and columns.
  trainer_courses: course_title, short_desc, image_path
  trainer_profiles: full_name, expertise_area, profile_photo
  Adjust column names here if your DB differs.
*/
$coursesRes = $conn->query("
  SELECT id, course_title AS title, short_desc AS description, image_path
  FROM trainer_courses
  ORDER BY id DESC LIMIT 12
");

$expertsRes = $conn->query("
  SELECT id, full_name AS name, expertise_area AS expertise, profile_photo AS image
  FROM trainer_profiles
  ORDER BY id DESC LIMIT 12
");
?>

<!--
  IMPORTANT:
  header.php already opened <div class="container container-page"> (you confirmed)
  So DO NOT open another container here. We'll output content directly inside that container.
-->

<!-- Inline CSS specific to this page -->
<style>
/* ---------------- Root tokens ---------------- */
:root{
  --primary-blue: #0a4aa1;
  --dark-blue: #084a83;
  --left-bg: #dff1ff;
  --mid-bg: #edfff3;
  --card-bg: #f8fbff;
  --gap-slider: 12px;     /* small premium gap above sliders */
  --leftmid-height: 80vh; /* final height for left and middle panels */
  --slider-thumb-h: 160px;/* slider thumbnail height (medium-large) */
}

/* Reset / base */
body { font-family: Arial, Helvetica, sans-serif; color: #173247; background:#f4f7fc; margin:0; }

/* Search bar (single row) */
.search-bar-full {
  background:#fff; padding:12px; border-radius:8px; display:flex; gap:10px; align-items:center;
  box-shadow:0 0 8px rgba(0,0,0,0.06); margin-top:12px; flex-wrap:nowrap;
}
.search-bar-full input, .search-bar-full select { padding:8px; border-radius:6px; border:1px solid #d3dfef; min-width:110px; }
.search-bar-full input[name="keywords"] { flex:1 1 260px; min-width:180px; }
.btn-search { background:var(--primary-blue); color:#fff; border:none; padding:9px 14px; border-radius:6px; font-weight:700; cursor:pointer; }

/* ---------------- Page grid (2 rows x 3 columns) ----------------
   We use CSS Grid so the RIGHT panel can span both rows (top content + sliders)
   grid-template-columns: left 25% | mid 50% | right 25%
   grid-template-rows: top-row (left+mid heights) | slider row (fixed ~ slider height + gap)
   This ensures pixel-perfect vertical alignment between scrollers and right column.
*/
.page-grid {
  display: grid;
  grid-template-columns: 25% 50% 25%;
  /* top row is the left/mid height; second row is slider height + gap */
  grid-template-rows: var(--leftmid-height) calc(var(--slider-thumb-h) + var(--gap-slider) + 40px);
  gap: 16px;
  margin-top: 14px;
}

/* LEFT panel (grid column 1, row 1) */
.left-panel {
  grid-column: 1 / 2;
  grid-row: 1 / 2;
}
.left-box {
  background: var(--left-bg);

  padding:12px;
  border-radius:10px;
  height:100%;         /* fill the grid row height (var(--leftmid-height)) */
  overflow:auto;       /* independent scrollbar for left panel */
}

/* MIDDLE panel (grid column 2, row 1) */
.mid-panel {
  grid-column: 2 / 3;
  grid-row: 1 / 2;
}
.middle-box {
  background: var(--mid-bg);
  padding:16px;
  border-radius:10px;
  height:100%;      /* fill the grid row */
  overflow:auto;
}

/* RIGHT panel spans both rows (grid column 3, rows 1..3) */
.right-panel {
  grid-column: 3 / 4;
  grid-row: 1 / 3;   /* span both rows */
}
.right-box {
  background: var(--dark-blue);
  color: #fff;
  padding:18px;
  border-radius:10px;
  height:100%;        /* FIXED */
  overflow: auto;
  box-sizing: border-box;
}


/* Job rows */
.job-row {
  padding:10px; border-radius:6px; background:transparent; margin-bottom:8px;
  border-bottom:1px solid #cfe6f6; cursor:pointer;
}
.job-row:hover { background:#cfeeff; }
.job-active { background:#8ecaff !important; }

/* ---------------- SCROLLERS SECTION ----------------
   The scrollers container is placed in the second grid row and spans columns 1..2 (left + mid)
*/
.scrollers-wrapper {
  grid-column: 1 / 3;   /* span left + mid */
  grid-row: 2 / 3;
  display:flex; gap:16px;
  align-items:flex-start; /* top align sliders within their row */
  padding-top: var(--gap-slider); /* small gap above sliders */
  box-sizing: border-box;
  /* width is implicitly left+mid (75%) due to grid placement */
}
.scroller-box {
  width: 50%;
  background: #fff; border-radius:10px; padding:12px; box-shadow:0 0 8px rgba(0,0,0,0.06);
}
.scroller-title { font-weight:700; margin-bottom:8px; font-size:15px; color:#173247; }

/* Swiper styles */
.swiper { width:100%; }
.swiper-slide {
  background: var(--card-bg); border-radius:8px; padding:10px; box-sizing:border-box;
  display:flex; flex-direction:column; gap:8px; cursor:pointer; height:auto;
}
.slide-thumb { width:100%; height: var(--slider-thumb-h); object-fit:cover; border-radius:6px; display:block; }
.slide-title { font-size:14px; font-weight:700; color:#0e3a4a; margin-top:6px; }
.slide-desc  { font-size:13px; color:#345; line-height:1.2; margin-top:4px; }

/* Swiper bullets */
.swiper-pagination-bullets { margin-top:6px; }
.swiper-pagination-bullet { background:#c4d6ef; opacity:1; }
.swiper-pagination-bullet-active { background:var(--primary-blue); }

/* Right panel small elements */
.profile-block { background:#fff; color:#222; padding:12px; border-radius:8px; display:flex; gap:12px; align-items:center; margin-bottom:14px; }
.profile-block img { width:56px; height:56px; border-radius:50%; object-fit:cover; }
.login-btn { background:#fff; color:var(--dark-blue); padding:10px 12px; border-radius:6px; text-align:center; font-weight:700; display:block; text-decoration:none; }

/* Responsive rules */
@media (max-width: 1000px) {
  .page-grid { grid-template-columns: 1fr; grid-template-rows: auto auto; }
  .left-panel, .mid-panel, .right-panel { grid-column: 1 / 2; grid-row: auto; }
  .scrollers-wrapper { width:100%; flex-direction:column; }
  .scroller-box { width:100%; }
  .left-box, .middle-box { height: 50vh; } /* reduce height on small screens */
  .right-box { height: auto; }
}
@media (max-width: 680px) {
  :root { --slider-thumb-h: 140px; }
}
</style>

<!-- ================== PAGE CONTENT ================== -->
<!-- search bar — inside header's container -->
<form id="jobSearchForm" class="search-bar-full" onsubmit="event.preventDefault();">
  <input type="text" name="keywords" placeholder="Keywords">
  <select name="job_type"><option value="">Type</option></select>
  <input type="text" name="role" placeholder="Job Role">

  <select name="skill">
    <option value="">Skill</option>
    <?php if ($skills && $skills->num_rows > 0): while ($sk = $skills->fetch_assoc()): ?>
      <option value="<?= intval($sk['id']) ?>"><?= htmlspecialchars($sk['skill_name']) ?></option>
    <?php endwhile; endif; ?>
  </select>

  <select name="state" id="stateSelect">
    <option value="">State</option>
    <?php if ($states && $states->num_rows > 0): while ($st = $states->fetch_assoc()): ?>
      <option value="<?= intval($st['id']) ?>"><?= htmlspecialchars($st['state_name']) ?></option>
    <?php endwhile; endif; ?>
  </select>

  <select name="city" id="citySelect"><option value="">City</option></select>
  <select name="experience"><option value="">Exp</option></select>
  <select name="salary"><option value="">Salary</option></select>

  <button type="submit" class="btn-search">Search</button>
</form>

<!-- main grid: left | mid | right  (right spans two rows; scrollers sit in 2nd row col 1-2) -->
<div class="page-grid">

  <!-- LEFT panel -->
  <div class="left-panel">
    <div class="left-box" id="jobList">
      <?php if ($jobs && $jobs->num_rows > 0): while ($job = $jobs->fetch_assoc()): ?>
        <div class="job-row" data-id="<?= intval($job['id']) ?>">
          <strong><?= htmlspecialchars($job['title']) ?></strong>
          <small><?= htmlspecialchars($job['company']) ?> • <?= htmlspecialchars($job['location']) ?></small>
        </div>
      <?php endwhile; else: ?>
        <div>No jobs posted.</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- MIDDLE panel -->
  <div class="mid-panel">
    <div class="middle-box" id="jobPreview">
      <h3>Select a job to see details.</h3>
      <!-- AJAX job previews will load here -->
    </div>
  </div>

  <!-- RIGHT panel (spans both rows) -->
  <div class="right-panel">
    <div class="right-box">
      <?php if (!$isLoggedIn): ?>
        <h3>Welcome to JobsToday</h3>
        <p style="color:#d3eaff;">
          Discover opportunities that match your skills.<br>
          Build your career with the right job.<br>
          Connect with top employers hiring now.
        </p>
        <a class="login-btn" href="/jobsweb/public/login.php">LOGIN</a>
      <?php else: ?>
        <div class="profile-block">
          <img src="/jobsweb/assets/user-icon.png" alt="user">
          <div>
            <strong><?= htmlspecialchars($_SESSION['applicant_name'] ?? 'Applicant') ?></strong><br>
            <small style="color:#456"><?= htmlspecialchars($_SESSION['applicant_skill'] ?? '') ?></small>
          </div>
        </div>

        <a class="login-btn" href="/jobsweb/applicant/profile.php">Update Profile</a>

        <hr style="border:none;border-top:1px solid rgba(255,255,255,0.12);margin:12px 0;">

        <strong>Recommended Jobs</strong>
        <div class="small-list" style="margin-top:8px;">
          <?php
            $rec = $conn->query("SELECT id, title FROM jobs ORDER BY id DESC LIMIT 3");
            if ($rec && $rec->num_rows>0):
              while($r = $rec->fetch_assoc()):
          ?>
            <a href="#" class="rec-job" data-id="<?= intval($r['id']) ?>"><?= htmlspecialchars($r['title']) ?></a>
          <?php
              endwhile;
            else:
          ?>
            <div style="color:#dfeeff">No recommendations yet.</div>
          <?php endif; ?>
        </div>

        <br>
        <strong>Recommended Courses</strong>
        <div class="small-list" style="margin-top:8px;">
          <a href="#">Training Name 1</a>
          <a href="#">Training Name 2</a>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- SCROLLERS (placed in the second grid row spanning left+mid) -->
  <div class="scrollers-wrapper" aria-hidden="false">
    <!-- COURSES scroller -->
    <div class="scroller-box" aria-label="Courses scroller">
      <div class="scroller-title">Courses</div>

      <div class="swiper courses-swiper">
        <div class="swiper-wrapper">
          <?php
            if ($coursesRes && $coursesRes->num_rows > 0):
              while($c = $coursesRes->fetch_assoc()):
                $thumb = !empty($c['image_path']) ? $c['image_path'] : '/jobsweb/assets/sample1.jpg';
                $title = $c['title'] ?? 'Untitled Course';
                $desc  = $c['description'] ?? '';
          ?>
            <div class="swiper-slide" data-id="<?= intval($c['id']) ?>">
              <img class="slide-thumb" src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($title) ?>">
              <div class="slide-title"><?= htmlspecialchars($title) ?></div>
              <div class="slide-desc"><?= htmlspecialchars(mb_substr($desc,0,140)) ?></div>
            </div>
          <?php
              endwhile;
            else:
          ?>
            <div class="swiper-slide">
              <img class="slide-thumb" src="/jobsweb/assets/sample1.jpg" alt="Sample">
              <div class="slide-title">Sample Course A</div>
              <div class="slide-desc">This is a sample course placeholder. Providers can add their single-slide here.</div>
            </div>
            <div class="swiper-slide">
              <img class="slide-thumb" src="/jobsweb/assets/sample2.jpg" alt="Sample">
              <div class="slide-title">Sample Course B</div>
              <div class="slide-desc">Add course short description here.</div>
            </div>
          <?php endif; ?>
        </div>
        <div class="swiper-pagination"></div>
      </div>
    </div>

    <!-- EXPERTS scroller -->
    <div class="scroller-box" aria-label="Experts scroller">
      <div class="scroller-title">Experts</div>

      <div class="swiper experts-swiper">
        <div class="swiper-wrapper">
          <?php
            if ($expertsRes && $expertsRes->num_rows > 0):
              while($e = $expertsRes->fetch_assoc()):
                $avatar = !empty($e['image']) ? $e['image'] : '/jobsweb/assets/sample3.jpg';
                $ename  = $e['name'] ?? 'Unnamed';
                $etag   = $e['expertise'] ?? '';
          ?>
            <div class="swiper-slide" data-id="<?= intval($e['id']) ?>">
              <img class="slide-thumb" src="<?= htmlspecialchars($avatar) ?>" alt="<?= htmlspecialchars($ename) ?>">
              <div class="slide-title"><?= htmlspecialchars($ename) ?></div>
              <div class="slide-desc"><?= htmlspecialchars(mb_substr($etag,0,140)) ?></div>
            </div>
          <?php
              endwhile;
            else:
          ?>
            <div class="swiper-slide">
              <img class="slide-thumb" src="/jobsweb/assets/sample3.jpg" alt="Expert">
              <div class="slide-title">Sample Expert A</div>
              <div class="slide-desc">Expert placeholder. Each expert can create/update their single slide.</div>
            </div>
            <div class="swiper-slide">
              <img class="slide-thumb" src="/jobsweb/assets/sample4.jpg" alt="Expert">
              <div class="slide-title">Sample Expert B</div>
              <div class="slide-desc">Expert placeholder 2.</div>
            </div>
          <?php endif; ?>
        </div>
        <div class="swiper-pagination"></div>
      </div>
    </div>
  </div> <!-- /.scrollers-wrapper -->

</div> <!-- /.page-grid -->

<!-- SCRIPTS: Swiper + jQuery -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
/* Swiper config:
   - loop: true
   - autoplay: 3000ms
   - breakpoints: 1 | 2 | 3 slides per view
   - pagination bullets enabled
*/
function initSwipers() {
  var cfg = {
    loop: true,
    speed: 700,
    autoplay: { delay: 3000, disableOnInteraction: false },
    pagination: { el: '.swiper-pagination', clickable: true },
    breakpoints: {
      0: { slidesPerView: 1, spaceBetween: 12 },
      680: { slidesPerView: 2, spaceBetween: 12 },
      1024: { slidesPerView: 3, spaceBetween: 16 }
    }
  };

  // instantiate swipers (distinct containers)
  new Swiper('.courses-swiper', cfg);
  new Swiper('.experts-swiper', cfg);
}

document.addEventListener('DOMContentLoaded', function() {
  initSwipers();

  // Auto-load latest job into preview (if any)
  var latestId = <?= json_encode($latestJobId); ?>;
  if (latestId && latestId > 0) {
    var $r = $('.job-row[data-id="'+latestId+'"]');
    if ($r.length) $r.addClass('job-active');
    $.post('/jobsweb/ajax/get-job.php', { id: latestId }, function(data){
      $('#jobPreview').html(data);
    }, 'html');
  }

  // job row click: load preview via AJAX
  $(document).on('click', '.job-row', function() {
    var id = $(this).data('id');
    $('.job-row').removeClass('job-active');
    $(this).addClass('job-active');
    $.post('/jobsweb/ajax/get-job.php', { id: id }, function(data){
      $('#jobPreview').html(data);
    }, 'html');
  });

  // slide click handlers for course/expert details (optional detail pages)
  $(document).on('click', '.courses-swiper .swiper-slide', function(){
    var id = $(this).data('id'); if (id) window.location.href = '/jobsweb/public/course.php?id=' + id;
  });
  $(document).on('click', '.experts-swiper .swiper-slide', function(){
    var id = $(this).data('id'); if (id) window.location.href = '/jobsweb/public/expert.php?id=' + id;
  });

  // recommended job click: highlight and open preview
  $(document).on('click', '.rec-job', function(e){
    e.preventDefault();
    var id = $(this).data('id'); if (!id) return;
    var $leftRow = $('.job-row[data-id="'+id+'"]');
    if ($leftRow.length) {
      $leftRow.trigger('click');
      $('#jobList').animate({ scrollTop: $leftRow.position().top + $('#jobList').scrollTop() - 40 }, 350);
    } else {
      $.post('/jobsweb/ajax/get-job.php', { id: id }, function(data){
        $('#jobPreview').html(data);
      }, 'html');
    }
  });
});
</script>

<?php
// include footer (this should close the container opened in header.php)
include(__DIR__ . '/includes/footer.php');
?>
