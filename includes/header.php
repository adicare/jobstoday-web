<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>JobsToday</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Universal Theme -->
  <?php include(__DIR__ . '/theme_link.php'); ?>

  <!-- Extra CSS -->
  <style>
      body {
          background-color: #e8f2ff !important;
          margin: 0;
          padding: 0;
      }
      .navbar {
          background-color: #0a4c90 !important;   /* New JobsToday blue */
      }
      .container-page {
          margin-top: 20px;
      }
  </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark px-4">
  <div class="container-fluid">

    <!-- BRAND LOGO -->
    <a class="navbar-brand fw-bold text-white" href="/jobsweb/index.php">
      <i class="bi bi-briefcase-fill me-2"></i>JobsToday
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto gap-3">

        <?php
        $path = $_SERVER['PHP_SELF'];

        /*
        --------------------------------------------------------
        ADMIN NAVIGATION
        --------------------------------------------------------
        */
        if (strpos($path, '/admin/') !== false):
        ?>
          <?php if (!empty($_SESSION['user_id'])): ?>
            <li class="nav-item"><a class="nav-link text-white" href="/jobsweb/admin/dashboard.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/jobsweb/admin/view-applicants.php">Applicants</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/jobsweb/admin/add-job.php">Add Job</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/jobsweb/admin/logout.php">Logout</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link text-white" href="/jobsweb/admin/login.php">Admin Login</a></li>
          <?php endif; ?>

        <?php
        /*
        --------------------------------------------------------
        EMPLOYER NAVIGATION
        --------------------------------------------------------
        */
        elseif (strpos($path, '/employer/') !== false):
        ?>
          <?php if (!empty($_SESSION['user_id'])): ?>
            <li class="nav-item"><a class="nav-link text-white" href="/jobsweb/employer/dashboard.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/jobsweb/employer/add-job.php">Add Job</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/jobsweb/employer/my-jobs.php">My Jobs</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/jobsweb/public/logout.php">Logout</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link text-white" href="/jobsweb/public/login.php">Employer Login</a></li>
          <?php endif; ?>

        <?php
        /*
        --------------------------------------------------------
        APPLICANT NAVIGATION
        --------------------------------------------------------
        */
        elseif (strpos($path, '/applicant/') !== false):
        ?>
          <?php if (!empty($_SESSION['user_id'])): ?>
            <li class="nav-item"><a class="nav-link text-white" href="/jobsweb/applicant/dashboard.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/jobsweb/public/jobs.php">Browse Jobs</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/jobsweb/public/logout.php">Logout</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link text-white" href="/jobsweb/public/login.php">Applicant Login</a></li>
          <?php endif; ?>

        <?php
        /*
        --------------------------------------------------------
        PUBLIC NAVIGATION
        --------------------------------------------------------
        */
        else:
        ?>
          <li class="nav-item"><a class="nav-link text-white" href="/jobsweb/public/jobs.php">Jobs</a></li>

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-white" href="#" id="registerMenu" role="button" data-bs-toggle="dropdown">
              Register
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="/jobsweb/public/register-applicant.php">Applicant Register</a></li>
              <li><a class="dropdown-item" href="/jobsweb/public/register-employer.php">Employer Register</a></li>
            </ul>
          </li>

          <li class="nav-item">
            <a class="nav-link text-white" href="/jobsweb/public/login.php">Login</a>
          </li>

        <?php endif; ?>
      </ul>
    </div>

  </div>
</nav>

<!-- PAGE WRAPPER -->
<div class="container container-page">
