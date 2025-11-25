<?php
// ===============================================
// RECREATED index.php — 4-column layout (Option A, Option1)
// Columns: 25% | 35% | 20% | 20%
// Column-4 (sliders) is fixed height and contains stacked sliders (Courses + Experts)
// Slide content: Image + Title only (no description)
// Header.php opens container; footer.php closes it
// ===============================================

if (session_status() === PHP_SESSION_NONE) session_start();
include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/config/config.php');

// detect applicant login
$isLoggedIn = isset($_SESSION['applicant_id']);

// fetch dropdowns
$skills = $conn->query("SELECT id, skill_name FROM skills_master ORDER BY skill_name ASC");
$states = $conn->query("SELECT id, state_name FROM state_master ORDER BY state_name ASC");

// fetch jobs for left panel
$jobs = $conn->query("SELECT id, title, company, location FROM jobs ORDER BY id DESC");

// latest job id for auto-load preview
$latestJobRow = $conn->query("SELECT id FROM jobs ORDER BY id DESC LIMIT 1");
$latestJobId  = ($latestJobRow && $latestJobRow->num_rows > 0) ? intval($latestJobRow->fetch_assoc()['id']) : 0;

/* sliders data */
$coursesRes = $conn->query("
  SELECT id, course_title AS title, image_path
  FROM trainer_courses
  ORDER BY id DESC LIMIT 12
");

$expertsRes = $conn->query("
  SELECT id, full_name AS name, profile_photo AS image
  FROM trainer_profiles
  ORDER BY id DESC LIMIT 12
");
?>
<style>
:root{
  --primary-blue:#0a4aa1;
  --dark-blue:#084a83;
  --left-bg:#f3fbff;
  --mid-bg:#f7fff7;
  --card-bg:#ffffff;
  --gap:14px;
  --page-height:72vh;
  --slide-thumb-h:160px;
}

/* base */
body{font-family:Arial, Helvetica, sans-serif;background:#f4f7fc;margin:0;color:#173247;}
.search-bar-full{background:#fff;padding:12px;border-radius:8px;display:flex;gap:10px;align-items:center;box-shadow:0 0 8px rgba(0,0,0,0.06);margin-top:12px;}
.search-bar-full input, .search-bar-full select{padding:8px;border-radius:6px;border:1px solid #dbeaf5;}
.search-bar-full input[name="keywords"]{flex:1;min-width:180px;}
.btn-search{background:var(--primary-blue);color:#fff;border:none;padding:9px 14px;border-radius:6px;font-weight:700;cursor:pointer;}

/* grid */
.page-grid{
  display:grid;
  grid-template-columns:25% 35% 20% 20%;
  grid-auto-rows: var(--page-height);
  gap:16px;
  margin-top:14px;
}

/* column boxes fill cell height */
.left-panel .left-box,
.mid-panel .middle-box,
.right-panel .right-box,
.slider-column .slider-content {
  height:100%;
  box-sizing:border-box;
}

/* left */
.left-panel{grid-column:1;}
.left-box{background:var(--left-bg);padding:12px;border-radius:10px;overflow:auto;}

/* mid */
.mid-panel{grid-column:2;}
.middle-box{background:var(--mid-bg);padding:16px;border-radius:10px;overflow:auto;}

/* right (login) */
.right-panel{grid-column:3;}
.right-box{background:var(--dark-blue);color:#fff;padding:16px;border-radius:10px;overflow:auto;}

/* slider column (fixed cell, internal scroll) */
.slider-column{grid-column:4;}
.slider-content{background:transparent;padding:12px;border-radius:10px;display:flex;flex-direction:column;gap:16px;overflow:auto;}

/* small components */
.job-row{padding:10px;border-radius:6px;border-bottom:1px solid #e1f0fb;cursor:pointer;background:transparent;}
.job-row:hover{background:#eaf7ff;}
.job-active{background:#c8eaff!important;}

/* slider cards */
.slider-box{background:var(--card-bg);border-radius:8px;padding:8px;box-shadow:0 2px 6px rgba(18,35,64,0.06);display:flex;flex-direction:column;align-items:center;}
.slider-title{margin-top:8px;font-weight:700;font-size:14px;color:#0e3a4a;text-align:center;}
.slider-thumb{width:100%;height:var(--slide-thumb-h);object-fit:cover;border-radius:6px;display:block;}

/* ensure swiper occupies container */
.swiper{width:100%;}
.swiper-slide{display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding:6px;box-sizing:border-box;height:auto;}

/* responsive */
@media (max-width:1100px){
  .page-grid{grid-template-columns:1fr;grid-auto-rows:auto;}
  .slider-content{flex-direction:row;overflow-x:auto;overflow-y:hidden;}
  .slider-box{min-width:260px;flex:0 0 auto;}
}
</style>

<!-- SEARCH -->
<form id="jobSearchForm" class="search-bar-full" onsubmit="event.preventDefault();">
  <input type="text" name="keywords" placeholder="Keywords">
  <select name="skill"><option value="">Skill</option>
    <?php if($skills): while($sk=$skills->fetch_assoc()): ?>
      <option value="<?= intval($sk['id']) ?>"><?= htmlspecialchars($sk['skill_name']) ?></option>
    <?php endwhile; endif; ?>
  </select>

  <select name="state" id="stateSelect"><option value="">State</option>
    <?php if($states): while($st=$states->fetch_assoc()): ?>
      <option value="<?= intval($st['id']) ?>"><?= htmlspecialchars($st['state_name']) ?></option>
    <?php endwhile; endif; ?>
  </select>

  <select name="city" id="citySelect"><option value="">City</option></select>
  <button class="btn-search">Search</button>
</form>

<div class="page-grid">

  <!-- column 1: jobs list -->
  <div class="left-panel">
    <div class="left-box" id="jobList">
      <?php if($jobs && $jobs->num_rows>0): while($job=$jobs->fetch_assoc()): ?>
        <div class="job-row" data-id="<?= intval($job['id']) ?>">
          <strong><?= htmlspecialchars($job['title']) ?></strong><br>
          <small><?= htmlspecialchars($job['company']) ?> • <?= htmlspecialchars($job['location']) ?></small>
        </div>
      <?php endwhile; else: ?>
        <div>No jobs posted.</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- column 2: job preview (wider) -->
  <div class="mid-panel">
    <div class="middle-box" id="jobPreview">
      <h3>Select a job to see details</h3>
    </div>
  </div>

  <!-- column 3: login/profile -->
  <div class="right-panel">
    <div class="right-box">
      <?php if(!$isLoggedIn): ?>
        <h3>Welcome to JobsToday</h3>
        <p style="color:#d3eaff;">Discover opportunities that match your skills.</p>
        <a class="login-btn" href="/jobsweb/public/login.php" style="display:inline-block;background:#fff;color:var(--dark-blue);padding:8px 12px;border-radius:6px;font-weight:700;text-decoration:none;">Login</a>
      <?php else: ?>
        <div class="profile-block" style="background:#fff;padding:10px;border-radius:8px;color:#222;display:flex;gap:10px;align-items:center;">
          <img src="/jobsweb/assets/user-icon.png" alt="user" style="width:56px;height:56px;border-radius:50%;object-fit:cover;">
          <div><strong><?= htmlspecialchars($_SESSION['applicant_name'] ?? 'Applicant') ?></strong><br><small style="color:#456"><?= htmlspecialchars($_SESSION['applicant_skill'] ?? '') ?></small></div>
        </div>
        <a class="login-btn" href="/jobsweb/applicant/profile.php" style="display:inline-block;background:#fff;color:var(--dark-blue);padding:8px 12px;border-radius:6px;font-weight:700;text-decoration:none;margin-top:10px;">Update Profile</a>
      <?php endif; ?>

      <hr style="border:none;border-top:1px solid rgba(255,255,255,0.08);margin:12px 0;">
      <strong style="color:#fff;">Recommended</strong>
      <div style="margin-top:8px;color:#fff;">
        <?php
          $rec = $conn->query("SELECT id,title FROM jobs ORDER BY id DESC LIMIT 3");
          if($rec && $rec->num_rows>0):
            while($r=$rec->fetch_assoc()): ?>
              <a href="#" class="rec-job" data-id="<?= intval($r['id']) ?>" style="color:#fff;display:block;margin-top:6px;text-decoration:none;"><?= htmlspecialchars($r['title']) ?></a>
        <?php endwhile; else: ?>
            <div style="color:#dfeeff">No recommendations</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- column 4: sliders (fixed height cell, internal scroll) -->
  <div class="slider-column">
    <div class="slider-content">
      <!-- COURSES SLIDER (image + title only) -->
      <div class="slider-box" aria-label="Courses">
        <div style="width:100%;font-weight:700;margin-bottom:8px;color:#0e3a4a;text-align:center;">Courses</div>
        <div class="swiper courses-swiper">
          <div class="swiper-wrapper">
            <?php if($coursesRes && $coursesRes->num_rows>0): while($c=$coursesRes->fetch_assoc()): ?>
              <div class="swiper-slide" data-id="<?= intval($c['id']) ?>">
                <img class="slider-thumb" src="<?= htmlspecialchars($c['image_path'] ?: '/jobsweb/assets/sample1.jpg') ?>" alt="<?= htmlspecialchars($c['title']) ?>">
                <div class="slider-title"><?= htmlspecialchars($c['title']) ?></div>
              </div>
            <?php endwhile; else: ?>
              <div class="swiper-slide"><img class="slider-thumb" src="/jobsweb/assets/sample1.jpg"><div class="slider-title">Sample Course</div></div>
            <?php endif; ?>
          </div>
          <div class="swiper-pagination"></div>
        </div>
      </div>

      <!-- EXPERTS SLIDER (image + name only) -->
      <div class="slider-box" aria-label="Experts">
        <div style="width:100%;font-weight:700;margin-bottom:8px;color:#0e3a4a;text-align:center;">Experts</div>
        <div class="swiper experts-swiper">
          <div class="swiper-wrapper">
            <?php if($expertsRes && $expertsRes->num_rows>0): while($e=$expertsRes->fetch_assoc()): ?>
              <div class="swiper-slide" data-id="<?= intval($e['id']) ?>">
                <img class="slider-thumb" src="<?= htmlspecialchars($e['image'] ?: '/jobsweb/assets/sample3.jpg') ?>" alt="<?= htmlspecialchars($e['name']) ?>">
                <div class="slider-title"><?= htmlspecialchars($e['name']) ?></div>
              </div>
            <?php endwhile; else: ?>
              <div class="swiper-slide"><img class="slider-thumb" src="/jobsweb/assets/sample3.jpg"><div class="slider-title">Sample Expert</div></div>
            <?php endif; ?>
          </div>
          <div class="swiper-pagination"></div>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Swiper + jQuery -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css">
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function initSwipers(){
  const cfg={
    loop:true, speed:700,
    autoplay:{delay:2800, disableOnInteraction:false},
    pagination:{el:'.swiper-pagination', clickable:true},
    slidesPerView:1,
    spaceBetween:12
  };
  new Swiper('.courses-swiper', cfg);
  new Swiper('.experts-swiper', cfg);
}

document.addEventListener('DOMContentLoaded', function(){
  initSwipers();

  // auto load latest job
  var latest = <?= json_encode($latestJobId) ?>;
  if(latest){
    var $row = $('.job-row[data-id="'+latest+'"]');
    if($row.length) $row.addClass('job-active');
    $.post('/jobsweb/ajax/get-job.php', {id: latest}, function(data){ $('#jobPreview').html(data); }, 'html');
  }

  // job click
  $(document).on('click', '.job-row', function(){
    var id = $(this).data('id');
    $('.job-row').removeClass('job-active');
    $(this).addClass('job-active');
    $.post('/jobsweb/ajax/get-job.php', {id: id}, function(data){ $('#jobPreview').html(data); }, 'html');
  });

  // slider click handlers
  $(document).on('click', '.courses-swiper .swiper-slide', function(){ var id=$(this).data('id'); if(id) window.location.href='/jobsweb/public/course.php?id='+id; });
  $(document).on('click', '.experts-swiper .swiper-slide', function(){ var id=$(this).data('id'); if(id) window.location.href='/jobsweb/public/expert.php?id='+id; });

  // recommended job
  $(document).on('click', '.rec-job', function(e){ e.preventDefault(); var id=$(this).data('id'); if(!id) return; var $left=$('.job-row[data-id="'+id+'"]'); if($left.length){ $left.trigger('click'); $('#jobList').animate({ scrollTop: $left.position().top + $('#jobList').scrollTop() - 40}, 350); } else { $.post('/jobsweb/ajax/get-job.php', {id:id}, function(data){ $('#jobPreview').html(data); }, 'html'); } });
});
</script>

<?php include(__DIR__ . '/includes/footer.php'); ?>
