<?php
/* FILE: /applicant/upload-resume.php
   PURPOSE: Upload/Replace resume with PDF, DOC, DOCX support
*/

session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

include "../config/config.php";
include "../includes/header.php";

$app_id = intval($_SESSION['applicant_id']);

/* Fetch existing resume + name */
$stmt = $conn->prepare("SELECT resume_file, full_name FROM job_seekers WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $app_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

$resume_file = $user['resume_file'] ?? '';
$uploaded_date = "";
$file_ext = "";

if (!empty($resume_file)) {
    $file_ext = strtolower(pathinfo($resume_file, PATHINFO_EXTENSION));

    // Extract timestamp from name: resume_6_1737007567.pdf
    $parts = explode("_", $resume_file);
    $ts = explode(".", $parts[2] ?? "")[0];

    if (is_numeric($ts)) {
        $uploaded_date = date("d-m-Y", intval($ts));
    }
}

// Determine file icon based on extension
$file_icon = "📄";
if ($file_ext === "pdf") {
    $file_icon = "📕";
} elseif ($file_ext === "doc" || $file_ext === "docx") {
    $file_icon = "📘";
}
?>

<style>
.wrapper { display:flex; margin-top:30px; max-width:1200px; margin-left:auto; margin-right:auto; gap:20px; }
.sidebar { width:240px; background:#0a4c90; color:#fff; padding:20px; border-radius:8px; flex-shrink:0; }
.sidebar a{ display:block; padding:12px; color:#fff; text-decoration:none; margin-bottom:8px; border-radius:5px; font-weight:600; }
.sidebar a.active{ background:#06376a; }
.content-box { flex:1; background:#fff; padding:25px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.08); }

.page-header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
    padding-bottom:15px;
    border-bottom:2px solid #e1e5ee;
}

.page-header h3 {
    margin:0;
    color:#004aad;
    font-weight:bold;
}

.nav-buttons {
    display:flex;
    gap:10px;
}

.btn-back {
    background:#6c757d;
    color:#fff;
    padding:9px 18px;
    border-radius:8px;
    border:0;
    cursor:pointer;
    text-decoration:none;
    display:inline-block;
    font-weight:600;
}

.btn-back:hover {
    background:#5a6268;
    color:#fff;
}

.btn-home {
    background:#007bff;
    color:#fff;
    padding:9px 18px;
    border-radius:8px;
    border:0;
    cursor:pointer;
    text-decoration:none;
    display:inline-block;
    font-weight:600;
}

.btn-home:hover {
    background:#0056b3;
    color:#fff;
}

.success-msg {
    background:#d1e7dd;
    color:#0f5132;
    padding:12px;
    border-radius:6px;
    margin-bottom:16px;
    border:1px solid #a3cfbb;
}

.error-msg {
    background:#f8d7da;
    color:#842029;
    padding:12px;
    border-radius:6px;
    margin-bottom:16px;
    border:1px solid #f5c2c7;
}

.info-box {
    background:#cff4fc;
    color:#055160;
    padding:12px;
    border-radius:6px;
    margin-bottom:16px;
    border:1px solid #9eeaf9;
}

.resume-info {
    background:#f6f9ff;
    padding:14px;
    border-radius:8px;
    margin-bottom:16px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    border:1px solid #d7e6fb;
}

.resume-meta { font-size:14px; color:#334155; font-weight:600; }
.resume-meta small { display:block; font-weight:500; color:#666; margin-top:4px; font-size:13px; }

.file-type-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    margin-left: 8px;
}

.badge-pdf {
    background: #dc3545;
    color: #fff;
}

.badge-doc, .badge-docx {
    background: #0d6efd;
    color: #fff;
}

.btn-download {
    padding:8px 16px;
    border-radius:6px;
    background:#004aad;
    border:1px solid #004aad;
    color:#fff;
    font-weight:600;
    text-decoration:none;
    display:inline-block;
}

.btn-download:hover {
    background:#003a8c;
    color:#fff;
}

.form-label {
    display:block;
    font-weight:600;
    margin-bottom:6px;
    color:#334155;
}

.form-control {
    width:100%;
    padding:10px;
    border:1px solid #d1d5db;
    border-radius:6px;
    font-size:14px;
}

.small-note { 
    color: #666; 
    font-size:13px; 
    margin-top:8px;
    line-height:1.6;
}

.btn-upload {
    padding:12px 24px;
    background: #28a745;
    color:#fff;
    border:none;
    border-radius:8px;
    font-weight:700;
    cursor: pointer;
    margin-top:16px;
}

.btn-upload:hover { 
    background: #218838; 
}

.btn-cancel {
    padding:12px 24px;
    background: #6c757d;
    color:#fff;
    border:none;
    border-radius:8px;
    font-weight:700;
    cursor: pointer;
    margin-left:10px;
    text-decoration:none;
    display:inline-block;
}

.btn-cancel:hover { 
    background: #5a6268;
    color:#fff;
}

.viewer-section {
    margin-top:24px; 
    padding-top:24px; 
    border-top:2px solid #e1e5ee;
}

.viewer-notice {
    background: #fff3cd;
    border: 1px solid #ffc107;
    color: #856404;
    padding: 14px;
    border-radius: 6px;
    margin-top: 12px;
    font-size: 14px;
    line-height:1.6;
}

@media(max-width:980px){
  .wrapper{flex-direction:column}
  .sidebar{width:100%}
}
</style>

<div class="container">
  <div class="wrapper">

    <!-- SIDEBAR -->
    <?php include "../includes/profile_sidebar.php"; ?>

    <!-- MAIN CONTENT -->
    <div class="content-box">
      
      <!-- PAGE HEADER -->
      <div class="page-header">
        <h3>📤 Upload / Replace Resume</h3>
        <div class="nav-buttons">
          <a href="preferred-industry.php" class="btn-back">← BACK</a>
          <a href="dashboard.php" class="btn-home">HOME →</a>
        </div>
      </div>

      <!-- SUCCESS MESSAGE -->
      <?php if (isset($_SESSION['resume_msg'])): ?>
        <div class="success-msg">
          ✅ <?= htmlspecialchars($_SESSION['resume_msg']) ?>
        </div>
        <?php unset($_SESSION['resume_msg']); ?>
      <?php endif; ?>

      <!-- ERROR MESSAGE -->
      <?php if (isset($_SESSION['resume_error'])): ?>
        <div class="error-msg">
          ❌ <?= htmlspecialchars($_SESSION['resume_error']) ?>
        </div>
        <?php unset($_SESSION['resume_error']); ?>
      <?php endif; ?>

      <!-- INFO: ALLOWED FILE TYPES -->
      <div class="info-box">
        <strong>ℹ️ Accepted File Formats:</strong> PDF, DOC, DOCX<br>
        <strong>📏 Maximum File Size:</strong> 5MB<br>
        <strong>⚠️ Note:</strong> Uploading a new file will replace your existing resume.
      </div>

      <!-- EXISTING RESUME -->
      <?php if (!empty($resume_file)): ?>
      <div class="resume-info">
        <div class="resume-meta">
          <?= $file_icon ?> <strong><?= htmlspecialchars($resume_file) ?></strong>
          <span class="file-type-badge badge-<?= $file_ext ?>">
            <?= strtoupper($file_ext) ?>
          </span>
          <?php if ($uploaded_date): ?>
            <small>📅 Uploaded on: <?= $uploaded_date ?></small>
          <?php endif; ?>
        </div>

        <a class="btn-download"
           href="/jobsweb/uploads/resume/<?= rawurlencode($resume_file) ?>"
           download>
           🔽 Download
        </a>
      </div>
      <?php else: ?>
      <div class="viewer-notice" style="margin-bottom:20px;">
        <strong>📋 No Resume Uploaded</strong><br>
        Please upload your resume using the form below.
      </div>
      <?php endif; ?>

      <!-- UPLOAD FORM -->
      <form action="upload-resume-process.php" method="POST" enctype="multipart/form-data" onsubmit="return validateFile();">
        
        <label class="form-label">📎 Choose Resume File</label>
        <input type="file" 
               id="resumeFile" 
               name="resume" 
               accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" 
               class="form-control" 
               required>

        <div class="small-note">
          ✓ Supported formats: <strong>PDF, DOC, DOCX</strong><br>
          ✓ Maximum file size: <strong>5MB</strong><br>
          ✓ Your current resume will be replaced after upload
        </div>

        <div>
          <button type="submit" class="btn-upload">
            📤 Upload / Replace Resume
          </button>

          <a href="dashboard.php" class="btn-cancel">
            Cancel
          </a>
        </div>
      </form>

      <!-- PREVIEW SECTION -->
      <?php if (!empty($resume_file)): ?>
      <div class="viewer-section">
        <h4 style="color:#004aad; margin-bottom:16px;">📋 Preview Your Resume</h4>

        <?php if ($file_ext === "pdf"): ?>
          <iframe src="/jobsweb/uploads/resume/<?= rawurlencode($resume_file) ?>"
                  style="width:100%; height:600px; border:2px solid #e1e5ee; border-radius:8px;">
          </iframe>

        <?php elseif ($file_ext === "doc" || $file_ext === "docx"): ?>
          <div class="viewer-notice">
            <strong>📘 Microsoft Word Document</strong><br>
            Word documents (.doc / .docx) cannot be previewed directly in the browser.<br>
            Please download the file to view its contents.
          </div>
          
          <div style="margin-top:16px;">
            <a href="/jobsweb/uploads/resume/<?= rawurlencode($resume_file) ?>" 
               class="btn-download" 
               download>
              🔽 Download <?= strtoupper($file_ext) ?> File
            </a>
          </div>

          <!-- Google Docs Viewer Alternative -->
          <div style="margin-top:16px;">
            <p style="color:#666; font-size:13px; margin-bottom:8px;">
              <strong>Alternative:</strong> Try viewing with Google Docs Viewer
            </p>
            <a href="https://docs.google.com/viewer?url=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . '/jobsweb/uploads/resume/' . $resume_file) ?>&embedded=true" 
               target="_blank" 
               class="btn-download"
               style="background:#34a853;">
              🌐 Open in Google Docs Viewer
            </a>
          </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<script>
function validateFile() {
    const fileInput = document.getElementById("resumeFile");
    const file = fileInput.files[0];
    
    if (!file) {
        alert("⚠️ Please select a file to upload.");
        return false;
    }

    // Check file size (5MB = 5 * 1024 * 1024 bytes)
    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
        alert("❌ File Too Large!\n\nMaximum allowed: 5MB\nYour file size: " + fileSizeMB + "MB\n\nPlease choose a smaller file.");
        return false;
    }

    // Check file extension
    const fileName = file.name.toLowerCase();
    const allowedExtensions = ['.pdf', '.doc', '.docx'];
    const isValidExtension = allowedExtensions.some(ext => fileName.endsWith(ext));
    
    if (!isValidExtension) {
        alert("❌ Invalid File Type!\n\nPlease upload only:\n• PDF files (.pdf)\n• Word documents (.doc, .docx)");
        return false;
    }

    // Show loading state
    const submitBtn = document.querySelector('.btn-upload');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '⏳ Uploading... Please wait';
    submitBtn.disabled = true;

    // Re-enable if there's an error
    setTimeout(function() {
        if (submitBtn.disabled) {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }, 30000); // 30 seconds timeout

    return true;
}

// Display selected file info
document.getElementById('resumeFile').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const fileSizeKB = (file.size / 1024).toFixed(2);
        const fileExt = file.name.split('.').pop().toUpperCase();
        console.log('Selected: ' + file.name + ' (' + fileSizeKB + ' KB, ' + fileExt + ')');
    }
});
</script>

<?php include "../includes/footer.php"; ?>