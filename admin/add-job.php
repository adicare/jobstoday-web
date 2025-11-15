<?php 
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../public/login.php");
    exit;
}
?>


<?php
session_start();
if (empty($_SESSION['user_id'])) { 
    header('Location: login.php'); 
    exit; 
}

//require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require __DIR__ . '/../includes/Header.php';

$success = $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize inputs
    $title     = trim($_POST['title'] ?? '');
    $company   = trim($_POST['company'] ?? '');
    $location  = trim($_POST['location'] ?? '');
    $desc      = trim($_POST['description'] ?? '');

    // Validate required fields
    if ($title && $company && $location && $desc) {
        // Insert into DB
        $stmt = $conn->prepare("
            INSERT INTO jobs (title, company, location, description, posted_on)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("ssss", $title, $company, $location, $desc);
        $stmt->execute();
        $stmt->close();

        $success = "✅ Job added successfully.";
    } else {
        $error = "⚠️ Please fill all required fields.";
    }
}
?>

<h3>Add New Job</h3>

<?php if ($success): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST">
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Job Title *</label>
      <input type="text" name="title" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Company *</label>
      <input type="text" name="company" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Location *</label>
      <input type="text" name="location" class="form-control" required>
    </div>
    <div class="col-12">
      <label class="form-label">Description *</label>
      <textarea name="description" class="form-control" rows="5" required></textarea>
    </div>
  </div>

  <button type="submit" class="btn btn-primary mt-3">Save Job</button>
</form>

<?php include __DIR__ . '/../includes/Footer.php'; ?>
