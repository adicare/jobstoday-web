<?php 
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../public/login.php");
    exit;
}
?>

<?php
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

require __DIR__ . '/../config/db.php';

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT resume_file FROM applications WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($resume);
$stmt->fetch();

$resume_path = "../uploads/resumes/" . $resume;

header("Content-type: application/pdf");
header("Content-Disposition: inline; filename='$resume'");
readfile($resume_path);
