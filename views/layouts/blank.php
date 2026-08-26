<!DOCTYPE html>
<html lang="es">
<head>
  <?php include __DIR__ . '/../partials/head.php'; ?>
</head>
<body class="blank-body">

  <?= $content ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <?php foreach ($scriptsPagina ?? [] as $scriptPagina): ?>
  <?php
    $scriptRelativo = ltrim((string) $scriptPagina, '/');
    $scriptFs = dirname(__DIR__, 2) . '/' . $scriptRelativo;
    $scriptVersion = is_file($scriptFs) ? (string) filemtime($scriptFs) : '1';
  ?>
  <script src="<?= htmlspecialchars($scriptRelativo) ?>?v=<?= htmlspecialchars($scriptVersion) ?>"></script>
  <?php endforeach; ?>
</body>
</html>
