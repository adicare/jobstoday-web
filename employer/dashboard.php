<?php
// =============================================================
// File: employer/dashboard.php
// Purpose: Employer Dashboard - Manage own job postings
// =============================================================

if (session_status() === PHP_SESSION_NONE) session_start();

// ✅ Ensure only employer can access
if (empty($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'employer') {
    header("Location: ../public/login.php");
    exit;
}

include('../includes/header.php');
?>

<div class="container my-5">
  <div class="card shadow-lg p-4 border-0">
    <h2 class="text-primary fw-bold mb-3">Employer Dashboard</h2>
    <p class="text-muted">Welcome, <strong><?= htmlspecialchars($_SESSION['user_name']); ?></strong>!</p>
    <p class="text-secondary small mb-4">You can manage your job postings, view applicants, and track your hiring activity here.</p>

    <hr class="mb-4">

    <div class="row text-center">
      <!-- Post a Job -->
      <div class="col-md-4 mb-3">
        <div class="card p-3 border-0 shadow-sm h-100">
          <h5 class="text-dark fw-semibold">Post a Job</h5>
          <p class="text-muted small">Add new openings to attract qualified candidates.</p>
          <a href="add-job.php" class="btn btn-success w-100 mt-2">Add Job</a>
        </div>
      </div>

      <!-- View My Jobs -->
      <div class="col-md-4 mb-3">
        <div class="card p-3 border-0 shadow-sm h-100">
          <h5 class="text-dark fw-semibold">View My Jobs</h5>
          <p class="text-muted small">Manage your posted jobs — edit or delete them.</p>
          <a href="my-jobs.php" class="btn btn-primary w-100 mt-2">View Jobs</a>
        </div>
      </div>

      <!-- Review Applicants -->
      <div class="col-md-4 mb-3">
        <div class="card p-3 border-0 shadow-sm h-100">
          <h5 class="text-dark fw-semibold">Review Applicants</h5>
          <p class="text-muted small">View and shortlist applicants for your job postings.</p>
          <a href="view-applicants.php" class="btn btn-info w-100 mt-2 text-white">View Applicants</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include('../includes/footer.php'); ?>
