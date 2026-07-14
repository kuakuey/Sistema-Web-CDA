<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="h4 mb-1">Estructura CDV</h2>
    <p class="text-muted small mb-0">Territorios, casas de vida y líderes para el formulario de ofrendas</p>
  </div>
</div>

<?php if ($mensaje): ?>
<div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<ul class="nav nav-tabs mb-4 flex-wrap">
  <li class="nav-item">
    <a class="nav-link <?= $pestaña === 'territorios' ? 'active' : '' ?>" href="estructura.php?pestaña=territorios">
      <i class="bi bi-map me-1"></i>1. Territorios
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $pestaña === 'casas' ? 'active' : '' ?>" href="estructura.php?pestaña=casas">
      <i class="bi bi-house-heart me-1"></i>2. Casas de vida
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $pestaña === 'lideres' ? 'active' : '' ?>" href="estructura.php?pestaña=lideres">
      <i class="bi bi-person-badge me-1"></i>3. Líderes
    </a>
  </li>
</ul>

<?php if ($pestaña === 'territorios'): ?>
<div class="row g-4">
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3"><h3 class="h6 mb-0">Nuevo territorio</h3></div>
      <div class="card-body">
        <form method="POST" action="estructura.php?pestaña=territorios">
          <input type="hidden" name="accion" value="crear_territorio">
          <div class="mb-3">
            <label class="form-label" for="nombre_territorio">Nombre</label>
            <input type="text" class="form-control" id="nombre_territorio" name="nombre" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Crear territorio</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <table class="table table-hover table-dashboard mb-0 align-middle">
          <thead class="table-light"><tr><th>#</th><th>Nombre</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($territorios as $t): ?>
            <tr>
              <td class="text-muted"><?= (int) $t['id'] ?></td>
              <td>
                <form method="POST" action="estructura.php?pestaña=territorios" class="d-flex gap-2">
                  <input type="hidden" name="accion" value="actualizar_territorio">
                  <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                  <input type="text" class="form-control form-control-sm" name="nombre" value="<?= htmlspecialchars($t['nombre']) ?>" required>
                  <button type="submit" class="btn btn-sm btn-outline-primary">Guardar</button>
                </form>
              </td>
              <td>
                <?php if ($puedeEliminar): ?>
                <form
                  method="POST"
                  action="acciones.php"
                  class="d-inline js-form-confirmar"
                  data-confirm-title="Eliminar territorio"
                  data-confirm="¿Eliminar territorio?"
                >
                  <input type="hidden" name="accion" value="eliminar_territorio">
                  <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                  <input type="hidden" name="redireccion" value="estructura.php?pestaña=territorios">
                  <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($territorios)): ?>
            <tr><td colspan="3" class="text-center text-muted py-4">No hay territorios registrados.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($pestaña === 'lideres'): ?>
<div class="row g-4">
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3"><h3 class="h6 mb-0">Nuevo líder</h3></div>
      <div class="card-body">
        <form method="POST" action="estructura.php?pestaña=lideres">
          <input type="hidden" name="accion" value="crear_lider">
          <div class="mb-2">
            <label class="form-label">Nombres</label>
            <input type="text" class="form-control" name="nombre" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Apellidos</label>
            <input type="text" class="form-control" name="apellido" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Cédula</label>
            <input type="text" class="form-control" name="cedula">
          </div>
          <div class="mb-2">
            <label class="form-label">Celular</label>
            <input type="tel" class="form-control" name="celular">
          </div>
          <div class="mb-2">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email">
          </div>
          <div class="mb-3">
            <label class="form-label">Notas</label>
            <textarea class="form-control" name="notas" rows="2"></textarea>
          </div>
          <button type="submit" class="btn btn-primary w-100">Crear líder</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <table class="table table-hover table-dashboard mb-0 align-middle">
          <thead class="table-light">
            <tr><th>#</th><th>Nombre</th><th>Celular</th><th>Email</th><th></th></tr>
          </thead>
          <tbody>
            <?php foreach ($lideres as $l): ?>
            <tr>
              <td class="text-muted"><?= (int) $l['id'] ?></td>
              <td><?= htmlspecialchars($l['nombre'] . ' ' . $l['apellido']) ?></td>
              <td><?= htmlspecialchars($l['celular'] ?? '—') ?></td>
              <td><?= htmlspecialchars($l['email'] ?? '—') ?></td>
              <td>
                <?php if ($puedeEliminar): ?>
                <form
                  method="POST"
                  action="acciones.php"
                  class="d-inline js-form-confirmar"
                  data-confirm-title="Eliminar líder"
                  data-confirm="¿Eliminar líder?"
                >
                  <input type="hidden" name="accion" value="eliminar_lider">
                  <input type="hidden" name="id" value="<?= (int) $l['id'] ?>">
                  <input type="hidden" name="redireccion" value="estructura.php?pestaña=lideres">
                  <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($lideres)): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">No hay líderes registrados.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($pestaña === 'casas'): ?>
<div class="row g-4">
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3"><h3 class="h6 mb-0">Nueva casa de vida</h3></div>
      <div class="card-body">
        <?php if (empty($territorios) || empty($lideres)): ?>
        <p class="text-muted small">Primero crea al menos un territorio y un líder.</p>
        <?php else: ?>
        <form method="POST" action="estructura.php?pestaña=casas">
          <input type="hidden" name="accion" value="crear_casa">
          <div class="mb-2">
            <label class="form-label">Territorio</label>
            <select class="form-select" name="territorio_id" required>
              <?php foreach ($territorios as $t): ?>
              <option value="<?= (int) $t['id'] ?>"><?= htmlspecialchars($t['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Líder</label>
            <select class="form-select" name="lider_id" required>
              <?php foreach ($lideres as $l): ?>
              <option value="<?= (int) $l['id'] ?>"><?= htmlspecialchars($l['nombre'] . ' ' . $l['apellido']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Nombre casa</label>
            <input type="text" class="form-control" name="nombre" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Dirección</label>
            <input type="text" class="form-control" name="direccion" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Crear casa</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <table class="table table-hover table-dashboard mb-0 align-middle">
          <thead class="table-light">
            <tr><th>#</th><th>Casa</th><th>Territorio</th><th>Líder</th><th>Dirección</th><th></th></tr>
          </thead>
          <tbody>
            <?php foreach ($casas as $c): ?>
            <tr>
              <td class="text-muted"><?= (int) $c['id'] ?></td>
              <td><?= htmlspecialchars($c['nombre']) ?></td>
              <td><?= htmlspecialchars($c['territorio_nombre']) ?></td>
              <td><?= htmlspecialchars($c['lider_nombre'] . ' ' . $c['lider_apellido']) ?></td>
              <td class="text-truncate-cell"><?= htmlspecialchars($c['direccion']) ?></td>
              <td>
                <?php if ($puedeEliminar): ?>
                <form
                  method="POST"
                  action="acciones.php"
                  class="d-inline js-form-confirmar"
                  data-confirm-title="Eliminar casa"
                  data-confirm="¿Eliminar casa?"
                >
                  <input type="hidden" name="accion" value="eliminar_casa">
                  <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                  <input type="hidden" name="redireccion" value="estructura.php?pestaña=casas">
                  <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($casas)): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No hay casas de vida registradas.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
