<?php
/* ============================================================
   FILE: applicant/upload-photo.php
   USE : Upload profile photo form
   RESPONSE #: 12
   ============================================================ */

session_start();
include "../config/config.php";

// Auth check
if (!isset($_SESSION['applicant_id'])) {
    header("Location: ../public/auth/login.php");
    exit;
}

$app_id = $_SESSION['applicant_id'];

// Fetch existing photo
$result = $conn->query("SELECT photo FROM job_seekers WHERE id = $app_id");
$user   = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Photo - CareerJano</title>

    <style>
        body { font-family:Arial; background:#f4f4f4; }
        .box {
            width:420px; margin:50px auto;
            background:white; padding:25px;
            border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1);
        }
        input[type="file"] {
            padding:10px; width:100%; margin:15px 0;
            border:1px solid #ccc; border-radius:6px;
        }
        button {
            width:100%; padding:12px; background:#007bff;
            border:none; color:white; border-radius:6px;
            cursor:pointer; font-size:16px;
        }
        button:hover { background:#0056b3; }
        .preview {
            margin-bottom:15px; text-align:center;
        }
        .preview img {
            width:120px; height:120px;
            border-radius:50%; object-fit:cover;
            border:3px solid #ddd;
        }
    </style>
</head>

<body>

<div class="box">
    <h2>Upload Your Profile Photo</h2>

    <div class="preview">
        <?php if (!empty($user['photo'])): ?>
            <img src="../uploads/photos/<?php echo $user['photo']; ?>" alt="Current Photo">
            <p>Current Photo</p>
        <?php else: ?>
            <p>No photo uploaded yet.</p>
        <?php endif; ?>
    </div>

    <form action="upload-photo-process.php" method="POST" enctype="multipart/form-data">
        <label>Select Image (JPG/PNG only):</label>
        <input type="file" name="photo" accept="image/*" required>
        <button type="submit">Upload Photo</button>
    </form>

</div>

</body>
</html>
