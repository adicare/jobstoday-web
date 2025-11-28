<?php
/* FILE: /applicant/upload-photo.php
   PURPOSE: Upload profile photo (using standardized sidebar layout)
*/

session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

include "../config/config.php";
include "../includes/header.php";

$app_id = $_SESSION['applicant_id'];

// Fetch user info
$res = $conn->query("SELECT photo, full_name FROM job_seekers WHERE id = $app_id LIMIT 1");
$user = $res->fetch_assoc();
?>

<style>
.content-box {
    background:#fff;
    padding:25px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
    margin-top:20px;
}

.photo-preview {
    width:110px;
    height:110px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid #eee;
}

.btn-upload {
    padding:10px 16px;
    background:#0a4c90;
    color:#fff;
    border:none;
    border-radius:6px;
}

.small-note {
    color:#666;
    font-size:13px;
    margin-top:8px;
}
</style>

<div class="container mt-3">
    <div class="row">

        <!-- LEFT SIDEBAR (NEW STANDARD LAYOUT) -->
        <div class="col-md-3">
            <?php include "profile-sidebar.php"; ?>
        </div>

        <!-- RIGHT CONTENT -->
        <div class="col-md-9">
            <div class="content-box">

                <h3 class="text-primary fw-bold mb-3">Profile Photo</h3>

                <!-- Existing Photo -->
                <div class="mb-3">
                    <?php if (!empty($user['photo'])): ?>
                        <img src="/jobsweb/uploads/photos/<?= htmlspecialchars($user['photo']) ?>"
                             alt="photo" class="photo-preview mb-2">
                    <?php else: ?>
                        <img src="/jobsweb/assets/img/default-avatar.png"
                             alt="no-photo" class="photo-preview mb-2">
                    <?php endif; ?>
                </div>

                <!-- Upload Form -->
                <form action="upload-photo-process.php" method="POST" enctype="multipart/form-data">

                    <label class="form-label">Choose photo (JPG / PNG)</label>
                    <input type="file" name="photo" accept=".jpg,.jpeg,.png" class="form-control" required>
                    <div class="small-note">Recommended size: 250x250px. Max 2MB.</div>

                    <button type="submit" class="btn-upload mt-3">Upload Photo</button>

                </form>

            </div>
        </div>

    </div>
</div>

<?php include "../includes/footer.php"; ?>
