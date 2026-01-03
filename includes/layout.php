<?php
// FILE: includes/layout.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/config.php'; // DB connection
// Expect $pageTitle and $contentHtml (or include module in content area)
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= htmlspecialchars($pageTitle ?? 'JobsToday') ?></title>
  <link rel="stylesheet" href="/assets/css/glass-theme.css" />
  <script defer src="/assets/js/app.js"></script>
</head>
<body class="jt-app">
  <?php include __DIR__ . '/header.php'; ?>

  <div class="jt-shell">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="jt-main">
      <div class="jt-content-wrapper">
        <!-- Page-level title -->
        <div class="page-title">
          <h1><?= htmlspecialchars($pageTitle ?? '') ?></h1>
        </div>

        <!-- Content card area (consistent look) -->
        <section class="glass-card" id="page-card">
          <?php
            // Modules should either set $contentHtml or include their markup here
            if (isset($contentHtml)) {
              echo $contentHtml;
            } else {
              // fallback: module inserts content directly after include
            }
          ?>
        </section>
      </div>
    </main>
  </div>

  <?php include __DIR__ . '/footer.php'; ?>
</body>
</html>
