<?php
require_once __DIR__ . '/../../includes/submissions.php';
require_once __DIR__ . '/../../includes/actividad_log.php';
require_once __DIR__ . '/../../includes/rutas.php';

$hayFiltrosActivos = ($filtros['buscar'] ?? '') !== ''
    || ($filtros['accion'] ?? '') !== ''
    || ($filtros['seccion'] ?? '') !== ''
    || ($filtros['fecha_desde'] ?? '') !== ''
    || ($filtros['fecha_hasta'] ?? '') !== '';
$modalesDetalle = [];
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h2 class="h4 mb-1">Log de actividad</h2>
    <p class="text-muted small mb-0">Registro de acciones del sistema (solo superadmin)</p>
  </div>
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <span class="badge bg-primary fs-6"><?= (int) $totalRegistros ?> registro(s)</span>
    <?php if ($hayFiltrosActivos && $totalRegistros > 0): ?>
    <form
      method="POST"
      action="actividad.php"
      class="d-inline js-form-confirmar"
      data-confirm-title="Limpiar log filtrado"
      data-confirm="¿Eliminar los <?= (int) $totalRegistros ?> registro(s) que coinciden con los filtros actuales?"
    >
      <input type="hidden" name="accion" value="limpiar_actividad_filtrada">
      <input type="hidden" name="filtro_buscar" value="<?= htmlspecialchars($filtros['buscar'] ?? '') ?>">
      <input type="hidden" name="filtro_accion" value="<?= htmlspecialchars($filtros['accion'] ?? '') ?>">
      <input type="hidden" name="filtro_seccion" value="<?= htmlspecialchars($filtros['seccion'] ?? '') ?>">
      <input type="hidden" name="filtro_fecha_desde" value="<?= htmlspecialchars($filtros['fecha_desde'] ?? '') ?>">
      <input type="hidden" name="filtro_fecha_hasta" value="<?= htmlspecialchars($filtros['fecha_hasta'] ?? '') ?>">
      <button type="submit" class="btn btn-outline-warning btn-sm">
        <i class="bi bi-funnel me-1"></i>Limpiar filtrados
      </button>
    </form>
    <?php endif; ?>
    <form
      method="POST"
      action="actividad.php"
      class="d-inline js-form-confirmar"
      data-confirm-title="Limpiar todo el log"
      data-confirm="¿Eliminar TODOS los registros del log de actividad? Esta acción no se puede deshacer."
    >
      <input type="hidden" name="accion" value="limpiar_actividad_todo">
      <button type="submit" class="btn btn-outline-danger btn-sm" <?= $totalRegistros <= 0 && !$hayFiltrosActivos ? 'disabled' : '' ?>>
        <i class="bi bi-trash me-1"></i>Limpiar todo
      </button>
    </form>
  </div>
</div>

<?php if (!empty($mensaje)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($mensaje) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
</div>
<?php endif; ?>

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
            placeholder="Usuario, detalle…"
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
          <a href="actividad.php" class="btn btn-outline-secondary btn-sm">Limpiar filtros</a>
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
            <th style="width: 10rem;">Fecha y hora</th>
            <th style="width: 10rem;">Usuario</th>
            <th style="width: 12rem;">Acción</th>
            <th style="width: 9rem;">Sección</th>
            <th class="text-end" style="width: 4rem;">Detalle</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($registros)): ?>
          <tr>
            <td colspan="5" class="text-center text-muted py-5">
              <i class="bi bi-inbox display-6 d-block mb-2"></i>
              No hay actividad con los filtros seleccionados.
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($registros as $fila):
            $modalId = 'detalle-actividad-' . (int) $fila['id'];
            $modalesDetalle[] = [
                'id'     => $modalId,
                'titulo' => 'Detalle de actividad',
                'filas'  => construirDetalleActividadLog($fila, $etiquetasSeccionesLog),
                'extra'  => '',
            ];
          ?>
          <tr>
            <td class="text-nowrap small"><?= htmlspecialchars(formatearFechaHora($fila['creado_en'] ?? null)) ?></td>
            <td>
              <?= htmlspecialchars(trim((string) ($fila['usuario_nombre'] ?? '')) !== '' ? $fila['usuario_nombre'] : (trim((string) ($fila['usuario_login'] ?? '')) !== '' ? $fila['usuario_login'] : '—')) ?>
            </td>
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
            <td class="text-end">
              <button
                type="button"
                class="btn btn-sm btn-outline-secondary"
                data-bs-toggle="modal"
                data-bs-target="#<?= htmlspecialchars($modalId) ?>"
                title="Ver detalle"
              >
                <i class="bi bi-eye"></i>
              </button>
            </td>
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

<?php foreach ($modalesDetalle as $modal):
    $modalId = $modal['id'];
    $tituloModal = $modal['titulo'];
    $filasDetalle = $modal['filas'];
    $contenidoExtra = $modal['extra'] ?? '';
    include __DIR__ . '/../partials/modal-detalle-registro.php';
endforeach; ?>

<?php endif; ?>
