<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h2 class="h4 mb-1">Avanzado</h2>
    <p class="text-muted small mb-0">Usuarios, permisos de roles y log de actividad</p>
  </div>
  <?php if ($pestaña === 'usuarios'): ?>
  <span class="badge bg-primary fs-6"><?= count($usuarios) ?> usuario(s)</span>
  <?php elseif ($pestaña === 'logs'): ?>
  <span class="badge bg-primary fs-6"><?= (int) $totalRegistrosLog ?> registro(s)</span>
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

<ul class="nav nav-tabs mb-4" role="tablist">
  <li class="nav-item" role="presentation">
    <a class="nav-link <?= $pestaña === 'usuarios' ? 'active' : '' ?>" href="avanzado.php?pestaña=usuarios" role="tab">
      <i class="bi bi-table me-1"></i>Tabla de usuarios
    </a>
  </li>
  <li class="nav-item" role="presentation">
    <a class="nav-link <?= $pestaña === 'permisos' ? 'active' : '' ?>" href="avanzado.php?pestaña=permisos" role="tab">
      <i class="bi bi-shield-check me-1"></i>Permisos de roles
    </a>
  </li>
  <li class="nav-item" role="presentation">
    <a class="nav-link <?= $pestaña === 'logs' ? 'active' : '' ?>" href="avanzado.php?pestaña=logs" role="tab">
      <i class="bi bi-journal-text me-1"></i>Log de actividad
    </a>
  </li>
</ul>

<?php if ($pestaña === 'usuarios'): ?>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center gap-2">
    <h3 class="h6 mb-0"><i class="bi bi-people me-2"></i>Usuarios registrados</h3>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
      <i class="bi bi-person-plus me-1"></i>Nuevo usuario
    </button>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-dashboard mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th class="text-center col-numero">#</th>
            <th>Usuario</th>
            <th>Nombre</th>
            <th>Rol</th>
            <th>Creado</th>
            <?php if ($puedeEliminar): ?><th class="text-end">Acciones</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($usuarios as $indice => $u): ?>
          <tr>
            <td class="text-center text-muted"><?= $indice + 1 ?></td>
            <td><?= htmlspecialchars($u['usuario']) ?></td>
            <td><?= htmlspecialchars($u['nombre'] ?? '') ?></td>
            <td>
              <span class="badge bg-secondary"><?= htmlspecialchars($etiquetasRoles[$u['rol']] ?? $u['rol']) ?></span>
            </td>
            <td class="text-muted small"><?= htmlspecialchars(formatearFechaHora($u['creado_en'])) ?></td>
            <?php if ($puedeEliminar): ?>
            <td class="text-end">
              <div class="d-inline-flex gap-1">
                <button
                  type="button"
                  class="btn btn-sm btn-outline-primary"
                  title="Cambiar contraseña"
                  data-bs-toggle="modal"
                  data-bs-target="#modalCambiarClave"
                  data-usuario-id="<?= (int) $u['id'] ?>"
                  data-usuario-nombre="<?= htmlspecialchars($u['usuario'], ENT_QUOTES) ?>"
                >
                  <i class="bi bi-key"></i>
                </button>
                <?php if ((int) $u['id'] !== (int) $usuario['id']): ?>
                <form
                  method="POST"
                  action="acciones.php"
                  class="d-inline js-form-confirmar"
                  data-confirm-title="Eliminar usuario"
                  data-confirm="¿Eliminar este usuario?"
                >
                  <input type="hidden" name="accion" value="eliminar_usuario">
                  <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                  <input type="hidden" name="redireccion" value="avanzado.php?pestaña=usuarios">
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
                <?php endif; ?>
              </div>
            </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="modalNuevoUsuario" tabindex="-1" aria-labelledby="modalNuevoUsuarioLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="avanzado.php?pestaña=usuarios">
        <input type="hidden" name="accion" value="crear_usuario">
        <div class="modal-header">
          <h5 class="modal-title" id="modalNuevoUsuarioLabel">Nuevo usuario</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label" for="nuevoUsuarioLogin">Usuario <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nuevoUsuarioLogin" name="usuario" required autocomplete="off">
          </div>
          <div class="mb-3">
            <label class="form-label" for="nuevoUsuarioNombre">Nombre</label>
            <input type="text" class="form-control" id="nuevoUsuarioNombre" name="nombre" autocomplete="off">
          </div>
          <div class="mb-3">
            <label class="form-label" for="nuevoUsuarioClave">Contraseña <span class="text-danger">*</span></label>
            <input type="password" class="form-control" id="nuevoUsuarioClave" name="clave" required minlength="6" autocomplete="new-password">
          </div>
          <div class="mb-0">
            <label class="form-label" for="nuevoUsuarioRol">Rol <span class="text-danger">*</span></label>
            <select class="form-select" id="nuevoUsuarioRol" name="rol" required>
              <?php foreach ($etiquetasRoles as $clave => $etiqueta): ?>
              <?php if ($clave === 'superadmin') { continue; } ?>
              <option value="<?= htmlspecialchars($clave) ?>"><?= htmlspecialchars($etiqueta) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Crear usuario
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalCambiarClave" tabindex="-1" aria-labelledby="modalCambiarClaveLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="acciones.php">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCambiarClaveLabel">Cambiar contraseña</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="accion" value="cambiar_clave_usuario">
          <input type="hidden" name="id" id="cambiarClaveUsuarioId" value="">
          <input type="hidden" name="redireccion" value="avanzado.php?pestaña=usuarios">

          <p class="text-muted small mb-3">
            Nueva contraseña para <strong id="cambiarClaveUsuarioNombre"></strong>
          </p>

          <div class="mb-3">
            <label class="form-label" for="cambiarClaveNueva">Nueva contraseña <span class="text-danger">*</span></label>
            <input type="password" class="form-control" id="cambiarClaveNueva" name="clave" required minlength="6" autocomplete="new-password">
          </div>

          <div class="mb-0">
            <label class="form-label" for="cambiarClaveConfirmacion">Confirmar contraseña <span class="text-danger">*</span></label>
            <input type="password" class="form-control" id="cambiarClaveConfirmacion" name="clave_confirmacion" required minlength="6" autocomplete="new-password">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-key me-1"></i>Guardar contraseña
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function () {
  var modalClave = document.getElementById('modalCambiarClave');

  if (modalClave) {
    modalClave.addEventListener('show.bs.modal', function (evento) {
      var boton = evento.relatedTarget;
      document.getElementById('cambiarClaveUsuarioId').value = boton.getAttribute('data-usuario-id') || '';
      document.getElementById('cambiarClaveUsuarioNombre').textContent = boton.getAttribute('data-usuario-nombre') || '';
      document.getElementById('cambiarClaveNueva').value = '';
      document.getElementById('cambiarClaveConfirmacion').value = '';
    });
  }

  <?php if ($error && $pestaña === 'usuarios'): ?>
  var modalNuevo = document.getElementById('modalNuevoUsuario');
  if (modalNuevo && window.bootstrap) {
    bootstrap.Modal.getOrCreateInstance(modalNuevo).show();
  }
  <?php endif; ?>
})();
</script>

<?php elseif ($pestaña === 'permisos'): ?>

<div class="card border-0 shadow-sm permisos-roles-card">
  <div class="card-header bg-white border-bottom py-3">
    <h3 class="h6 mb-0"><i class="bi bi-shield-check me-2"></i>Permisos por rol</h3>
  </div>
  <div class="card-body">
    <p class="text-muted small mb-4">
      Define qué pestañas y acciones puede usar cada rol. El superadmin siempre tiene acceso total.
    </p>

    <ul class="nav nav-pills permisos-roles-tabs mb-4 flex-wrap gap-1">
      <?php foreach ($rolesPermisos as $claveRol => $etiquetaRol): ?>
      <li class="nav-item">
        <a
          class="nav-link <?= $rolPermisosActivo === $claveRol ? 'active' : '' ?>"
          href="avanzado.php?pestaña=permisos&rol=<?= urlencode($claveRol) ?>"
        >
          <?= htmlspecialchars($etiquetaRol) ?>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>

    <form method="POST" action="acciones.php" class="permisos-roles-form">
      <input type="hidden" name="accion" value="guardar_permisos_rol">
      <input type="hidden" name="rol" value="<?= htmlspecialchars($rolPermisosActivo) ?>">
      <input type="hidden" name="redireccion" value="avanzado.php?pestaña=permisos&rol=<?= urlencode($rolPermisosActivo) ?>">

      <div class="permisos-modulos">
        <?php foreach ($catalogoPermisos as $modulo => $infoModulo):
            $permisosModulo = $infoModulo['permisos'];
            $totalModulo = count($permisosModulo);
            $activosModulo = 0;

            foreach ($permisosModulo as $detalle => $_etiquetaDetalle) {
                if (in_array(codificarPermisoDetalle($modulo, $detalle), $permisosActivosRol, true)) {
                    $activosModulo++;
                }
            }
        ?>
        <section class="permisos-modulo">
          <div class="permisos-modulo__header">
            <div class="permisos-modulo__titulo">
              <i class="bi <?= htmlspecialchars($infoModulo['icono']) ?> permisos-modulo__icono"></i>
              <div>
                <h4 class="h6 mb-0"><?= htmlspecialchars($infoModulo['etiqueta']) ?></h4>
                <span class="text-muted small">
                  <?= $activosModulo ?> de <?= $totalModulo ?> activo(s)
                </span>
              </div>
            </div>
            <?php if ($totalModulo > 1): ?>
            <button
              type="button"
              class="btn btn-sm btn-outline-secondary permisos-modulo__toggle"
              data-modulo="<?= htmlspecialchars($modulo) ?>"
            >
              Marcar todo
            </button>
            <?php endif; ?>
          </div>

          <div class="permisos-modulo__items">
            <?php foreach ($permisosModulo as $detalle => $etiquetaDetalle):
                $clavePermiso = codificarPermisoDetalle($modulo, $detalle);
                $marcado = in_array($clavePermiso, $permisosActivosRol, true);
            ?>
            <label class="permisos-modulo__item">
              <input
                class="form-check-input permisos-modulo__check"
                type="checkbox"
                name="permisos[]"
                value="<?= htmlspecialchars($clavePermiso) ?>"
                data-modulo="<?= htmlspecialchars($modulo) ?>"
                <?= $marcado ? 'checked' : '' ?>
              >
              <span><?= htmlspecialchars($etiquetaDetalle) ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endforeach; ?>
      </div>

      <div class="permisos-roles-form__footer">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-lg me-1"></i>Guardar permisos de <?= htmlspecialchars($rolesPermisos[$rolPermisosActivo] ?? $rolPermisosActivo) ?>
        </button>
      </div>
    </form>
  </div>
</div>
<script>
(function () {
  document.querySelectorAll('.permisos-modulo__toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var modulo = btn.getAttribute('data-modulo');
      var checks = document.querySelectorAll('.permisos-modulo__check[data-modulo="' + modulo + '"]');
      var todosMarcados = Array.prototype.every.call(checks, function (c) { return c.checked; });

      checks.forEach(function (check) {
        check.checked = !todosMarcados;
      });

      btn.textContent = todosMarcados ? 'Marcar todo' : 'Desmarcar todo';
    });
  });
})();
</script>

<?php elseif ($pestaña === 'logs'): ?>
<?php
require_once __DIR__ . '/../../includes/submissions.php';
require_once __DIR__ . '/../../includes/actividad_log.php';

$hayFiltrosActivos = ($filtrosLog['buscar'] ?? '') !== ''
    || ($filtrosLog['accion'] ?? '') !== ''
    || ($filtrosLog['seccion'] ?? '') !== ''
    || ($filtrosLog['fecha_desde'] ?? '') !== ''
    || ($filtrosLog['fecha_hasta'] ?? '') !== '';
$modalesDetalle = [];
?>

<div class="d-flex justify-content-end align-items-center gap-2 flex-wrap mb-3">
  <?php if ($hayFiltrosActivos && $totalRegistrosLog > 0): ?>
  <form
    method="POST"
    action="avanzado.php?pestaña=logs"
    class="d-inline js-form-confirmar"
    data-confirm-title="Limpiar log filtrado"
    data-confirm="¿Eliminar los <?= (int) $totalRegistrosLog ?> registro(s) que coinciden con los filtros actuales?"
  >
    <input type="hidden" name="accion" value="limpiar_actividad_filtrada">
    <input type="hidden" name="filtro_buscar" value="<?= htmlspecialchars($filtrosLog['buscar'] ?? '') ?>">
    <input type="hidden" name="filtro_accion" value="<?= htmlspecialchars($filtrosLog['accion'] ?? '') ?>">
    <input type="hidden" name="filtro_seccion" value="<?= htmlspecialchars($filtrosLog['seccion'] ?? '') ?>">
    <input type="hidden" name="filtro_fecha_desde" value="<?= htmlspecialchars($filtrosLog['fecha_desde'] ?? '') ?>">
    <input type="hidden" name="filtro_fecha_hasta" value="<?= htmlspecialchars($filtrosLog['fecha_hasta'] ?? '') ?>">
    <button type="submit" class="btn btn-outline-warning btn-sm">
      <i class="bi bi-funnel me-1"></i>Limpiar filtrados
    </button>
  </form>
  <?php endif; ?>
  <form
    method="POST"
    action="avanzado.php?pestaña=logs"
    class="d-inline js-form-confirmar"
    data-confirm-title="Limpiar todo el log"
    data-confirm="¿Eliminar TODOS los registros del log de actividad? Esta acción no se puede deshacer."
  >
    <input type="hidden" name="accion" value="limpiar_actividad_todo">
    <button type="submit" class="btn btn-outline-danger btn-sm" <?= $totalRegistrosLog <= 0 && !$hayFiltrosActivos ? 'disabled' : '' ?>>
      <i class="bi bi-trash me-1"></i>Limpiar todo
    </button>
  </form>
</div>

<?php if ($errorBdLog): ?>
<div class="alert alert-warning" role="alert">
  <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($errorBdLog) ?>
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
      <form method="GET" action="avanzado.php" class="row g-3 align-items-end">
        <input type="hidden" name="pestaña" value="logs">
        <div class="col-md-3">
          <label class="form-label small" for="buscar">Buscar</label>
          <input type="search" class="form-control form-control-sm" id="buscar" name="buscar" value="<?= htmlspecialchars($filtrosLog['buscar']) ?>" placeholder="Usuario, detalle…">
        </div>
        <div class="col-md-3">
          <label class="form-label small" for="accion">Acción</label>
          <select class="form-select form-select-sm" id="accion" name="accion">
            <option value="">Todas</option>
            <?php foreach ($etiquetasAcciones as $claveAccion => $etiquetaAccion): ?>
            <option value="<?= htmlspecialchars($claveAccion) ?>" <?= ($filtrosLog['accion'] ?? '') === $claveAccion ? 'selected' : '' ?>>
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
            <option value="<?= htmlspecialchars($claveSeccion) ?>" <?= ($filtrosLog['seccion'] ?? '') === $claveSeccion ? 'selected' : '' ?>>
              <?= htmlspecialchars($etiquetaSeccion) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small" for="fecha_desde">Desde</label>
          <input type="date" class="form-control form-control-sm" id="fecha_desde" name="fecha_desde" value="<?= htmlspecialchars($filtrosLog['fecha_desde']) ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label small" for="fecha_hasta">Hasta</label>
          <input type="date" class="form-control form-control-sm" id="fecha_hasta" name="fecha_hasta" value="<?= htmlspecialchars($filtrosLog['fecha_hasta']) ?>">
        </div>
        <div class="col-md-auto d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filtrar</button>
          <a href="avanzado.php?pestaña=logs" class="btn btn-outline-secondary btn-sm">Limpiar filtros</a>
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
          <?php if (empty($registrosLog)): ?>
          <tr>
            <td colspan="5" class="text-center text-muted py-5">
              <i class="bi bi-inbox display-6 d-block mb-2"></i>
              No hay actividad con los filtros seleccionados.
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($registrosLog as $fila):
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
    $filtros = $filtrosLog;
    $totalPaginas = $totalPaginasLog;
    $pestañaPaginacion = 'logs';
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
<?php endif; ?>
