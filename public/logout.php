<?php
session_start();

// Destroy all applicant sessions
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
?>
