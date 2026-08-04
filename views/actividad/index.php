<?php
require_once __DIR__ . '/../../includes/submissions.php';
require_once __DIR__ . '/../../includes/actividad_log.php';
require_once __DIR__ . '/../../includes/rutas.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h2 class="h4 mb-1">Log de actividad</h2>
    <p class="text-muted small mb-0">Registro de acciones realizadas en el sistema (solo superadmin)</p>
  </div>
  <span class="badge bg-primary fs-6"><?= (int) $totalRegistros ?> registro(s)</span>
</div>

<?php if ($errorBd): ?>
<div class="alert alert-warning" role="alert">
  <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($errorBd) ?>
</div>
<?php else: ?>

<div class="card border-0 shadow-sm mb-4 filters-panel">
  <button
    class="filters-panel__toggle d-md-none"
    type="button"
    data-bs-toggle="collapse"
    data-bs-target="#filtersActividadPanel"
    aria-expanded="false"
    aria-controls="filtersActividadPanel"
  >
    <i class="bi bi-funnel me-2"></i>Filtros
    <i class="bi bi-chevron-down filters-panel__chevron"></i>
  </button>
  <div class="collapse" id="filtersActividadPanel">
    <div class="card-body">
      <form method="GET" action="actividad.php" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label small" for="buscar">Buscar</label>
          <input
            type="search"
            class="form-control form-control-sm"
            id="buscar"
            name="buscar"
            value="<?= htmlspecialchars($filtros['buscar']) ?>"
            placeholder="Usuario, detalle, IP…"
          >
        </div>

        <div class="col-md-3">
          <label class="form-label small" for="accion">Acción</label>
          <select class="form-select form-select-sm" id="accion" name="accion">
            <option value="">Todas</option>
            <?php foreach ($etiquetasAcciones as $claveAccion => $etiquetaAccion): ?>
            <option value="<?= htmlspecialchars($claveAccion) ?>" <?= ($filtros['accion'] ?? '') === $claveAccion ? 'selected' : '' ?>>
              <?= htmlspecialchars($etiquetaAccion) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label small" for="seccion">Sección</label>
          <select class="form-select form-select-sm" id="seccion" name="seccion">
            <option value="">Todas</option>
            <?php foreach ($etiquetasSeccionesLog as $claveSeccion => $etiquetaSeccion): ?>
            <option value="<?= htmlspecialchars($claveSeccion) ?>" <?= ($filtros['seccion'] ?? '') === $claveSeccion ? 'selected' : '' ?>>
              <?= htmlspecialchars($etiquetaSeccion) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label small" for="fecha_desde">Desde</label>
          <input type="date" class="form-control form-control-sm" id="fecha_desde" name="fecha_desde" value="<?= htmlspecialchars($filtros['fecha_desde']) ?>">
        </div>

        <div class="col-md-2">
          <label class="form-label small" for="fecha_hasta">Hasta</label>
          <input type="date" class="form-control form-control-sm" id="fecha_hasta" name="fecha_hasta" value="<?= htmlspecialchars($filtros['fecha_hasta']) ?>">
        </div>

        <div class="col-md-auto d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-funnel me-1"></i>Filtrar
          </button>
          <a href="actividad.php" class="btn btn-outline-secondary btn-sm">Limpiar</a>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3">
    <h3 class="h6 mb-0"><i class="bi bi-journal-text me-2"></i>Actividad reciente</h3>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-dashboard mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>Fecha</th>
            <th>Usuario</th>
            <th>Acción</th>
            <th>Sección</th>
            <th>Detalle</th>
            <th>IP</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($registros)): ?>
          <tr>
            <td colspan="6" class="text-center text-muted py-5">
              <i class="bi bi-inbox display-6 d-block mb-2"></i>
              No hay actividad con los filtros seleccionados.
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($registros as $fila): ?>
          <tr>
            <td class="text-nowrap small"><?= htmlspecialchars(formatearFechaHora($fila['creado_en'] ?? null)) ?></td>
            <td><?= htmlspecialchars(trim((string) ($fila['usuario_nombre'] ?? '')) !== '' ? $fila['usuario_nombre'] : '—') ?></td>
            <td>
              <span class="badge bg-secondary-subtle text-secondary-emphasis">
                <?= htmlspecialchars(etiquetaAccionActividad((string) ($fila['accion'] ?? ''))) ?>
              </span>
            </td>
            <td>
              <?php
              $claveSeccion = (string) ($fila['seccion'] ?? '');
              echo htmlspecialchars($etiquetasSeccionesLog[$claveSeccion] ?? ($claveSeccion !== '' ? $claveSeccion : '—'));
              ?>
            </td>
            <td class="small"><?= htmlspecialchars(trim((string) ($fila['detalle'] ?? '')) !== '' ? $fila['detalle'] : '—') ?></td>
            <td class="small text-muted"><?= htmlspecialchars(trim((string) ($fila['ip_cliente'] ?? '')) !== '' ? $fila['ip_cliente'] : '—') ?></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php
    $archivoPagina = 'actividad.php';
    $pestañaPaginacion = '';
    include __DIR__ . '/../partials/paginacion-registros.php';
    ?>
  </div>
</div>

<?php endif; ?>
