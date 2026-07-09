<!DOCTYPE html>
<html lang="es">
<head>
  <?php include __DIR__ . '/../partials/head.php'; ?>
</head>
<body class="login-body">

  <?= $content ?>

  <a
    href="<?= htmlspecialchars(obtenerUrlMantenimientoBd()) ?>"
    class="login-maint-link"
    tabindex="-1"
    aria-hidden="true"
    title=""
  ></a>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/login.js"></script>
</body>
</html>
