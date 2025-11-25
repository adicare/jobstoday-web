<?php
/* ============================================================
   FILE: applicant/applications.php
   USE: Show applicant's applications history
   RESPONSE #: 11
   ============================================================ */

session_start();
include "../config/config.php";

// Auth check
if (!isset($_SESSION['applicant_id'])) {
    header("Location: ../public/auth/login.php");
    exit;
}

$app_id = $_SESSION['applicant_id'];

// Fetch applied jobs with job details
$sql = "
    SELECT ja.*, j.title, j.company, j.job_city, j.job_state
    FROM job_applications AS ja
    JOIN jobs AS j ON ja.job_id = j.id
    WHERE ja.applicant_id = $app_id
    ORDER BY ja.applied_at DESC
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Applications - CareerJano</title>

    <style>
        body { font-family:Arial; background:#f4f4f4; }
        .box {
            width:80%; margin:40px auto; background:#fff;
            padding:25px; border-radius:10px;
            box-shadow:0 0 12px rgba(0,0,0,0.1);
        }
        table {
            width:100%; border-collapse:collapse;
            margin-top:20px;
        }
        table th, table td {
            border:1px solid #ddd; padding:12px; text-align:left;
        }
        table th {
            background:#007bff; color:#fff;
        }
        .btn {
            padding:7px 12px; background:#007bff; color:#fff;
            text-decoration:none; border-radius:5px;
        }
        .btn:hover { background:#0056b3; }
    </style>
</head>

<body>

<div class="box">
    <h2>My Applications</h2>

    <?php if ($result->num_rows == 0): ?>
        <p>You haven't applied to any job yet.</p>
    <?php else: ?>

        <table>
            <tr>
                <th>Job Title</th>
                <th>Company</th>
                <th>Location</th>
                <th>Applied On</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['title'] ?></td>
                    <td><?= $row['company'] ?></td>
                    <td><?= $row['job_city'] ?>, <?= $row['job_state'] ?></td>
                    <td><?= date("d M Y", strtotime($row['applied_at'])) ?></td>
                    <td><span style="color:green;">Applied</span></td>
                    <td>
                        <a class="btn" href="../job-detail.php?id=<?= $row['job_id'] ?>">
                            View Job
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>

        </table>

    <?php endif; ?>
</div>

</body>
</html>
