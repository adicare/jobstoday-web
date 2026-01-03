<?php
/* ============================================================
   FILE: applicant/profile_test_view.php
   PURPOSE: Debug page to display COMPLETE job_seekers data
            for verifying what exactly is stored in DB.
   ============================================================ */

session_start();

if (!isset($_SESSION['applicant_id'])) {
    die("Not logged in.");
}

$app_id = intval($_SESSION['applicant_id']);

require_once "../config/config.php";

$query = $conn->query("SELECT * FROM job_seekers WHERE id = $app_id LIMIT 1");

if (!$query || $query->num_rows === 0) {
    die("<h2>No applicant data found.</h2>");
}

$user = $query->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile Data Debug View</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 30px;
        }
        h2 {
            color: #004aad;
        }
        table {
            width: 80%;
            border-collapse: collapse;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #004aad;
            color: white;
            text-align: left;
            width: 250px;
        }
        tr:hover {
            background: #f1f7ff;
        }
    </style>
</head>
<body>

<h2>🔍 Debug View: Stored Profile Data (job_seekers)</h2>

<table>
<?php
foreach ($user as $field => $value) {
    $cleanVal = htmlspecialchars($value ?? "(NULL)");
    if ($cleanVal === "") $cleanVal = "(empty)";
    echo "<tr><th>$field</th><td>$cleanVal</td></tr>";
}
?>
</table>

</body>
</html>
