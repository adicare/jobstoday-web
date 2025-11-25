<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$isLoggedIn = !empty($_SESSION['user_id']);
$userRole   = $_SESSION['user_role'] ?? '';
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>JobsToday</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
      body {
          background-color: #e8f2ff !important;
          margin: 0;
          padding: 0;
      }

      .top-header {
          background-color: #0a4c90;
          color: white;
          padding: 14px 40px;
          display: flex;
          justify-content: space-between;
          align-items: center;
          width: 100%;           /* FULL WIDTH HEADER */
      }

      .top-header .logo {
          font-size: 24px;
          font-weight: 700;
      }

      .top-header nav ul {
          list-style: none;
          display: flex;
          gap: 28px;
          margin: 0;
          padding: 0;
      }

      .top-header nav a {
          color: white;
          text-decoration: none;
          font-weight: 600;
          font-size: 17px;
      }

      .top-header nav a:hover {
          text-decoration: underline;
      }

      .login-btn-top a {
          color: white;
          font-weight: bold;
          text-decoration: none;
      }
  </style>
</head>

<body>

<div class="top-header">
    
    <div class="logo">
        <i class="bi bi-briefcase-fill me-2"></i>JobsToday
    </div>

    <nav>
        <ul>
            <li><a href="/jobsweb/index.php">Jobs</a></li>
            <li><a href="/jobsweb/public/courses.php">Courses</a></li>
            <li><a href="/jobsweb/public/experts.php">Experts</a></li>
            <li><a href="/jobsweb/public/resume-builder.php">Resume Builder</a></li>
            <li><a href="/jobsweb/public/contact.php">Contact</a></li>
            <li><a href="/jobsweb/public/register.php">Register</a></li>
        </ul>
    </nav>

    <div class="login-btn-top">
        <?php if ($isLoggedIn): ?>
            <a href="/jobsweb/public/logout.php">Logout</a>
        <?php else: ?>
            <a href="/jobsweb/public/login.php">Login</a>
        <?php endif; ?>
    </div>

</div>

<!-- FIX: FULL WIDTH PAGE WRAPPER -->
<div class="container-fluid px-4">
