<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'employer') {
    header("Location: ../public/login.php");
    exit;
}

require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $company = trim($_POST['company']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $employer_id = $_SESSION['user_id']; // track who posted it

    if ($title && $company && $location) {
        $stmt = $conn->prepare("INSERT INTO jobs (title, company, location, description, employer_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $title, $company, $location, $description, $employer_id);
        if ($stmt->execute()) {
            $success = "Job added successfully!";
        } else {
            $error = "Database error: " . htmlspecialchars($conn->error);
        }
    } else {
        $error = "Please fill all required fields.";
    }
}
?>

<?php include('header.php'); ?>

<div class="card shadow-sm p-4 mt-4">
  <h3 class="text-primary mb-3">Add New Job</h3>

  <?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php elseif (!empty($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label fw-bold">Job Title</label>
      <input type="text" name="title" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label fw-bold">Company</label>
      <input type="text" name="company" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label fw-bold">Location</label>
      <input type="text" name="location" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label fw-bold">Description</label>
      <textarea name="description" class="form-control" rows="4"></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Add Job</button>
  </form>
</div>

<?php include('../includes/footer.php'); ?>
