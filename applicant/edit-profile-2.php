<?php
/* ======================================================
   FINAL SAARC-only edit-profile.php
   Layout FIXED + Full SAARC JSON + Alphabetical + Scroll
   ====================================================== */

session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

require_once "../config/config.php";
include "../includes/header.php";

/* Header opens container → close once */
echo "</div>";

$app_id = (int) $_SESSION['applicant_id'];

/* Fetch applicant */
$stmt = $conn->prepare("SELECT * FROM job_seekers WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $app_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "<div style='padding:20px'><h3>User not found</h3></div>";
    include "../includes/footer.php";
    exit;
}

/* Presets */
$P_country  = $user['country'] ?? '';
$P_state    = $user['state'] ?? '';
$P_district = $user['district'] ?? '';
$P_tehsil   = $user['tehsil'] ?? '';
$P_city     = $user['city'] ?? '';
$P_pin      = $user['present_pincode'] ?? '';
$P_lat      = $user['present_lat'] ?? '';
$P_lng      = $user['present_lng'] ?? '';

$B_country  = $user['birth_country'] ?? '';
$B_state    = $user['birth_state'] ?? '';
$B_district = $user['birth_district'] ?? '';
$B_tehsil   = $user['birth_tehsil'] ?? '';
$B_pin      = $user['birth_pincode'] ?? '';
$B_lat      = $user['birth_lat'] ?? '';
$B_lng      = $user['birth_lng'] ?? '';

if (!$B_country && $P_country) $B_country = $P_country;

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES); }
?>

<style>
/* ===== Layout ===== */
.page-wrapper{max-width:1200px;margin:22px auto;padding:0 16px}
.profile-layout{display:flex;gap:20px;align-items:flex-start}
.profile-sidebar{width:240px;flex-shrink:0}
.profile-main{flex:1;min-width:0}

/* ===== Card ===== */
.profile-card{background:#fff;padding:20px;border-radius:10px;border:1px solid #e6edf9;
box-shadow:0 6px 20px rgba(9,30,66,.04)}

.page-title{color:#004aad;margin-bottom:12px}
.form-row{display:flex;gap:10px;margin-bottom:10px;flex-wrap:wrap}
.form-col{flex:1;min-width:160px}
.form-control{width:100%;height:38px;padding:6px 8px}
.form-section-title{margin:14px 0 6px;font-weight:700;color:#004aad}

.actions{margin-top:18px;display:flex;gap:12px}
.btn-primary{background:#004aad;color:#fff;padding:9px 18px;border-radius:8px;border:0}
.btn-secondary{background:#f6f9ff;border:1px solid #d7e6fb;padding:8px 14px;border-radius:8px}

.flash-success{background:#e6ffed;border:1px solid #28a745;color:#155724;
padding:10px;border-radius:6px;margin-bottom:14px}

/* ===== Dropdown scroll ===== */
select.form-control{max-height:220px;overflow-y:auto}
select.form-control option{padding:6px}

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

<h2 class="page-title">Edit Your Personal Details</h2>

<?php if (!empty($_GET['success']) || !empty($_SESSION['form_success'])): ?>
<div class="flash-success">✅ Profile updated successfully.</div>
<?php unset($_SESSION['form_success']); endif; ?>

<form action="edit-profile-process.php" method="POST" novalidate>

<!-- PERSONAL -->
<div class="form-row">
  <div class="form-col">
    <label>Full Name</label>
    <input type="text" name="full_name" class="form-control" value="<?=h($user['full_name'])?>">
  </div>
  <div class="form-col">
    <label>Gender</label>
    <select name="gender" class="form-control">
      <option value="">Select</option>
      <option <?=($user['gender']=='Male')?'selected':''?>>Male</option>
      <option <?=($user['gender']=='Female')?'selected':''?>>Female</option>
      <option <?=($user['gender']=='Other')?'selected':''?>>Other</option>
    </select>
  </div>
  <div class="form-col">
    <label>Date of Birth</label>
    <input type="date" name="dob" class="form-control" value="<?=h($user['dob'])?>">
  </div>
</div>

<!-- PRESENT -->
<h6 class="form-section-title">Present Address</h6>
<div class="form-row">
  <div class="form-col"><label>Country</label><select id="country" name="country" class="form-control"></select></div>
  <div class="form-col"><label>State</label><select id="level1" name="state" class="form-control"></select></div>
  <div class="form-col"><label>District</label><select id="level2" name="district" class="form-control"></select></div>
  <div class="form-col"><label>Tehsil</label><select id="level3" name="tehsil" class="form-control"></select></div>
</div>

<div class="form-row">
  <div class="form-col"><label>Present City</label><input type="text" name="present_city" class="form-control" value="<?=h($P_city)?>"></div>
  <div class="form-col"><label>Pincode</label><input type="text" name="present_pincode" class="form-control" value="<?=h($P_pin)?>"></div>
</div>

<input type="hidden" name="present_lat" id="present_lat" value="<?=h($P_lat)?>">
<input type="hidden" name="present_lng" id="present_lng" value="<?=h($P_lng)?>">

<!-- BIRTH -->
<h6 class="form-section-title">Birth Details</h6>
<div class="form-row">
  <div class="form-col"><label>Birth Country</label><select id="birth_country" name="birth_country" class="form-control"></select></div>
  <div class="form-col"><label>Birth State</label><select id="birth_state" name="birth_state" class="form-control"></select></div>
  <div class="form-col"><label>Birth District</label><select id="birth_district" name="birth_district" class="form-control"></select></div>
  <div class="form-col"><label>Birth Tehsil</label><select id="birth_tehsil" name="birth_tehsil" class="form-control"></select></div>
</div>

<div class="form-row">
  <div class="form-col"><label>Birth Pincode</label><input type="text" name="birth_pincode" class="form-control" value="<?=h($B_pin)?>"></div>
  <div class="form-col"><label>Birth Time</label><input type="time" name="birth_time" class="form-control" value="<?=h($user['birth_time'])?>"></div>
</div>

<input type="hidden" name="birth_lat" id="birth_lat" value="<?=h($B_lat)?>">
<input type="hidden" name="birth_lng" id="birth_lng" value="<?=h($B_lng)?>">

<!-- CONTACT -->
<h6 class="form-section-title">Contact</h6>
<div class="form-row">
  <div class="form-col"><label>Email</label><input type="email" class="form-control" value="<?=h($user['email'])?>" disabled></div>
  <div class="form-col"><label>Country Code</label><input type="text" name="country_code" class="form-control" value="<?=h($user['country_code'])?>"></div>
  <div class="form-col"><label>Mobile</label><input type="text" name="mobile" class="form-control" value="<?=h($user['mobile'])?>"></div>
</div>

<div class="actions">
  <button class="btn-primary">Update Profile</button>
  <a href="dashboard.php" class="btn-secondary">Cancel</a>
</div>

</form>
</main>

<!-- ================= SAARC JSON JS (SORTED + FULL) ================= -->

<!-- REPLACE  


<script>
(function(){
const saarcPath="/jobsweb/data/saarc_subdistricts.json";
const countryEl=country,level1El=level1,level2El=level2,level3El=level3;
const birthCountryEl=birth_country,birthStateEl=birth_state,birthDistrictEl=birth_district,birthTehsilEl=birth_tehsil;

const P={c:"<?=h($P_country)?>",s:"<?=h($P_state)?>",d:"<?=h($P_district)?>",t:"<?=h($P_tehsil)?>"};
const B={c:"<?=h($B_country)?>",s:"<?=h($B_state)?>",d:"<?=h($B_district)?>",t:"<?=h($B_tehsil)?>"};

const clear=(el,l='Select')=>{el.innerHTML=`<option value="">${l}</option>`;el.disabled=true};

fetch(saarcPath).then(r=>r.json()).then(j=>{
const countries=(j.countries||j).sort((a,b)=>a.name.localeCompare(b.name));

countries.forEach(c=>{
  countryEl.add(new Option(c.name,c.code));
  birthCountryEl.add(new Option(c.name,c.code));
});

const loadStates=(c,st,di,te,pS,pD,pT)=>{
  clear(st,'Select State');clear(di);clear(te);
  const C=countries.find(x=>x.code===c); if(!C) return;
  C.level1.sort((a,b)=>a.name.localeCompare(b.name))
   .forEach(s=>st.add(new Option(s.name,s.name)));
  st.disabled=false;
  if(pS){st.value=pS;loadDistricts(c,pS,di,te,pD,pT)}
};

const loadDistricts=(c,s,di,te,pD,pT)=>{
  clear(di,'Select District');clear(te);
  const S=countries.find(x=>x.code===c)?.level1.find(x=>x.name===s); if(!S) return;
  S.level2.sort((a,b)=>a.name.localeCompare(b.name))
   .forEach(d=>di.add(new Option(d.name,d.name)));
  di.disabled=false;
  if(pD){di.value=pD;loadTehsils(c,s,pD,te,pT)}
};

const loadTehsils=(c,s,d,te,pT)=>{
  clear(te,'Select Tehsil');
  const D=countries.find(x=>x.code===c)?.level1.find(x=>x.name===s)?.level2.find(x=>x.name===d); if(!D) return;
  D.level3.sort((a,b)=>a.localeCompare(b)).forEach(t=>te.add(new Option(t,t)));
  te.disabled=false;
  if(pT) te.value=pT;
};

if(P.c) loadStates(P.c,level1El,level2El,level3El,P.s,P.d,P.t);
if(B.c) loadStates(B.c,birthStateEl,birthDistrictEl,birthTehsilEl,B.s,B.d,B.t);

countryEl.onchange=()=>loadStates(countryEl.value,level1El,level2El,level3El);
level1El.onchange=()=>loadDistricts(countryEl.value,level1El.value,level2El,level3El);
level2El.onchange=()=>loadTehsils(countryEl.value,level1El.value,level2El.value,level3El);

birthCountryEl.onchange=()=>loadStates(birthCountryEl.value,birthStateEl,birthDistrictEl,birthTehsilEl);
birthStateEl.onchange=()=>loadDistricts(birthCountryEl.value,birthStateEl.value,birthDistrictEl,birthTehsilEl);
birthDistrictEl.onchange=()=>loadTehsils(birthCountryEl.value,birthStateEl.value,birthDistrictEl.value,birthTehsilEl);
});
})();
</script> -->

<script>
(function(){

const saarcPath = "/jobsweb/data/saarc_subdistricts.json";

/* Elements */
const countryEl = document.getElementById('country');
const stateEl   = document.getElementById('level1');
const distEl    = document.getElementById('level2');
const tehsilEl  = document.getElementById('level3');

const bCountryEl = document.getElementById('birth_country');
const bStateEl   = document.getElementById('birth_state');
const bDistEl    = document.getElementById('birth_district');
const bTehsilEl  = document.getElementById('birth_tehsil');

/* Presets from DB */
const P = {
  c: "<?= h($P_country) ?>",
  s: "<?= h($P_state) ?>",
  d: "<?= h($P_district) ?>",
  t: "<?= h($P_tehsil) ?>"
};

const B = {
  c: "<?= h($B_country) ?>",
  s: "<?= h($B_state) ?>",
  d: "<?= h($B_district) ?>",
  t: "<?= h($B_tehsil) ?>"
};

function reset(el, label){
  el.innerHTML = `<option value="">${label}</option>`;
  el.disabled = false;
}

fetch(saarcPath)
.then(r => r.json())
.then(data => {

  const countries = (data.countries || data)
    .filter(c => c.code && c.level1)
    .sort((a,b)=>a.name.localeCompare(b.name));

  /* Countries */
  reset(countryEl,'Select Country');
  reset(bCountryEl,'Select Country');

  countries.forEach(c=>{
    countryEl.add(new Option(c.name, c.code));
    bCountryEl.add(new Option(c.name, c.code));
  });

  function loadStates(code, sEl, dEl, tEl, ps, pd, pt){
    reset(sEl,'Select State');
    reset(dEl,'Select District');
    reset(tEl,'Select Tehsil');

    const C = countries.find(x=>x.code===code);
    if(!C) return;

    C.level1
      .sort((a,b)=>a.name.localeCompare(b.name))
      .forEach(s=>sEl.add(new Option(s.name,s.name)));

    if(ps && C.level1.some(x=>x.name===ps)){
      sEl.value = ps;
      loadDistricts(code, ps, dEl, tEl, pd, pt);
    }
  }

  function loadDistricts(code, state, dEl, tEl, pd, pt){
    reset(dEl,'Select District');
    reset(tEl,'Select Tehsil');

    const C = countries.find(x=>x.code===code);
    const S = C?.level1.find(x=>x.name===state);
    if(!S) return;

    S.level2
      .sort((a,b)=>a.name.localeCompare(b.name))
      .forEach(d=>dEl.add(new Option(d.name,d.name)));

    if(pd && S.level2.some(x=>x.name===pd)){
      dEl.value = pd;
      loadTehsils(code, state, pd, tEl, pt);
    }
  }

  function loadTehsils(code, state, dist, tEl, pt){
    reset(tEl,'Select Tehsil');

    const C = countries.find(x=>x.code===code);
    const S = C?.level1.find(x=>x.name===state);
    const D = S?.level2.find(x=>x.name===dist);
    if(!D) return;

    D.level3
      .sort((a,b)=>a.localeCompare(b))
      .forEach(t=>tEl.add(new Option(t,t)));

    if(pt && D.level3.includes(pt)){
      tEl.value = pt;
    }
  }

  /* Init with DB values */
  if(P.c) loadStates(P.c, stateEl, distEl, tehsilEl, P.s, P.d, P.t);
  if(B.c) loadStates(B.c, bStateEl, bDistEl, bTehsilEl, B.s, B.d, B.t);

  /* Events */
  countryEl.onchange = ()=> loadStates(countryEl.value, stateEl, distEl, tehsilEl);
  stateEl.onchange   = ()=> loadDistricts(countryEl.value, stateEl.value, distEl, tehsilEl);
  distEl.onchange    = ()=> loadTehsils(countryEl.value, stateEl.value, distEl.value, tehsilEl);

  bCountryEl.onchange = ()=> loadStates(bCountryEl.value, bStateEl, bDistEl, bTehsilEl);
  bStateEl.onchange   = ()=> loadDistricts(bCountryEl.value, bStateEl.value, bDistEl, bTehsilEl);
  bDistEl.onchange    = ()=> loadTehsils(bCountryEl.value, bStateEl.value, bDistEl.value, bTehsilEl);

});
})();
</script>





</div>
</div>

<?php include "../includes/footer.php"; ?>
