<?php
session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

require_once "../config/config.php";
include "../includes/header.php";

/* close header container */
echo "</div>";

$app_id = (int)$_SESSION['applicant_id'];

/* fetch logged-in email */
$stmt = $conn->prepare("SELECT email FROM job_seekers WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $app_id);
$stmt->execute();
$stmt->bind_result($user_email);
$stmt->fetch();
$stmt->close();
?>

<style>
.page-wrapper{max-width:1200px;margin:22px auto;padding:0 16px}
.profile-layout{display:flex;gap:20px;align-items:flex-start}
.profile-sidebar{width:240px;flex-shrink:0}
.profile-main{flex:1}

.profile-card{
  background:#fff;
  padding:20px;
  border-radius:10px;
  border:1px solid #e6edf9;
}

.form-section-title{
  margin:16px 0 8px;
  font-weight:700;
  color:#004aad;
}

.form-row{display:flex;gap:10px;margin-bottom:10px;flex-wrap:wrap}
.form-col{flex:1;min-width:160px}

.form-control{
  width:100%;
  height:38px;
  padding:6px 8px;
}

.readonly{
  background:#f1f3f6;
}

.actions{margin-top:20px;display:flex;gap:12px}

.btn-primary{
  background:#004aad;
  color:#fff;
  padding:9px 18px;
  border-radius:8px;
  border:0;
}

.btn-secondary{
  background:#f6f9ff;
  border:1px solid #d7e6fb;
  padding:8px 14px;
  border-radius:8px;
}

@media(max-width:980px){
  .profile-layout{flex-direction:column}
  .profile-sidebar{width:100%}
}
</style>

<div class="page-wrapper">
<div class="profile-layout">

<!-- SIDEBAR -->
<aside class="profile-sidebar">
  <?php include "../includes/profile_sidebar.php"; ?>
</aside>

<!-- MAIN -->
<main class="profile-card profile-main">

<h2>Edit Your Personal Details</h2>

<?php if (!empty($_GET['success'])): ?>
  <div style="background:#e6ffed;border:1px solid #28a745;padding:8px;border-radius:6px">
    ✅ Profile updated successfully
  </div>
<?php endif; ?>

<form action="edit-profile-process.php" method="POST" novalidate>

<!-- ================= PERSONAL DETAILS ================= -->
<h6 class="form-section-title">Personal Details</h6>

<div class="form-row">
  <div class="form-col">
    <label>Full Name</label>
    <input type="text" name="full_name" class="form-control" required>
  </div>

  <div class="form-col">
    <label>Gender</label>
    <select name="gender" class="form-control">
      <option value="">Select</option>
      <option>Male</option>
      <option>Female</option>
      <option>Other</option>
    </select>
  </div>

  <div class="form-col">
    <label>Date of Birth</label>
    <input type="date" name="dob" class="form-control">
  </div>

  <div class="form-col">
    <label>Birth Time</label>
    <input type="time" name="birth_time" class="form-control">
  </div>
</div>

<!-- ================= PRESENT LOCATION ================= -->
<h6 class="form-section-title">Present Location (India)</h6>

<div class="form-row">
  <div class="form-col">
    <label>State</label>
    <select id="present_state" name="state" class="form-control"></select>
  </div>

  <div class="form-col">
    <label>District</label>
    <select id="present_district" name="district" class="form-control" disabled></select>
  </div>

  <div class="form-col">
    <label>Village / Town</label>
    <select id="present_village" name="village" class="form-control" disabled></select>
  </div>

  <div class="form-col">
    <label>Pincode</label>
    <input type="text" id="present_pincode_view" class="form-control readonly" readonly>
    <input type="hidden" name="present_pincode" id="present_pincode">
  </div>
</div>

<!--<div class="form-row">
  <div class="form-col">
    <label>Present City (optional)</label>
    <input type="text" name="city" class="form-control">
  </div>
</div> -->

<input type="hidden" name="present_lat" id="present_lat">
<input type="hidden" name="present_lng" id="present_lng">

<!-- ================= BIRTH LOCATION ================= -->
<h6 class="form-section-title">Birth Location</h6>

<div class="form-row">
  <div class="form-col">
    <label>
      <input type="radio" name="birth_same_as_present" value="yes" checked>
      Same as present location
    </label>
  </div>
  <div class="form-col">
    <label>
      <input type="radio" name="birth_same_as_present" value="no">
      Different from present location
    </label>
  </div>
</div>

<div id="birthBlock" style="display:none">

<div class="form-row">
  <div class="form-col">
    <label>Birth State</label>
    <select id="birth_state" name="birth_state" class="form-control"></select>
  </div>

  <div class="form-col">
    <label>Birth District</label>
    <select id="birth_district" name="birth_district" class="form-control" disabled></select>
  </div>

  <div class="form-col">
    <label>Birth Village</label>
    <select id="birth_village" name="birth_village" class="form-control" disabled></select>
  </div>

  <div class="form-col">
    <label>Birth Pincode</label>
    <input type="text" id="birth_pincode_view" class="form-control readonly" readonly>
    <input type="hidden" name="birth_pincode" id="birth_pincode">
  </div>
</div>

<input type="hidden" name="birth_lat" id="birth_lat">
<input type="hidden" name="birth_lng" id="birth_lng">

</div>

<!-- ================= CONTACT DETAILS ================= -->
<h6 class="form-section-title">Contact Details</h6>

<div class="form-row">
  <div class="form-col">
    <label>Email</label>
    <input type="email" class="form-control readonly"
           value="<?= htmlspecialchars($user_email) ?>" readonly>
  </div>

  <div class="form-col">
    <label>Country Code</label>
    <input type="text" class="form-control readonly" value="+91" readonly>
    <input type="hidden" name="country_code" value="+91">
  </div>

  <div class="form-col">
    <label>Mobile</label>
    <input type="text" name="mobile" class="form-control">
  </div>
</div>

<div class="actions">
  <button type="submit" class="btn-primary">Update Profile</button>
  <a href="dashboard.php" class="btn-secondary">Cancel</a>
</div>

</form>

</main>
</div>
</div>

<script>
function fillSelect(sel, data, label){
  sel.innerHTML = `<option value="">${label}</option>`;
  data.forEach(v=>{
    const o = new Option(v.village || v, v.village || v);
    if(v.latitude){
      o.dataset.lat=v.latitude;
      o.dataset.lng=v.longitude;
      o.dataset.pin=v.pincode;
    }
    sel.add(o);
  });
  sel.disabled=false;
}

/* PRESENT */
fetch('../ajax/india-location.php?type=states')
.then(r=>r.json()).then(d=>fillSelect(present_state,d,'Select State'));

present_state.onchange=()=>{
  fetch(`../ajax/india-location.php?type=districts&state=${encodeURIComponent(present_state.value)}`)
  .then(r=>r.json()).then(d=>fillSelect(present_district,d,'Select District'));
};

present_district.onchange=()=>{
  fetch(`../ajax/india-location.php?type=villages&state=${encodeURIComponent(present_state.value)}&district=${encodeURIComponent(present_district.value)}`)
  .then(r=>r.json()).then(d=>fillSelect(present_village,d,'Select Village'));
};

present_village.onchange=()=>{
  const o=present_village.selectedOptions[0];
  present_lat.value=o.dataset.lat||'';
  present_lng.value=o.dataset.lng||'';
  present_pincode.value=o.dataset.pin||'';
  present_pincode_view.value=o.dataset.pin||'';
};

/* BIRTH TOGGLE */
document.querySelectorAll('input[name="birth_same_as_present"]').forEach(r=>{
  r.onchange=()=>birthBlock.style.display=(r.value==='no'&&r.checked)?'block':'none';
});

/* BIRTH */
fetch('../ajax/india-location.php?type=states')
.then(r=>r.json()).then(d=>fillSelect(birth_state,d,'Select State'));

birth_state.onchange=()=>{
  fetch(`../ajax/india-location.php?type=districts&state=${encodeURIComponent(birth_state.value)}`)
  .then(r=>r.json()).then(d=>fillSelect(birth_district,d,'Select District'));
};

birth_district.onchange=()=>{
  fetch(`../ajax/india-location.php?type=villages&state=${encodeURIComponent(birth_state.value)}&district=${encodeURIComponent(birth_district.value)}`)
  .then(r=>r.json()).then(d=>fillSelect(birth_village,d,'Select Village'));
};

birth_village.onchange=()=>{
  const o=birth_village.selectedOptions[0];
  birth_lat.value=o.dataset.lat||'';
  birth_lng.value=o.dataset.lng||'';
  birth_pincode.value=o.dataset.pin||'';
  birth_pincode_view.value=o.dataset.pin||'';
};
</script>

<?php include "../includes/footer.php"; ?>
