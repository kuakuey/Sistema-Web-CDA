<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h2 class="h4 mb-1">Eventos</h2>
    <p class="text-muted small mb-0">Gestiona eventos y registra participantes</p>
  </div>
  <?php if ($pestaña === 'tabla' || $pestaña === 'participantes'): ?>
  <span class="badge bg-primary fs-6"><?= (int) $totalRegistros ?> registro(s)</span>
  <?php else: ?>
  <span class="badge bg-primary fs-6"><?= count($eventos) ?> evento(s)</span>
  <?php endif; ?>
</div>

<?php if ($mensaje): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($mensaje) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
</div>
<?php endif; ?>

<?php if (!empty($errorBd)): ?>
<div class="alert alert-warning" role="alert">
  <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($errorBd) ?>
</div>
<?php else: ?>

<ul class="nav nav-tabs mb-4" role="tablist">
  <?php if (!empty($puedeVerTabla)): ?>
  <li class="nav-item" role="presentation">
    <a class="nav-link <?= $pestaña === 'tabla' ? 'active' : '' ?>" href="eventos.php?pestaña=tabla" role="tab">
      <i class="bi bi-table me-1"></i>Tabla de eventos
    </a>
  </li>
  <?php endif; ?>
  <?php if (!empty($puedeRegistrar)): ?>
  <li class="nav-item" role="presentation">
    <a class="nav-link <?= $pestaña === 'registrar' ? 'active' : '' ?>" href="eventos.php?pestaña=registrar" role="tab">
      <i class="bi bi-pencil-square me-1"></i>Registro de eventos
    </a>
  </li>
  <?php endif; ?>
  <?php if (!empty($puedeAgregar)): ?>
  <li class="nav-item" role="presentation">
    <a class="nav-link <?= $pestaña === 'agregar' ? 'active' : '' ?>" href="eventos.php?pestaña=agregar" role="tab">
      <i class="bi bi-plus-circle me-1"></i>Agregar eventos
    </a>
  </li>
  <?php endif; ?>
  <?php if (!empty($puedeVerCatalogo)): ?>
  <li class="nav-item" role="presentation">
    <a class="nav-link <?= in_array($pestaña, ['catalogo', 'participantes'], true) ? 'active' : '' ?>" href="eventos.php?pestaña=catalogo" role="tab">
      <i class="bi bi-calendar-event me-1"></i>Eventos registrados
    </a>
  </li>
  <?php endif; ?>
</ul>

<?php if ($pestaña === 'tabla'): ?>

<div class="card border-0 shadow-sm mb-4 filters-panel">
  <button
    class="filters-panel__toggle d-md-none"
    type="button"
    data-bs-toggle="collapse"
    data-bs-target="#filtersEventosPanel"
    aria-expanded="false"
    aria-controls="filtersEventosPanel"
  >
    <i class="bi bi-funnel me-2"></i>Filtros
    <i class="bi bi-chevron-down filters-panel__chevron"></i>
  </button>
  <div class="collapse" id="filtersEventosPanel">
    <div class="card-body">
      <form method="GET" action="eventos.php" class="row g-3 align-items-end">
        <input type="hidden" name="pestaña" value="tabla">
        <div class="col-md-4">
          <label class="form-label small" for="buscar">Buscar</label>
          <input type="search" class="form-control form-control-sm" id="buscar" name="buscar" value="<?= htmlspecialchars($filtros['buscar']) ?>" placeholder="Nombre, teléfono, evento…">
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
          <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filtrar</button>
          <a href="eventos.php?pestaña=tabla" class="btn btn-outline-secondary btn-sm">Limpiar</a>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3">
    <h3 class="h6 mb-0"><i class="bi bi-table me-2"></i>Registros de participantes</h3>
  </div>
  <div class="card-body p-0">
    <?php
    $pestañaPaginacion = 'tabla';
    include __DIR__ . '/../partials/tabla-registros-eventos.php';
    ?>
  </div>
</div>

<?php elseif ($pestaña === 'participantes' && !empty($eventoParticipantes)): ?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="eventos.php?pestaña=catalogo">Eventos registrados</a></li>
    <li class="breadcrumb-item active" aria-current="page">Participantes</li>
  </ol>
</nav>

<div class="card border-0 shadow-sm mb-4 filters-panel">
  <button
    class="filters-panel__toggle d-md-none"
    type="button"
    data-bs-toggle="collapse"
    data-bs-target="#filtersParticipantesPanel"
    aria-expanded="false"
    aria-controls="filtersParticipantesPanel"
  >
    <i class="bi bi-funnel me-2"></i>Filtros
    <i class="bi bi-chevron-down filters-panel__chevron"></i>
  </button>
  <div class="collapse" id="filtersParticipantesPanel">
    <div class="card-body">
      <form method="GET" action="eventos.php" class="row g-3 align-items-end">
        <input type="hidden" name="pestaña" value="participantes">
        <input type="hidden" name="evento_id" value="<?= (int) $eventoParticipantes['id'] ?>">
        <div class="col-md-4">
          <label class="form-label small" for="buscar">Buscar</label>
          <input type="search" class="form-control form-control-sm" id="buscar" name="buscar" value="<?= htmlspecialchars($filtros['buscar']) ?>" placeholder="Nombre, teléfono…">
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
          <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filtrar</button>
          <a href="eventos.php?pestaña=participantes&evento_id=<?= (int) $eventoParticipantes['id'] ?>" class="btn btn-outline-secondary btn-sm">Limpiar</a>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
      <h3 class="h6 mb-1"><i class="bi bi-people me-2"></i>Participantes — <?= htmlspecialchars($eventoParticipantes['nombre']) ?></h3>
      <p class="text-muted small mb-0">
        <?= htmlspecialchars(formatearFechaTabla($eventoParticipantes['fecha'] ?? '')) ?>
        <?php if ((int) ($eventoParticipantes['habilitado'] ?? 0) !== 1): ?>
        · <span class="badge bg-secondary">Evento deshabilitado</span>
        <?php endif; ?>
      </p>
    </div>
    <a href="eventos.php?pestaña=catalogo" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Volver al catálogo
    </a>
  </div>
  <div class="card-body p-0">
    <?php
    $pestañaPaginacion = 'participantes';
    $mensajeVacio = 'No hay participantes registrados en este evento.';
    include __DIR__ . '/../partials/tabla-registros-eventos.php';
    ?>
  </div>
</div>

<?php elseif ($pestaña === 'registrar' && !empty($puedeRegistrar)): ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3">
    <h3 class="h6 mb-0"><i class="bi bi-pencil-square me-2"></i>Registro de eventos</h3>
  </div>
  <div class="card-body">
    <?php if (empty($eventosHabilitados)): ?>
    <div class="alert alert-warning mb-0">
      <i class="bi bi-exclamation-triangle me-1"></i>
      No hay eventos habilitados. Un superadmin debe agregar y habilitar eventos primero.
    </div>
    <?php else: ?>
    <form method="POST" action="acciones.php" class="row g-3 js-form-registro js-form-registro-evento" id="formRegistroEvento" data-mensaje-exito="Participante registrado correctamente.">
      <input type="hidden" name="accion" value="registrar_evento">
      <input type="hidden" name="redireccion" value="eventos.php?pestaña=registrar">

      <div class="col-md-6">
        <label class="form-label" for="evento_id">Nombre evento <span class="text-danger">*</span></label>
        <select class="form-select" id="evento_id" name="evento_id" required>
          <option value="">Seleccione evento…</option>
          <?php foreach ($eventosHabilitados as $evento):
            $tiposVisibles = filtrarTiposEntradaEventoPorRol($evento['tipos_entrada'] ?? [], (string) ($usuario['rol'] ?? ''));
            $tiposJson = array_map(static function (array $tipo): array {
                return [
                    'id'       => (int) ($tipo['id'] ?? 0),
                    'nombre'   => (string) ($tipo['nombre'] ?? ''),
                    'valor'    => (float) ($tipo['valor'] ?? 0),
                    'es_gratis' => (int) ($tipo['es_gratis'] ?? 0),
                ];
            }, $tiposVisibles);
          ?>
          <option
            value="<?= (int) $evento['id'] ?>"
            data-valor="<?= htmlspecialchars((string) $evento['valor']) ?>"
            data-requiere-numeracion="<?= (int) ($evento['requiere_numeracion'] ?? 0) ?>"
            data-tipos-entrada="<?= htmlspecialchars(json_encode($tiposJson, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
          >
            <?= htmlspecialchars($evento['nombre']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6 js-campo-tipo-entrada-evento">
        <label class="form-label" for="tipo_entrada_id">Tipo de entrada <span class="text-danger">*</span></label>
        <select class="form-select js-tipo-entrada-evento" id="tipo_entrada_id" name="tipo_entrada_id" required disabled>
          <option value="">Seleccione evento primero…</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label" for="nombre">Nombre completo <span class="text-danger">*</span></label>
        <input type="text" class="form-control js-paso-despues-tipo" id="nombre" name="nombre" required maxlength="100" disabled>
      </div>

      <div class="col-md-6">
        <label class="form-label" for="telefono">Teléfono <span class="text-danger">*</span></label>
        <input type="tel" class="form-control js-paso-despues-tipo" id="telefono" name="telefono" required maxlength="30" disabled>
      </div>

      <div class="col-md-6 js-campo-valor-evento">
        <label class="form-label" for="valor">Valor <span class="text-danger">*</span></label>
        <input type="number" class="form-control js-valor-evento js-paso-despues-tipo" id="valor" name="valor" min="0" step="0.01" placeholder="0.00" disabled>
      </div>

      <div class="col-md-6 js-campo-numeracion-evento">
        <label class="form-label" for="numeracion">Numeración</label>
        <input type="text" class="form-control" id="numeracion" name="numeracion" maxlength="30" placeholder="Seleccione evento primero…" disabled>
      </div>

      <div class="col-md-4 js-bloque-estado-pago-evento">
        <label class="form-label d-block">Estado <span class="text-danger">*</span></label>
        <div class="form-check form-check-inline">
          <input class="form-check-input js-estado-pago-evento" type="radio" name="estado_pago" id="estado-por-cancelar" value="por_cancelar" checked disabled>
          <label class="form-check-label" for="estado-por-cancelar">Por cancelar</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input js-estado-pago-evento" type="radio" name="estado_pago" id="estado-pagado" value="pagado" disabled>
          <label class="form-check-label" for="estado-pagado">Pagado</label>
        </div>
      </div>

      <div class="col-md-4 js-bloque-forma-pago-evento">
        <label class="form-label d-block">Forma de pago <span class="text-danger">*</span></label>
        <div class="form-check form-check-inline">
          <input class="form-check-input js-metodo-pago-evento" type="radio" name="forma_pago" id="pago-efectivo" value="efectivo" checked disabled>
          <label class="form-check-label" for="pago-efectivo">Efectivo</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input js-metodo-pago-evento" type="radio" name="forma_pago" id="pago-transferencia" value="transferencia" disabled>
          <label class="form-check-label" for="pago-transferencia">Transferencia</label>
        </div>
      </div>

      <div class="col-md-4">
        <label class="form-label" for="fecha">Fecha <span class="text-danger">*</span></label>
        <input type="date" class="form-control js-paso-despues-tipo" id="fecha" name="fecha" required value="<?= htmlspecialchars(date('Y-m-d')) ?>" disabled>
      </div>

      <input type="hidden" name="forma_pago" class="js-forma-pago-gratuito" value="gratuito" disabled>
      <input type="hidden" name="forma_pago" class="js-forma-pago-pendiente" value="pendiente" disabled>
      <input type="hidden" name="valor" class="js-valor-gratuito" value="0" disabled>
      <input type="hidden" name="estado_pago" class="js-estado-pago-gratuito" value="pagado" disabled>
      <input type="hidden" name="estado_pago" class="js-estado-pago-pendiente" value="por_cancelar" disabled>

      <div class="col-12">
        <label class="form-label" for="observacion">Observación</label>
        <textarea class="form-control js-paso-despues-tipo" id="observacion" name="observacion" rows="2" maxlength="500" disabled></textarea>
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-primary js-submit-registro-evento" disabled><i class="bi bi-check-lg me-1"></i>Registrar</button>
      </div>
    </form>
    <?php endif; ?>
  </div>
</div>

<?php elseif ($pestaña === 'agregar' && !empty($puedeAgregar)): ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3">
    <h3 class="h6 mb-0"><i class="bi bi-plus-circle me-2"></i>Agregar evento</h3>
  </div>
  <div class="card-body">
    <p class="text-muted small">Solo el superadmin puede crear eventos en el catálogo.</p>
    <form method="POST" action="eventos.php?pestaña=agregar" class="row g-3" id="formAgregarEvento">
      <input type="hidden" name="accion" value="crear_evento">
      <div class="col-md-6 col-lg-4">
        <label class="form-label" for="nombre_evento">Nombre <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="nombre_evento" name="nombre" required maxlength="150">
      </div>
      <div class="col-md-6 col-lg-4">
        <label class="form-label" for="fecha_evento">Fecha <span class="text-danger">*</span></label>
        <input type="date" class="form-control" id="fecha_evento" name="fecha" required value="<?= htmlspecialchars(date('Y-m-d')) ?>">
      </div>
      <div class="col-12">
        <?php
        $tiposEntradaEvento = [];
        $prefijoId = 'nuevo';
        include __DIR__ . '/../partials/tipos-entrada-evento-catalogo.php';
        ?>
      </div>
      <div class="col-12">
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="habilitado" id="habilitado_nuevo" value="1" checked>
          <label class="form-check-label" for="habilitado_nuevo">Habilitado para registro</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="requiere_numeracion" id="requiere_numeracion_nuevo" value="1">
          <label class="form-check-label" for="requiere_numeracion_nuevo">¿Requiere numeración?</label>
        </div>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Agregar evento</button>
      </div>
    </form>
  </div>
</div>

<?php elseif ($pestaña === 'catalogo'): ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3">
    <h3 class="h6 mb-0"><i class="bi bi-calendar-event me-2"></i>Eventos registrados</h3>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-dashboard mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th class="text-center col-numero">#</th>
            <th>Nombre</th>
            <th>Fecha</th>
            <th>Tipos de entrada</th>
            <th>Numeración</th>
            <th>Estado</th>
            <th>Registros</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($eventos)): ?>
          <tr>
            <td colspan="8" class="text-center text-muted py-5">No hay eventos en el catálogo.</td>
          </tr>
          <?php else: ?>
          <?php foreach ($eventos as $indice => $evento): ?>
          <tr>
            <td class="text-center text-muted"><?= $indice + 1 ?></td>
            <td><?= htmlspecialchars($evento['nombre']) ?></td>
            <td><?= htmlspecialchars(formatearFechaTabla($evento['fecha'] ?? '')) ?></td>
            <td><small><?= htmlspecialchars(formatearTiposEntradaEvento($evento)) ?></small></td>
            <td>
              <?php if ((int) ($evento['requiere_numeracion'] ?? 0) === 1): ?>
              <span class="badge bg-info text-dark">Sí</span>
              <?php else: ?>
              <span class="text-muted">No</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ((int) ($evento['habilitado'] ?? 0) === 1): ?>
              <span class="badge bg-success">Habilitado</span>
              <?php else: ?>
              <span class="badge bg-secondary">Deshabilitado</span>
              <?php endif; ?>
            </td>
            <td><span class="badge bg-secondary"><?= (int) ($evento['total_registros'] ?? 0) ?></span></td>
            <td class="text-end">
              <?php if (!empty($puedeVerParticipantes) && (int) ($evento['total_registros'] ?? 0) > 0): ?>
              <a
                href="eventos.php?pestaña=participantes&evento_id=<?= (int) $evento['id'] ?>"
                class="btn btn-sm btn-outline-secondary"
                title="Ver participantes"
              >
                <i class="bi bi-people"></i>
              </a>
              <?php endif; ?>
              <?php if ($puedeAgregar): ?>
              <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditarEvento<?= (int) $evento['id'] ?>" title="Editar">
                <i class="bi bi-pencil"></i>
              </button>
              <?php endif; ?>
              <?php if ($puedeEliminar): ?>
              <form
                method="POST"
                action="acciones.php"
                class="d-inline js-form-confirmar"
                data-confirm-title="Eliminar evento"
                data-confirm="¿Eliminar este evento del catálogo?"
              >
                <input type="hidden" name="accion" value="eliminar_evento">
                <input type="hidden" name="id" value="<?= (int) $evento['id'] ?>">
                <input type="hidden" name="redireccion" value="eventos.php?pestaña=catalogo">
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if (!empty($puedeAgregar)): ?>
<?php foreach ($eventos as $evento): ?>
<div class="modal fade" id="modalEditarEvento<?= (int) $evento['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="eventos.php?pestaña=catalogo">
        <input type="hidden" name="accion" value="actualizar_evento_catalogo">
        <input type="hidden" name="id" value="<?= (int) $evento['id'] ?>">
        <div class="modal-header">
          <h5 class="modal-title">Editar evento</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nombre <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nombre" required maxlength="150" value="<?= htmlspecialchars($evento['nombre']) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Fecha <span class="text-danger">*</span></label>
            <input type="date" class="form-control" name="fecha" required value="<?= htmlspecialchars($evento['fecha'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <?php
            $tiposEntradaEvento = $evento['tipos_entrada'] ?? [];
            if (!is_array($tiposEntradaEvento) || $tiposEntradaEvento === []) {
                $tiposEntradaEvento = [[
                    'nombre'          => 'General',
                    'valor'           => (float) ($evento['valor'] ?? 0),
                    'visible_publico' => 1,
                    'es_gratis'       => (float) ($evento['valor'] ?? 0) <= 0 ? 1 : 0,
                ]];
            }
            $prefijoId = 'editar' . (int) $evento['id'];
            include __DIR__ . '/../partials/tipos-entrada-evento-catalogo.php';
            ?>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="habilitado" id="habilitado<?= (int) $evento['id'] ?>" value="1" <?= (int) ($evento['habilitado'] ?? 0) === 1 ? 'checked' : '' ?>>
            <label class="form-check-label" for="habilitado<?= (int) $evento['id'] ?>">Habilitado para registro</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="requiere_numeracion" id="requiereNumeracion<?= (int) $evento['id'] ?>" value="1" <?= (int) ($evento['requiere_numeracion'] ?? 0) === 1 ? 'checked' : '' ?>>
            <label class="form-check-label" for="requiereNumeracion<?= (int) $evento['id'] ?>">¿Requiere numeración?</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php endif; ?>

<?php
$eventosRegistroJs = dirname(__DIR__, 2) . '/js/eventos-registro.js';
$eventosRegistroJsVersion = is_file($eventosRegistroJs) ? (string) filemtime($eventosRegistroJs) : '1';
?>
<script src="js/eventos-registro.js?v=<?= htmlspecialchars($eventosRegistroJsVersion) ?>"></script>

<?php endif; ?>
