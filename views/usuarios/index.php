<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h2 class="h4 mb-1">Usuarios del sistema</h2>
    <p class="text-muted small mb-0">Gestiona usuarios, registros y permisos por rol</p>
  </div>
  <span class="badge bg-primary fs-6"><?= count($usuarios) ?> usuario(s)</span>
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
    <a
      class="nav-link <?= $pestaña === 'lista' ? 'active' : '' ?>"
      href="usuarios.php?pestaña=lista"
      role="tab"
    >
      <i class="bi bi-table me-1"></i>Tabla de usuarios
    </a>
  </li>
  <li class="nav-item" role="presentation">
    <a
      class="nav-link <?= $pestaña === 'registrar' ? 'active' : '' ?>"
      href="usuarios.php?pestaña=registrar"
      role="tab"
    >
      <i class="bi bi-person-plus me-1"></i>Registrar usuarios
    </a>
  </li>
  <li class="nav-item" role="presentation">
    <a
      class="nav-link <?= $pestaña === 'permisos' ? 'active' : '' ?>"
      href="usuarios.php?pestaña=permisos"
      role="tab"
    >
      <i class="bi bi-shield-check me-1"></i>Permiso de roles
    </a>
  </li>
</ul>

<?php if ($pestaña === 'lista'): ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-bottom py-3">
    <h3 class="h6 mb-0"><i class="bi bi-people me-2"></i>Usuarios registrados</h3>
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
            <td><strong><?= htmlspecialchars($u['usuario']) ?></strong></td>
            <td><?= htmlspecialchars($u['nombre'] ?? '') ?></td>
            <td>
              <span class="badge bg-secondary"><?= htmlspecialchars($etiquetasRoles[$u['rol']] ?? $u['rol']) ?></span>
            </td>
            <td class="text-muted small"><?= htmlspecialchars(formatearFechaHora($u['creado_en'])) ?></td>
            <?php if ($puedeEliminar): ?>
            <td class="text-end">
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
                <input type="hidden" name="redireccion" value="usuarios.php?pestaña=lista">
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
              <?php endif; ?>
            </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php elseif ($pestaña === 'registrar'): ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-bottom py-3">
    <h3 class="h6 mb-0"><i class="bi bi-person-plus me-2"></i>Nuevo usuario</h3>
  </div>
  <div class="card-body">
    <form method="POST" action="usuarios.php?pestaña=registrar" class="row g-3 js-form-registro" id="formRegistroUsuario" data-mensaje-exito="Usuario creado correctamente.">
      <input type="hidden" name="accion" value="crear_usuario">
      <div class="col-md-6 col-lg-4">
        <label class="form-label" for="usuario">Usuario <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="usuario" name="usuario" required>
      </div>
      <div class="col-md-6 col-lg-4">
        <label class="form-label" for="nombre">Nombre</label>
        <input type="text" class="form-control" id="nombre" name="nombre">
      </div>
      <div class="col-md-6 col-lg-4">
        <label class="form-label" for="clave">Contraseña <span class="text-danger">*</span></label>
        <input type="password" class="form-control" id="clave" name="clave" required minlength="6">
      </div>
      <div class="col-md-6 col-lg-4">
        <label class="form-label" for="rol">Rol <span class="text-danger">*</span></label>
        <select class="form-select" id="rol" name="rol" required>
          <?php foreach ($etiquetasRoles as $clave => $etiqueta): ?>
          <?php if ($clave === 'superadmin') { continue; } ?>
          <option value="<?= htmlspecialchars($clave) ?>"><?= htmlspecialchars($etiqueta) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-plus-lg me-1"></i>Crear usuario
        </button>
      </div>
    </form>
  </div>
</div>

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
          href="usuarios.php?pestaña=permisos&rol=<?= urlencode($claveRol) ?>"
        >
          <?= htmlspecialchars($etiquetaRol) ?>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>

    <form method="POST" action="acciones.php" class="permisos-roles-form">
      <input type="hidden" name="accion" value="guardar_permisos_rol">
      <input type="hidden" name="rol" value="<?= htmlspecialchars($rolPermisosActivo) ?>">
      <input type="hidden" name="redireccion" value="usuarios.php?pestaña=permisos&rol=<?= urlencode($rolPermisosActivo) ?>">

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
<?php endif; ?>
