<div class="informe-page">
  <div class="mb-3">
    <h2 class="h4 mb-1">Generar informe</h2>
    <p class="text-muted small mb-0">Descarga un PDF con ofrendas por casa de vida y valores adicionales</p>
  </div>

  <?php if ($error): ?>
  <div class="alert alert-danger">
    <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?>
  </div>
  <?php endif; ?>

  <?php if ($errorBd): ?>
  <div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($errorBd) ?>
  </div>
  <?php endif; ?>

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
      <h3 class="h6 mb-0"><i class="bi bi-calendar-range me-2"></i>Rango de fechas de registro</h3>
    </div>
    <div class="card-body">
      <p class="text-muted small mb-3">
        El informe filtra por la fecha en que se registró cada ofrenda o valor adicional en el sistema,
        no por la fecha de ofrenda.
      </p>
      <form method="GET" action="generar-informe.php" class="row g-3 align-items-end">
        <input type="hidden" name="generar" value="1">

        <div class="col-md-4">
          <label class="form-label" for="fecha_desde">Desde <span class="text-danger">*</span></label>
          <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" required value="<?= htmlspecialchars($fechaDesde) ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label" for="fecha_hasta">Hasta <span class="text-danger">*</span></label>
          <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" required value="<?= htmlspecialchars($fechaHasta) ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label" for="turno">Turno</label>
          <select class="form-select" id="turno" name="turno">
            <?php foreach ($etiquetasTurno as $clave => $etiqueta): ?>
            <option value="<?= htmlspecialchars($clave) ?>" <?= $turno === $clave ? 'selected' : '' ?>>
              <?= htmlspecialchars($etiqueta) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-3">
          <div class="form-check mt-2">
            <input
              class="form-check-input"
              type="checkbox"
              id="mostrar_sin_entregar"
              name="mostrar_sin_entregar"
              value="1"
              <?= !empty($mostrarSinEntregar) ? 'checked' : '' ?>
            >
            <label class="form-check-label" for="mostrar_sin_entregar">
              Mostrar casas de vida que no entregaron
            </label>
          </div>
        </div>

        <div class="col-md-auto">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-file-earmark-pdf me-1"></i>Generar PDF
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
