<?php
// =============================================================
// File: admin/dashboard.php
// Purpose: Admin home page after login (aligned with new login.php)
// =============================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ Start session safely
if (session_status() === PHP_SESSION_NONE) session_start();

// ✅ Access control: only admins can access
if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'admin'
) {
    header("Location: ../public/login.php");
    exit;
}

// ✅ Include config + universal header
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
  <div class="card shadow-lg p-4 border-0">
    <h2 class="text-primary fw-bold mb-3">Admin Dashboard</h2>
    <p class="text-muted">
      Welcome, <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></strong>!
    </p>

    <hr class="mb-4">

    <div class="row text-center">
      <!-- Manage Applicants -->
      <div class="col-md-4 mb-3">
        <div class="card p-3 border-0 shadow-sm h-100">
          <h5 class="text-dark fw-semibold">Manage Applicants</h5>
          <p class="text-muted small">View and manage all registered applicants.</p>
          <a href="view-applicants.php" class="btn btn-outline-primary btn-sm mt-2">View Applicants</a>
        </div>
      </div>

      <!-- Manage Employers -->
      <div class="col-md-4 mb-3">
        <div class="card p-3 border-0 shadow-sm h-100">
          <h5 class="text-dark fw-semibold">Manage Employers</h5>
          <p class="text-muted small">Check and verify employer accounts.</p>
          <a href="view-employers.php" class="btn btn-outline-primary btn-sm mt-2">View Employers</a>
        </div>
      </div>

      <!-- Manage Job Postings -->
      <div class="col-md-4 mb-3">
        <div class="card p-3 border-0 shadow-sm h-100">
          <h5 class="text-dark fw-semibold">Job Postings</h5>
          <p class="text-muted small">Review and approve new job listings.</p>
          <a href="view-jobs.php" class="btn btn-outline-primary btn-sm mt-2">View Jobs</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
