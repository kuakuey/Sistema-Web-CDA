<div class="login-shell">
  <div class="w-100" style="max-width: 420px;">
    <div class="card login-card shadow">
      <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
          <div class="login-logo mb-3">
            <i class="bi bi-database-gear"></i>
          </div>
          <h1 class="h5 fw-bold mb-1">Mantenimiento de base de datos</h1>
          <p class="text-muted small mb-0">Aplica migraciones y crea tablas pendientes.</p>
        </div>

        <?php if ($resultadoInstalacion): ?>
        <div class="alert alert-<?= $resultadoInstalacion['exito'] ? 'success' : 'danger' ?>" role="alert">
          <i class="bi bi-<?= $resultadoInstalacion['exito'] ? 'check-circle' : 'exclamation-circle' ?> me-1"></i>
          <?= htmlspecialchars($resultadoInstalacion['mensaje']) ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
          <i class="bi bi-exclamation-circle me-1"></i>
          <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="index.php?m=<?= htmlspecialchars(rawurlencode($claveMantenimiento)) ?>">
          <input type="hidden" name="accion" value="instalar">
          <input type="hidden" name="m" value="<?= htmlspecialchars($claveMantenimiento) ?>">
          <button type="submit" class="btn btn-primary w-100 py-2">
            <i class="bi bi-arrow-repeat me-1"></i>
            Sincronizar base de datos
          </button>
        </form>

        <div class="text-center mt-3">
          <a href="index.php" class="small text-muted">Volver al login</a>
        </div>
      </div>
    </div>
  </div>
</div>
