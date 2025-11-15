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

$id = $_GET['id'];
$conn->query("UPDATE applications SET status='Reviewed' WHERE id=$id");

header("Location: view-applicants.php");
exit;
