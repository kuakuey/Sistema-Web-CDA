<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h2 class="h4 mb-1">Eventos</h2>
    <p class="text-muted small mb-0">Gestiona eventos y registra participantes</p>
  </div>
  <?php if ($pestaña === 'tabla'): ?>
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
    <a class="nav-link <?= $pestaña === 'catalogo' ? 'active' : '' ?>" href="eventos.php?pestaña=catalogo" role="tab">
      <i class="bi bi-calendar-event me-1"></i>Eventos registrados
    </a>
  </li>
  <?php endif; ?>
  <?php if (!empty($puedeVerInforme)): ?>
  <li class="nav-item" role="presentation">
    <a class="nav-link <?= $pestaña === 'informe' ? 'active' : '' ?>" href="eventos.php?pestaña=informe" role="tab">
      <i class="bi bi-file-earmark-pdf me-1"></i>Informe
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
    $modalesDetalle = [];
    $modalesEditar = [];
    $redireccionRegistros = construirUrlRegistros('eventos.php', $filtros, $paginaActual, 'tabla');
    ?>
    <div class="table-responsive">
      <table class="table table-hover table-dashboard mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>Nombre</th>
            <th>Evento</th>
            <th>Numeración</th>
            <th>Valor</th>
            <th>Forma de pago</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($registros)): ?>
          <tr>
            <td colspan="6" class="text-center text-muted py-5">
              <i class="bi bi-inbox display-6 d-block mb-2"></i>
              No hay registros de eventos.
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($registros as $fila):
            $modalId = 'detalle-evento-' . (int) $fila['id'];
            $modalesDetalle[] = [
                'id'     => $modalId,
                'titulo' => 'Registro de evento #' . (int) $fila['id'],
                'filas'  => construirDetalleRegistroEvento($fila),
                'extra'  => '',
            ];
            if (!empty($puedeEditar)) {
                $modalesEditar[] = [
                    'id'         => 'editar-' . $modalId,
                    'tipo'       => 'registro_evento',
                    'fila'       => $fila,
                    'redireccion'=> $redireccionRegistros,
                ];
            }
          ?>
          <tr>
            <td><?= htmlspecialchars($fila['nombre']) ?></td>
            <td><span class="badge bg-secondary"><?= htmlspecialchars($fila['evento_nombre'] ?? '—') ?></span></td>
            <td><?= htmlspecialchars($fila['numeracion'] ?? '—') ?></td>
            <td><?= htmlspecialchars(formatearMonto((float) $fila['valor'])) ?></td>
            <td><?= htmlspecialchars(etiquetaFormaPagoEvento($fila['forma_pago'] ?? null)) ?></td>
            <td class="text-end">
              <?php
              $eliminarAccion = 'eliminar_valor_adicional';
              $eliminarId = (int) $fila['id'];
              $eliminarRedireccion = $redireccionRegistros;
              $modalEditarId = !empty($puedeEditar) ? 'editar-' . $modalId : '';
              include __DIR__ . '/../partials/tabla-acciones-registro.php';
              ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php $pestañaPaginacion = 'tabla'; include __DIR__ . '/../partials/paginacion-registros.php'; ?>
    <?php foreach ($modalesDetalle as $modal):
        $modalId = $modal['id'];
        $tituloModal = $modal['titulo'];
        $filasDetalle = $modal['filas'];
        $contenidoExtra = $modal['extra'];
        include __DIR__ . '/../partials/modal-detalle-registro.php';
    endforeach;
    include __DIR__ . '/../partials/modales-editar-registro.php';
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
    <form method="POST" action="acciones.php" class="row g-3 js-form-registro" id="formRegistroEvento" data-mensaje-exito="Participante registrado correctamente.">
      <input type="hidden" name="accion" value="registrar_evento">
      <input type="hidden" name="redireccion" value="eventos.php?pestaña=registrar">

      <div class="col-md-6">
        <label class="form-label" for="evento_id">Nombre evento <span class="text-danger">*</span></label>
        <select class="form-select" id="evento_id" name="evento_id" required>
          <option value="">Seleccione evento…</option>
          <?php foreach ($eventosHabilitados as $evento): ?>
          <option
            value="<?= (int) $evento['id'] ?>"
            data-valor="<?= htmlspecialchars((string) $evento['valor']) ?>"
            data-requiere-numeracion="<?= (int) ($evento['requiere_numeracion'] ?? 0) ?>"
          >
            <?= htmlspecialchars($evento['nombre']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6 js-campo-numeracion-evento invisible" id="campoNumeracionEvento" aria-hidden="true">
        <label class="form-label" for="numeracion">Numeración <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="numeracion" name="numeracion" maxlength="30">
      </div>

      <div class="col-md-6">
        <label class="form-label" for="nombre">Nombre <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="nombre" name="nombre" required maxlength="100">
      </div>

      <div class="col-md-6">
        <label class="form-label" for="telefono">Teléfono <span class="text-danger">*</span></label>
        <input type="tel" class="form-control" id="telefono" name="telefono" required maxlength="30">
      </div>

      <div class="col-md-6">
        <label class="form-label" for="fecha">Fecha <span class="text-danger">*</span></label>
        <input type="date" class="form-control" id="fecha" name="fecha" required value="<?= htmlspecialchars(date('Y-m-d')) ?>">
      </div>

      <div class="col-md-6 js-campo-valor-evento invisible" aria-hidden="true">
        <label class="form-label" for="valor">Valor <span class="text-danger">*</span></label>
        <input type="number" class="form-control js-valor-evento" id="valor" name="valor" min="0.01" step="0.01">
      </div>

      <div class="col-12 js-bloque-forma-pago-evento" style="display:none">
        <label class="form-label d-block">Forma de pago <span class="text-danger">*</span></label>
        <div class="form-check form-check-inline">
          <input class="form-check-input js-metodo-pago-evento" type="radio" name="forma_pago" id="pago-efectivo" value="efectivo" checked>
          <label class="form-check-label" for="pago-efectivo">Efectivo</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input js-metodo-pago-evento" type="radio" name="forma_pago" id="pago-transferencia" value="transferencia">
          <label class="form-check-label" for="pago-transferencia">Transferencia</label>
        </div>
      </div>

      <input type="hidden" name="forma_pago" class="js-forma-pago-gratuito" value="gratuito" disabled>
      <input type="hidden" name="valor" class="js-valor-gratuito" value="0" disabled>

      <div class="col-12">
        <label class="form-label" for="observacion">Observación</label>
        <textarea class="form-control" id="observacion" name="observacion" rows="2" maxlength="500"></textarea>
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Registrar</button>
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
        <label class="form-label d-block">Tipo <span class="text-danger">*</span></label>
        <div class="form-check form-check-inline">
          <input class="form-check-input js-tipo-cobro-catalogo" type="radio" name="tipo_cobro" id="catalogo-gratuito" value="gratuito">
          <label class="form-check-label" for="catalogo-gratuito">Gratuito</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input js-tipo-cobro-catalogo" type="radio" name="tipo_cobro" id="catalogo-pago" value="pago" checked>
          <label class="form-check-label" for="catalogo-pago">Pago</label>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 js-bloque-valor-catalogo">
        <label class="form-label" for="valor_evento">Valor <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="valor_evento" name="valor" min="0.01" step="0.01">
      </div>
      <input type="hidden" name="valor" class="js-valor-catalogo-gratuito" value="0" disabled>
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

<?php elseif ($pestaña === 'informe' && !empty($puedeVerInforme)): ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3">
    <h3 class="h6 mb-0"><i class="bi bi-file-earmark-pdf me-2"></i>Informe de evento</h3>
  </div>
  <div class="card-body">
    <p class="text-muted small mb-4">Selecciona un evento para descargar un PDF con todos los participantes registrados.</p>
    <?php if (empty($eventos)): ?>
    <div class="alert alert-warning mb-0">
      <i class="bi bi-exclamation-triangle me-1"></i>
      No hay eventos en el catálogo. Crea un evento primero.
    </div>
    <?php else: ?>
    <form method="GET" action="eventos.php" class="row g-3 align-items-end">
      <input type="hidden" name="pestaña" value="informe">
      <input type="hidden" name="generar" value="1">
      <div class="col-md-6 col-lg-5">
        <label class="form-label" for="evento_informe">Evento <span class="text-danger">*</span></label>
        <select class="form-select" id="evento_informe" name="evento_id" required>
          <option value="">Seleccione evento…</option>
          <?php foreach ($eventos as $evento): ?>
          <option
            value="<?= (int) $evento['id'] ?>"
            <?= (int) ($eventoInformeSeleccionado ?? 0) === (int) $evento['id'] ? 'selected' : '' ?>
          >
            <?= htmlspecialchars($evento['nombre']) ?> (<?= (int) ($evento['total_registros'] ?? 0) ?> registro(s))
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-auto">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-download me-1"></i>Descargar PDF
        </button>
      </div>
    </form>
    <?php endif; ?>
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
            <th>Valor</th>
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
            <td><?= htmlspecialchars(formatearMonto((float) ($evento['valor'] ?? 0))) ?></td>
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
  <div class="modal-dialog">
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
            <label class="form-label d-block">Tipo <span class="text-danger">*</span></label>
            <?php $esGratuitoCatalogo = (float) ($evento['valor'] ?? 0) <= 0; ?>
            <div class="form-check form-check-inline">
              <input class="form-check-input js-tipo-cobro-catalogo" type="radio" name="tipo_cobro" id="catalogo-gratuito<?= (int) $evento['id'] ?>" value="gratuito" <?= $esGratuitoCatalogo ? 'checked' : '' ?>>
              <label class="form-check-label" for="catalogo-gratuito<?= (int) $evento['id'] ?>">Gratuito</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input js-tipo-cobro-catalogo" type="radio" name="tipo_cobro" id="catalogo-pago<?= (int) $evento['id'] ?>" value="pago" <?= !$esGratuitoCatalogo ? 'checked' : '' ?>>
              <label class="form-check-label" for="catalogo-pago<?= (int) $evento['id'] ?>">Pago</label>
            </div>
          </div>
          <div class="mb-3 js-bloque-valor-catalogo" style="<?= $esGratuitoCatalogo ? 'display:none' : '' ?>">
            <label class="form-label">Valor <span class="text-danger">*</span></label>
            <input type="number" class="form-control" name="valor" min="0.01" step="0.01" value="<?= htmlspecialchars((string) ($evento['valor'] ?? '')) ?>">
          </div>
          <input type="hidden" name="valor" class="js-valor-catalogo-gratuito" value="0" <?= !$esGratuitoCatalogo ? 'disabled' : '' ?>>
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
