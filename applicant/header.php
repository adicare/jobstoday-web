<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in or wrong role
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'applicant') {
    header("Location: ../public/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="../assets/css/style.css?v=5">

  <meta charset="UTF-8">
  <title>Applicant Panel | CareerJano</title>

  <!-- Global Theme -->
  <?php include('../includes/theme_link.php'); ?>
</head>

<body>
  <!-- Applicant Navigation Bar -->
  <nav class="navbar navbar-expand-lg navbar-dark px-4">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="dashboard.php">CareerJano</a>
      <div class="navbar-nav ms-auto gap-3">
        <a class="nav-link text-white" href="dashboard.php">Dashboard</a>
        <a class="nav-link text-white" href="../public/jobs.php">Browse Jobs</a>
        <a class="nav-link text-white" href="../public/logout.php">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container mt-4">
