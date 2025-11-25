<?php
/* ============================================================
   FILE: applicant/edit-profile.php
   USE : Applicant Profile Editing with Sidebar Navigation
   DESIGN: Modern (Option A)
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
/* Sidebar layout */
.wrapper {
    display: flex;
    margin-top: 30px;
}

.sidebar {
    width: 240px;
    background: #0a4c90;
    color: white;
    padding: 20px;
    border-radius: 8px;
    height: 100%;
}

.sidebar h4 {
    font-size: 20px;
    margin-bottom: 20px;
    font-weight: bold;
}

.sidebar a {
    display: block;
    padding: 12px;
    color: white;
    text-decoration: none;
    margin-bottom: 8px;
    border-radius: 5px;
    font-weight: bold;
}

.sidebar a.active {
    background: #06376a;
}

.sidebar a:hover {
    background: #094983;
}

/* Content area */
.content-box {
    flex: 1;
    margin-left: 20px;
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

input, select {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border-radius: 6px;
    border: 1px solid #ccc;
}

button {
    padding: 12px;
    background: #28a745;
    border: none;
    color: white;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
}
button:hover { background:#218838; }
</style>


<div class="container">

    <div class="wrapper">

        <!-- ======================================
             LEFT SIDEBAR
        ======================================= -->
        <div class="sidebar">
            <h4><i class="bi bi-person-lines-fill me-2"></i> My Profile</h4>

            <a href="edit-profile.php" class="active">Personal Details</a>
            <a href="upload-photo.php">Profile Photo</a>
            <a href="upload-resume.php">Resume Upload</a>
            <a href="skills.php">Skills</a>
        </div>

        <!-- ======================================
             RIGHT CONTENT AREA
        ======================================= -->
        <div class="content-box">

            <h3 class="text-primary fw-bold mb-3">
                Edit Your Personal Details
            </h3>

            <form action="edit-profile-process.php" method="POST">

                <label>Full Name</label>
                <input type="text" name="full_name" 
                       value="<?= $user['full_name'] ?>" required>

                <label>Email (cannot change)</label>
                <input type="email" value="<?= $user['email'] ?>" disabled>

                <label>Mobile Number</label>
                <input type="text" name="mobile" 
                       value="<?= $user['mobile'] ?>">

                <label>Gender</label>
                <select name="gender">
                    <option value="">Select</option>
                    <option <?= $user['gender']=="Male" ? "selected" : "" ?>>Male</option>
                    <option <?= $user['gender']=="Female" ? "selected" : "" ?>>Female</option>
                    <option <?= $user['gender']=="Other" ? "selected" : "" ?>>Other</option>
                </select>

                <label>Date of Birth</label>
                <input type="date" name="dob" value="<?= $user['dob'] ?>">

                <label>State</label>
                <input type="text" name="state" value="<?= $user['state'] ?>">

                <label>City</label>
                <input type="text" name="city" value="<?= $user['city'] ?>">

                <button type="submit">Update Profile</button>
            </form>
        </div>

    </div>
</div>

<?php include "../includes/footer.php"; ?>
