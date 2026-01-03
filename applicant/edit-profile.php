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

/* Fetch all user data */
$stmt = $conn->prepare("SELECT * FROM job_seekers WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $app_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found");
}

// Check if this is first time profile edit
$is_first_time = empty($user['full_name']) || empty($user['mobile']);
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
  cursor:pointer;
}

.btn-secondary{
  background:#f6f9ff;
  border:1px solid #d7e6fb;
  padding:8px 14px;
  border-radius:8px;
  text-decoration:none;
  color:#004aad;
}

.btn-next{
  background:#28a745;
  color:#fff;
  padding:9px 18px;
  border-radius:8px;
  border:0;
  cursor:pointer;
  text-decoration:none;
  display:inline-block;
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

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
  <h2>Edit Your Personal Details</h2>
  <a href="upload-photo.php" class="btn-next">NEXT →</a>
</div>

<?php if (!empty($_GET['success'])): ?>
  <div style="background:#e6ffed;border:1px solid #28a745;padding:8px;border-radius:6px;margin-bottom:12px">
    ✅ Profile updated successfully
    <?php if (!empty($_GET['skipped'])): ?>
      <br><small style="color:#ff9800">⚠️ Please verify your email later from your profile settings.</small>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php if (!empty($_GET['verified'])): ?>
  <div style="background:#e6ffed;border:1px solid #28a745;padding:8px;border-radius:6px;margin-bottom:12px">
    ✅ Email and Mobile verified successfully!
  </div>
<?php endif; ?>

<form action="edit-profile-process.php" method="POST" novalidate>

<!-- ================= PERSONAL DETAILS ================= -->
<h6 class="form-section-title">Personal Details</h6>

<div class="form-row">
  <div class="form-col">
    <label>Full Name</label>
    <input type="text" name="full_name" class="form-control" 
           value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
  </div>

  <div class="form-col">
    <label>Gender</label>
    <select name="gender" class="form-control">
      <option value="">Select</option>
      <option <?= ($user['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
      <option <?= ($user['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
      <option <?= ($user['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
    </select>
  </div>

  <div class="form-col">
    <label>Date of Birth</label>
    <input type="date" name="dob" class="form-control" 
           value="<?= htmlspecialchars($user['dob'] ?? '') ?>">
  </div>

  <div class="form-col">
    <label>Birth Time</label>
    <input type="time" name="birth_time" class="form-control" 
           value="<?= htmlspecialchars($user['birth_time'] ?? '') ?>">
  </div>
</div>

<!-- ================= PRESENT LOCATION ================= -->
<h6 class="form-section-title">Present Location (India)</h6>

<div class="form-row">
  <div class="form-col">
    <label>State</label>
    <select id="present_state" name="state" class="form-control" 
            data-selected="<?= htmlspecialchars($user['state'] ?? '') ?>"></select>
  </div>

  <div class="form-col">
    <label>District</label>
    <select id="present_district" name="district" class="form-control" disabled
            data-selected="<?= htmlspecialchars($user['district'] ?? '') ?>"></select>
  </div>

  <div class="form-col">
    <label>Village / Town</label>
    <select id="present_village" name="village" class="form-control" disabled
            data-selected="<?= htmlspecialchars($user['village'] ?? '') ?>"></select>
  </div>

  <div class="form-col">
    <label>Pincode</label>
    <input type="text" id="present_pincode_view" class="form-control readonly" readonly
           value="<?= htmlspecialchars($user['present_pincode'] ?? '') ?>">
    <input type="hidden" name="present_pincode" id="present_pincode" 
           value="<?= htmlspecialchars($user['present_pincode'] ?? '') ?>">
  </div>
</div>

<input type="hidden" name="present_lat" id="present_lat" 
       value="<?= htmlspecialchars($user['present_lat'] ?? '') ?>">
<input type="hidden" name="present_lng" id="present_lng" 
       value="<?= htmlspecialchars($user['present_lng'] ?? '') ?>">

<!-- ================= BIRTH LOCATION ================= -->
<h6 class="form-section-title">Birth Location</h6>

<?php
// Check if birth location is same as present
$birth_same = 'yes';
if (!empty($user['birth_state']) && $user['birth_state'] !== $user['state']) {
    $birth_same = 'no';
}
?>

<div class="form-row">
  <div class="form-col">
    <label>
      <input type="radio" name="birth_same_as_present" value="yes" 
             <?= $birth_same === 'yes' ? 'checked' : '' ?>>
      Same as present location
    </label>
  </div>
  <div class="form-col">
    <label>
      <input type="radio" name="birth_same_as_present" value="no"
             <?= $birth_same === 'no' ? 'checked' : '' ?>>
      Different from present location
    </label>
  </div>
</div>

<div id="birthBlock" style="display:<?= $birth_same === 'no' ? 'block' : 'none' ?>">

<div class="form-row">
  <div class="form-col">
    <label>Birth State</label>
    <select id="birth_state" name="birth_state" class="form-control"
            data-selected="<?= htmlspecialchars($user['birth_state'] ?? '') ?>"></select>
  </div>

  <div class="form-col">
    <label>Birth District</label>
    <select id="birth_district" name="birth_district" class="form-control" disabled
            data-selected="<?= htmlspecialchars($user['birth_district'] ?? '') ?>"></select>
  </div>

  <div class="form-col">
    <label>Birth Village</label>
    <select id="birth_village" name="birth_village" class="form-control" disabled
            data-selected="<?= htmlspecialchars($user['birth_village'] ?? '') ?>"></select>
  </div>

  <div class="form-col">
    <label>Birth Pincode</label>
    <input type="text" id="birth_pincode_view" class="form-control readonly" readonly
           value="<?= htmlspecialchars($user['birth_pincode'] ?? '') ?>">
    <input type="hidden" name="birth_pincode" id="birth_pincode"
           value="<?= htmlspecialchars($user['birth_pincode'] ?? '') ?>">
  </div>
</div>

<input type="hidden" name="birth_lat" id="birth_lat"
       value="<?= htmlspecialchars($user['birth_lat'] ?? '') ?>">
<input type="hidden" name="birth_lng" id="birth_lng"
       value="<?= htmlspecialchars($user['birth_lng'] ?? '') ?>">

</div>

<!-- ================= CONTACT DETAILS ================= -->
<h6 class="form-section-title">Contact Details</h6>

<div class="form-row">
  <div class="form-col">
    <label>Email <?= $user['email_verified'] ? '<span style="color:#28a745">✓ Verified</span>' : '<span style="color:#dc3545">✗ Not Verified</span>' ?></label>
    <input type="email" class="form-control readonly"
           value="<?= htmlspecialchars($user['email']) ?>" readonly>
  </div>

  <div class="form-col">
    <label>Country Code</label>
    <input type="text" class="form-control readonly" value="+91" readonly>
    <input type="hidden" name="country_code" value="+91">
  </div>

  <div class="form-col">
    <label>Mobile 
    <!-- DISABLED - Mobile verification status -->
    <!-- <?= $user['mobile_verified'] ? '<span style="color:#28a745">✓ Verified</span>' : '<span style="color:#dc3545">✗ Not Verified</span>' ?> -->
    </label>
    <input type="text" name="mobile" class="form-control"
           value="<?= htmlspecialchars($user['mobile'] ?? '') ?>">
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
const userData = <?= json_encode($user) ?>;

function fillSelect(sel, data, label, selectedValue = ''){
  sel.innerHTML = `<option value="">${label}</option>`;
  data.forEach(v=>{
    const o = new Option(v.village || v, v.village || v);
    if(v.latitude){
      o.dataset.lat=v.latitude;
      o.dataset.lng=v.longitude;
      o.dataset.pin=v.pincode;
    }
    if(selectedValue && (v.village || v) === selectedValue){
      o.selected = true;
    }
    sel.add(o);
  });
  sel.disabled=false;
}

/* PRESENT LOCATION - Load with existing data */
fetch('../ajax/india-location.php?type=states')
.then(r=>r.json()).then(d=>{
  const selected = present_state.dataset.selected;
  fillSelect(present_state, d, 'Select State', selected);
  
  if(selected){
    fetch(`../ajax/india-location.php?type=districts&state=${encodeURIComponent(selected)}`)
    .then(r=>r.json()).then(d=>{
      const distSelected = present_district.dataset.selected;
      fillSelect(present_district, d, 'Select District', distSelected);
      
      if(distSelected){
        fetch(`../ajax/india-location.php?type=villages&state=${encodeURIComponent(selected)}&district=${encodeURIComponent(distSelected)}`)
        .then(r=>r.json()).then(d=>{
          const villageSelected = present_village.dataset.selected;
          fillSelect(present_village, d, 'Select Village', villageSelected);
        });
      }
    });
  }
});

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

/* BIRTH LOCATION - Load with existing data */
fetch('../ajax/india-location.php?type=states')
.then(r=>r.json()).then(d=>{
  const selected = birth_state.dataset.selected;
  fillSelect(birth_state, d, 'Select State', selected);
  
  if(selected){
    fetch(`../ajax/india-location.php?type=districts&state=${encodeURIComponent(selected)}`)
    .then(r=>r.json()).then(d=>{
      const distSelected = birth_district.dataset.selected;
      fillSelect(birth_district, d, 'Select District', distSelected);
      
      if(distSelected){
        fetch(`../ajax/india-location.php?type=villages&state=${encodeURIComponent(selected)}&district=${encodeURIComponent(distSelected)}`)
        .then(r=>r.json()).then(d=>{
          const villageSelected = birth_village.dataset.selected;
          fillSelect(birth_village, d, 'Select Village', villageSelected);
        });
      }
    });
  }
});

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