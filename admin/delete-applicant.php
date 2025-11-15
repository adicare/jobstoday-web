<?php 
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

require __DIR__ . '/../config/db.php';

$id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM applications WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: view-applicants.php");
exit;
