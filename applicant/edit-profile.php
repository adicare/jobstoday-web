<?php
/* ============================================================
   FILE: applicant/edit-profile.php
   USE : Show pre-filled profile fields for editing
   RESPONSE #: FIXED
   ============================================================ */

session_start();

// Redirect if not logged in
if (!isset($_SESSION['applicant_id'])) {
    header("Location: /jobsweb/public/login.php");
    exit;
}

include "../config/config.php";

$app_id = $_SESSION['applicant_id'];
//echo "<p>DEBUG: SESSION applicant_id = $app_id</p>";

// Fetch applicant data
$sql = "SELECT * FROM job_seekers WHERE id = $app_id LIMIT 1";
//$sql = "SELECT * FROM job_seekers WHERE id = 6 LIMIT 1";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Safety check
if (!$user) {
    die("Error: Applicant data not found.");
}
?>


<!DOCTYPE html>
<html>
<head>
<title>Edit Profile - CareerJano</title>

<style>
    body { background:#f2f2f2; font-family:Arial; }
    .box {
        width:500px; margin:40px auto; background:white;
        padding:25px; border-radius:10px;
        box-shadow:0 0 10px rgba(0,0,0,0.1);
    }
    input, select {
        width:100%; padding:10px; margin:10px 0;
        border:1px solid #ccc; border-radius:6px;
    }
    button {
        width:100%; padding:12px; background:#28a745;
        border:none; color:white; border-radius:6px;
        cursor:pointer; font-size:16px;
    }
    button:hover { background:#218838; }
</style>

</head>
<body>

<div class="box">
    <h2>Edit Your Profile</h2>

    <form action="edit-profile-process.php" method="POST">

        <label>Full Name</label>
        <input type="text" name="full_name"
               value="<?php echo $user['full_name']; ?>" required>

        <label>Email (cannot change)</label>
        <input type="email" value="<?php echo $user['email']; ?>" disabled>

        <label>Mobile Number</label>
        <input type="text" name="mobile"
               value="<?php echo $user['mobile']; ?>">

        <label>Gender</label>
        <select name="gender">
            <option value="">Select</option>
            <option <?php if($user['gender']=="Male") echo "selected"; ?>>Male</option>
            <option <?php if($user['gender']=="Female") echo "selected"; ?>>Female</option>
            <option <?php if($user['gender']=="Other") echo "selected"; ?>>Other</option>
        </select>

        <label>Date of Birth</label>
        <input type="date" name="dob"
               value="<?php echo $user['dob']; ?>">

        <label>State</label>
        <input type="text" name="state"
               value="<?php echo $user['state']; ?>">

        <label>City</label>
        <input type="text" name="city"
               value="<?php echo $user['city']; ?>">

        <button type="submit">Update Profile</button>
    </form>

</div>

</body>
</html>
