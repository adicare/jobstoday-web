<?php
// FINAL OPTIMIZED index.php (F1)
// - Option B layout (Left / Mid / Right columns)
// - Two scrollers (Courses + Experts) placed below Left+Mid (occupying 75% width)
// - SwiperJS sliders (3 / 2 / 1 responsive)
// - Reduced heights for left & middle to accommodate scrollers
// - Fully commented for easy understanding
// - Make sure includes/header.php and includes/footer.php exist and work correctly

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include(__DIR__ . '/includes/header.php');   // Global header (nav, top bar)
include(__DIR__ . '/config/config.php');     // DB connection

// Detect applicant login (Option A session names)
$isLoggedIn = isset($_SESSION['applicant_id']);

// Fetch master lists
$skills = $conn->query("SELECT id, skill_name FROM skills_master ORDER BY skill_name ASC");
$states = $conn->query("SELECT id, state_name FROM state_master ORDER BY state_name ASC");

// Fetch jobs for left panel
$jobs   = $conn->query("SELECT id, title, company, location FROM jobs ORDER BY id DESC");

// Latest job id for auto-load
$latestJobRow = $conn->query("SELECT id FROM jobs ORDER BY id DESC LIMIT 1");
$latestJobId  = ($latestJobRow && $latestJobRow->num_rows > 0)
    ? intval($latestJobRow->fetch_assoc()['id'])
    : 0;

// Fetch slides content for scrollers (Courses & Experts).
// These queries are simple examples. Adjust fields/tables as per your schema.
// We only fetch a limited number of items; each provider should only have one slide.
$coursesRes = $conn->query("
    SELECT id, course_title AS title, short_desc AS description, image_path
    FROM trainer_courses
    ORDER BY id DESC LIMIT 6
");
$expertsRes = $conn->query("
    SELECT id, full_name AS name, expertise_area AS expertise, profile_photo AS image
    FROM trainer_profiles
    ORDER BY id DESC LIMIT 6
");

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>JobsToday</title>

  <!-- SwiperJS CSS (CDN) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

  <style>
    /* ---------------- Global / Typography ---------------- */
    :root {
      --primary-blue: #0a4aa1;
      --dark-blue: #084a83;
      --left-bg: #dff1ff;
      --mid-bg: #edfff3;
      --card-bg: #f8fbff;
      --body-font: 14px;
    }
    html,body { height:100%; }
    body {
      margin:0;
      font-family: Arial, Helvetica, sans-serif;
      font-size: var(--body-font);
      background:#f4f7fc;
      color:#173247;
    }
    .container-fluid { width:100% !important; max-width:100% !important; padding-left:18px; padding-right:18px; box-sizing:border-box; }

    /* ---------------- Search bar ---------------- */
    .search-bar-full {
        background:white;
        padding:12px;
        border-radius:8px;
        display:flex;
        gap:10px;
        flex-wrap:nowrap;
        align-items:center;
        box-shadow:0 0 8px rgba(0,0,0,0.06);
        margin-top:16px;
    }
    .search-bar-full input, .search-bar-full select {
        padding:8px;
        border:1px solid #d3dfef;
        border-radius:6px;
        min-width:110px;
    }
    .search-bar-full input[name="keywords"] { flex:1 1 260px; min-width:180px; }
    .btn-search { background:var(--primary-blue); color:#fff; border:none; padding:9px 14px; border-radius:6px; font-weight:700; cursor:pointer; }

    /* ---------------- Main Grid (reduced height for left+mid) ---------------- */
    .grid {
        display:flex;
        gap:16px;
        margin-top:14px;
        width:100%;
        align-items:flex-start;
    }
    .col-left  { width:25%; }
    .col-mid   { width:50%; }
    .col-right { width:25%; }

    /* left & mid reduced heights to make vertical room for scrollers */
    .left-box {
        background: var(--left-bg);
        padding:12px;
        border-radius:10px;
        height:56vh;          /* reduced height (was ~72vh earlier) */
        overflow:auto;
    }
    .middle-box {
        background: var(--mid-bg);
        padding:16px;
        border-radius:10px;
        height:56vh;         /* reduced height */
        overflow:auto;
    }
    .right-box {
        background: var(--dark-blue);
        color:white;
        padding:18px;
        border-radius:10px;
        height:82vh;         /* keep right column taller to match your preference */
        overflow:auto;
    }

    .job-row {
        padding:10px;
        border-bottom:1px solid #cfe6f6;
        cursor:pointer;
        border-radius:6px;
        margin-bottom:8px;
        background:transparent;
    }
    .job-row:hover { background:#cfeeff; }
    .job-active { background:#8ecaff !important; }
    .job-row small { display:block; color:#264b6b; margin-top:6px; font-size:13px; }

    /* ---------------- Scrollers area (placed below left+mid, occupying 75% width) ---------------- */
    .scrollers-wrapper {
        display:flex;
        gap:16px;
        margin-top:14px;
        width:75%;              /* Important: restrict to left+mid width (25% + 50% = 75%) */
        box-sizing:border-box;
        float:left;             /* keep it aligned to the left - beside the right column */
    }

    .scroller-box {
        width:50%;              /* Two scrollers side-by-side inside the 75% container */
        background:white;
        border-radius:10px;
        padding:12px;
        box-shadow:0 0 8px rgba(0,0,0,0.06);
    }
    .scroller-title { font-weight:700; margin-bottom:8px; font-size:15px; color:#173247; }

    /* Swiper container adjustments */
    .swiper { width:100%; padding-bottom:10px; }
    .swiper-slide {
        background: var(--card-bg);
        border-radius:8px;
        display:flex;
        flex-direction:column;
        gap:8px;
        padding:10px;
        box-sizing:border-box;
        box-shadow:0 0 6px rgba(0,0,0,0.04);
        cursor:pointer;
    }
    .slide-thumb { width:100%; height:120px; object-fit:cover; border-radius:6px; }
    .slide-title { font-size:14px; font-weight:700; color:#0e3a4a; margin-top:6px; }
    .slide-desc  { font-size:13px; color:#345; line-height:1.2; margin-top:4px; }

    /* Swiper bullets style (simple) */
    .swiper-pagination-bullets { margin-top:6px; }
    .swiper-pagination-bullet { background:#c4d6ef; opacity:1; }
    .swiper-pagination-bullet-active { background:var(--primary-blue); }

    /* Right column small elements */
    .profile-block { background:white; color:#222; padding:12px; border-radius:8px; display:flex; gap:12px; align-items:center; margin-bottom:14px; }
    .profile-block img { width:56px; height:56px; border-radius:50%; object-fit:cover; }

    .login-btn { background:white; color:var(--dark-blue); padding:10px 12px; border-radius:6px; font-weight:700; text-align:center; display:block; text-decoration:none; }

    /* Responsive: when screen narrow, stack the grid & scrollers nicely */
    @media (max-width: 1000px) {
        .scrollers-wrapper { width:100%; float:none; }
        .col-left, .col-mid, .col-right { width:100%; }
        .grid { flex-direction:column; }
        .left-box, .middle-box { height:50vh; }
        .right-box { height:auto; }
    }
    @media (max-width: 680px) {
        .swiper-slide .slide-thumb { height:140px; }
    }
  </style>
</head>
<body>

<div class="container-fluid">

  <!-- ================= Search Bar ================= -->
  <form id="jobSearchForm" class="search-bar-full" onsubmit="event.preventDefault();">
      <input type="text" name="keywords" placeholder="Keywords">
      <select name="job_type"><option value="">Type</option></select>
      <input type="text" name="role" placeholder="Job Role">

      <select name="skill" name="skill">
          <option value="">Skill</option>
          <?php if ($skills && $skills->num_rows>0): while($sk = $skills->fetch_assoc()): ?>
              <option value="<?= intval($sk['id']) ?>"><?= htmlspecialchars($sk['skill_name']) ?></option>
          <?php endwhile; endif; ?>
      </select>

      <select name="state" id="stateSelect">
          <option value="">State</option>
          <?php if ($states && $states->num_rows>0): while($st = $states->fetch_assoc()): ?>
              <option value="<?= intval($st['id']) ?>"><?= htmlspecialchars($st['state_name']) ?></option>
          <?php endwhile; endif; ?>
      </select>

      <select name="city" id="citySelect"><option value="">City</option></select>
      <select name="experience"><option value="">Exp</option></select>
      <select name="salary"><option value="">Salary</option></select>

      <button type="submit" class="btn-search">Search</button>
  </form>

  <!-- ================= Main Grid (Left / Mid / Right) ================= -->
  <div class="grid">

    <!-- LEFT: Job List (reduced height to make room for scrollers) -->
    <div class="col-left">
      <div class="left-box" id="jobList">
        <?php if ($jobs && $jobs->num_rows>0): while($job = $jobs->fetch_assoc()): ?>
          <div class="job-row" data-id="<?= intval($job['id']) ?>">
              <strong><?= htmlspecialchars($job['title']) ?></strong>
              <small><?= htmlspecialchars($job['company']) ?> • <?= htmlspecialchars($job['location']) ?></small>
          </div>
        <?php endwhile; else: ?>
          <div>No jobs posted.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- MIDDLE: Job Preview (reduced height) -->
    <div class="col-mid">
      <div class="middle-box" id="jobPreview">
        <h3>Select a job to see details.</h3>
        <!-- AJAX content from /ajax/get-job.php will load here -->
      </div>
    </div>

    <!-- RIGHT: Profile / Login / Recommended -->
    <div class="col-right">
      <div class="right-box">
        <?php if (!$isLoggedIn): ?>
          <h3>Welcome to JobsToday</h3>
          <p style="color: #d3eaff;">
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
          <div style="margin-top:8px;" class="small-list">
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
          <div style="margin-top:8px;" class="small-list">
            <a href="#">Training Name 1</a>
            <a href="#">Training Name 2</a>
          </div>

        <?php endif; ?>
      </div>
    </div>

  </div> <!-- /.grid -->

  <!-- ================= Scrollers (placed BELOW left+mid columns, side-by-side) ================= -->
  <div class="scrollers-wrapper" aria-hidden="false">
    <!-- Courses scroller (left half of the 75% area) -->
    <div class="scroller-box" aria-label="Courses scroller">
      <div class="scroller-title">Courses</div>

      <!-- Swiper container for courses -->
      <div class="swiper courses-swiper" id="coursesSwiper">
        <div class="swiper-wrapper">
          <?php
            // Render slides from $coursesRes, fallback to placeholder if table not present or empty
            if ($coursesRes && $coursesRes->num_rows > 0):
              while($c = $coursesRes->fetch_assoc()):
                // image fallback logic
                $thumb = !empty($c['thumbnail']) ? $c['thumbnail'] : '/jobsweb/assets/sample1.jpg';
          ?>
            <div class="swiper-slide" data-id="<?= intval($c['id']) ?>">
              <img class="slide-thumb" src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($c['title']) ?>">
              <div class="slide-title"><?= htmlspecialchars($c['title']) ?></div>
              <div class="slide-desc"><?= htmlspecialchars(substr($c['short_desc'],0,120)) ?></div>
            </div>
          <?php
              endwhile;
            else:
              // placeholders if no courses table or empty
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

        <!-- pagination bullets (auto-styled by Swiper) -->
        <div class="swiper-pagination"></div>
      </div>
    </div>

    <!-- Experts scroller (right half of the 75% area) -->
    <div class="scroller-box" aria-label="Experts scroller">
      <div class="scroller-title">Experts</div>

      <!-- Swiper container for experts -->
      <div class="swiper experts-swiper" id="expertsSwiper">
        <div class="swiper-wrapper">
          <?php
            if ($expertsRes && $expertsRes->num_rows > 0):
              while($e = $expertsRes->fetch_assoc()):
                $avatar = !empty($e['avatar']) ? $e['avatar'] : '/jobsweb/assets/sample3.jpg';
          ?>
            <div class="swiper-slide" data-id="<?= intval($e['id']) ?>">
              <img class="slide-thumb" src="<?= htmlspecialchars($avatar) ?>" alt="<?= htmlspecialchars($e['name']) ?>">
              <div class="slide-title"><?= htmlspecialchars($e['name']) ?></div>
              <div class="slide-desc"><?= htmlspecialchars(substr($e['tagline'],0,120)) ?></div>
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

        <!-- pagination bullets -->
        <div class="swiper-pagination"></div>
      </div>
    </div>
  </div> <!-- /.scrollers-wrapper -->

  <!-- Clear float so following content (if any) is below -->
  <div style="clear:both; height:8px;"></div>

</div> <!-- /.container-fluid -->

<!-- ================= Scripts ================= -->
<!-- SwiperJS (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
/* ----------------- Initialize Swiper Instances -----------------
  - coursesSwiper and expertsSwiper use identical config:
    * autoplay enabled
    * responsive breakpoints:
      - >= 1024px: 3 slides per view
      - 768px–1023px: 2 slides per view
      - <= 767px: 1 slide per view
    * draggable, pagination bullets
*/
function initSwipers() {
  var commonConfig = {
    loop: true,
    speed: 700,
    autoplay: {
      delay: 3000,
      disableOnInteraction: false,
    },
    pagination: {
      el: '.swiper-pagination',
      clickable: true,
    },
    breakpoints: {
      0: { slidesPerView: 1, spaceBetween: 12 },
      680: { slidesPerView: 2, spaceBetween: 12 },
      1024: { slidesPerView: 3, spaceBetween: 16 }
    }
  };

  // Courses swiper
  var cSwiper = new Swiper('.courses-swiper', commonConfig);

  // Experts swiper
  var eSwiper = new Swiper('.experts-swiper', commonConfig);

  // Optional: clicking a slide can navigate to detail page.
  // We'll attach click handlers below using jQuery.
}

document.addEventListener('DOMContentLoaded', function() {
  initSwipers();

  // ----------------- Auto-load latest job into middle preview -----------------
  var latestId = <?= json_encode($latestJobId); ?>;
  if (latestId && latestId > 0) {
    // mark active job on left
    var $row = $('.job-row[data-id="'+latestId+'"]');
    if ($row.length) $row.addClass('job-active');

    // load via AJAX
    $.post('/jobsweb/ajax/get-job.php', { id: latestId }, function(data) {
      $('#jobPreview').html(data);
    }, 'html');
  }

  // ----------------- Click handlers -----------------
  // Click a job item on left -> load preview
  $(document).on('click', '.job-row', function() {
    var id = $(this).data('id');
    $('.job-row').removeClass('job-active');
    $(this).addClass('job-active');
    $.post('/jobsweb/ajax/get-job.php', { id: id }, function(data) {
      $('#jobPreview').html(data);
    }, 'html');
  });

  // Clicking a course slide -> open course detail page (if exists)
  $(document).on('click', '.courses-swiper .swiper-slide', function() {
    var id = $(this).data('id');
    if (id) {
      window.location.href = '/jobsweb/public/course.php?id=' + id;
    }
  });

  // Clicking an expert slide -> open expert detail page (if exists)
  $(document).on('click', '.experts-swiper .swiper-slide', function() {
    var id = $(this).data('id');
    if (id) {
      window.location.href = '/jobsweb/public/expert.php?id=' + id;
    }
  });

  // Clicking recommended job from right panel will highlight and open preview
  $(document).on('click', '.rec-job', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    if (!id) return;
    var $leftRow = $('.job-row[data-id="'+id+'"]');
    if ($leftRow.length) {
      $leftRow.trigger('click');
      // scroll left list to active item (if needed)
      $('#jobList').animate({ scrollTop: $leftRow.position().top + $('#jobList').scrollTop() - 40 }, 350);
    } else {
      // if not present in left list, just try to load via AJAX
      $.post('/jobsweb/ajax/get-job.php', { id: id }, function(data) {
        $('#jobPreview').html(data);
      }, 'html');
    }
  });

}); // DOMContentLoaded
</script>

<?php include(__DIR__ . '/includes/footer.php'); ?>
</body>
</html>
