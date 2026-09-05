<?php
$pestañasEstructura = $pestañasPermitidas ?? ['lideres', 'territorios', 'casas', 'importar'];
$pasosImportar = $pasosImportar ?? [];
$pasoImportar = $pasoImportar ?? 'miembros';
$resultadoImportEstructura = $resultadoImportEstructura ?? null;
$parejasParentesco = $parejasParentesco ?? [];
$parentescoPorMiembro = $parentescoPorMiembro ?? [];
$miembrosMasculinos = $miembrosMasculinos ?? [];
$miembrosFemeninos = $miembrosFemeninos ?? [];
$resumenParejas = $resumenParejas ?? [];
$conteoAsignaciones = $conteoAsignaciones ?? [];
$modalEstructura = $modalEstructura ?? null;
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="h4 mb-1">Estructura CDV</h2>
    <p class="text-muted small mb-0">Primero registra los miembros. Cuando ya existan, conéctalos por parentesco y luego asígnalos a los territorios.</p>
  </div>
</div>

<?php if ($mensaje): ?>
<div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<ul class="nav nav-tabs mb-4 flex-wrap">
  <?php if (in_array('lideres', $pestañasEstructura, true)): ?>
  <li class="nav-item">
    <a class="nav-link <?= $pestaña === 'lideres' ? 'active' : '' ?>" href="estructura.php?pestaña=lideres">
      <i class="bi bi-people me-1"></i>1. Miembros
    </a>
  </li>
  <?php endif; ?>
  <?php if (in_array('territorios', $pestañasEstructura, true)): ?>
  <li class="nav-item">
    <a class="nav-link <?= $pestaña === 'territorios' ? 'active' : '' ?>" href="estructura.php?pestaña=territorios">
      <i class="bi bi-map me-1"></i>2. Territorios
    </a>
  </li>
  <?php endif; ?>
  <?php if (in_array('casas', $pestañasEstructura, true)): ?>
  <li class="nav-item">
    <a class="nav-link <?= $pestaña === 'casas' ? 'active' : '' ?>" href="estructura.php?pestaña=casas">
      <i class="bi bi-house-heart me-1"></i>3. Casas de vida
    </a>
  </li>
  <?php endif; ?>
  <?php if (in_array('importar', $pestañasEstructura, true)): ?>
  <li class="nav-item">
    <a class="nav-link <?= $pestaña === 'importar' ? 'active' : '' ?>" href="estructura.php?pestaña=importar">
      <i class="bi bi-file-earmark-spreadsheet me-1"></i>Carga masiva
    </a>
  </li>
  <?php endif; ?>
</ul>

<?php if ($pestaña === 'lideres'): ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <p class="text-muted small mb-0">
    Paso 1: crea los miembros. Cuando haya al menos dos, conéctalos por parentesco (esposo y esposa).
    <?php if (in_array('importar', $pestañasEstructura, true)): ?>
    También puedes
    <a href="estructura.php?pestaña=importar&amp;paso=miembros">cargarlos desde Excel o CSV</a>.
    <?php endif; ?>
  </p>
  <div class="d-flex flex-wrap gap-2">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoMiembro">
      <i class="bi bi-plus-lg me-1"></i>Nuevo miembro
    </button>
    <button
      type="button"
      class="btn btn-outline-primary"
      data-bs-toggle="modal"
      data-bs-target="#modalParentesco"
      <?= (count($miembrosMasculinos) < 1 || count($miembrosFemeninos) < 1) ? 'disabled' : '' ?>
    >
      <i class="bi bi-people me-1"></i>Conectar parentesco
    </button>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover table-dashboard mb-0 align-middle">
      <thead class="table-light">
        <tr><th>#</th><th>Nombre</th><th>Género</th><th>Cédula</th><th>Parentesco</th><th>Territorios</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($lideres as $l): ?>
        <?php
        $conteo = $conteoAsignaciones[(int) $l['id']] ?? ['coordinador' => 0, 'encargado' => 0];
        $parentesco = $parentescoPorMiembro[(int) $l['id']] ?? null;
        ?>
        <tr>
          <td class="text-muted"><?= (int) $l['id'] ?></td>
          <td>
            <?= htmlspecialchars($l['nombre'] . ' ' . $l['apellido']) ?>
            <?php if (!empty($l['celular'])): ?>
            <div class="text-muted small"><?= htmlspecialchars((string) $l['celular']) ?></div>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars(etiquetaGeneroMiembro($l['genero'] ?? '')) ?></td>
          <td><?= htmlspecialchars(($l['cedula'] ?? '') !== '' ? (string) $l['cedula'] : '—') ?></td>
          <td class="small">
            <?php if ($parentesco): ?>
            <?= htmlspecialchars(etiquetaParentescoMiembro((string) $parentesco['parentesco'])) ?>
            de <?= htmlspecialchars(trim($parentesco['pariente_nombre'] . ' ' . $parentesco['pariente_apellido'])) ?>
            <form method="POST" action="estructura.php?pestaña=lideres" class="d-inline">
              <input type="hidden" name="accion" value="eliminar_parentesco">
              <input type="hidden" name="miembro_id" value="<?= (int) $parentesco['miembro_id'] ?>">
              <input type="hidden" name="pariente_id" value="<?= (int) $parentesco['pariente_id'] ?>">
              <button type="submit" class="btn btn-link btn-sm p-0 ms-1">Quitar</button>
            </form>
            <?php else: ?>
            <span class="text-muted">Sin conectar</span>
            <?php endif; ?>
          </td>
          <td class="small">
            <?php if ((int) $conteo['coordinador'] > 0): ?>
            <div>Coord. <?= (int) $conteo['coordinador'] ?></div>
            <?php endif; ?>
            <?php if ((int) $conteo['encargado'] > 0): ?>
            <div>Enc. <?= (int) $conteo['encargado'] ?></div>
            <?php endif; ?>
            <?php if ((int) $conteo['coordinador'] === 0 && (int) $conteo['encargado'] === 0): ?>
            <span class="text-muted">Sin asignar</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($puedeEliminar): ?>
            <form
              method="POST"
              action="acciones.php"
              class="d-inline js-form-confirmar"
              data-confirm-title="Eliminar miembro"
              data-confirm="¿Eliminar miembro?"
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
            <tr><td colspan="7" class="text-center text-muted py-4">No hay miembros registrados.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalNuevoMiembro" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="estructura.php?pestaña=lideres">
        <div class="modal-header">
          <h5 class="modal-title">Nuevo miembro</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="accion" value="crear_lider">
          <div class="mb-3">
            <label class="form-label">Nombres <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nombre" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Apellidos <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="apellido" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Género <span class="text-danger">*</span></label>
            <select class="form-select" name="genero" required>
              <option value="">Seleccione…</option>
              <option value="masculino">Masculino</option>
              <option value="femenino">Femenino</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Cédula</label>
            <input type="text" class="form-control" name="cedula">
          </div>
          <div class="mb-3">
            <label class="form-label">Celular</label>
            <input type="tel" class="form-control" name="celular">
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email">
          </div>
          <div class="mb-0">
            <label class="form-label">Notas</label>
            <textarea class="form-control" name="notas" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Crear miembro</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalParentesco" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="estructura.php?pestaña=lideres">
        <div class="modal-header">
          <h5 class="modal-title">Conectar parentesco</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="accion" value="conectar_parentesco">
          <input type="hidden" name="parentesco" value="esposo">
          <p class="text-muted small">Conecta un miembro masculino y uno femenino ya registrados.</p>
          <div class="mb-3">
            <label class="form-label">Esposo</label>
            <select class="form-select" name="miembro_id" required>
              <option value="">Seleccione…</option>
              <?php foreach ($miembrosMasculinos as $m): ?>
              <option value="<?= (int) $m['id'] ?>"><?= htmlspecialchars($m['nombre'] . ' ' . $m['apellido']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-0">
            <label class="form-label">Esposa</label>
            <select class="form-select" name="pariente_id" required>
              <option value="">Seleccione…</option>
              <?php foreach ($miembrosFemeninos as $m): ?>
              <option value="<?= (int) $m['id'] ?>"><?= htmlspecialchars($m['nombre'] . ' ' . $m['apellido']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Conectar</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php if ($modalEstructura === 'miembro' || $modalEstructura === 'parentesco'): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var id = <?= json_encode($modalEstructura === 'parentesco' ? 'modalParentesco' : 'modalNuevoMiembro') ?>;
  var el = document.getElementById(id);
  if (el && window.bootstrap) {
    window.bootstrap.Modal.getOrCreateInstance(el).show();
  }
});
</script>
<?php endif; ?>
<?php endif; ?>

<?php if ($pestaña === 'territorios'): ?>
<div class="alert alert-light border small mb-4">
  <i class="bi bi-lightbulb me-1 text-primary"></i>
  Paso 2: crea los territorios y asigna una pareja ya conectada por parentesco como coordinadores o encargados.
  Un coordinador puede tener varios territorios; un encargado puede tener uno o más.
  <?php if (in_array('importar', $pestañasEstructura, true)): ?>
  También puedes
  <a href="estructura.php?pestaña=importar&amp;paso=asignaciones">cargarlo desde Excel o CSV</a>.
  <?php endif; ?>
</div>
<div class="row g-4">
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm mb-4">
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
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3"><h3 class="h6 mb-0">Asignar pareja a territorios</h3></div>
      <div class="card-body">
        <?php if (empty($parejasParentesco) || empty($territorios)): ?>
        <p class="text-muted small mb-2">Para asignar necesitas territorios y al menos una pareja conectada por parentesco.</p>
        <div class="d-flex flex-wrap gap-2">
          <?php if (empty($parejasParentesco) && in_array('lideres', $pestañasEstructura, true)): ?>
          <a class="btn btn-sm btn-outline-primary" href="estructura.php?pestaña=lideres">Ir a miembros</a>
          <?php endif; ?>
        </div>
        <?php else: ?>
        <form method="POST" action="estructura.php?pestaña=territorios">
          <input type="hidden" name="accion" value="asignar_pareja_territorio">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Rol</label>
              <select class="form-select" name="rol" required>
                <option value="coordinador">Coordinador</option>
                <option value="encargado">Encargado</option>
              </select>
            </div>
            <div class="col-md-8">
              <label class="form-label">Pareja (parentesco)</label>
              <select class="form-select" name="pareja_clave" required>
                <option value="">Seleccione…</option>
                <?php foreach ($parejasParentesco as $pareja): ?>
                <option value="<?= htmlspecialchars((string) $pareja['clave']) ?>">
                  <?= htmlspecialchars(nombreCompletoLider($pareja['esposo'])) ?>
                  ·
                  <?= htmlspecialchars(nombreCompletoLider($pareja['esposa'])) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="mt-3">
            <label class="form-label">Territorios</label>
            <div class="cdv-territorios-check border rounded p-3">
              <?php foreach ($territorios as $t): ?>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="territorio_ids[]" value="<?= (int) $t['id'] ?>" id="territorio_asig_<?= (int) $t['id'] ?>">
                <label class="form-check-label" for="territorio_asig_<?= (int) $t['id'] ?>"><?= htmlspecialchars((string) $t['nombre']) ?></label>
              </div>
              <?php endforeach; ?>
            </div>
            <div class="form-text">Marca todos los territorios de esta pareja. Un coordinador puede tener 6; un encargado, 1 o los que correspondan.</div>
          </div>
          <button type="submit" class="btn btn-primary mt-3">Guardar asignación</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if ($resumenParejas !== []): ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white py-3"><h3 class="h6 mb-0">Parejas y cobertura</h3></div>
  <div class="card-body p-0">
    <table class="table table-hover table-dashboard mb-0 align-middle">
      <thead class="table-light">
        <tr><th>Rol</th><th>Esposo</th><th>Esposa</th><th>Territorios</th><th>Total</th></tr>
      </thead>
      <tbody>
        <?php foreach ($resumenParejas as $parejaResumen): ?>
        <tr>
          <td><?= htmlspecialchars(etiquetaRolTerritorio((string) $parejaResumen['rol'])) ?></td>
          <td><?= htmlspecialchars($parejaResumen['esposo'] ? nombreCompletoLider(['nombre' => $parejaResumen['esposo']['miembro_nombre'], 'apellido' => $parejaResumen['esposo']['miembro_apellido']]) : '—') ?></td>
          <td><?= htmlspecialchars($parejaResumen['esposa'] ? nombreCompletoLider(['nombre' => $parejaResumen['esposa']['miembro_nombre'], 'apellido' => $parejaResumen['esposa']['miembro_apellido']]) : '—') ?></td>
          <td class="small"><?= htmlspecialchars(implode(', ', array_map(static fn (array $territorio): string => (string) $territorio['nombre'], $parejaResumen['territorios']))) ?></td>
          <td><span class="badge bg-primary"><?= (int) $parejaResumen['total'] ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover table-dashboard mb-0 align-middle">
      <thead class="table-light">
        <tr><th>#</th><th>Territorio</th><th>Coordinadores</th><th>Encargados</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($territorios as $t): ?>
        <?php
        $coord = $t['asignaciones']['coordinador'] ?? ['esposo' => null, 'esposa' => null];
        $enc = $t['asignaciones']['encargado'] ?? ['esposo' => null, 'esposa' => null];
        ?>
        <tr>
          <td class="text-muted"><?= (int) $t['id'] ?></td>
          <td>
            <form method="POST" action="estructura.php?pestaña=territorios" class="d-flex gap-2">
              <input type="hidden" name="accion" value="actualizar_territorio">
              <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
              <input type="text" class="form-control form-control-sm" name="nombre" value="<?= htmlspecialchars((string) $t['nombre']) ?>" required>
              <button type="submit" class="btn btn-sm btn-outline-primary">Guardar</button>
            </form>
          </td>
          <td class="small">
            <?= htmlspecialchars(nombreParejaAsignada($coord['esposo'] ?? null, $coord['esposa'] ?? null)) ?>
            <?php if (($coord['esposo'] ?? null) || ($coord['esposa'] ?? null)): ?>
            <form method="POST" action="estructura.php?pestaña=territorios" class="mt-1">
              <input type="hidden" name="accion" value="quitar_asignacion_territorio">
              <input type="hidden" name="territorio_id" value="<?= (int) $t['id'] ?>">
              <input type="hidden" name="rol" value="coordinador">
              <button type="submit" class="btn btn-link btn-sm p-0">Quitar</button>
            </form>
            <?php endif; ?>
          </td>
          <td class="small">
            <?= htmlspecialchars(nombreParejaAsignada($enc['esposo'] ?? null, $enc['esposa'] ?? null)) ?>
            <?php if (($enc['esposo'] ?? null) || ($enc['esposa'] ?? null)): ?>
            <form method="POST" action="estructura.php?pestaña=territorios" class="mt-1">
              <input type="hidden" name="accion" value="quitar_asignacion_territorio">
              <input type="hidden" name="territorio_id" value="<?= (int) $t['id'] ?>">
              <input type="hidden" name="rol" value="encargado">
              <button type="submit" class="btn btn-link btn-sm p-0">Quitar</button>
            </form>
            <?php endif; ?>
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
        <tr><td colspan="5" class="text-center text-muted py-4">No hay territorios registrados.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php if ($pestaña === 'casas'): ?>
<div class="alert alert-light border small mb-4">
  <i class="bi bi-lightbulb me-1 text-primary"></i>
  Paso 3: asigna cada casa de vida a un territorio y a un miembro líder.
  <?php if (in_array('importar', $pestañasEstructura, true)): ?>
  Si ya cargaste miembros y territorios, puedes
  <a href="estructura.php?pestaña=importar&amp;paso=casas">importar las casas desde Excel o CSV</a>.
  <?php endif; ?>
</div>
<div class="row g-4">
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3"><h3 class="h6 mb-0">Nueva casa de vida</h3></div>
      <div class="card-body">
        <?php if (empty($territorios) || empty($lideres)): ?>
        <p class="text-muted small mb-2">Primero crea al menos un miembro y un territorio.</p>
        <div class="d-flex flex-wrap gap-2">
          <?php if (empty($lideres) && in_array('lideres', $pestañasEstructura, true)): ?>
          <a class="btn btn-sm btn-outline-primary" href="estructura.php?pestaña=lideres">Ir a miembros</a>
          <?php endif; ?>
          <?php if (empty($territorios) && in_array('territorios', $pestañasEstructura, true)): ?>
          <a class="btn btn-sm btn-outline-primary" href="estructura.php?pestaña=territorios">Ir a territorios</a>
          <?php endif; ?>
        </div>
        <?php else: ?>
        <form method="POST" action="estructura.php?pestaña=casas">
          <input type="hidden" name="accion" value="crear_casa">
          <div class="mb-2">
            <label class="form-label">Territorio</label>
            <select class="form-select" name="territorio_id" required>
              <?php foreach ($territorios as $t): ?>
              <option value="<?= (int) $t['id'] ?>"><?= htmlspecialchars((string) $t['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Miembro (líder)</label>
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

<?php if ($pestaña === 'importar'): ?>
<?php include __DIR__ . '/pestaña-importar.php'; ?>
<?php endif; ?>
