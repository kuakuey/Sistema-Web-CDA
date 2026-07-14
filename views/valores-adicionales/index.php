<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h2 class="h4 mb-1">Valores adicionales</h2>
    <p class="text-muted small mb-0">
      <?php if (!empty($puedeRegistrar)): ?>
      Registra y consulta valores adicionales del ministerio
      <?php else: ?>
      Consulta de valores adicionales (solo lectura)
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
      href="valores-adicionales.php?pestaña=registros"
      role="tab"
    >
      <i class="bi bi-list-ul me-1"></i>Registros
    </a>
  </li>
  <?php if (!empty($puedeRegistrar)): ?>
  <li class="nav-item" role="presentation">
    <a
      class="nav-link <?= $pestaña === 'nuevo' ? 'active' : '' ?>"
      href="valores-adicionales.php?pestaña=nuevo"
      role="tab"
    >
      <i class="bi bi-plus-circle me-1"></i>Nuevo valor
    </a>
  </li>
  <?php endif; ?>
  <?php if (!empty($puedeGestionarTipos)): ?>
  <li class="nav-item" role="presentation">
    <a
      class="nav-link <?= $pestaña === 'tipos' ? 'active' : '' ?>"
      href="valores-adicionales.php?pestaña=tipos"
      role="tab"
    >
      <i class="bi bi-tags me-1"></i>Tipos de valores
    </a>
  </li>
  <?php endif; ?>
</ul>

<?php if ($pestaña === 'nuevo' && !empty($puedeRegistrar)): ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3">
    <h3 class="h6 mb-0"><i class="bi bi-plus-circle me-2"></i>Nuevo valor adicional</h3>
  </div>
  <div class="card-body">
    <form method="POST" action="acciones.php" class="row g-3 js-form-registro" id="formRegistroValorAdicional" data-mensaje-exito="Valor adicional registrado correctamente.">
      <input type="hidden" name="accion" value="crear_valor_adicional">
      <input type="hidden" name="redireccion" value="valores-adicionales.php?pestaña=nuevo">

      <div class="col-md-6 col-lg-4">
        <label class="form-label" for="tipo">Tipo <span class="text-danger">*</span></label>
        <select class="form-select" id="tipo" name="tipo" required>
          <option value="">Seleccione…</option>
          <?php foreach ($tiposValor as $clave => $etiqueta): ?>
          <option value="<?= htmlspecialchars($clave) ?>"><?= htmlspecialchars($etiqueta) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="w-100"></div>

      <div class="col-md-6 col-lg-3">
        <label class="form-label" for="nombre">Nombre <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="nombre" name="nombre" required maxlength="100">
      </div>

      <div class="col-md-6 col-lg-3">
        <label class="form-label" for="fecha">Fecha <span class="text-danger">*</span></label>
        <input type="date" class="form-control" id="fecha" name="fecha" required value="<?= htmlspecialchars(date('Y-m-d')) ?>">
      </div>

      <div class="col-md-6 col-lg-3">
        <label class="form-label" for="telefono">Teléfono <span class="text-danger">*</span></label>
        <input type="tel" class="form-control" id="telefono" name="telefono" required maxlength="30">
      </div>

      <div class="col-md-6 col-lg-3">
        <label class="form-label" for="valor">Valor <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="valor" name="valor" step="0.01" min="0.01" required placeholder="0.00">
      </div>

      <div class="w-100"></div>

      <div class="col-12">
        <label class="form-label" for="observacion">Observación</label>
        <textarea class="form-control" id="observacion" name="observacion" rows="2" maxlength="1000" placeholder="Notas opcionales…"></textarea>
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-save me-1"></i>Guardar
        </button>
      </div>
    </form>
  </div>
</div>

<?php elseif ($pestaña === 'tipos' && !empty($puedeGestionarTipos)): ?>
<div class="row g-4">
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3">
        <h3 class="h6 mb-0"><i class="bi bi-plus-circle me-2"></i>Nuevo tipo</h3>
      </div>
      <div class="card-body">
        <form method="POST" action="valores-adicionales.php?pestaña=tipos">
          <input type="hidden" name="accion" value="crear_tipo_valor">
          <div class="mb-3">
            <label class="form-label" for="etiqueta_tipo">Nombre del tipo <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="etiqueta_tipo" name="etiqueta" required maxlength="100">
          </div>
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-plus-lg me-1"></i>Agregar tipo
          </button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3">
        <h3 class="h6 mb-0"><i class="bi bi-tags me-2"></i>Tipos registrados</h3>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover table-dashboard mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th class="text-center col-numero">#</th>
                <th>Nombre</th>
                <th>Registros</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($filasTipos)): ?>
              <tr>
                <td colspan="4" class="text-center text-muted py-5">No hay tipos registrados.</td>
              </tr>
              <?php else: ?>
              <?php foreach ($filasTipos as $indice => $tipoFila): ?>
              <tr>
                <td class="text-center text-muted"><?= $indice + 1 ?></td>
                <td>
                  <form method="POST" action="valores-adicionales.php?pestaña=tipos" class="row g-2 align-items-center">
                    <input type="hidden" name="accion" value="actualizar_tipo_valor">
                    <input type="hidden" name="id" value="<?= (int) $tipoFila['id'] ?>">
                    <div class="col">
                      <input
                        type="text"
                        class="form-control form-control-sm"
                        name="etiqueta"
                        value="<?= htmlspecialchars($tipoFila['etiqueta']) ?>"
                        required
                        maxlength="100"
                      >
                    </div>
                    <div class="col-auto">
                      <button type="submit" class="btn btn-sm btn-outline-primary">Guardar</button>
                    </div>
                  </form>
                </td>
                <td><span class="badge bg-secondary"><?= (int) ($tipoFila['total_registros'] ?? 0) ?></span></td>
                <td class="text-end">
                  <?php if ($puedeEliminar): ?>
                  <form
                    method="POST"
                    action="acciones.php"
                    class="d-inline js-form-confirmar"
                    data-confirm-title="Eliminar tipo"
                    data-confirm="¿Eliminar este tipo?"
                  >
                    <input type="hidden" name="accion" value="eliminar_tipo_valor">
                    <input type="hidden" name="id" value="<?= (int) $tipoFila['id'] ?>">
                    <input type="hidden" name="redireccion" value="valores-adicionales.php?pestaña=tipos">
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                      <i class="bi bi-trash"></i>
                    </button>
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
  </div>
</div>

<?php elseif ($pestaña === 'registros'): ?>

<div class="card border-0 shadow-sm mb-4 filters-panel">
  <button
    class="filters-panel__toggle d-md-none"
    type="button"
    data-bs-toggle="collapse"
    data-bs-target="#filtersValoresPanel"
    aria-expanded="false"
    aria-controls="filtersValoresPanel"
  >
    <i class="bi bi-funnel me-2"></i>Filtros
    <i class="bi bi-chevron-down filters-panel__chevron"></i>
  </button>
  <div class="collapse" id="filtersValoresPanel">
    <div class="card-body">
      <form method="GET" action="valores-adicionales.php" class="row g-3 align-items-end">
        <input type="hidden" name="pestaña" value="registros">

        <div class="col-md-3">
          <label class="form-label small" for="buscar">Buscar</label>
          <input
            type="search"
            class="form-control form-control-sm"
            id="buscar"
            name="buscar"
            value="<?= htmlspecialchars($filtros['buscar']) ?>"
            placeholder="Nombre, teléfono, observación…"
          >
        </div>

        <div class="col-md-2">
          <label class="form-label small" for="tipo_valor">Tipo</label>
          <select class="form-select form-select-sm" id="tipo_valor" name="tipo_valor">
            <option value="">Todos</option>
            <?php foreach ($tiposValor as $clave => $etiqueta): ?>
            <option value="<?= htmlspecialchars($clave) ?>" <?= $filtros['tipo_valor'] === $clave ? 'selected' : '' ?>>
              <?= htmlspecialchars($etiqueta) ?>
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
          <a href="valores-adicionales.php?pestaña=registros" class="btn btn-outline-secondary btn-sm">Limpiar</a>
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
      No hay valores adicionales con los filtros seleccionados.
    </div>
    <?php else: ?>
    <?php
    $modalesDetalle = [];
    $modalesEditar = [];
    $redireccionRegistros = construirUrlRegistros('valores-adicionales.php', $filtros, $paginaActual);
    ?>
    <div class="table-responsive">
      <table class="table table-hover table-dashboard mb-0 align-middle">
        <thead class="table-light">
          <?php
          $mostrarTipo = true;
          $mostrarValor = true;
          $mostrarObservacion = true;
          include __DIR__ . '/../partials/tabla-registros-cabecera.php';
          ?>
        </thead>
        <tbody>
          <?php foreach ($registros as $indice => $fila):
            $numeroRegistro = $offsetRegistros + $indice + 1;
            $modalId = 'detalle-valor-' . (int) $fila['id'];
            $modalesDetalle[] = [
                'id'     => $modalId,
                'titulo' => 'Valor adicional #' . (int) $fila['id'],
                'filas'  => construirDetalleValorAdicional($fila),
                'extra'  => '',
            ];
            if (!empty($puedeEditar)) {
                $modalesEditar[] = [
                    'id'         => 'editar-' . $modalId,
                    'tipo'       => 'valor_adicional',
                    'fila'       => $fila,
                    'redireccion'=> $redireccionRegistros,
                ];
            }
            $observacion = $fila['observacion'] ?? null;
          ?>
          <tr>
            <td class="text-center text-muted"><?= $numeroRegistro ?></td>
            <td>
              <span class="badge bg-secondary"><?= htmlspecialchars(etiquetaTipoValorAdicional($fila['tipo'])) ?></span>
            </td>
            <td><?= htmlspecialchars($fila['nombre']) ?></td>
            <td><?= htmlspecialchars(formatearFechaTabla($fila['fecha'])) ?></td>
            <td><?php $telefono = $fila['telefono']; include __DIR__ . '/../partials/celda-telefono-whatsapp.php'; ?></td>
            <td><?= htmlspecialchars(formatearMonto((float) $fila['valor'])) ?></td>
            <td><?php include __DIR__ . '/../partials/celda-observacion.php'; ?></td>
            <td class="text-end">
              <?php
              $eliminarAccion = 'eliminar_valor_adicional';
              $eliminarId = (int) $fila['id'];
              $eliminarRedireccion = $redireccionRegistros;
              $modalEditarId = 'editar-' . $modalId;
              include __DIR__ . '/../partials/tabla-acciones-registro.php';
              ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php $pestañaPaginacion = 'registros'; include __DIR__ . '/../partials/paginacion-registros.php'; ?>
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
