<?php
// ===============================================
// FINAL — index.php (COMPRESSED RIGHT PANEL VERSION)
// ===============================================

if (session_status() === PHP_SESSION_NONE) session_start();
include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/config/config.php');

$isLoggedIn = isset($_SESSION['applicant_id']);

$skills  = $conn->query("SELECT id, skill_name FROM skills_master ORDER BY skill_name ASC");
$states  = $conn->query("SELECT id, state_name FROM state_master ORDER BY state_name ASC");
$jobs    = $conn->query("SELECT id, title, company, location FROM jobs ORDER BY id DESC");

$latestJobRow = $conn->query("SELECT id FROM jobs ORDER BY id DESC LIMIT 1");
$latestJobId  = ($latestJobRow && $latestJobRow->num_rows > 0) ? intval($latestJobRow->fetch_assoc()['id']) : 0;

$coursesRes = $conn->query("
  SELECT id, course_title AS title, image_path
  FROM trainer_courses ORDER BY id DESC LIMIT 12
");

$expertsRes = $conn->query("
  SELECT id, full_name AS name, profile_photo AS image
  FROM trainer_profiles ORDER BY id DESC LIMIT 12
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

/* Sticky Header */
header, .topbar, .site-header {
  position: sticky;
  top: 0;
  z-index: 1100;
  background: inherit;
  backdrop-filter: saturate(120%) blur(4px);
}

body{font-family:Arial;background:#f4f7fc;margin:0;color:#173247;}

/* SEARCH BAR */
.search-bar-full{
  background:#fff;padding:10px;border-radius:8px;display:flex;gap:8px;
  align-items:center;box-shadow:0 0 8px rgba(0,0,0,0.06);
  margin-top:12px;flex-wrap:wrap;
}
.search-bar-full input, .search-bar-full select{
  padding:8px;border-radius:6px;border:1px solid #dbeaf5;background:#fff;
}
.search-bar-full input[name="keywords"]{flex:1;min-width:180px;}
.search-bar-full select[name="state"]{width:120px;}
.search-bar-full select[name="city"]{width:180px;}

.btn-search{
  background:var(--primary-blue);color:#fff;border:none;padding:9px 14px;
  border-radius:6px;font-weight:700;cursor:pointer;
}

/* GRID */
.page-grid{
  display:grid;
  grid-template-columns:25% 35% 20% 20%;
  grid-auto-rows: var(--page-height);
  gap:16px;
  margin-top:14px;
}

/* PANELS */
.left-box, .middle-box, .right-box, .slider-content {
  height:100%;
  box-sizing:border-box;
}

/* LEFT PANEL */
.left-box{
  background:var(--left-bg);padding:12px;border-radius:10px;overflow:auto;
}

/* MIDDLE PANEL */
.middle-box{
  background:var(--mid-bg);padding:16px;border-radius:10px;overflow:auto;
}

/* RIGHT PANEL – compact version */
.right-box{
  background:var(--dark-blue);
  color:#fff;
  padding:10px;           /* compact */
  border-radius:10px;
  overflow:auto;
}

/* profile compact */
.profile-block{
  background:#fff;
  padding:6px;            /* compact */
  border-radius:8px;
  color:#222;
  display:flex;
  gap:8px;                /* compact */
  align-items:center;
}

/* job list rows */
.job-row{
  padding:10px;border-radius:6px;border-bottom:1px solid #e1f0fb;
  cursor:pointer;background:transparent;
}
.job-row:hover{background:#eaf7ff;}
.job-active{background:#c8eaff!important;}

/* SLIDERS */
.slider-column{grid-column:4;}
.slider-content{
  background:transparent;padding:12px;border-radius:10px;
  display:flex;flex-direction:column;gap:16px;overflow:auto;
}
.slider-box{
  background:var(--card-bg);border-radius:8px;padding:8px;
  box-shadow:0 2px 6px rgba(18,35,64,0.06);
  display:flex;flex-direction:column;align-items:center;
}
.slider-thumb{width:100%;height:var(--slide-thumb-h);object-fit:cover;border-radius:6px;}
.slider-title{margin-top:8px;font-weight:700;font-size:14px;color:#0e3a4a;text-align:center;}

/* Right Panel headings – compact */
.right-box strong{
  font-size:14px;         /* compact */
  margin-bottom:2px;      /* compact */
  display:block;
}

/* Recommended links – compact */
.rec-job, .right-box a{
  margin-top:3px;         /* compact */
  font-size:13px;         /* compact */
  display:block;
  color:#fff;
  text-decoration:none;
}

/* hr compact */
.right-box hr{
  margin:6px 0;           /* compact */
  border:none;
  border-top:1px solid rgba(255,255,255,0.08);
}

/* responsive */
@media (max-width:1100px){
  .page-grid{grid-template-columns:1fr;}
  .slider-content{flex-direction:row;overflow-x:auto;}
  .slider-box{min-width:260px;}
}
</style>

<!-- SEARCH -->
<form id="jobSearchForm" class="search-bar-full" onsubmit="event.preventDefault();">

  <input type="text" name="keywords" placeholder="Keywords">

  <select name="work_mode">
    <option value="">Work Type</option>
    <option value="On-site">On-site</option>
    <option value="Work From Home">Work From Home</option>
    <option value="Hybrid">Hybrid</option>
  </select>

  <select name="skill">
    <option value="">Skill</option>
    <?php if($skills): while($sk=$skills->fetch_assoc()): ?>
      <option value="<?= $sk['id'] ?>"><?= htmlspecialchars($sk['skill_name']) ?></option>
    <?php endwhile; endif; ?>
  </select>

  <select name="state" id="stateSelect">
    <option value="">State</option>
    <?php if($states): while($st=$states->fetch_assoc()): ?>
      <option value="<?= $st['id'] ?>"><?= htmlspecialchars($st['state_name']) ?></option>
    <?php endwhile; endif; ?>
  </select>

  <select name="city" id="citySelect"><option value="">City</option></select>

  <button class="btn-search">Search</button>
</form>

<div class="page-grid">

  <!-- LEFT -->
  <div class="left-panel">
    <div class="left-box" id="jobList">
      <?php if($jobs): while($job=$jobs->fetch_assoc()): ?>
        <div class="job-row" data-id="<?= $job['id'] ?>">
          <strong><?= htmlspecialchars($job['title']) ?></strong><br>
          <small><?= htmlspecialchars($job['company']) ?> • <?= htmlspecialchars($job['location']) ?></small>
        </div>
      <?php endwhile; endif; ?>
    </div>
  </div>

  <!-- MIDDLE PREVIEW -->
  <div class="mid-panel">
    <div class="middle-box" id="jobPreview">
      <h3>Select a job to see details</h3>
    </div>
  </div>

  <!-- RIGHT PANEL (COMPACT) -->
  <div class="right-panel">
    <div class="right-box">

     <?php if(!$isLoggedIn): ?>

    <h3 style="margin-bottom:6px;">Welcome to JobsToday</h3>
    <div class="right-sub">Discover opportunities that match your skills.</div>

    <a class="login-btn" href="/jobsweb/public/login.php">Login</a>

<?php else: ?>

    <div class="profile-block">
        <img src="/jobsweb/assets/user-icon.png">
        <div>
            <strong><?= htmlspecialchars($_SESSION['applicant_name']) ?></strong><br>
            <small style="color:#456"><?= htmlspecialchars($_SESSION['applicant_skill']) ?></small>
        </div>
    </div>

    <a class="login-btn" href="/jobsweb/applicant/profile.php">Profile</a>

<?php endif; ?>

<hr>

<strong>Recommended Jobs</strong>
<?php
  $rec = $conn->query("SELECT id,title FROM jobs ORDER BY id DESC LIMIT 3");
  if($rec):
    while($r=$rec->fetch_assoc()):
?>
    <a href="#" class="rec-item rec-job" data-id="<?= $r['id'] ?>"><?= htmlspecialchars($r['title']) ?></a>
<?php endwhile; endif; ?>

<hr>

<strong>Recommended Courses</strong>
<?php
  $rc = $conn->query("SELECT id,course_title AS title FROM trainer_courses ORDER BY id DESC LIMIT 1");
  if($rc && $rc->num_rows>0): $cr=$rc->fetch_assoc(); ?>
    <a href="/jobsweb/public/course.php?id=<?= $cr['id'] ?>" class="rec-item"><?= htmlspecialchars($cr['title']) ?></a>
<?php else: ?>
    <div class="right-sub">No courses found</div>
<?php endif; ?>

<hr>

<strong>Recommended Experts</strong>
<?php
  $re = $conn->query("SELECT id,full_name AS name FROM trainer_profiles ORDER BY id DESC LIMIT 1");
  if($re && $re->num_rows>0): $ex=$re->fetch_assoc(); ?>
    <a href="/jobsweb/public/expert.php?id=<?= $ex['id'] ?>" class="rec-item"><?= htmlspecialchars($ex['name']) ?></a>
<?php else: ?>
    <div class="right-sub">No experts found</div>
<?php endif; ?>

      <hr>

      <!-- Recommended Jobs -->
      <strong>Recommended Jobs</strong>
      <?php
        $rec = $conn->query("SELECT id,title FROM jobs ORDER BY id DESC LIMIT 3");
        if($rec): while($r=$rec->fetch_assoc()): ?>
          <a class="rec-job" data-id="<?= $r['id'] ?>"><?= htmlspecialchars($r['title']) ?></a>
      <?php endwhile; endif; ?>

      <hr>

      <!-- Recommended Course -->
      <strong>Recommended Courses</strong>
      <?php
        $rc = $conn->query("SELECT id,course_title AS title FROM trainer_courses ORDER BY id DESC LIMIT 1");
        if($rc && $rc->num_rows>0): $cr=$rc->fetch_assoc(); ?>
          <a href="/jobsweb/public/course.php?id=<?= $cr['id'] ?>"><?= htmlspecialchars($cr['title']) ?></a>
      <?php endif; ?>

      <hr>

      <!-- Recommended Expert -->
      <strong>Recommended Experts</strong>
      <?php
        $rexp = $conn->query("SELECT id,full_name AS name FROM trainer_profiles ORDER BY id DESC LIMIT 1");
        if($rexp && $rexp->num_rows>0): $ex=$rexp->fetch_assoc(); ?>
          <a href="/jobsweb/public/expert.php?id=<?= $ex['id'] ?>"><?= htmlspecialchars($ex['name']) ?></a>
      <?php endif; ?>

    </div>
  </div>

  <!-- SLIDER COLUMN -->
  <div class="slider-column">
    <div class="slider-content">

      <!-- COURSES -->
      <div class="slider-box">
        <div style="font-weight:700;margin-bottom:6px;">Courses</div>
        <div class="swiper courses-swiper">
          <div class="swiper-wrapper">
            <?php if($coursesRes): while($c=$coursesRes->fetch_assoc()): ?>
              <div class="swiper-slide" data-id="<?= $c['id'] ?>">
                <img class="slider-thumb" src="<?= htmlspecialchars($c['image_path'] ?: '/jobsweb/assets/sample1.jpg') ?>">
                <div class="slider-title"><?= htmlspecialchars($c['title']) ?></div>
              </div>
            <?php endwhile; endif; ?>
          </div>
          <div class="swiper-pagination"></div>
        </div>
      </div>

      <!-- EXPERTS -->
      <div class="slider-box">
        <div style="font-weight:700;margin-bottom:6px;">Experts</div>
        <div class="swiper experts-swiper">
          <div class="swiper-wrapper">
            <?php if($expertsRes): while($e=$expertsRes->fetch_assoc()): ?>
              <div class="swiper-slide" data-id="<?= $e['id'] ?>">
                <img class="slider-thumb" src="<?= htmlspecialchars($e['image'] ?: '/jobsweb/assets/sample3.jpg') ?>">
                <div class="slider-title"><?= htmlspecialchars($e['name']) ?></div>
              </div>
            <?php endwhile; endif; ?>
          </div>
          <div class="swiper-pagination"></div>
        </div>
      </div>

    </div>
  </div>

</div>

<!-- JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css">
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function initSwipers(){
  const cfg={
    loop:true,speed:700,
    autoplay:{delay:3000,disableOnInteraction:false},
    pagination:{el:'.swiper-pagination',clickable:true},
    slidesPerView:1,spaceBetween:12
  };
  new Swiper('.courses-swiper', cfg);
  new Swiper('.experts-swiper', cfg);
}

document.addEventListener('DOMContentLoaded', function(){

  initSwipers();

  var latest = <?= json_encode($latestJobId) ?>;
  if(latest){
    var $row = $('.job-row[data-id="'+latest+'"]');
    if($row.length){$row.addClass('job-active');}
    $.post('/jobsweb/ajax/get-job.php',{id:latest},function(d){
      $('#jobPreview').html(d);
    });
  }

  $(document).on('click','.job-row',function(){
    var id=$(this).data('id');
    $('.job-row').removeClass('job-active');
    $(this).addClass('job-active');
    $.post('/jobsweb/ajax/get-job.php',{id:id},function(d){
      $('#jobPreview').html(d);
    });
  });

  $(document).on('click','.courses-swiper .swiper-slide',function(){
    var id=$(this).data('id'); if(id) window.location='/jobsweb/public/course.php?id='+id;
  });

  $(document).on('click','.experts-swiper .swiper-slide',function(){
    var id=$(this).data('id'); if(id) window.location='/jobsweb/public/expert.php?id='+id;
  });

  $(document).on('click','.rec-job',function(e){
    e.preventDefault();
    var id=$(this).data('id');
    var $left=$('.job-row[data-id="'+id+'"]');
    if($left.length){
      $left.trigger('click');
      $('#jobList').scrollTop($left.position().top - 50);
    } else {
      $.post('/jobsweb/ajax/get-job.php',{id:id},function(d){
        $('#jobPreview').html(d);
      });
    }
  });

});
</script>

<?php include(__DIR__ . '/includes/footer.php'); ?>
