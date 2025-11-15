<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("../includes/config.php");

// Check if job ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid request.");
}

$job_id = intval($_GET['id']);

// Fetch job details
$sql = "SELECT * FROM jobs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Job not found.");
}

$job = $result->fetch_assoc();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($job['title']); ?> | CareerJano</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-sm p-4">
        <h2 class="text-primary"><?php echo htmlspecialchars($job['title']); ?></h2>
        <p><strong>Company:</strong> <?php echo htmlspecialchars($job['company']); ?></p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
        <p><strong>Type:</strong> <?php echo htmlspecialchars($job['job_type']); ?></p>
        <p><strong>Salary:</strong> <?php echo htmlspecialchars($job['salary']); ?></p>
        <p><strong>Description:</strong><br> <?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
        <p><small>Posted on: <?php echo date("M d, Y", strtotime($job['created_at'])); ?></small></p>
        <a href="jobs.php" class="btn btn-secondary mt-3">← Back to Jobs</a>
    </div>
</div>

</body>
</html>
