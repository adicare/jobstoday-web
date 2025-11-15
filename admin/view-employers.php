<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) session_start();

// ✅ New unified session check
if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'admin'
) {
    header("Location: ../public/login.php");
    exit;
}

require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
  <h2 class="mb-4 text-primary">Registered Employers</h2>

  <?php
  $query = "SELECT id, name, email, created_at FROM users WHERE role = 'employer' ORDER BY id DESC";
  $stmt = $conn->prepare($query);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 0) {
      echo '<div class="alert alert-info">No employers found.</div>';
  } else {
      echo '<div class="table-responsive">';
      echo '<table class="table table-bordered table-striped align-middle">';
      echo '<thead class="table-primary"><tr><th>ID</th><th>Name</th><th>Email</th><th>Registered On</th></tr></thead><tbody>';

      while ($row = $result->fetch_assoc()) {
          echo "<tr>
                  <td>{$row['id']}</td>
                  <td>" . htmlspecialchars($row['name']) . "</td>
                  <td>" . htmlspecialchars($row['email']) . "</td>
                  <td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>
                </tr>";
      }

      echo '</tbody></table></div>';
  }

  $stmt->close();
  ?>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
