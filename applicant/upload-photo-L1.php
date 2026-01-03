<?php
/* FILE: /applicant/upload-photo.php
   PURPOSE: Upload profile photo (Premium polished version)
*/

session_start();
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

include "../config/config.php";
include "../includes/header.php";

$app_id = intval($_SESSION['applicant_id']);

$stmt = $conn->prepare("SELECT photo, full_name FROM job_seekers WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $app_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<style>
.page-wrapper {
    max-width: 1100px;
    margin: 28px auto;
    padding: 0 16px;
}

/* GRID: Sidebar + Content */
.profile-grid {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 26px;
    align-items: start;
}

@media(max-width:880px){
    .profile-grid {
        grid-template-columns: 1fr;
    }
}

.photo-box {
    background:#fff;
    padding:28px;
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
    border:1px solid #e1e5ee;
}

.photo-preview {
    width:130px;
    height:130px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid #e8f1ff;
    margin-bottom:12px;
}

.btn-upload {
    padding:10px 18px;
    background: var(--brand-secondary);
    color:white;
    border:none;
    border-radius:6px;
    font-weight:600;
}

.btn-upload:hover { background: var(--brand-primary); }

.small-note {
    font-size:13px;
    color:#667;
    margin-top:6px;
}
</style>


<div class="page-wrapper">

    <div class="profile-grid">

        <!-- LEFT SIDEBAR (Correct include) -->
        <?php include "../includes/profile_sidebar.php"; ?>

        <!-- RIGHT CONTENT -->
        <section class="photo-box">

            <h3 style="color:var(--brand-primary); margin-bottom:16px;">
                Upload Profile Photo
            </h3>

            <!-- Current Photo -->
            <div>
                <?php if (!empty($user['photo'])): ?>
                    <img src="/jobsweb/uploads/photos/<?= htmlspecialchars($user['photo']) ?>"
                         class="photo-preview" alt="Profile Photo">
                <?php else: ?>
                    <img src="/jobsweb/assets/img/default-avatar.png"
                         class="photo-preview" alt="No photo">
                <?php endif; ?>
            </div>

            <!-- Upload Form -->
            <form action="upload-photo-process.php" method="POST" enctype="multipart/form-data">

                <label class="form-label">Choose a new photo</label>
                <input type="file" name="photo" accept=".jpg,.jpeg,.png"
                       class="form-control" required>

                <div class="small-note">
                    Accepted: JPG, PNG • Recommended: 250×250px • Max: 2MB
                </div>

                <button type="submit" class="btn-upload mt-3">Upload Photo</button>

            </form>

        </section>

    </div>

</div>


<?php include "../includes/footer.php"; ?>
