<?php
/* ============================================================
   FILE: applicant/edit-profile.php
   PURPOSE: Applicant Profile Editing (SAARC Version)
   RESPONSIVE + COUNTRY CODE + STATE/DISTRICT
   ============================================================ */

session_start();

// Redirect if not logged in
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

include "../config/config.php";
include "../includes/header.php";   // Top Navigation

$app_id = $_SESSION['applicant_id'];

// Fetch applicant details
$sql = "SELECT * FROM job_seekers WHERE id = $app_id LIMIT 1";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if (!$user) {
    die("Error: Applicant data not found.");
}
?>

<style>
.wrapper { display: flex; margin-top: 30px; }
.sidebar {
    width: 240px; background: #0a4c90; color: white;
    padding: 20px; border-radius: 8px;
}
.sidebar h4 { font-size: 20px; margin-bottom: 20px; font-weight: bold; }
.sidebar a {
    display: block; padding: 12px; color: white;
    text-decoration: none; margin-bottom: 8px;
    border-radius: 5px; font-weight: bold;
}
.sidebar a.active { background: #06376a; }
.sidebar a:hover { background: #094983; }

.content-box {
    flex: 1; margin-left: 20px; background: white;
    padding: 25px; border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
</style>

<div class="container">
    <div class="wrapper">

        <!-- ============= LEFT SIDEBAR ============= -->
        <div class="sidebar">
            <h4><i class="bi bi-person-lines-fill me-2"></i> My Profile</h4>

            <a href="edit-profile.php" class="active">Personal Details</a>
            <a href="upload-photo.php">Profile Photo</a>
            <a href="upload-resume.php">Resume Upload</a>
            <a href="skills.php">Skills</a>
            <a href="qualification.php">Qualification</a>
            <a href="preferred-industry.php">Preferred Industry</a>
        </div>

        <!-- ============= RIGHT CONTENT ============= -->
        <div class="content-box">

            <h3 class="text-primary fw-bold mb-3">Edit Your Personal Details</h3>

            <form action="edit-profile-process.php" method="POST">

                <!-- Full Name -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control"
                               value="<?= htmlspecialchars($user['full_name']) ?>" required>
                    </div>
                </div>

                <!-- Email + Mobile -->
                <div class="row mb-3">

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control"
                               value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Mobile Number</label>
                        <div class="input-group">

                            <!-- SAARC Phone Codes -->
                            <select class="form-select" name="country_code" id="country_code" style="max-width:130px;">
                                <option value="+91"  <?= $user['country_code']=='+91'  ?'selected':''; ?>>+91 IN</option>
                                <option value="+977" <?= $user['country_code']=='+977' ?'selected':''; ?>>+977 NP</option>
                                <option value="+880" <?= $user['country_code']=='+880' ?'selected':''; ?>>+880 BD</option>
                                <option value="+94"  <?= $user['country_code']=='+94'  ?'selected':''; ?>>+94 LK</option>
                                <option value="+975" <?= $user['country_code']=='+975' ?'selected':''; ?>>+975 BT</option>
                            </select>

                            <input type="text" name="mobile" class="form-control"
                                   value="<?= htmlspecialchars($user['mobile']) ?>"
                                   placeholder="Enter mobile number" required>
                        </div>
                    </div>

                </div>

                <!-- Gender + DOB -->
                <div class="row mb-3">

                    <div class="col-md-6">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">Select</option>
                            <option value="Male"   <?= $user['gender']=='Male'?'selected':''; ?>>Male</option>
                            <option value="Female" <?= $user['gender']=='Female'?'selected':''; ?>>Female</option>
                            <option value="Other"  <?= $user['gender']=='Other'?'selected':''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="dob" value="<?= $user['dob'] ?>" class="form-control">
                    </div>

                </div>

                <!-- Country -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Country</label>
                        <select name="country" id="country" class="form-select" required>
                            <option value="">Select Country</option>
                            <option value="IN" <?= $user['country']=="IN"?"selected":""; ?>>India</option>
                            <option value="NP" <?= $user['country']=="NP"?"selected":""; ?>>Nepal</option>
                            <option value="BD" <?= $user['country']=="BD"?"selected":""; ?>>Bangladesh</option>
                            <option value="LK" <?= $user['country']=="LK"?"selected":""; ?>>Sri Lanka</option>
                            <option value="BT" <?= $user['country']=="BT"?"selected":""; ?>>Bhutan</option>
                        </select>
                    </div>
                </div>

                <!-- State + District + City -->
                <div class="row mb-3">

                    <div class="col-md-4">
                        <label class="form-label">State</label>
                        <select name="state" id="state" class="form-select" required>
                            <option value="">Select State</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">District</label>
                        <select name="district" id="district" class="form-select" required>
                            <option value="">Select District</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">City (optional)</label>
                        <input type="text" name="city" class="form-control"
                               value="<?= htmlspecialchars($user['city']) ?>"
                               placeholder="Enter city / town">
                    </div>

                </div>

                <button type="submit" class="btn btn-success px-4">Update Profile</button>

            </form>

        </div>

    </div>
</div>

<?php include "../includes/footer.php"; ?>


<!-- ========== AUTO PHONE CODE SYNC + STATE/DISTRICT JS ========== -->
<script>
const phoneMap = {
    "IN": "+91",
    "NP": "+977",
    "BD": "+880",
    "LK": "+94",
    "BT": "+975"
};

document.getElementById("country")?.addEventListener("change", function () {
    let code = phoneMap[this.value];
    if (code) {
        document.getElementById("country_code").value = code;
    }
});
</script>
