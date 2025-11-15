<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel | CareerJano</title>

  <!-- Universal Theme Link -->
  <?php include('../includes/theme_link.php'); ?>
</head>

<body>
  <!-- Common Admin Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark px-4">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="dashboard.php">CareerJano Admin</a>
      <div class="navbar-nav ms-auto gap-3">
        <a class="nav-link text-white" href="dashboard.php">Dashboard</a>
        <a class="nav-link text-white" href="view-applicants.php">Applicants</a>
        <a class="nav-link text-white" href="add-job.php">Add Job</a>
        <a class="nav-link text-white" href="../public/logout.php">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container mt-4">
