<?php
require_once("../config/config.php");
include __DIR__ . '/../includes/header.php';

// ✅ Validate and get job ID
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($job_id <= 0) {
    echo '<div class="container my-5"><div class="alert alert-danger">Invalid job ID.</div></div>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

// ✅ Prepare query securely
$stmt = $conn->prepare("SELECT id, title, company, location, description, created_at FROM jobs WHERE id = ?");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="container my-5"><div class="alert alert-warning">Job not found.</div></div>';
} else {
    $job = $result->fetch_assoc();
    ?>

    <div class="container my-5">
      <div class="card shadow-lg border-0 p-4">
        <h2 class="text-primary fw-bold mb-3"><?= htmlspecialchars($job['title']); ?></h2>
        <p class="mb-1"><strong>Company:</strong> <?= htmlspecialchars($job['company']); ?></p>
        <p class="mb-1"><strong>Location:</strong> <?= htmlspecialchars($job['location']); ?></p>

        <?php
          $posted_raw = $job['created_at'] ?? $job['posted_on'] ?? null;
          $posted = $posted_raw ? date("M d, Y", strtotime($posted_raw)) : "N/A";
        ?>
        <p class="text-muted small mb-4"><strong>Posted on:</strong> <?= $posted; ?></p>

        <hr>
        <h5 class="fw-semibold mt-3">Job Description</h5>
        <p class="text-secondary"><?= nl2br(htmlspecialchars($job['description'])); ?></p>

        <div class="mt-4">
          <a href="apply.php?job_id=<?= $job['id']; ?>" class="btn btn-success">Apply Now</a>
          <a href="jobs.php" class="btn btn-outline-primary ms-2">Back to Jobs</a>
        </div>
      </div>
    </div>

    <?php
}

$stmt->close();
include __DIR__ . '/../includes/footer.php';
?>
