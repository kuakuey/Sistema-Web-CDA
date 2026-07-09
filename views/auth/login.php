<div class="login-shell">
  <div class="w-100" style="max-width: 420px;">
    <div class="card login-card shadow">
      <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
          <div class="login-logo mb-3">
            <i class="bi bi-grid-3x3-gap-fill"></i>
          </div>
          <h1 class="h4 fw-bold">Bienvenido</h1>
          <p class="text-muted small">Ingresa tus credenciales para continuar</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
          <i class="bi bi-exclamation-circle me-1"></i>
          <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="index.php" novalidate>
          <input type="hidden" name="accion" value="iniciar">
          <div class="mb-3">
            <label for="usuario" class="form-label">Usuario</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person"></i></span>
              <input
                type="text"
                class="form-control"
                id="usuario"
                name="usuario"
                placeholder="Usuario"
                required
                autocomplete="username"
                value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
              >
            </div>
          </div>

          <div class="mb-4">
            <label for="clave" class="form-label">Contraseña</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock"></i></span>
              <input
                type="password"
                class="form-control"
                id="clave"
                name="clave"
                placeholder="••••••••"
                required
                autocomplete="current-password"
              >
              <button
                type="button"
                class="btn btn-toggle-password"
                id="toggleClave"
                aria-label="Mostrar contraseña"
                aria-pressed="false"
                title="Mostrar contraseña"
              >
                <i class="bi bi-eye" aria-hidden="true"></i>
              </button>
            </div>
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2">
            <i class="bi bi-box-arrow-in-right me-1"></i>
            Iniciar sesión
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
