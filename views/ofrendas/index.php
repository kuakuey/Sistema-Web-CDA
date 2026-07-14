<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h2 class="h4 mb-1">Ofrendas</h2>
    <p class="text-muted small mb-0">
      <?php if (!empty($puedeRegistrar)): ?>
      Registra y consulta las ofrendas de las casas de vida
      <?php else: ?>
      Consulta de ofrendas (solo lectura)
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
      href="ofrendas.php?pestaña=registros"
      role="tab"
    >
      <i class="bi bi-list-ul me-1"></i>Registros
    </a>
  </li>
  <?php if (!empty($puedeRegistrar)): ?>
  <li class="nav-item" role="presentation">
    <a
      class="nav-link <?= $pestaña === 'nuevo' ? 'active' : '' ?>"
      href="ofrendas.php?pestaña=nuevo"
      role="tab"
    >
      <i class="bi bi-plus-circle me-1"></i>Nueva ofrenda
    </a>
  </li>
  <?php endif; ?>
</ul>

<?php if ($pestaña === 'nuevo' && !empty($puedeRegistrar)): ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3">
    <h3 class="h6 mb-0"><i class="bi bi-plus-circle me-2"></i>Registrar ofrenda</h3>
  </div>
  <div class="card-body">
    <?php if (empty($casas)): ?>
    <div class="alert alert-warning mb-0">
      <i class="bi bi-exclamation-triangle me-1"></i>
      No hay casas de vida registradas. Configura la estructura CDV antes de registrar ofrendas.
    </div>
    <?php else: ?>
    <form method="POST" action="acciones.php" class="row g-3 js-form-registro" id="formOfrenda" data-mensaje-exito="Ofrenda registrada correctamente.">
      <input type="hidden" name="accion" value="crear_ofrenda">
      <input type="hidden" name="redireccion" value="ofrendas.php?pestaña=nuevo">

      <div class="col-md-6 col-lg-4">
        <label class="form-label" for="territorio_id">Territorio <span class="text-danger">*</span></label>
        <select class="form-select" id="territorio_id" name="territorio_id" required>
          <option value="">Seleccione…</option>
          <?php foreach ($territorios as $territorio): ?>
          <option value="<?= (int) $territorio['id'] ?>"><?= htmlspecialchars($territorio['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6 col-lg-4">
        <label class="form-label" for="casa_id">Casa de vida <span class="text-danger">*</span></label>
        <select class="form-select" id="casa_id" name="casa_id" required disabled>
          <option value="">Primero elija un territorio</option>
        </select>
      </div>

      <div class="col-md-6 col-lg-4">
        <label class="form-label" for="lider">Líder</label>
        <input type="text" class="form-control" id="lider" readonly placeholder="—">
      </div>

      <div class="col-md-6 col-lg-4">
        <label class="form-label" for="fecha_ofrenda">Fecha de ofrenda <span class="text-danger">*</span></label>
        <input type="date" class="form-control" id="fecha_ofrenda" name="fecha_ofrenda" required value="<?= htmlspecialchars(date('Y-m-d')) ?>">
      </div>

      <div class="col-md-6 col-lg-4">
        <label class="form-label" for="monto">Valor <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="monto" name="monto" step="0.01" min="0.01" required placeholder="0.00">
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-save me-1"></i>Guardar
        </button>
      </div>
    </form>
    <script>
      window.cdaOfrendasCasas = <?= json_encode($casas, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    </script>
    <?php endif; ?>
  </div>
</div>

<?php else: ?>

<div class="card border-0 shadow-sm mb-4 filters-panel">
  <button
    class="filters-panel__toggle d-md-none"
    type="button"
    data-bs-toggle="collapse"
    data-bs-target="#filtersOfrendasPanel"
    aria-expanded="false"
    aria-controls="filtersOfrendasPanel"
  >
    <i class="bi bi-funnel me-2"></i>Filtros
    <i class="bi bi-chevron-down filters-panel__chevron"></i>
  </button>
  <div class="collapse" id="filtersOfrendasPanel">
    <div class="card-body">
      <form method="GET" action="ofrendas.php" class="row g-3 align-items-end">
        <input type="hidden" name="pestaña" value="registros">

        <div class="col-md-4">
          <label class="form-label small" for="buscar">Buscar</label>
          <input
            type="search"
            class="form-control form-control-sm"
            id="buscar"
            name="buscar"
            value="<?= htmlspecialchars($filtros['buscar']) ?>"
            placeholder="Casa de vida, líder, registró…"
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

        <div class="col-md-2">
          <label class="form-label small" for="monto_min">Valor mín.</label>
          <input type="number" step="0.01" class="form-control form-control-sm" id="monto_min" name="monto_min" value="<?= htmlspecialchars($filtros['monto_min']) ?>">
        </div>

        <div class="col-md-2">
          <label class="form-label small" for="monto_max">Valor máx.</label>
          <input type="number" step="0.01" class="form-control form-control-sm" id="monto_max" name="monto_max" value="<?= htmlspecialchars($filtros['monto_max']) ?>">
        </div>

        <div class="col-md-auto d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-funnel me-1"></i>Filtrar
          </button>
          <a href="ofrendas.php?pestaña=registros" class="btn btn-outline-secondary btn-sm">Limpiar</a>
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
      No hay ofrendas con los filtros seleccionados.
    </div>
    <?php else: ?>
    <?php
    $modalesDetalle = [];
    $modalesEditar = [];
    $redireccionRegistros = construirUrlRegistros('ofrendas.php', $filtros, $paginaActual);
    ?>
    <div class="table-responsive">
      <table class="table table-hover table-dashboard mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>Nombre</th>
            <th>Fecha de CDV</th>
            <th>Valor</th>
            <th>Fecha de registro</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($registros as $fila):
            $modalId = 'detalle-ofrenda-' . (int) $fila['id'];
            $modalesDetalle[] = [
                'id'     => $modalId,
                'titulo' => 'Ofrenda #' . (int) $fila['id'],
                'filas'  => construirDetalleOfrenda($fila),
                'extra'  => '',
            ];
            if (!empty($puedeEditar)) {
                $modalesEditar[] = [
                    'id'         => 'editar-' . $modalId,
                    'tipo'       => 'ofrenda',
                    'fila'       => $fila,
                    'redireccion'=> $redireccionRegistros,
                ];
            }
          ?>
          <tr>
            <td><?= htmlspecialchars($fila['casa_vida'] ?? '—') ?></td>
            <td><?= htmlspecialchars(formatearFechaTabla($fila['fecha_ofrenda'])) ?></td>
            <td><strong><?= htmlspecialchars(formatearMonto((float) $fila['monto'])) ?></strong></td>
            <td><?= htmlspecialchars(formatearFechaTabla($fila['creado_en'] ?? null)) ?></td>
            <td class="text-end">
              <?php
              $eliminarAccion = 'eliminar_ofrenda';
              $eliminarId = (int) $fila['id'];
              $eliminarRedireccion = $redireccionRegistros;
              $mensajeConfirmar = '¿Eliminar esta ofrenda?';
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
