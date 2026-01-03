<?php
/* FILE: /applicant/upload-resume.php
   PURPOSE: Upload/Replace resume with correct theme layout + sidebar
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
?>

<style>
.page-wrapper { max-width: 1100px; margin: 28px auto; padding: 0 16px; }
.profile-grid { display: grid; grid-template-columns: 260px 1fr; gap: 20px; align-items: start; }
@media (max-width: 880px) { .profile-grid { grid-template-columns: 1fr; } }

.content-card {
    background: #fff;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    border: 1px solid #e1e5ee;
}

.resume-info {
    background: #f6f9ff;
    padding: 12px 14px;
    border-radius: 8px;
    margin-bottom: 12px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
}

.resume-meta { font-size:14px; color:#334155; font-weight:600; }
.resume-meta small { display:block; font-weight:500; color:#666; margin-top:4px; font-size:13px; }

.btn-upload {
    padding:10px 16px;
    background: #004aad;
    color:#fff;
    border:none;
    border-radius:8px;
    font-weight:700;
}
.btn-upload:hover { background: #003a8c; }

.small-note { color: #666; font-size:13px; margin-top:6px; }

.btn-sm-outline {
    padding:8px 12px;
    border-radius:8px;
    background:transparent;
    border:1px solid #d7e6fb;
    color:#004aad;
    font-weight:700;
    text-decoration:none;
}

.btn-sm-outline:hover {
    background:#f6f9ff;
    color:#004aad;
}

.page-header-nav {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.page-header-nav h3 {
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
    padding:10px;
    border-radius:6px;
    margin-bottom:10px;
    border:1px solid #a3cfbb;
}
</style>

<div class="page-wrapper">
    <div class="profile-grid">

        <!-- SIDEBAR -->
        <?php include "../includes/profile_sidebar.php"; ?>

        <!-- RIGHT CONTENT -->
        <section class="content-card">
            
            <!-- PAGE HEADER WITH BACK/HOME -->
            <div class="page-header-nav">
                <h3>Upload / Replace Resume</h3>
                <div class="nav-buttons">
                    <a href="preferred-industry.php" class="btn-back">← BACK</a>
                    <a href="/jobsweb/index.php" class="btn-home">HOME →</a>
                </div>
            </div>

            <!-- SUCCESS MESSAGE -->
            <?php if (isset($_SESSION['resume_msg'])): ?>
                <div class="success-msg">
                    ✅ <?= htmlspecialchars($_SESSION['resume_msg']) ?>
                </div>
                <?php unset($_SESSION['resume_msg']); ?>
            <?php endif; ?>

            <!-- EXISTING RESUME -->
            <div class="resume-info">
                <div class="resume-meta">
                    <?php if (!empty($resume_file)): ?>
                        Resume: <strong><?= htmlspecialchars($resume_file) ?></strong>
                        <?php if ($uploaded_date): ?>
                            <small>Uploaded on: <?= $uploaded_date ?></small>
                        <?php endif; ?>
                    <?php else: ?>
                        No resume uploaded yet.
                        <small>Upload a PDF, DOC or DOCX resume.</small>
                    <?php endif; ?>
                </div>

                <?php if (!empty($resume_file)): ?>
                <div style="display:flex; gap:10px;">
                    <a class="btn-sm-outline"
                       href="/jobsweb/uploads/resume/<?= rawurlencode($resume_file) ?>"
                       download>
                       📥 Download
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- UPLOAD FORM -->
            <form action="upload-resume-process.php" method="POST" enctype="multipart/form-data" onsubmit="return checkFileSize();">
                <label class="form-label">Choose resume (PDF, DOC, DOCX)</label>
                <input type="file" id="resumeFile" name="resume" accept=".pdf,.doc,.docx" class="form-control" required>

                <div class="small-note">
                    Allowed: PDF, DOC, DOCX • Max size 5MB
                </div>

                <button type="submit" class="btn-upload" style="margin-top:14px;">
                    Upload / Replace Resume
                </button>

                <a href="edit-profile.php" class="btn-sm-outline" style="margin-left:14px;">
                    Back to Profile
                </a>
            </form>

            <!-- VIEWER -->
            <?php if (!empty($resume_file)): ?>
            <div style="margin-top:20px;">
                <h4 style="color:#004aad;">Your Uploaded Resume</h4>

                <?php if ($file_ext === "pdf"): ?>
                    <iframe src="/jobsweb/uploads/resume/<?= rawurlencode($resume_file) ?>"
                            style="width:100%; height:500px; border:1px solid #ccc; border-radius:6px;">
                    </iframe>

                <?php else: ?>
                    <p style="color:#777;">DOC/DOCX cannot be viewed online. Download to view.</p>
                    <a href="/jobsweb/uploads/resume/<?= rawurlencode($resume_file) ?>" class="btn-sm-outline" download>📥 Download File</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </section>

    </div>
</div>

<script>
function checkFileSize() {
    const f = document.getElementById("resumeFile").files[0];
    if (!f) return true;

    if (f.size > 5 * 1024 * 1024) {
        alert("File too large! Maximum allowed size is 5MB.");
        return false;
    }
    return true;
}
</script>

<?php include "../includes/footer.php"; ?>