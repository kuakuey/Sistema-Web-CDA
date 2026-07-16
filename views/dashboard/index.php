<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h2 class="h4 mb-1"><?= htmlspecialchars($etiquetasSecciones[$seccion] ?? 'Registros') ?></h2>
    <p class="text-muted small mb-0">
      <?php if ($seccion === 'generales'): ?>
      Registros ingresados hoy, <?= htmlspecialchars(formatearFechaTabla(date('Y-m-d'))) ?>
      <?php elseif (!empty($puedeRegistrar)): ?>
      Consulta e ingresa registros del ministerio
      <?php else: ?>
      Consulta de registros (solo lectura)
      <?php endif; ?>
    </p>
  </div>
  <span class="badge bg-primary fs-6"><?= (int) $totalRegistros ?> resultado(s)</span>
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

<?php if ($seccion !== 'generales'): ?>
<ul class="nav nav-tabs mb-4" role="tablist">
  <li class="nav-item" role="presentation">
    <a
      class="nav-link <?= $pestaña === 'registros' ? 'active' : '' ?>"
      href="<?= htmlspecialchars($archivoPagina) ?>?pestaña=registros"
      role="tab"
    >
      <i class="bi bi-list-ul me-1"></i>Registros
    </a>
  </li>
  <?php if (!empty($puedeRegistrar)): ?>
  <li class="nav-item" role="presentation">
    <a
      class="nav-link <?= $pestaña === 'nuevo' ? 'active' : '' ?>"
      href="<?= htmlspecialchars($archivoPagina) ?>?pestaña=nuevo"
      role="tab"
    >
      <i class="bi bi-plus-circle me-1"></i>Nuevo registro
    </a>
  </li>
  <?php endif; ?>
</ul>
<?php endif; ?>

<?php if ($pestaña === 'nuevo' && !empty($puedeRegistrar)): ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3">
    <h3 class="h6 mb-0">
      <i class="bi bi-plus-circle me-2"></i>
      <?php if ($tipoRegistro === 'presentaciones'): ?>
      Nueva presentación
      <?php else: ?>
      Nuevo registro
      <?php endif; ?>
    </h3>
  </div>
  <div class="card-body">
    <?php if ($tipoRegistro === 'presentaciones'): ?>
    <?php include __DIR__ . '/../partials/form-nuevo-presentacion.php'; ?>
    <?php else: ?>
    <?php include __DIR__ . '/../partials/form-nuevo-registro.php'; ?>
    <?php endif; ?>
  </div>
</div>

<?php else: ?>

<?php if ($seccion !== 'generales'): ?>
<div class="card border-0 shadow-sm mb-4 filters-panel">
  <button
    class="filters-panel__toggle d-md-none"
    type="button"
    data-bs-toggle="collapse"
    data-bs-target="#filtersPanel"
    aria-expanded="false"
    aria-controls="filtersPanel"
  >
    <i class="bi bi-funnel me-2"></i>Filtros
    <i class="bi bi-chevron-down filters-panel__chevron"></i>
  </button>
  <div class="collapse" id="filtersPanel">
    <div class="card-body">
    <form method="GET" action="<?= htmlspecialchars($archivoPagina) ?>" class="row g-3 align-items-end">
      <input type="hidden" name="pestaña" value="registros">

      <div class="col-md-4">
        <label class="form-label small" for="buscar">Buscar</label>
        <input
          type="search"
          class="form-control form-control-sm"
          id="buscar"
          name="buscar"
          value="<?= htmlspecialchars($filtros['buscar']) ?>"
          placeholder="Nombre, email, teléfono…"
        >
      </div>

      <div class="col-md-2">
        <label class="form-label small" for="fecha_desde">Desde</label>
        <input type="date" class="form-control form-control-sm" id="fecha_desde" name="fecha_desde" value="<?= htmlspecialchars($filtros['fecha_desde']) ?>">
      </div>

      <div class="col-md-2">
        <label class="form-label small" for="fecha_hasta">Hasta</label>
        <input type="date" class="form-control form-control-sm" id="fecha_hasta" name="fecha_hasta" value="<?= htmlspecialchars($filtros['fecha_hasta']) ?>">
      </div>

      <?php if ($tipoRegistro === 'inscripciones' && $seccion === 'conexion'): ?>
      <div class="col-md-2">
        <label class="form-label small" for="zona">Zona</label>
        <select class="form-select form-select-sm" id="zona" name="zona">
          <option value="">Todas</option>
          <?php foreach ($zonas as $slug => $etiqueta): ?>
          <option value="<?= htmlspecialchars($slug) ?>" <?= $filtros['zona'] === $slug ? 'selected' : '' ?>>
            <?= htmlspecialchars($etiqueta) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>

      <?php if ($tipoRegistro === 'inscripciones' && $seccion === 'conexion'): ?>
      <div class="col-md-2">
        <label class="form-label small" for="contactado">Contactado</label>
        <select class="form-select form-select-sm" id="contactado" name="contactado">
          <option value="todos" <?= $filtros['contactado'] === 'todos' ? 'selected' : '' ?>>Todos</option>
          <option value="0" <?= $filtros['contactado'] === '0' ? 'selected' : '' ?>>Recibido</option>
          <option value="1" <?= $filtros['contactado'] === '1' ? 'selected' : '' ?>>Contactado</option>
        </select>
      </div>
      <?php endif; ?>

      <?php if ($tipoRegistro === 'presentaciones'): ?>
      <div class="col-md-2">
        <label class="form-label small" for="estado">Estado</label>
        <select class="form-select form-select-sm" id="estado" name="estado">
          <option value="">Todos</option>
          <?php foreach ($estadosPresentacion as $estado): ?>
          <option value="<?= htmlspecialchars($estado) ?>" <?= $filtros['estado'] === $estado ? 'selected' : '' ?>>
            <?= htmlspecialchars($etiquetasEstadosPresentacion[$estado] ?? $estado) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>

      <?php if ($tipoRegistro === 'inscripciones' && $seccion === 'bautismo'): ?>
      <div class="col-md-2">
        <label class="form-label small" for="estado">Estado</label>
        <select class="form-select form-select-sm" id="estado" name="estado">
          <option value="">Todos</option>
          <?php foreach ($estadosBautismo as $estadoBautismo): ?>
          <option value="<?= htmlspecialchars($estadoBautismo) ?>" <?= $filtros['estado'] === $estadoBautismo ? 'selected' : '' ?>>
            <?= htmlspecialchars($etiquetasEstadosBautismo[$estadoBautismo] ?? $estadoBautismo) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>

      <div class="col-md-auto d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="bi bi-funnel me-1"></i>Filtrar
        </button>
        <a href="<?= htmlspecialchars($archivoPagina) ?>?pestaña=registros" class="btn btn-outline-secondary btn-sm">Limpiar</a>
      </div>
    </form>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3">
    <h3 class="h6 mb-0">
      <i class="bi bi-list-ul me-2"></i>
      <?= $seccion === 'generales' ? 'Registros de hoy' : 'Registros' ?>
    </h3>
  </div>
  <div class="card-body p-0">
    <?php if (empty($registros)): ?>
    <div class="text-center text-muted py-5">
      <i class="bi bi-inbox display-6 d-block mb-2"></i>
      No hay registros<?= $seccion === 'generales' ? ' ingresados hoy' : ' con los filtros seleccionados' ?>.
    </div>
    <?php elseif ($tipoRegistro === 'inscripciones'): ?>
    <?php
    $mostrarTipo = ($seccion === 'generales');
    $mostrarEstado = ($seccion === 'conexion' || $seccion === 'bautismo');
    $modalesDetalle = [];
    $modalesEditar = [];
    ?>
    <div class="table-responsive">
      <table class="table table-hover table-dashboard mb-0 align-middle">
        <thead class="table-light">
          <?php
          $mostrarValor = false;
          $mostrarObservacion = false;
          include __DIR__ . '/../partials/tabla-registros-cabecera.php';
          ?>
        </thead>
        <tbody>
          <?php foreach ($registros as $indice => $fila):
            $numeroRegistro = $offsetRegistros + $indice + 1;
            $modalId = 'detalle-inscripcion-' . (int) $fila['id'];
            $modalesDetalle[] = [
                'id'      => $modalId,
                'titulo'  => 'Inscripción #' . (int) $fila['id'],
                'filas'   => construirDetalleInscripcion($fila, $etiquetasFormulario),
                'extra'   => '',
            ];
            if (!empty($puedeEditar)) {
                $modalesEditar[] = [
                    'id'         => 'editar-' . $modalId,
                    'tipo'       => 'inscripcion',
                    'fila'       => $fila,
                    'redireccion'=> $urlPaginaConFiltros,
                ];
            }
          ?>
          <tr>
            <td class="text-center text-muted"><?= $numeroRegistro ?></td>
            <?php if ($mostrarTipo): ?>
            <td>
              <span class="badge badge-form badge-form--<?= htmlspecialchars($fila['tipo_formulario']) ?>">
                <?= htmlspecialchars($etiquetasFormulario[$fila['tipo_formulario']] ?? $fila['tipo_formulario']) ?>
              </span>
            </td>
            <?php endif; ?>
            <td><?= htmlspecialchars(trim($fila['nombre'] . ' ' . $fila['apellido'])) ?></td>
            <td><?= htmlspecialchars(formatearFechaTabla($fila['creado_en'])) ?></td>
            <td><?php $telefono = $fila['celular']; include __DIR__ . '/../partials/celda-telefono-whatsapp.php'; ?></td>
            <?php if ($mostrarEstado): ?>
            <td>
              <?php if ($seccion === 'conexion'): ?>
              <?php
              $puedeEditarEstado = $puedeGestionarEstadoConexion ?? false;
              $urlRedireccion = $urlPaginaConFiltros;
              include __DIR__ . '/../partials/celda-estado-conexion.php';
              ?>
              <?php elseif ($seccion === 'bautismo'): ?>
              <?php
              $puedeEditarEstado = $puedeGestionarEstadoBautismo ?? false;
              $urlRedireccion = $urlPaginaConFiltros;
              include __DIR__ . '/../partials/celda-estado-bautismo.php';
              ?>
              <?php endif; ?>
            </td>
            <?php endif; ?>
            <td class="text-end">
              <?php
              $eliminarAccion = 'eliminar_inscripcion';
              $eliminarId = (int) $fila['id'];
              $eliminarRedireccion = $urlPaginaConFiltros;
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

    <?php elseif ($tipoRegistro === 'presentaciones'): ?>
    <?php
    $modalesDetalle = [];
    $modalesEditar = [];
    ?>
    <div class="table-responsive">
      <table class="table table-hover table-dashboard mb-0 align-middle">
        <thead class="table-light">
          <?php
          $mostrarTipo = false;
          $mostrarValor = false;
          $mostrarObservacion = false;
          $mostrarEstado = true;
          include __DIR__ . '/../partials/tabla-registros-cabecera.php';
          ?>
        </thead>
        <tbody>
          <?php foreach ($registros as $indice => $fila):
            $numeroRegistro = $offsetRegistros + $indice + 1;
            $modalId = 'detalle-presentacion-' . (int) $fila['id'];
            $modalesDetalle[] = [
                'id'     => $modalId,
                'titulo' => 'Presentación #' . (int) $fila['id'],
                'filas'  => construirDetallePresentacion($fila, $etiquetasEstadosPresentacion),
                'extra'  => '',
            ];
            if (!empty($puedeEditar)) {
                $modalesEditar[] = [
                    'id'         => 'editar-' . $modalId,
                    'tipo'       => 'presentacion',
                    'fila'       => $fila,
                    'redireccion'=> $urlPaginaConFiltros,
                ];
            }
          ?>
          <tr>
            <td class="text-center text-muted"><?= $numeroRegistro ?></td>
            <td><?= htmlspecialchars($fila['nombre_presentado']) ?></td>
            <td><?= htmlspecialchars(formatearFechaTabla($fila['creado_en'])) ?></td>
            <td><?= enlacesWhatsAppPresentacion($fila) ?></td>
            <td>
              <?php
              $urlRedireccion = $urlPaginaConFiltros;
              include __DIR__ . '/../partials/celda-estado-presentacion.php';
              ?>
            </td>
            <td class="text-end">
              <?php
              $eliminarAccion = 'eliminar_presentacion';
              $eliminarId = (int) $fila['id'];
              $eliminarRedireccion = $urlPaginaConFiltros;
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
