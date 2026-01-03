<?php
session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

include "../config/config.php";
include "../includes/header.php";

/* ================= MASTER DATA ================= */
$levels = $conn->query("SELECT DISTINCT level FROM qualification_master ORDER BY level")->fetch_all(MYSQLI_ASSOC);
$courses = $conn->query("SELECT DISTINCT level, course FROM qualification_master WHERE course<>''")->fetch_all(MYSQLI_ASSOC);
$specializations = $conn->query("SELECT DISTINCT course, specialization FROM qualification_master WHERE specialization<>''")->fetch_all(MYSQLI_ASSOC);
$states = $conn->query("SELECT DISTINCT state FROM india_location WHERE state<>'' ORDER BY state")->fetch_all(MYSQLI_ASSOC);

/* ================= EXISTING QUALIFICATIONS ================= */
$existingQualifications = [];
$stmt = $conn->prepare("
    SELECT id, qualification_level, course_name, specialization,
           university, state, study_mode, is_pursuing,
           year_of_passing, percentage
    FROM applicant_education
    WHERE applicant_id=?
    ORDER BY id ASC
");
$stmt->bind_param("i", $_SESSION['applicant_id']);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    $existingQualifications[] = [
        'id'=>$r['id'],
        'level'=>$r['qualification_level'],
        'course'=>$r['course_name'],
        'specialization'=>$r['specialization'] ?: 'Not Applicable',
        'study_mode'=>$r['study_mode'],
        'status'=>$r['is_pursuing']?'pursuing':'completed',
        'year'=>$r['year_of_passing'],
        'result'=>$r['percentage'],
        'university'=>$r['university'],
        'state'=>$r['state']
    ];
}
$stmt->close();
?>

<div class="container-fluid mt-3">
<div class="row">
<aside class="col-md-3"><?php include "../includes/profile_sidebar.php"; ?></aside>

<section class="col-md-9">

<h4>Qualification Details</h4>
<p class="text-success">
Add/Edit qualifications chronologically (10 → 12 → Graduation → PG).  
Maximum 6 most relevant entries. Must press SUBMIT button to save added qualifications.
</p>

<!-- ================= INPUTS ================= -->
<div class="row g-3">

<div class="col-md-2">
<label>Level</label>
<select id="level" class="form-select">
<option value="">Select</option>
<?php foreach($levels as $l): ?><option><?= htmlspecialchars($l['level']) ?></option><?php endforeach; ?>
</select>
</div>

<div class="col-md-3">
<label>Course</label>
<select id="course" class="form-select" disabled>
<option value="">Select</option>
<?php foreach($courses as $c): ?>
<option data-level="<?= htmlspecialchars($c['level']) ?>"><?= htmlspecialchars($c['course']) ?></option>
<?php endforeach; ?>
</select>
</div>

<div class="col-md-3">
<label>Specialization</label>
<select id="specialization" class="form-select" disabled>
<option value="">Select</option>
<?php foreach($specializations as $s): ?>
<option data-course="<?= htmlspecialchars($s['course']) ?>"><?= htmlspecialchars($s['specialization']) ?></option>
<?php endforeach; ?>
</select>
</div>

<div class="col-md-3 position-relative">
  <label>University / Board / Institute</label>

  <input type="text"
         id="provider_input"
         class="form-control"
         placeholder="Type at least 3 characters"
         autocomplete="off">

  <small class="text-muted">
    If not found in drop down, type full name
  </small>

  <div id="provider_box"
       class="border bg-white position-absolute w-100"
       style="display:none; max-height:160px; overflow:auto; z-index:1000">
  </div>
</div>


<div class="col-md-2">
<label>Study State</label>
<select id="inst_state" class="form-select">
<option value="">Select</option>
<?php foreach($states as $st): ?><option><?= htmlspecialchars($st['state']) ?></option><?php endforeach; ?>
</select>
</div>

<div class="col-md-2">
<label>Study Mode</label>
<select id="study_mode" class="form-select">
<option value="">Select</option>
<option>Regular</option><option>Distance</option>
<option>Online</option><option>Correspondence</option>
</select>
</div>

<div class="col-md-2">
<label>Status</label>
<select id="status" class="form-select">
<option value="">Select</option>
<option value="completed">Completed</option>
<option value="pursuing">Pursuing</option>
</select>
</div>

<div class="col-md-2">
<label>Passing Year</label>
<select id="year" class="form-select">
<option value="">Select</option>
<?php for($y=date('Y');$y>=1980;$y--) echo "<option>$y</option>"; ?>
</select>
</div>

<div class="col-md-2">
<label>% / Grade</label>
<input type="text" id="result" class="form-control">
</div>

<div class="col-md-2 d-flex align-items-end">
<button type="button" id="addBtn" class="btn btn-success w-100">Add</button>
</div>

</div>

<hr>

<form method="POST" action="qualification-process.php">
<input type="hidden" name="action" value="bulk_add">

<table class="table table-bordered" id="previewTable">
<thead class="table-light">
<tr>
<th>Level</th><th>Course</th><th>Spec</th><th>Mode</th>
<th>Status</th><th>Year</th><th>%</th>
<th>University</th><th>State</th><th>Remove</th>
</tr>
</thead>
<tbody></tbody>
</table>

<button class="btn btn-primary">SUBMIT</button>

<div class="d-flex justify-content-between mt-4">
<a href="/jobsweb/index.php" class="btn btn-outline-secondary">⬅ HOME</a>
<a href="/jobsweb/applicant/experience.php" class="btn btn-success">NEXT ➜</a>
</div>
</form>

</section>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

const level = document.getElementById('level');
const course = document.getElementById('course');
const specialization = document.getElementById('specialization');
const provider_input = document.getElementById('provider_input');
const provider_box = document.getElementById('provider_box');
const inst_state = document.getElementById('inst_state');
const study_mode = document.getElementById('study_mode');
const status = document.getElementById('status');
const year = document.getElementById('year');
const result = document.getElementById('result');
const addBtn = document.getElementById('addBtn');
const tbody = document.querySelector('#previewTable tbody');

const existing = <?= json_encode($existingQualifications) ?>;
let fresh = [];

/* ---------- Dropdown Logic ---------- */
level.onchange = () => {
  course.disabled = false;
  [...course.options].forEach(o=>{
    if(!o.value) return;
    o.hidden = o.dataset.level !== level.value;
  });
};

course.onchange = () => {
  specialization.disabled = false;
  [...specialization.options].forEach(o=>{
    if(!o.value) return;
    o.hidden = o.dataset.course !== course.value;
  });
};

status.onchange = () => {
  const pursuing = status.value === 'pursuing';
  year.disabled = pursuing;
  result.disabled = pursuing;
};

/* ---------- University Search ---------- */
provider_input.onkeyup = () => {
  if (provider_input.value.length < 3) {
    provider_box.style.display = 'none';
    return;
  }
  fetch('../ajax/search_provider.php?q=' + encodeURIComponent(provider_input.value))
    .then(r=>r.json())
    .then(data=>{
      provider_box.innerHTML='';
      if(!data.length){ provider_box.style.display='none'; return; }
      data.forEach(row=>{
        let d=document.createElement('div');
        d.className='p-2 border-bottom';
        d.textContent=row.provider_name;
        d.onclick=()=>{ provider_input.value=row.provider_name; provider_box.style.display='none'; };
        provider_box.appendChild(d);
      });
      provider_box.style.display='block';
    });
};

/* ---------- Render ---------- */
function render(){
  tbody.innerHTML='';
  existing.forEach((q,i)=>row(q,false,i));
  fresh.forEach((q,i)=>row(q,true,i));
}

function row(q,isNew,i){
  tbody.innerHTML+=`
  <tr>
    <td>${q.level}</td><td>${q.course}</td><td>${q.specialization}</td>
    <td>${q.study_mode}</td><td>${q.status}</td>
    <td>${q.year||'-'}</td><td>${q.result||'-'}</td>
    <td>${q.university}</td><td>${q.state}</td>
    <td>
      ${isNew
        ? `<button type="button" class="btn btn-danger btn-sm" onclick="fresh.splice(${i},1);render();">❌</button>
           <input type="hidden" name="qualifications[${i}][data]" value='${JSON.stringify(q)}'>`
        : `<button type="button" class="btn btn-danger btn-sm"
           onclick="deleteExisting(${q.id},${i})">❌</button>`
      }
    </td>
  </tr>`;
}

/* ---------- Add ---------- */
addBtn.onclick = () => {

  if (
    !level.value ||
    !course.value ||
    !provider_input.value ||
    !inst_state.value ||
    !study_mode.value ||
    !status.value
  ) {
    alert('Please fill all required fields');
    return;
  }

  // ---- Push new qualification ----
  fresh.push({
    level: level.value,
    course: course.value,
    specialization: specialization.value || 'Not Applicable',
    study_mode: study_mode.value,
    status: status.value,
    year: year.value || '',
    result: result.value || '',
    university: provider_input.value,
    state: inst_state.value
  });

  render();

  /* ================= FULL RESET (LIKE FIRST PAGE LOAD) ================= */

  // Level
  level.value = '';

  // Course
  course.value = '';
  course.disabled = true;

  // Specialization
  specialization.value = '';
  specialization.disabled = true;

  // University
  provider_input.value = '';

  // State
  inst_state.value = '';

  // Study Mode
  study_mode.value = '';

  // Status
  status.value = '';

  // Passing Year
  year.value = '';
  year.disabled = false;

  // Percentage / Grade
  result.value = '';
  result.disabled = false;

  // Optional UX polish
  level.focus();
};



/* ---------- Delete Existing ---------- */
window.deleteExisting = (id,i)=>{
  if(!confirm('Delete this qualification permanently?')) return;
  fetch('qualification-delete.php',{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({id})
  }).then(()=>{ existing.splice(i,1); render(); });
};

render();
});
</script>

<?php include "../includes/footer.php"; ?>
