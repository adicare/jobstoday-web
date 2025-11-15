<?php
// ==========================================================
// config/config.php
// Purpose: Central database connection file for CareerJano
// ==========================================================

// ❌ Remove any include_once("../includes/config.php") — not needed

$host   = "localhost";
$user   = "root";
$pass   = ""; // Default for XAMPP
$dbname = "defchitt_jobsweb_db"; // ✅ Make sure this matches your phpMyAdmin DB name

// ✅ Better error reporting for mysqli (during development)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    // Friendly error message for dev environment
    die("Database connection failed: " . $e->getMessage());
}
?>
