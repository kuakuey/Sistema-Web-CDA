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

          <div class="mb-3">
            <label class="form-label" for="numeracion">Numeración del ticket</label>
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

          <button type="submit" class="btn btn-primary w-100 py-2 mb-4 js-checkout-submit">
            <i class="bi bi-search me-1"></i>Consultar
          </button>

          <div class="checkout-datos">
            <div class="mb-3">
              <label class="form-label" for="ticket_nombre">Nombre</label>
              <input type="text" class="form-control" id="ticket_nombre" value="" readonly>
            </div>
            <div class="mb-3">
              <label class="form-label" for="ticket_numeracion">Numeración</label>
              <input type="text" class="form-control" id="ticket_numeracion" value="" readonly>
            </div>
            <div class="mb-3">
              <label class="form-label" for="ticket_tipo">Tipo de entrada</label>
              <input type="text" class="form-control" id="ticket_tipo" value="" readonly>
            </div>
            <div class="mb-0">
              <label class="form-label" for="ticket_estado">Estado</label>
              <input type="text" class="form-control" id="ticket_estado" value="" readonly>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
