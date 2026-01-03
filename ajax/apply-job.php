<?php
// /jobsweb/ajax/apply-job.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
include(__DIR__ . '/../config/config.php');

if (!isset($_SESSION['applicant_id'])) {
    echo json_encode(['success'=>false,'message'=>'Please login to apply.']);
    exit;
}
$applicant_id = intval($_SESSION['applicant_id']);
$job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
$resume_option = isset($_POST['resume_option']) ? $_POST['resume_option'] : 'profile';
$cover_letter = isset($_POST['cover_letter']) ? trim($_POST['cover_letter']) : '';

if (!$job_id) {
    echo json_encode(['success'=>false,'message'=>'Invalid job.']); exit;
}

// check already applied
$chk = $conn->prepare("SELECT id FROM job_applications WHERE applicant_id=? AND job_id=? LIMIT 1");
$chk->bind_param("ii", $applicant_id, $job_id);
$chk->execute();
$res = $chk->get_result();
if ($res && $res->num_rows>0){
    echo json_encode(['success'=>false,'message'=>'You have already applied to this job.']); exit;
}

// helper: get profile resume filename (job_seekers.resume_file)
function get_profile_resume_path($conn, $applicant_id){
    $sql = "SELECT resume_file FROM job_seekers WHERE id = ? LIMIT 1";
    $st = $conn->prepare($sql);
    $st->bind_param('i', $applicant_id);
    $st->execute();
    $r = $st->get_result();
    if($row = $r->fetch_assoc()){
        return !empty($row['resume_file']) ? $row['resume_file'] : null;
    }
    return null;
}

$resume_path = null;
if ($resume_option === 'profile') {
    $profile = get_profile_resume_path($conn, $applicant_id);
    if (!$profile) {
        echo json_encode(['success'=>false,'message'=>'No default resume found in your profile. Please upload one or choose Upload option.']); exit;
    }
    // if resume_file stored as full web path, adapt. We'll store exactly what DB has.
    $resume_path = $profile;
} else {
    // handle file upload
    if (empty($_FILES['resume_file']) || $_FILES['resume_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success'=>false,'message'=>'Please choose a resume file to upload.']); exit;
    }
    $file = $_FILES['resume_file'];
    $allowed_mimes = [
      'application/pdf',
      'application/msword',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    $maxBytes = 3 * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        echo json_encode(['success'=>false,'message'=>'Resume file too large (max 3MB).']); exit;
    }
    if (!in_array($mime, $allowed_mimes)) {
        echo json_encode(['success'=>false,'message'=>'Invalid file type. Allowed: pdf, doc, docx.']); exit;
    }
    $uploadsDir = __DIR__ . '/../uploads/resumes/';
    if (!is_dir($uploadsDir)) @mkdir($uploadsDir,0755,true);
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safeName = 'resume_' . $applicant_id . '_' . time() . '.' . $ext;
    $dest = $uploadsDir . $safeName;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        echo json_encode(['success'=>false,'message'=>'Failed to save resume.']); exit;
    }
    $resume_path = '/jobsweb/uploads/resumes/' . $safeName;
    // optional: do NOT overwrite profile resume by default. If you want, update job_seekers.resume_file here.
}

// insert application
$ins = $conn->prepare("INSERT INTO job_applications (job_id, applicant_id, resume_path, cover_letter, status, applied_at) VALUES (?, ?, ?, ?, 'Applied', NOW())");
$ins->bind_param("iiss", $job_id, $applicant_id, $resume_path, $cover_letter);
if (!$ins->execute()) {
    error_log("Apply insert error: " . $conn->error);
    echo json_encode(['success'=>false,'message'=>'Failed to submit application.']); exit;
}
$appId = $ins->insert_id;

// create alert for applicant (optional)
$alertMsg = 'Application submitted for job ID ' . $job_id;
$a = $conn->prepare("INSERT INTO alerts (applicant_id, message, created_at, is_read) VALUES (?,?,NOW(),0)");
$a->bind_param("is", $applicant_id, $alertMsg);
$a->execute();

// return counts (applications count)
$cQ = $conn->prepare("SELECT COUNT(*) as c FROM job_applications WHERE applicant_id=?");
$cQ->bind_param("i", $applicant_id);
$cQ->execute();
$cRes = $cQ->get_result();
$appCount = ($r = $cRes->fetch_assoc()) ? intval($r['c']) : 0;

echo json_encode(['success'=>true,'message'=>'Application submitted successfully.','application_id'=>$appId,'application_count'=>$appCount]);
exit;
