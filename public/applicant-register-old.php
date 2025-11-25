<?php
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/header.php'; // ✅ Universal theme header

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if ($name && $email && $password && $confirm) {
        if ($password !== $confirm) {
            $error = "Passwords do not match!";
        } else {
            // Check if email already exists
            $check = $conn->prepare("SELECT id FROM job_seekers WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = "Email already registered. Please login instead.";
            } else {
                // Secure password hash
                $hashed = password_hash($password, PASSWORD_DEFAULT);

                // Insert new job seeker record
                $stmt = $conn->prepare("INSERT INTO job_seekers (name, email, phone, password) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $email, $phone, $hashed);

                if ($stmt->execute()) {
                    $success = "Registration successful! You can now log in.";
                } else {
                    $error = "Database error. Please try again.";
                }
                $stmt->close();
            }
            $check->close();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>

<div class="container d-flex justify-content-center align-items-center" style="min-height:90vh;">
  <div class="card shadow-lg p-4 border-0" style="width: 480px; border-radius: 15px;">
    <h3 class="text-center text-primary fw-bold mb-3">Create Your <span class="text-dark">CareerJano</span> Account</h3>
    <p class="text-center text-muted mb-4">Join as an applicant and start your career journey today.</p>

    <?php if ($success): ?>
      <div class="alert alert-success text-center"><?= htmlspecialchars($success); ?></div>
    <?php elseif ($error): ?>
      <div class="alert alert-danger text-center"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label fw-semibold">Full Name *</label>
        <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Email *</label>
        <input type="email" name="email" class="form-control" placeholder="Enter a valid email address" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Phone</label>
        <input type="text" name="phone" class="form-control" placeholder="Optional">
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Password *</label>
        <input type="password" name="password" class="form-control" placeholder="Create password" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Confirm Password *</label>
        <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
      </div>

      <button type="submit" class="btn btn-primary w-100 mt-2">Register</button>

      <p class="text-center mt-3 mb-0">
        Already have an account?
        <a href="login.php" class="text-decoration-none text-primary fw-semibold">Login here</a>
      </p>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
