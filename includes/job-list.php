<?php
/*
   FILE: /includes/job-list.php
*/
?>

<?php
$jobs = $conn->query("SELECT id, title, company, location FROM jobs ORDER BY id DESC");
if ($jobs && $jobs->num_rows > 0):
    while($job = $jobs->fetch_assoc()):
?>
    <div class="job-row" data-id="<?= (int)$job['id'] ?>">
        <strong class="job-title"><?= htmlspecialchars($job['title']) ?></strong>
        <div class="job-sub"><?= htmlspecialchars($job['company']) ?> • <?= htmlspecialchars($job['location']) ?></div>
    </div>
<?php
    endwhile;
else:
    echo "<div class='no-jobs'>No jobs found.</div>";
endif;
?>
