<?php
require_once("../config/config.php");
include __DIR__ . '/../includes/header.php'; // ✅ universal header (includes your theme)

// ✅ Fetch job listings safely
$result = $conn->query("SELECT id, title, company, location, description, created_at FROM jobs ORDER BY created_at DESC");
?>

<div class="container my-5">
  <h2 class="text-primary fw-bold mb-4 text-center">Available Jobs</h2>

  <?php if ($result && $result->num_rows > 0): ?>
    <div class="row g-4">
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card job-card shadow-sm h-100 border-0">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title text-primary fw-bold">
                <?= htmlspecialchars($row['title'] ?? 'Untitled'); ?>
              </h5>
              <p class="mb-1"><strong>Company:</strong> <?= htmlspecialchars($row['company'] ?? 'N/A'); ?></p>
              <p class="mb-1"><strong>Location:</strong> <?= htmlspecialchars($row['location'] ?? 'N/A'); ?></p>

              <p class="text-muted small mt-2 flex-grow-1">
                <?= nl2br(htmlspecialchars(substr($row['description'] ?? '', 0, 100))) . '...'; ?>
              </p>

              <div class="mt-auto">
                <?php
                  $posted_raw = $row['created_at'] ?? $row['posted_on'] ?? null;
                  $posted = $posted_raw ? date("M d, Y", strtotime($posted_raw)) : "N/A";
                ?>
                <p class="text-secondary small mb-2">
                  <strong>Posted:</strong> <?= $posted; ?>
                </p>
                <a href="job-detail.php?id=<?= (int)$row['id']; ?>" class="btn btn-primary w-100">
                  View Details
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="alert alert-info text-center mt-5">
      No jobs available at the moment. Please check back soon.
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
