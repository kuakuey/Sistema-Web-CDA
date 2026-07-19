<!DOCTYPE html>
<html lang="es" class="app-html">
<head>
  <?php include __DIR__ . '/../partials/head.php'; ?>
</head>
<body class="app-body">

  <?php include __DIR__ . '/../partials/navbar.php'; ?>

  <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

  <?php
  $sidebarVariant = 'mobile';
  include __DIR__ . '/../partials/sidebar.php';
  ?>

  <div class="container-fluid app-wrapper">
    <div class="row g-0">
      <?php
      $sidebarVariant = 'desktop';
      include __DIR__ . '/../partials/sidebar.php';
      ?>

      <main class="col-12 col-lg-10 main-content p-4">
        <?= $content ?>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="js/theme.js"></script>
  <?php
  $appJs = dirname(__DIR__, 2) . '/js/app.js';
  $appJsVersion = is_file($appJs) ? (string) filemtime($appJs) : '1';
  ?>
  <script src="js/app.js?v=<?= htmlspecialchars($appJsVersion) ?>"></script>
  <?php
  $sweetalertJs = dirname(__DIR__, 2) . '/js/sweetalert-app.js';
  $sweetalertJsVersion = is_file($sweetalertJs) ? (string) filemtime($sweetalertJs) : '1';
  ?>
  <script src="js/sweetalert-app.js?v=<?= htmlspecialchars($sweetalertJsVersion) ?>"></script>
</body>
</html>
