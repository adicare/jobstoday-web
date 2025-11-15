
<?php 
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroy session and redirect to public login page
session_unset();
session_destroy();

// Redirect to main login page
header("Location: ../public/login.php");
exit;
?>
