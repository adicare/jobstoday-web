<?php
// /jobsweb/applicant/update_personal.php
session_start();
require __DIR__ . '/../includes/db.php'; // adjust path to your DB connection

// Protect page: ensure user logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /jobsweb/login.php');
    exit;
}

$user_id = intval($_SESSION['user_id']);
$errors = [];
$success = '';

// Fetch existing user info
$stmt = $pdo->prepare("SELECT id, full_name, email, phone, dob, gender, linkedin, photo FROM applicants WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle POST (update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // basic sanitization
    $full_name = trim($_POST['full_name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone = preg_replace('/[^0-9+]/', '', trim($_POST['phone'] ?? ''));
    $dob = trim($_POST['dob'] ?? '');
    $gender = in_array($_POST['gender'] ?? '', ['Male','Female','Other']) ? $_POST['gender'] : null;
    $linkedin = filter_var(trim($_POST['linkedin'] ?? ''), FILTER_SANITIZE_URL);

    if (!$full_name) $errors[] = 'Full name is required.';
    if (!$email) $errors[] = 'Valid email is required.';
    if ($phone && (strlen($phone) < 7 || strlen($phone) > 20)) $errors[] = 'Enter a valid phone number.';

    // handle profile photo upload (optional)
    $photo_path = $user['photo']; // keep existing
    if (!empty($_FILES['photo']['name'])) {
        $allowed = ['image/png','image/jpeg','image/jpg'];
        if ($_FILES['photo']['error'] === 0 && in_array($_FILES['photo']['type'], $allowed)) {
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $fname = 'photo_user_' . $user_id . '_' . time() . '.' . $ext;
            $dest_dir = __DIR__ . '/../uploads/photos/';
            if (!is_dir($dest_dir)) mkdir($dest_dir, 0755, true);
            $dest = $dest_dir . $fname;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
                $photo_path = '/jobsweb/uploads/photos/' . $fname;
            } else {
                $errors[] = 'Could not save uploaded photo.';
            }
        } else {
            $errors[] = 'Photo must be JPG or PNG and under server limits.';
        }
    }

    if (empty($errors)) {
        $sql = "UPDATE applicants SET full_name = ?, email = ?, phone = ?, dob = ?, gender = ?, linkedin = ?, photo = ? WHERE id = ?";
        $up = $pdo->prepare($sql);
        $ok = $up->execute([$full_name, $email, $phone, $dob, $gender, $linkedin, $photo_path, $user_id]);
        if ($ok) {
            $success = 'Profile updated successfully.';
            // refresh user data
            $stmt = $pdo->prepare("SELECT id, full_name, email, phone, dob, gender, linkedin, photo FROM applicants WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $errors[] = 'Database error while saving.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Update Personal Information - Jobstoday</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="/jobsweb/assets/css/theme.css">
    <style>
        /* small page-specific polish */
        .page-wrapper { max-width: 940px; margin: 24px auto; padding: 0 16px; }
        .profile-grid { display: grid; grid-template-columns: 260px 1fr; gap: 20px; }
        .avatar-box { text-align: center; padding-top: 8px; }
        .avatar-box img { width: 140px; height: 140px; object-fit: cover; border-radius: 12px; border: 1px solid var(--border); }
        .muted { color: var(--text-light); font-size: 13px; }
        .error { background: #fff3f2; border: 1px solid #f5c6cb; padding: 10px; border-radius: 6px; color: #8b1b1b; margin-bottom: 12px; }
        .success { background: #f3fff4; border: 1px solid #b7e4c7; padding: 10px; border-radius: 6px; color: #0b5e2f; margin-bottom: 12px; }
        @media (max-width: 720px) { .profile-grid { grid-template-columns: 1fr; } .avatar-box img { width: 120px; height:120px; } }
    </style>
</head>
<body>
<div class="page-wrapper">
    <h2>Update Personal Information</h2>

    <div class="card profile-grid">
        <!-- left avatar -->
        <div>
            <div class="avatar-box">
                <?php $photo_url = !empty($user['photo']) ? $user['photo'] : '/jobsweb/assets/img/default-avatar.png'; ?>
                <img id="avatarPreview" src="<?=htmlspecialchars($photo_url)?>" alt="Profile photo">
                <p class="muted" style="margin-top:10px">Profile photo</p>
                <p class="muted">JPEG or PNG, smaller than server limit</p>
            </div>
        </div>

        <!-- right form -->
        <div>
            <?php if ($errors): ?>
                <div class="error">
                    <ul style="margin:0 0 0 18px;padding:0;">
                        <?php foreach ($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success"><?=htmlspecialchars($success)?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" id="personalForm" novalidate>
                <label class="form-label" for="full_name">Full Name</label>
                <input id="full_name" name="full_name" value="<?=htmlspecialchars($user['full_name'] ?? '')?>" required>

                <label class="form-label" for="email">Email</label>
                <input id="email" name="email" type="email" value="<?=htmlspecialchars($user['email'] ?? '')?>" required>

                <label class="form-label" for="phone">Phone</label>
                <input id="phone" name="phone" value="<?=htmlspecialchars($user['phone'] ?? '')?>" placeholder="+91XXXXXXXXXX">

                <label class="form-label" for="dob">Date of Birth</label>
                <input id="dob" name="dob" type="date" value="<?=htmlspecialchars($user['dob'] ?? '')?>">

                <label class="form-label" for="gender">Gender</label>
                <select id="gender" name="gender">
                    <option value="">Prefer not to say</option>
                    <option <?=($user['gender']=='Male')?'selected':''?>>Male</option>
                    <option <?=($user['gender']=='Female')?'selected':''?>>Female</option>
                    <option <?=($user['gender']=='Other')?'selected':''?>>Other</option>
                </select>

                <label class="form-label" for="linkedin">LinkedIn profile (optional)</label>
                <input id="linkedin" name="linkedin" type="url" value="<?=htmlspecialchars($user['linkedin'] ?? '')?>" placeholder="https://">

                <label class="form-label" for="photo">Upload Photo</label>
                <input id="photo" name="photo" type="file" accept="image/*">

                <div style="display:flex;gap:12px;margin-top:12px;">
                    <button type="submit" class="btn-primary">Save Changes</button>
                    <a href="/jobsweb/applicant/dashboard.php" class="btn-secondary" style="text-decoration:none;display:inline-flex;align-items:center;padding:10px 16px;">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <div style="margin-top:18px" class="muted">Tip: Keep your profile photo professional — headshot, plain background, friendly smile.</div>
</div>

<script>
    // avatar preview + simple client-side checks
    (function(){
        const photoInput = document.getElementById('photo');
        const avatar = document.getElementById('avatarPreview');
        photoInput?.addEventListener('change', function(e){
            const f = this.files[0];
            if (!f) return;
            if (!['image/jpeg','image/png','image/jpg'].includes(f.type)) {
                alert('Only JPG/PNG images allowed for profile photo.');
                this.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = function(ev){ avatar.src = ev.target.result; };
            reader.readAsDataURL(f);
        });

        // small client validation before submit
        const form = document.getElementById('personalForm');
        form.addEventListener('submit', function(e){
            const name = document.getElementById('full_name').value.trim();
            const email = document.getElementById('email').value.trim();
            if (!name) { alert('Please enter your full name.'); e.preventDefault(); return; }
            if (!email || !email.includes('@')) { alert('Please enter a valid email.'); e.preventDefault(); return; }
        });
    })();
</script>
</body>
</html>
