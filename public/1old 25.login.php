<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/config.php';

$error = "";

/* ----------------------------------------------------------
   IF ALREADY LOGGED IN → SEND TO CORRECT DASHBOARD
---------------------------------------------------------- */
if (!empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {

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

/* ----------------------------------------------------------
   LOGIN FORM SUBMISSION
---------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email    = strtolower(trim($_POST['email']));
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";

    } else {

        /* ----------------------------------------------
           FIND USER IN `users` TABLE
        ---------------------------------------------- */
        $sql = "SELECT id, name, email, password, role 
                FROM users 
                WHERE LOWER(TRIM(email)) = ? 
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            /* ------------------------------------------
               CHECK PASSWORD (plain or hashed)
            ------------------------------------------ */
            $pass_ok = ($user['password'] === $password) ||
                       password_verify($password, $user['password']);

            if ($pass_ok) {

                session_regenerate_id(true); // security

                /* --- COMMON SESSION VALUES --- */
                $_SESSION['logged_in']  = true;
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role']  = strtolower($user['role']);

                /* ------------------------------------------
                   APPLICANT ROLE → LOAD applicant ID
                ------------------------------------------ */
                if ($_SESSION['user_role'] === 'applicant') {

                    // Fetch job_seekers.id
                    $stmt2 = $conn->prepare("
                        SELECT id 
                        FROM job_seekers 
                        WHERE email = ? 
                        LIMIT 1
                    ");
                    $stmt2->bind_param("s", $user['email']);
                    $stmt2->execute();
                    $res2 = $stmt2->get_result();

                    if ($res2->num_rows === 1) {
                        $row2 = $res2->fetch_assoc();
                        $_SESSION['applicant_id'] = $row2['id'];
                    } else {
                        $_SESSION['applicant_id'] = null;
                    }
                }

                /* ------------------------------------------
                   FINAL REDIRECT BASED ON ROLE
                ------------------------------------------ */
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
            else {
                $error = "Incorrect password.";
            }

        } else {
            $error = "No account found with this email.";
        }

        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Login - JobsToday</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background:#e8f2ff;">

<div class="container d-flex justify-content-center align-items-center" style="min-height:90vh;">
  <div class="card shadow-lg p-4 border-0" style="width: 420px; border-radius: 15px;">

    <h3 class="text-center text-primary fw-bold">Welcome Back</h3>
    <p class="text-center text-muted mb-4">Login to continue</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label fw-semibold">Email</label>
        <input type="email" name="email" class="form-control" required placeholder="Enter your email">
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Password</label>
        <input type="password" name="password" class="form-control" required placeholder="Enter password">
      </div>

      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <div class="text-center mt-3">
      <a href="register.php" class="text-decoration-none text-primary fw-semibold">
        New user? Register here
      </a>
    </div>

  </div>
</div>

</body>
</html>
