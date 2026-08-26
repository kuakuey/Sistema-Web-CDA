<div class="checkout-shell">
  <div class="checkout-page">
    <div class="checkout-page__top">
      <a href="eventos.php" class="checkout-back">
        <i class="bi bi-arrow-left me-1"></i>Volver
      </a>
    </div>

    <div class="card checkout-card shadow-sm border-0">
      <div class="card-body p-4">
        <div class="text-center mb-4">
          <div class="checkout-icon mb-3">
            <i class="bi bi-ticket-perforated"></i>
          </div>
          <h1 class="h4 fw-bold mb-1">Checkout</h1>
          <p class="text-muted small mb-0">Consulta un ticket por evento y numeración</p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="alert alert-danger" role="alert">
          <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($errorBd)): ?>
        <div class="alert alert-warning" role="alert">
          <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($errorBd) ?>
        </div>
        <?php endif; ?>

        <form method="GET" action="checkout.php" class="js-checkout-form" autocomplete="off">
          <input type="hidden" name="consultar" value="1">

          <div class="mb-3">
            <label class="form-label" for="evento_id">Evento</label>
            <select class="form-select form-select-lg" id="evento_id" name="evento_id" required>
              <option value="">Seleccione evento…</option>
              <?php foreach ($eventos as $evento): ?>
              <option value="<?= (int) $evento['id'] ?>" <?= (int) $eventoId === (int) $evento['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($evento['nombre']) ?>
                <?php if (!empty($evento['fecha'])): ?>
                  (<?= htmlspecialchars(formatearFechaTabla($evento['fecha'])) ?>)
                <?php endif; ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-4">
            <label class="form-label" for="numeracion">Numeración del ticket</label>
            <input
              type="text"
              class="form-control form-control-lg"
              id="numeracion"
              name="numeracion"
              maxlength="30"
              value="<?= htmlspecialchars($numeracion) ?>"
              placeholder="Seleccione evento primero…"
              enterkeyhint="search"
              <?= $eventoId > 0 ? '' : 'disabled' ?>
            >
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2">
            <i class="bi bi-search me-1"></i>Consultar
          </button>
        </form>
      </div>
    </div>

    <?php if (!empty($consultado)): ?>
      <?php if (empty($registros)): ?>
      <div class="card checkout-result checkout-result--empty shadow-sm border-0 mt-3">
        <div class="card-body text-center py-4">
          <i class="bi bi-x-circle checkout-result__icon text-danger d-block mb-2"></i>
          <h2 class="h5 mb-1">Ticket no encontrado</h2>
          <p class="text-muted small mb-0">
            No hay un registro con la numeración
            <strong><?= htmlspecialchars($numeracion) ?></strong>
            en este evento.
          </p>
        </div>
      </div>
      <?php else: ?>
        <?php foreach ($registros as $registro):
            $tipoEntrada = trim((string) ($registro['tipo_entrada'] ?? ''));
            $numeracionRegistro = trim((string) ($registro['numeracion'] ?? ''));
        ?>
        <div class="card checkout-result shadow-sm border-0 mt-3">
          <div class="card-body p-4">
            <dl class="checkout-result__datos mb-0">
              <div class="checkout-result__fila">
                <dt>Nombre</dt>
                <dd><?= htmlspecialchars($registro['nombre'] ?? '—') ?></dd>
              </div>
              <div class="checkout-result__fila">
                <dt>Numeración</dt>
                <dd><?= htmlspecialchars($numeracionRegistro !== '' ? $numeracionRegistro : '—') ?></dd>
              </div>
              <div class="checkout-result__fila">
                <dt>Tipo de entrada</dt>
                <dd><?= htmlspecialchars($tipoEntrada !== '' ? $tipoEntrada : '—') ?></dd>
              </div>
              <div class="checkout-result__fila">
                <dt>Estado</dt>
                <dd>
                  <span class="badge <?= htmlspecialchars(claseBadgeEstadoPagoRegistroEvento($registro)) ?>">
                    <?= htmlspecialchars(etiquetaEstadoPagoRegistroEvento($registro)) ?>
                  </span>
                </dd>
              </div>
            </dl>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
