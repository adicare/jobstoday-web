<?php
// ==========================================================
// config/db.php
// Purpose: Central database connection file for JobsWeb
// ==========================================================

$host   = "localhost";
$user   = "root";
$pass   = "";   // Default for XAMPP
$dbname = "defchitt_jobsweb_db"; // ✅ Make sure this matches phpMyAdmin DB name

// ✅ Better error reporting for mysqli (during development)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
