<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h2 class="h4 mb-1">Transporte Aniversario</h2>
    <p class="text-muted small mb-0">
      <?php if (!empty($puedeRegistrar)): ?>
      Registra movilización propia o solicitudes de transporte para el aniversario
      <?php else: ?>
      Consulta de registros de transporte para el aniversario
      <?php endif; ?>
    </p>
  </div>
  <span class="badge bg-primary fs-6"><?= (int) $totalRegistros ?> registro(s)</span>
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

<?php if ($errorBd): ?>
<div class="alert alert-warning" role="alert">
  <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($errorBd) ?>
</div>
<?php else: ?>

<ul class="nav nav-tabs mb-4" role="tablist">
  <li class="nav-item" role="presentation">
    <a
      class="nav-link <?= $pestaña === 'registros' ? 'active' : '' ?>"
      href="transporte-aniversario.php?pestaña=registros"
      role="tab"
    >
      <i class="bi bi-list-ul me-1"></i>Registros
    </a>
  </li>
  <?php if (!empty($puedeRegistrar)): ?>
  <li class="nav-item" role="presentation">
    <a
      class="nav-link <?= $pestaña === 'nuevo' ? 'active' : '' ?>"
      href="transporte-aniversario.php?pestaña=nuevo"
      role="tab"
    >
      <i class="bi bi-plus-circle me-1"></i>Nuevo registro
    </a>
  </li>
  <?php endif; ?>
  <?php if (!empty($puedeVerReporte)): ?>
  <li class="nav-item" role="presentation">
    <a
      class="nav-link <?= $pestaña === 'reporte' ? 'active' : '' ?>"
      href="transporte-aniversario.php?pestaña=reporte"
      role="tab"
    >
      <i class="bi bi-bar-chart me-1"></i>Reporte
    </a>
  </li>
  <?php endif; ?>
</ul>

<?php if ($pestaña === 'nuevo' && !empty($puedeRegistrar)): ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3">
    <h3 class="h6 mb-0"><i class="bi bi-plus-circle me-2"></i>Nuevo registro de transporte</h3>
  </div>
  <div class="card-body">
    <form
      method="POST"
      action="acciones.php"
      class="row g-3 js-form-registro"
      id="formTransporteAniversario"
      data-mensaje-exito="Registro de transporte guardado correctamente."
    >
      <input type="hidden" name="accion" value="crear_transporte_aniversario">
      <input type="hidden" name="redireccion" value="transporte-aniversario.php?pestaña=nuevo">

      <div class="col-12 col-md-6">
        <label class="form-label" for="nombre_completo">Nombre completo <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" required maxlength="200">
      </div>

      <div class="col-12 col-md-6">
        <label class="form-label" for="telefono">Teléfono <span class="text-danger">*</span></label>
        <input type="tel" class="form-control" id="telefono" name="telefono" required maxlength="30">
      </div>

      <div class="col-12">
        <div class="form-check">
          <input
            class="form-check-input js-posee-movilizacion"
            type="checkbox"
            id="posee_movilizacion"
            name="posee_movilizacion"
            value="1"
          >
          <label class="form-check-label" for="posee_movilizacion">Posee movilización</label>
        </div>
      </div>

      <div class="col-12 col-md-4 js-campo-asientos" style="display:none">
        <label class="form-label" for="asientos_disponibles">Asientos disponibles <span class="text-danger">*</span></label>
        <input
          type="number"
          class="form-control"
          id="asientos_disponibles"
          name="asientos_disponibles"
          min="1"
          max="99"
          placeholder="Ej. 3"
        >
        <div class="form-text">Indica cuántas personas puede transportar.</div>
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-save me-1"></i>Guardar
        </button>
      </div>
    </form>
  </div>
</div>

<?php elseif ($pestaña === 'reporte' && !empty($puedeVerReporte) && $reporteAsignacion): ?>
<?php $resumen = $reporteAsignacion['resumen']; ?>
<div class="row g-3 mb-4">
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body text-center">
        <div class="text-muted small">Registrados</div>
        <div class="fs-4 fw-semibold"><?= (int) $resumen['total'] ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body text-center">
        <div class="text-muted small">Con carro</div>
        <div class="fs-4 fw-semibold text-success"><?= (int) $resumen['con_carro'] ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body text-center">
        <div class="text-muted small">Necesitan transporte</div>
        <div class="fs-4 fw-semibold text-warning"><?= (int) $resumen['necesitan_transporte'] ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body text-center">
        <div class="text-muted small">Asientos ofrecidos</div>
        <div class="fs-4 fw-semibold"><?= (int) $resumen['asientos_ofrecidos'] ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body text-center">
        <div class="text-muted small">Asignados</div>
        <div class="fs-4 fw-semibold text-primary"><?= (int) $resumen['pasajeros_asignados'] ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body text-center">
        <div class="text-muted small">Sin cupo</div>
        <div class="fs-4 fw-semibold text-danger"><?= (int) $resumen['pasajeros_sin_cupo'] ?></div>
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white py-3">
    <h3 class="h6 mb-0"><i class="bi bi-car-front me-2"></i>Asignación por conductor</h3>
  </div>
  <div class="card-body p-0">
    <?php if (empty($reporteAsignacion['conductores'])): ?>
    <div class="text-center text-muted py-5">
      <i class="bi bi-car-front display-6 d-block mb-2"></i>
      No hay personas registradas con movilización propia.
    </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>Conductor</th>
            <th>Teléfono</th>
            <th class="text-center">Asientos</th>
            <th>Estado</th>
            <th>Pasajeros asignados</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reporteAsignacion['conductores'] as $conductor): ?>
          <?php
          $asignados = count($conductor['pasajeros']);
          $restantes = (int) $conductor['asientos_restantes'];
          $estadoClase = $restantes > 0 ? 'bg-success' : 'bg-secondary';
          $estadoTexto = $restantes > 0
              ? $restantes . ' asiento(s) disponible(s)'
              : 'Sin cupo';
          ?>
          <tr>
            <td><strong><?= htmlspecialchars($conductor['nombre_completo']) ?></strong></td>
            <td><?php $telefono = $conductor['telefono']; include __DIR__ . '/../partials/celda-telefono-whatsapp.php'; ?></td>
            <td class="text-center">
              <?= $asignados ?> / <?= (int) $conductor['asientos_total'] ?>
            </td>
            <td><span class="badge <?= $estadoClase ?>"><?= htmlspecialchars($estadoTexto) ?></span></td>
            <td>
              <?php if ($asignados === 0): ?>
              <span class="text-muted">—</span>
              <?php else: ?>
              <ul class="mb-0 ps-3">
                <?php foreach ($conductor['pasajeros'] as $pasajero): ?>
                <li><?= htmlspecialchars($pasajero['nombre_completo']) ?></li>
                <?php endforeach; ?>
              </ul>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($reporteAsignacion['sin_asignar'])): ?>
<div class="card border-0 shadow-sm border-danger">
  <div class="card-header bg-white py-3">
    <h3 class="h6 mb-0 text-danger"><i class="bi bi-exclamation-circle me-2"></i>Personas sin cupo asignado</h3>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>Nombre completo</th>
            <th>Teléfono</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reporteAsignacion['sin_asignar'] as $pasajero): ?>
          <tr>
            <td><strong><?= htmlspecialchars($pasajero['nombre_completo']) ?></strong></td>
            <td><?php $telefono = $pasajero['telefono']; include __DIR__ . '/../partials/celda-telefono-whatsapp.php'; ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<?php else: ?>

<div class="card border-0 shadow-sm mb-4 filters-panel">
  <button
    class="filters-panel__toggle d-md-none"
    type="button"
    data-bs-toggle="collapse"
    data-bs-target="#filtersTransportePanel"
    aria-expanded="false"
    aria-controls="filtersTransportePanel"
  >
    <i class="bi bi-funnel me-2"></i>Filtros
    <i class="bi bi-chevron-down filters-panel__chevron"></i>
  </button>
  <div class="collapse" id="filtersTransportePanel">
    <div class="card-body">
      <form method="GET" action="transporte-aniversario.php" class="row g-3 align-items-end">
        <input type="hidden" name="pestaña" value="registros">

        <div class="col-md-4">
          <label class="form-label small" for="buscar">Buscar</label>
          <input
            type="search"
            class="form-control form-control-sm"
            id="buscar"
            name="buscar"
            value="<?= htmlspecialchars($filtros['buscar']) ?>"
            placeholder="Nombre, teléfono…"
          >
        </div>

        <div class="col-md-3">
          <label class="form-label small" for="filtro_tipo_transporte">Tipo</label>
          <select class="form-select form-select-sm" id="filtro_tipo_transporte" name="tipo_transporte">
            <option value="">Todos</option>
            <option value="con_carro" <?= ($filtros['tipo_transporte'] ?? '') === 'con_carro' ? 'selected' : '' ?>>Tiene carro</option>
            <option value="necesita_transporte" <?= ($filtros['tipo_transporte'] ?? '') === 'necesita_transporte' ? 'selected' : '' ?>>Necesita transporte</option>
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
          <a href="transporte-aniversario.php?pestaña=registros" class="btn btn-outline-secondary btn-sm">Limpiar</a>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3">
    <h3 class="h6 mb-0"><i class="bi bi-list-ul me-2"></i>Registros</h3>
  </div>
  <div class="card-body p-0">
    <?php if (empty($registros)): ?>
    <div class="text-center text-muted py-5">
      <i class="bi bi-inbox display-6 d-block mb-2"></i>
      No hay registros de transporte con los filtros seleccionados.
    </div>
    <?php else: ?>
    <?php
    $modalesDetalle = [];
    $modalesEditar = [];
    $redireccionRegistros = construirUrlRegistros('transporte-aniversario.php', $filtros, $paginaActual);
    ?>
    <div class="table-responsive">
      <table class="table table-hover table-dashboard mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th class="text-center col-numero">#</th>
            <th>Nombre completo</th>
            <th>Tipo</th>
            <th>Teléfono</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($registros as $indice => $fila):
            $numeroRegistro = $offsetRegistros + $indice + 1;
            $modalId = 'detalle-transporte-' . (int) $fila['id'];
            $poseeMovilizacion = !empty($fila['posee_movilizacion']);
            $modalesDetalle[] = [
                'id'     => $modalId,
                'titulo' => 'Transporte #' . (int) $fila['id'],
                'filas'  => construirDetalleTransporteAniversario($fila),
                'extra'  => '',
            ];
            if (!empty($puedeEditar)) {
                $modalesEditar[] = [
                    'id'          => 'editar-' . $modalId,
                    'tipo'        => 'transporte_aniversario',
                    'fila'        => $fila,
                    'redireccion' => $redireccionRegistros,
                ];
            }
          ?>
          <tr>
            <td class="text-center text-muted"><?= $numeroRegistro ?></td>
            <td><strong><?= htmlspecialchars($fila['nombre_completo']) ?></strong></td>
            <td>
              <span class="badge <?= claseBadgeTipoTransporteAniversario($poseeMovilizacion) ?>">
                <?= htmlspecialchars(etiquetaTipoTransporteAniversario($poseeMovilizacion)) ?>
              </span>
            </td>
            <td><?php $telefono = $fila['telefono']; include __DIR__ . '/../partials/celda-telefono-whatsapp.php'; ?></td>
            <td class="text-end">
              <?php
              $eliminarAccion = 'eliminar_transporte_aniversario';
              $eliminarId = (int) $fila['id'];
              $eliminarRedireccion = $redireccionRegistros;
              $mensajeConfirmar = '¿Eliminar este registro de transporte?';
              $modalEditarId = 'editar-' . $modalId;
              include __DIR__ . '/../partials/tabla-acciones-registro.php';
              ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php include __DIR__ . '/../partials/paginacion-registros.php'; ?>
    <?php foreach ($modalesDetalle as $modal):
        $modalId = $modal['id'];
        $tituloModal = $modal['titulo'];
        $filasDetalle = $modal['filas'];
        $contenidoExtra = $modal['extra'];
        include __DIR__ . '/../partials/modal-detalle-registro.php';
    endforeach;
    include __DIR__ . '/../partials/modales-editar-registro.php';
    ?>
    <?php endif; ?>
  </div>
</div>

<?php endif; ?>

<?php endif; ?>

<script>
(function () {
  function configurarToggleMovilizacion(contenedor) {
    var checkbox = contenedor.querySelector('.js-posee-movilizacion');
    var campoAsientos = contenedor.querySelector('.js-campo-asientos');
    var inputAsientos = contenedor.querySelector('[name="asientos_disponibles"]');

    if (!checkbox || !campoAsientos) {
      return;
    }

    function actualizar() {
      var activo = checkbox.checked;
      campoAsientos.style.display = activo ? '' : 'none';

      if (inputAsientos) {
        inputAsientos.required = activo;
        if (!activo) {
          inputAsientos.value = '';
        }
      }
    }

    checkbox.addEventListener('change', actualizar);
    actualizar();
  }

  var formularioNuevo = document.getElementById('formTransporteAniversario');
  if (formularioNuevo) {
    configurarToggleMovilizacion(formularioNuevo);
  }

  document.querySelectorAll('.modal-editar-registro form').forEach(function (formulario) {
    if (formulario.querySelector('.js-posee-movilizacion')) {
      configurarToggleMovilizacion(formulario);
    }
  });
})();
</script>
