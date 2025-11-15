<?php
// =============================================================
// File: employer/my-jobs.php
// Purpose: Display jobs posted by employer
// =============================================================

if (session_status() === PHP_SESSION_NONE) session_start();

// ✅ Employer-only access
if (empty($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'employer') {
    header("Location: ../public/login.php");
    exit;
}

// ✅ Database connection
require_once __DIR__ . '/../config/config.php';
$employer_id = $_SESSION['user_id'] ?? 0;

// ✅ Fetch jobs for this employer
$query = "SELECT id, title, company, location, created_at FROM jobs WHERE employer_id = ? ORDER BY id DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $employer_id);
$stmt->execute();
$result = $stmt->get_result();

include('header.php');
?>

<div class="card shadow-sm p-4 mt-4">
  <h3 class="text-primary fw-bold mb-4">My Posted Jobs</h3>

  <div class="table-responsive">
    <table class="table table-hover align-middle text-center">
      <thead class="table-primary">
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Company</th>
          <th>Location</th>
          <th>Posted On</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['id']); ?></td>
              <td><?= htmlspecialchars($row['title']); ?></td>
              <td><?= htmlspecialchars($row['company']); ?></td>
              <td><?= htmlspecialchars($row['location']); ?></td>
              <td><?= $row['created_at'] ? date("M d, Y", strtotime($row['created_at'])) : '—'; ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="text-muted py-4">No jobs posted yet.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include('../includes/footer.php'); ?>
