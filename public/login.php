<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/header.php';

$error = "";

// ✅ If already logged in, redirect directly
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    switch ($_SESSION['user_role']) {
        case 'admin':
            header("Location: ../admin/dashboard.php");
            exit;
        case 'employer':
            header("Location: ../employer/dashboard.php");
            exit;
        case 'applicant':
            header("Location: ../applicant/dashboard.php");
            exit;
    }
}

// ✅ Handle Login Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email']));
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE LOWER(TRIM(email)) = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // ✅ Verify password (plain or hashed)
            if ($user['password'] === $password || password_verify($password, $user['password'])) {
                session_regenerate_id(true);

                // ✅ Store universal session data
                $_SESSION['logged_in']  = true;
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role']  = strtolower($user['role']); // admin | employer | applicant

                // ✅ Redirect based on role
                switch ($_SESSION['user_role']) {
                    case 'admin':
                        header("Location: ../admin/dashboard.php");
                        break;
                    case 'employer':
                        header("Location: ../employer/dashboard.php");
                        break;
                    case 'applicant':
                        header("Location: ../applicant/dashboard.php");
                        break;
                    default:
                        $error = "Unknown user role found.";
                }
                exit;
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No account found with this email.";
        }

        $stmt->close();
    }
}
?>

<div class="container d-flex justify-content-center align-items-center" style="min-height:90vh;">
  <div class="card shadow-lg p-4 border-0" style="width: 420px; border-radius: 15px;">
    <h3 class="text-center text-primary mb-3 fw-bold">
      Login to <span class="text-dark">CareerJano</span>
    </h3>
    <p class="text-center text-muted mb-4">
      Access your dashboard as <strong>Admin</strong>, <strong>Employer</strong>, or <strong>Applicant</strong>
    </p>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="mb-3">
        <label class="form-label fw-semibold">Email Address</label>
        <input type="email" name="email" class="form-control" required placeholder="Enter your registered email">
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Password</label>
        <input type="password" name="password" class="form-control" required placeholder="Enter your password">
      </div>

      <button type="submit" class="btn btn-primary w-100 mt-2">Login</button>
    </form>

    <div class="text-center mt-3">
      <a href="register.php" class="text-decoration-none text-primary fw-semibold">
        New user? Register here
      </a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
