<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$isLoggedIn = !empty($_SESSION['user_id']);
$userRole   = $_SESSION['user_role'] ?? '';
$userName   = $_SESSION['user_name'] ?? '';
?>

<!doctype html>
<html lang="en">
<head>

    <!-- Load HOME/THEME CSS FIRST -->
    <link rel="stylesheet" href="/jobsweb/assets/css/home.css?v=<?= time() ?>">

    <!-- Load MAIN STYLE.CSS (your full global design system, sidebar, meter, layout) -->
    <link rel="stylesheet" href="/jobsweb/assets/css/style.css?v=<?= time() ?>">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>JobsToday</title>

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Remove page-wide background override; use global from style.css -->
    <style>
        .top-header {
            background-color: #0a4c90;
            color: white;
            padding: 14px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
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

            <?php if (!$isLoggedIn): ?>
                <li><a href="/jobsweb/public/register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="login-btn-top">

        <?php if ($isLoggedIn): ?>

            <?php if ($userRole === 'applicant'): ?>
                <a href="/jobsweb/applicant/dashboard.php">
                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($userName) ?>
                </a>
            <?php endif; ?>

            <?php if ($userRole === 'employer'): ?>
                <a href="/jobsweb/employer/dashboard.php">
                    <i class="bi bi-building"></i> Employer Dashboard
                </a>
            <?php endif; ?>

            <?php if ($userRole === 'admin'): ?>
                <a href="/jobsweb/admin/dashboard.php">
                    <i class="bi bi-shield-lock"></i> Admin Panel
                </a>
            <?php endif; ?>

            &nbsp;&nbsp;|&nbsp;&nbsp;
            <a href="/jobsweb/public/logout.php" class="text-warning">Logout</a>

        <?php else: ?>

            <a href="/jobsweb/public/login.php">Login</a>

        <?php endif; ?>

    </div>

</div>

<!-- PAGE CONTENT WRAPPER (start of every page content) -->
<div class="container-fluid px-4">
