<?php
// includes/admin_protect.php
// Use on all admin pages: include("../includes/admin_protect.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Troubleshooting helper (comment out in production).
// If you're debugging a redirect loop, uncomment the next two lines to dump session:
// echo '<pre>'; print_r($_SESSION); echo '</pre>'; // <-- remove or comment later

// Accepted admin indicators (check all common names you may have used)
$isAdmin = false;
if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $isAdmin = true;
}
if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $isAdmin = true;
}
if (!empty($_SESSION['is_admin']) && ($_SESSION['is_admin'] == 1 || $_SESSION['is_admin'] === true)) {
    $isAdmin = true;
}

// If not admin, redirect to public login (do not redirect back into admin)
if (!$isAdmin) {
    header("Location: ../public/login.php");
    exit;
}
