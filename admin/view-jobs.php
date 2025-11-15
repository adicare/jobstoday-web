<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) session_start();

// ✅ Allow only admin
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
  <h2 class="mb-4 text-primary">All Job Postings</h2>

  <?php
  // ✅ Fixed: match your actual DB column names
  $query = "SELECT id, title, company, location, description, created_at FROM jobs ORDER BY created_at DESC";
  $stmt = $conn->prepare($query);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 0) {
      echo '<div class="alert alert-info">No job postings found.</div>';
  } else {
      echo '<div class="table-responsive">';
      echo '<table class="table table-bordered table-striped align-middle">';
      echo '<thead class="table-primary">
              <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Company</th>
                <th>Location</th>
                <th>Posted On</th>
                <th>Actions</th>
              </tr>
            </thead><tbody>';

      while ($row = $result->fetch_assoc()) {
          $id = (int)$row['id'];
          $title = htmlspecialchars($row['title']);
          $company = htmlspecialchars($row['company']);
          $location = htmlspecialchars($row['location']);
          $posted = !empty($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : 'N/A';

          echo "<tr>
                  <td>{$id}</td>
                  <td>{$title}</td>
                  <td>{$company}</td>
                  <td>{$location}</td>
                  <td>{$posted}</td>
                  <td>
                    <a href='../public/job-detail.php?id={$id}' target='_blank' class='btn btn-sm btn-primary'>View</a>
                    <a href='../employer/add-job.php?edit={$id}' class='btn btn-sm btn-warning'>Edit</a>
                    <a href='delete-job.php?id={$id}' class='btn btn-sm btn-danger' onclick='return confirm(\"Delete this job?\")'>Delete</a>
                  </td>
                </tr>";
      }

      echo '</tbody></table></div>';
  }

  $stmt->close();
  ?>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
