<?php
include(__DIR__ . '/../config/config.php');

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$limit = 8;
$offset = ($page - 1) * $limit;

// Fetch 8 jobs
$sql = "
    SELECT id, title, company, location 
    FROM jobs 
    ORDER BY id DESC 
    LIMIT $limit OFFSET $offset
";

$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "<p>No more jobs.</p>";
    exit;
}

while ($job = $result->fetch_assoc()):
?>

<div class="job-row" data-id="<?= $job['id']; ?>">
    <div class="fw-bold"><?= htmlspecialchars($job['title']); ?></div>
    <small><?= htmlspecialchars($job['company']); ?> • <?= htmlspecialchars($job['location']); ?></small>
</div>

<?php endwhile; ?>
