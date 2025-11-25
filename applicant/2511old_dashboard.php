<?php
// =============================================================
// File: applicant/dashboard.php
// Purpose: Applicant's main dashboard after login
// =============================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) session_start();

// ✅ Access control: only applicant role allowed
if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'applicant'
) {
    header("Location: ../public/login.php");
    exit;
}

// ✅ Include global config & header
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
  <div class="card shadow-lg p-4 border-0">
    <h2 class="text-primary fw-bold mb-3">Applicant Dashboard</h2>
    <p class="text-muted">
      Welcome, <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Applicant'); ?></strong>!
    </p>
    <p class="text-secondary small">
      Here you can explore available jobs, manage your applications, and update your career profile.
    </p>

    <hr class="my-4">

    <div class="row text-center">
      <!-- View Available Jobs -->
      <div class="col-md-4 mb-4">
        <div class="card shadow-sm p-4 border-0 h-100">
          <h5 class="fw-bold text-primary">Browse Jobs</h5>
          <p class="text-muted small">Explore and apply for the latest job openings.</p>
          <a href="../public/jobs.php" class="btn btn-primary w-100">View Jobs</a>
        </div>
      </div>

      <!-- My Applications -->
      <div class="col-md-4 mb-4">
        <div class="card shadow-sm p-4 border-0 h-100">
          <h5 class="fw-bold text-primary">My Applications</h5>
          <p class="text-muted small">Track the jobs you’ve applied for and view their status.</p>
          <a href="my-applications.php" class="btn btn-success w-100">View Applications</a>
        </div>
      </div>

      <!-- Update Profile -->
      <div class="col-md-4 mb-4">
        <div class="card shadow-sm p-4 border-0 h-100">
          <h5 class="fw-bold text-primary">Update Profile</h5>
          <p class="text-muted small">Edit your resume or update personal details anytime.</p>
          <a href="profile.php" class="btn btn-info w-100 text-white">Edit Profile</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
