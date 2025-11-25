<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/config/config.php');

// detect login
$isLoggedIn = isset($_SESSION['applicant_id']);

// dropdowns
$skills = $conn->query("SELECT id, skill_name FROM skills_master ORDER BY skill_name ASC");
$states = $conn->query("SELECT id, state_name FROM state_master ORDER BY state_name ASC");

// jobs list
$jobs = $conn->query("SELECT id,title,company,location FROM jobs ORDER BY id DESC");

// latest job
$latestRow = $conn->query("SELECT id FROM jobs ORDER BY id DESC LIMIT 1");
$latestJobId = ($latestRow && $latestRow->num_rows>0) ? intval($latestRow->fetch_assoc()['id']) : 0;

// sliders
$coursesRes = $conn->query("SELECT id,course_title AS title,image_path FROM trainer_courses ORDER BY id DESC LIMIT 12");
$expertsRes  = $conn->query("SELECT id,full_name AS name,profile_photo AS image FROM trainer_profiles ORDER BY id DESC LIMIT 12");
?>

<!-- External CSS -->
<link rel="stylesheet" href="/jobsweb/assets/css/home.css">

<!-- SEARCH PANEL -->
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

<!-- MAIN GRID -->
<div class="page-grid">

  <!-- LEFT COLUMN -->
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

  <!-- MIDDLE COLUMN -->
  <div class="mid-panel">
    <div class="middle-box" id="jobPreview">
      <h3>Select a job to see details</h3>
    </div>
  </div>

  <!-- RIGHT COLUMN -->
  <div class="right-panel">
    <div class="right-box">

      <?php if(!$isLoggedIn): ?>
        <h3>Welcome to JobsToday</h3>
        <p class="right-sub">Discover opportunities that match your skills.</p>

        <a class="login-btn" href="/jobsweb/public/login.php">Login</a>

      <?php else: ?>

        <div class="profile-block">
          <img src="/jobsweb/assets/user-icon.png">
          <div>
            <strong><?= htmlspecialchars($_SESSION['applicant_name']) ?></strong><br>
            <small><?= htmlspecialchars($_SESSION['applicant_skill']) ?></small>
          </div>
        </div>

        <a class="login-btn" href="/jobsweb/applicant/edit-profile.php">Update Profile</a>

      <?php endif; ?>

      <hr>

      <!-- Recommended Jobs -->
      <strong>Recommended Jobs</strong>
      <?php
        $rj = $conn->query("SELECT id,title FROM jobs ORDER BY id DESC LIMIT 3");
        if($rj): while($row=$rj->fetch_assoc()): ?>
          <a class="rec-item rec-job" data-id="<?= $row['id'] ?>">
            <?= htmlspecialchars($row['title']) ?>
          </a>
      <?php endwhile; endif; ?>

      <hr>

      <!-- Recommended Courses -->
      <strong>Recommended Courses</strong>
      <?php
        $rc = $conn->query("SELECT id,course_title AS title FROM trainer_courses ORDER BY id DESC LIMIT 1");
        if($rc && $rc->num_rows>0): $cr=$rc->fetch_assoc(); ?>
          <a class="rec-item" href="/jobsweb/public/course.php?id=<?= $cr['id'] ?>">
            <?= htmlspecialchars($cr['title']) ?>
          </a>
      <?php endif; ?>

      <hr>

      <!-- Recommended Experts -->
      <strong>Recommended Experts</strong>
      <?php
        $re = $conn->query("SELECT id,full_name AS name FROM trainer_profiles ORDER BY id DESC LIMIT 1");
        if($re && $re->num_rows>0): $ex=$re->fetch_assoc(); ?>
          <a class="rec-item" href="/jobsweb/public/expert.php?id=<?= $ex['id'] ?>">
            <?= htmlspecialchars($ex['name']) ?>
          </a>
      <?php endif; ?>

    </div>
  </div>

  <!-- COLUMN-4 SLIDERS -->
  <div class="slider-column">
    <div class="slider-content">

      <!-- COURSES -->
      <div class="slider-box">
        <div class="slider-head">Courses</div>

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
        <div class="slider-head">Experts</div>

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

</div><!-- GRID END -->

<!-- JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css">
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function initSwipers(){
  const cfg={
    loop:true,
    speed:700,
    autoplay:{ delay:3000, disableOnInteraction:false },
    pagination:{ el:'.swiper-pagination', clickable:true },
    slidesPerView:1,
    spaceBetween:12
  };
  new Swiper('.courses-swiper', cfg);
  new Swiper('.experts-swiper', cfg);
}

document.addEventListener('DOMContentLoaded', function(){

  initSwipers();

  var latest = <?= json_encode($latestJobId) ?>;
  if(latest){
    let $r = $('.job-row[data-id="'+latest+'"]');
    if($r.length) $r.addClass('job-active');
    $.post('/jobsweb/ajax/get-job.php', {id:latest}, function(d){
      $('#jobPreview').html(d);
    });
  }

  $(document).on('click','.job-row',function(){
    $('.job-row').removeClass('job-active');
    $(this).addClass('job-active');
    $.post('/jobsweb/ajax/get-job.php',{id:$(this).data('id')},function(d){
      $('#jobPreview').html(d);
    });
  });

  $(document).on('click','.courses-swiper .swiper-slide',function(){
    var id=$(this).data('id'); if(id) window.location="/jobsweb/public/course.php?id="+id;
  });

  $(document).on('click','.experts-swiper .swiper-slide',function(){
    var id=$(this).data('id'); if(id) window.location="/jobsweb/public/expert.php?id="+id;
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
<script src="/jobsweb/assets/js/apply-handler.js"></script>
<?php include(__DIR__ . '/includes/footer.php'); ?>

