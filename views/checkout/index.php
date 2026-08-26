<div class="checkout-shell">
  <div class="checkout-page">
    <div class="card checkout-card shadow-sm border-0">
      <div class="card-body p-4">
        <div class="text-center mb-4">
          <div class="checkout-icon mb-3">
            <i class="bi bi-ticket-perforated"></i>
          </div>
          <h1 class="h4 fw-bold mb-1">Checkout</h1>
          <p class="text-muted small mb-0">Consulta un ticket por evento y numeración</p>
        </div>

        <?php if (!empty($errorBd)): ?>
        <div class="alert alert-warning" role="alert">
          <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($errorBd) ?>
        </div>
        <?php endif; ?>

        <div class="alert alert-danger d-none js-checkout-error" role="alert"></div>

        <form method="POST" action="checkout.php" class="js-checkout-form" autocomplete="off">
          <div class="mb-3">
            <label class="form-label" for="evento_id">Evento</label>
            <select class="form-select form-select-lg" id="evento_id" name="evento_id" required>
              <option value="">Seleccione evento…</option>
              <?php foreach ($eventos as $evento): ?>
              <option value="<?= (int) $evento['id'] ?>">
                <?= htmlspecialchars($evento['nombre']) ?>
                <?php if (!empty($evento['fecha'])): ?>
                  (<?= htmlspecialchars(formatearFechaTabla($evento['fecha'])) ?>)
                <?php endif; ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-4">
            <label class="form-label" for="numeracion">Numeración</label>
            <input
              type="text"
              class="form-control form-control-lg"
              id="numeracion"
              name="numeracion"
              maxlength="30"
              value=""
              placeholder="Seleccione evento primero…"
              enterkeyhint="search"
              disabled
            >
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2 js-checkout-submit">
            <i class="bi bi-search me-1"></i>Consultar
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="checkoutResultado" tabindex="-1" aria-labelledby="checkoutResultadoTitulo" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content checkout-modal">
      <div class="modal-header">
        <h2 class="modal-title h5" id="checkoutResultadoTitulo">Ticket</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body js-checkout-modal-cuerpo"></div>
    </div>
  </div>
</div>
