<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h2 class="h4 mb-1">Consejería</h2>
    <p class="text-muted small mb-0">
      <?php if (!empty($puedeRegistrar)): ?>
      Registra solicitudes y asigna fecha y hora de cita
      <?php else: ?>
      Consulta de solicitudes de consejería
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
      href="consejeria.php?pestaña=registros"
      role="tab"
    >
      <i class="bi bi-list-ul me-1"></i>Registros
    </a>
  </li>
  <?php if (!empty($puedeRegistrar)): ?>
  <li class="nav-item" role="presentation">
    <a
      class="nav-link <?= $pestaña === 'nuevo' ? 'active' : '' ?>"
      href="consejeria.php?pestaña=nuevo"
      role="tab"
    >
      <i class="bi bi-plus-circle me-1"></i>Nueva solicitud
    </a>
  </li>
  <?php endif; ?>
</ul>

<?php if ($pestaña === 'nuevo' && !empty($puedeRegistrar)): ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3">
    <h3 class="h6 mb-0"><i class="bi bi-plus-circle me-2"></i>Nueva solicitud de consejería</h3>
  </div>
  <div class="card-body">
    <form method="POST" action="acciones.php" class="row g-3 js-form-registro" id="formRegistroConsejeria" data-mensaje-exito="Solicitud de consejería registrada correctamente.">
      <input type="hidden" name="accion" value="crear_consejeria">
      <input type="hidden" name="redireccion" value="consejeria.php?pestaña=nuevo">

      <div class="col-md-6">
        <label class="form-label" for="nombre_completo">Nombre completo <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" required maxlength="200">
      </div>

      <div class="col-md-6">
        <label class="form-label" for="telefono">Teléfono <span class="text-danger">*</span></label>
        <input type="tel" class="form-control" id="telefono" name="telefono" required maxlength="30">
      </div>

      <div class="col-md-6 col-lg-4">
        <label class="form-label" for="tipo_consejeria">Tipo de consejería <span class="text-danger">*</span></label>
        <select class="form-select" id="tipo_consejeria" name="tipo_consejeria" required>
          <option value="">Seleccione…</option>
          <?php foreach ($tiposConsejeria as $clave => $etiqueta): ?>
          <option value="<?= htmlspecialchars($clave) ?>"><?= htmlspecialchars($etiqueta) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6 col-lg-4">
        <label class="form-label" for="anio_en_cda">Tiempo en CDA (año) <span class="text-danger">*</span></label>
        <input
          type="number"
          class="form-control"
          id="anio_en_cda"
          name="anio_en_cda"
          required
          min="1900"
          max="<?= (int) date('Y') ?>"
          placeholder="<?= (int) date('Y') ?>"
        >
        <div class="form-text">Año en que ingresó al CDA</div>
      </div>

      <div class="col-md-6 col-lg-4">
        <label class="form-label d-block">¿Es primera consejería? <span class="text-danger">*</span></label>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="primera_consejeria" id="primera_si" value="1" required checked>
          <label class="form-check-label" for="primera_si">Sí</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="primera_consejeria" id="primera_no" value="0">
          <label class="form-check-label" for="primera_no">No</label>
        </div>
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-save me-1"></i>Guardar
        </button>
      </div>
    </form>
  </div>
</div>

<?php else: ?>

<div class="card border-0 shadow-sm mb-4 filters-panel">
  <button
    class="filters-panel__toggle d-md-none"
    type="button"
    data-bs-toggle="collapse"
    data-bs-target="#filtersConsejeriaPanel"
    aria-expanded="false"
    aria-controls="filtersConsejeriaPanel"
  >
    <i class="bi bi-funnel me-2"></i>Filtros
    <i class="bi bi-chevron-down filters-panel__chevron"></i>
  </button>
  <div class="collapse" id="filtersConsejeriaPanel">
    <div class="card-body">
      <form method="GET" action="consejeria.php" class="row g-3 align-items-end">
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
          <label class="form-label small" for="filtro_tipo_consejeria">Tipo</label>
          <select class="form-select form-select-sm" id="filtro_tipo_consejeria" name="tipo_consejeria">
            <option value="">Todos</option>
            <?php foreach ($tiposConsejeria as $clave => $etiqueta): ?>
            <option value="<?= htmlspecialchars($clave) ?>" <?= $filtros['tipo_consejeria'] === $clave ? 'selected' : '' ?>>
              <?= htmlspecialchars($etiqueta) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label small" for="fecha_desde">Cita desde</label>
          <input type="date" class="form-control form-control-sm" id="fecha_desde" name="fecha_desde" value="<?= htmlspecialchars($filtros['fecha_desde']) ?>">
        </div>

        <div class="col-md-2">
          <label class="form-label small" for="fecha_hasta">Cita hasta</label>
          <input type="date" class="form-control form-control-sm" id="fecha_hasta" name="fecha_hasta" value="<?= htmlspecialchars($filtros['fecha_hasta']) ?>">
        </div>

        <div class="col-md-auto d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-funnel me-1"></i>Filtrar
          </button>
          <a href="consejeria.php?pestaña=registros" class="btn btn-outline-secondary btn-sm">Limpiar</a>
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
      No hay solicitudes de consejería con los filtros seleccionados.
    </div>
    <?php else: ?>
    <?php
    $modalesDetalle = [];
    $modalesEditar = [];
    $redireccionRegistros = construirUrlRegistros('consejeria.php', $filtros, $paginaActual);
    ?>
    <div class="table-responsive">
      <table class="table table-hover table-dashboard mb-0 align-middle">
        <thead class="table-light">
          <?php
          $mostrarTipo = true;
          $mostrarValor = false;
          $mostrarObservacion = false;
          include __DIR__ . '/../partials/tabla-registros-cabecera.php';
          ?>
        </thead>
        <tbody>
          <?php foreach ($registros as $indice => $fila):
            $numeroRegistro = $offsetRegistros + $indice + 1;
            $modalId = 'detalle-consejeria-' . (int) $fila['id'];
            $fechaTabla = ($fila['cita_fecha'] ?? '') !== ''
                ? formatearCitaConsejeria($fila['cita_fecha'], $fila['cita_hora'] ?? null)
                : formatearFechaTabla($fila['creado_en']);
            ob_start();
            if (!empty($puedeAsignar)):
          ?>
          <form method="POST" action="acciones.php" class="row g-2 align-items-end">
            <input type="hidden" name="accion" value="asignar_cita_consejeria">
            <input type="hidden" name="id" value="<?= (int) $fila['id'] ?>">
            <input type="hidden" name="redireccion" value="<?= htmlspecialchars(construirUrlRegistros('consejeria.php', $filtros, $paginaActual)) ?>">
            <div class="col-sm-5">
              <label class="form-label small mb-1">Fecha de cita</label>
              <input type="date" class="form-control form-control-sm" name="cita_fecha" value="<?= htmlspecialchars($fila['cita_fecha'] ?? '') ?>">
            </div>
            <div class="col-sm-4">
              <label class="form-label small mb-1">Hora</label>
              <input type="time" class="form-control form-control-sm" name="cita_hora" value="<?= htmlspecialchars(formatearHoraConsejeria($fila['cita_hora'] ?? null)) ?>">
            </div>
            <div class="col-sm-3">
              <button type="submit" class="btn btn-primary btn-sm w-100">
                <i class="bi bi-calendar-check me-1"></i>Asignar
              </button>
            </div>
          </form>
          <?php
            endif;
            $extraConsejeria = ob_get_clean();
            $modalesDetalle[] = [
                'id'     => $modalId,
                'titulo' => 'Consejería #' . (int) $fila['id'],
                'filas'  => construirDetalleConsejeria($fila),
                'extra'  => $extraConsejeria,
            ];
            if (!empty($puedeEditar)) {
                $modalesEditar[] = [
                    'id'         => 'editar-' . $modalId,
                    'tipo'       => 'consejeria',
                    'fila'       => $fila,
                    'redireccion'=> $redireccionRegistros,
                ];
            }
          ?>
          <tr>
            <td class="text-center text-muted"><?= $numeroRegistro ?></td>
            <td>
              <span class="badge bg-secondary"><?= htmlspecialchars(etiquetaTipoConsejeria($fila['tipo_consejeria'])) ?></span>
            </td>
            <td><?= htmlspecialchars($fila['nombre_completo']) ?></td>
            <td><?= htmlspecialchars($fechaTabla) ?></td>
            <td><?php $telefono = $fila['telefono']; include __DIR__ . '/../partials/celda-telefono-whatsapp.php'; ?></td>
            <td class="text-end">
              <?php
              $eliminarAccion = 'eliminar_consejeria';
              $eliminarId = (int) $fila['id'];
              $eliminarRedireccion = $redireccionRegistros;
              $mensajeConfirmar = '¿Eliminar esta solicitud?';
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
