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
$territorioEdicion = $territorioEdicion ?? null;
$miembroDetalle = $miembroDetalle ?? null;
$datosMiembro = $datosMiembro ?? null;
$asignacionesMiembro = $asignacionesMiembro ?? [];
$casasMiembro = $casasMiembro ?? [];
$lideresPagina = $lideresPagina ?? $lideres ?? [];
$totalMiembros = $totalMiembros ?? count($lideres ?? []);
$totalMiembrosRegistrados = $totalMiembrosRegistrados ?? $totalMiembros;
$paginaMiembros = $paginaMiembros ?? 1;
$totalPaginasMiembros = $totalPaginasMiembros ?? 1;
$buscarEstructura = $buscarEstructura ?? '';
$casasPagina = $casasPagina ?? $casas ?? [];
$totalCasas = $totalCasas ?? count($casas ?? []);
$totalCasasRegistradas = $totalCasasRegistradas ?? $totalCasas;
$paginaCasas = $paginaCasas ?? 1;
$totalPaginasCasas = $totalPaginasCasas ?? 1;
$filtrosListaEstructura = $buscarEstructura !== '' ? ['buscar' => $buscarEstructura] : [];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="h4 mb-1">Estructura CDV</h2>
    <p class="text-muted small mb-0">Primero registra los miembros. Luego crea territorios y asígnales coordinadores y encargados.</p>
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

<?php if ($pestaña === 'lideres' && $miembroDetalle): ?>
<?php include __DIR__ . '/pestaña-miembro.php'; ?>
<?php elseif ($pestaña === 'lideres'): ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div class="d-flex flex-wrap gap-2">
    <?php if ($totalMiembrosRegistrados > 0): ?>
    <a class="btn btn-outline-success" href="estructura.php?pestaña=lideres&amp;descargar=miembros">
      <i class="bi bi-download me-1"></i>Exportar miembros
    </a>
    <?php endif; ?>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoMiembro">
      <i class="bi bi-plus-lg me-1"></i>Nuevo miembro
    </button>
  </div>
</div>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" action="estructura.php" class="row g-2 align-items-end">
      <input type="hidden" name="pestaña" value="lideres">
      <div class="col-md-6 col-lg-4">
        <label class="form-label small mb-1" for="buscar-miembros">Buscar</label>
        <input
          type="search"
          class="form-control form-control-sm"
          id="buscar-miembros"
          name="buscar"
          value="<?= htmlspecialchars((string) $buscarEstructura) ?>"
          placeholder="Nombre, cédula, celular…"
        >
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-outline-primary">Buscar</button>
        <?php if ($buscarEstructura !== ''): ?>
        <a class="btn btn-sm btn-outline-secondary" href="estructura.php?pestaña=lideres">Limpiar</a>
        <?php endif; ?>
      </div>
    </form>
    <p class="mb-0 mt-3">
      <?php if ($buscarEstructura !== ''): ?>
      <strong><?= (int) $totalMiembros ?></strong> resultado(s) de <?= (int) $totalMiembrosRegistrados ?> miembro(s)
      para «<?= htmlspecialchars((string) $buscarEstructura) ?>».
      <?php else: ?>
      Hay <strong><?= (int) $totalMiembrosRegistrados ?></strong> miembro(s) registrado(s). Se muestran 20 por página.
      <?php endif; ?>
    </p>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-dashboard mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th style="width: 70%">Nombre</th>
            <th style="width: 15%">Celular</th>
            <th style="width: 15%">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($lideresPagina as $l): ?>
          <tr>
            <td><?= htmlspecialchars($l['nombre'] . ' ' . $l['apellido']) ?></td>
            <td><?= htmlspecialchars(($l['celular'] ?? '') !== '' ? (string) $l['celular'] : '—') ?></td>
            <td class="text-nowrap">
              <a
                class="btn btn-sm btn-outline-primary"
                href="<?= htmlspecialchars(urlFichaMiembro((int) $l['id'], (int) $paginaMiembros, (string) $buscarEstructura)) ?>"
                title="Ver"
              >
                <i class="bi bi-eye"></i>
              </a>
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
                <input type="hidden" name="redireccion" value="<?= htmlspecialchars(construirUrlRegistros('estructura.php', $filtrosListaEstructura, (int) $paginaMiembros, 'lideres')) ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($lideresPagina)): ?>
          <tr>
            <td colspan="3" class="text-center text-muted py-4">
              <?= $buscarEstructura !== '' ? 'No se encontraron miembros para esa búsqueda.' : 'No hay miembros registrados.' ?>
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php
    $paginaActual = (int) $paginaMiembros;
    $totalPaginas = (int) $totalPaginasMiembros;
    $totalRegistros = (int) $totalMiembros;
    $archivoPagina = 'estructura.php';
    $filtros = $filtrosListaEstructura;
    $pestañaPaginacion = 'lideres';
    include __DIR__ . '/../partials/paginacion-registros.php';
    ?>
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

<?php if ($modalEstructura === 'miembro'): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var el = document.getElementById('modalNuevoMiembro');
  if (el && window.bootstrap) {
    window.bootstrap.Modal.getOrCreateInstance(el).show();
  }
});
</script>
<?php endif; ?>
<?php endif; ?>

<?php if ($pestaña === 'territorios'): ?>
<?php
$miembrosBusqueda = [];
foreach ($lideres as $miembro) {
    $miembrosBusqueda[] = [
        'id'     => (int) $miembro['id'],
        'nombre' => trim((string) $miembro['nombre'] . ' ' . (string) $miembro['apellido']),
        'cedula' => (string) ($miembro['cedula'] ?? ''),
    ];
}
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <p class="text-muted small mb-0">
    Paso 2: crea territorios y, al editar, asigna uno o más coordinadores y encargados.
    <?php if (in_array('importar', $pestañasEstructura, true)): ?>
    También puedes
    <a href="estructura.php?pestaña=importar&amp;paso=asignaciones">cargarlo desde Excel o CSV</a>.
    <?php endif; ?>
  </p>
  <div class="d-flex flex-wrap gap-2">
    <?php if (!empty($territorios)): ?>
    <a class="btn btn-outline-success" href="estructura.php?pestaña=territorios&amp;descargar=territorios">
      <i class="bi bi-download me-1"></i>Exportar territorios
    </a>
    <?php endif; ?>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoTerritorio">
      <i class="bi bi-plus-lg me-1"></i>Nuevo territorio
    </button>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-dashboard mb-0 align-middle">
        <thead class="table-light">
          <tr><th>#</th><th>Territorio</th><th>Coordinadores</th><th>Encargados</th><th>Acciones</th></tr>
        </thead>
        <tbody>
          <?php foreach ($territorios as $t): ?>
          <?php
          $coord = array_values($t['asignaciones']['coordinador'] ?? []);
          $enc = array_values($t['asignaciones']['encargado'] ?? []);
          ?>
          <tr>
            <td class="text-muted"><?= (int) $t['id'] ?></td>
            <td><?= htmlspecialchars((string) $t['nombre']) ?></td>
            <td class="small">
              <?php if ($coord === []): ?>
              <span class="text-muted">Sin asignar</span>
              <?php else: ?>
                <?php foreach ($coord as $asignado): ?>
                <div><?= htmlspecialchars(nombreCompletoLider([
                    'nombre'   => $asignado['miembro_nombre'] ?? '',
                    'apellido' => $asignado['miembro_apellido'] ?? '',
                ])) ?></div>
                <?php endforeach; ?>
              <?php endif; ?>
            </td>
            <td class="small">
              <?php if ($enc === []): ?>
              <span class="text-muted">Sin asignar</span>
              <?php else: ?>
                <?php foreach ($enc as $asignado): ?>
                <div><?= htmlspecialchars(nombreCompletoLider([
                    'nombre'   => $asignado['miembro_nombre'] ?? '',
                    'apellido' => $asignado['miembro_apellido'] ?? '',
                ])) ?></div>
                <?php endforeach; ?>
              <?php endif; ?>
            </td>
            <td class="text-nowrap">
              <button
                type="button"
                class="btn btn-sm btn-outline-primary"
                data-bs-toggle="modal"
                data-bs-target="#modalEditarTerritorio"
                data-id="<?= (int) $t['id'] ?>"
                data-nombre="<?= htmlspecialchars((string) $t['nombre'], ENT_QUOTES) ?>"
                data-coordinadores="<?= htmlspecialchars(implode(',', idsMiembrosAsignados($coord)), ENT_QUOTES) ?>"
                data-encargados="<?= htmlspecialchars(implode(',', idsMiembrosAsignados($enc)), ENT_QUOTES) ?>"
                title="Editar"
              >
                <i class="bi bi-pencil"></i>
              </button>
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
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
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
</div>

<div class="modal fade" id="modalNuevoTerritorio" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="estructura.php?pestaña=territorios">
        <div class="modal-header">
          <h5 class="modal-title">Nuevo territorio</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="accion" value="crear_territorio">
          <label class="form-label" for="nombre_territorio">Nombre</label>
          <input type="text" class="form-control" id="nombre_territorio" name="nombre" required>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Crear territorio</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalEditarTerritorio" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST" action="estructura.php?pestaña=territorios">
        <div class="modal-header">
          <h5 class="modal-title">Editar territorio</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="accion" value="guardar_territorio">
          <input type="hidden" name="id" id="territorioEditarId" value="">
          <div class="mb-3">
            <label class="form-label" for="territorioEditarNombre">Nombre</label>
            <input type="text" class="form-control" id="territorioEditarNombre" name="nombre" required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="territorioBuscarMiembro">Buscar miembro</label>
            <input type="search" class="form-control" id="territorioBuscarMiembro" placeholder="Nombre o cédula" autocomplete="off">
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Coordinadores</label>
              <div id="territorioChipsCoord" class="cdv-miembro-chips"></div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Encargados</label>
              <div id="territorioChipsEnc" class="cdv-miembro-chips"></div>
            </div>
          </div>
          <div class="cdv-miembros-picker border rounded" id="territorioListaMiembros"></div>
          <div id="territorioInputsCoord"></div>
          <div id="territorioInputsEnc"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var miembros = <?= json_encode($miembrosBusqueda, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
  var edicion = <?= json_encode($territorioEdicion, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
  var modalNuevo = document.getElementById('modalNuevoTerritorio');
  var modal = document.getElementById('modalEditarTerritorio');
  var campoId = document.getElementById('territorioEditarId');
  var campoNombre = document.getElementById('territorioEditarNombre');
  var campoBuscar = document.getElementById('territorioBuscarMiembro');
  var lista = document.getElementById('territorioListaMiembros');
  var chipsCoord = document.getElementById('territorioChipsCoord');
  var chipsEnc = document.getElementById('territorioChipsEnc');
  var inputsCoord = document.getElementById('territorioInputsCoord');
  var inputsEnc = document.getElementById('territorioInputsEnc');
  var seleccion = { coordinador: [], encargado: [] };

  function idsDeTexto(valor) {
    return String(valor || '').split(',').map(function (id) { return parseInt(id, 10); }).filter(function (id) { return id > 0; });
  }

  function miembroPorId(id) {
    return miembros.filter(function (m) { return m.id === id; })[0] || null;
  }

  function renderChips(contenedor, rol) {
    contenedor.innerHTML = '';
    if (!seleccion[rol].length) {
      contenedor.innerHTML = '<span class="text-muted small">Ninguno</span>';
      return;
    }
    seleccion[rol].forEach(function (id) {
      var miembro = miembroPorId(id);
      var chip = document.createElement('span');
      chip.className = 'badge rounded-pill text-bg-light border cdv-miembro-chip';
      chip.textContent = miembro ? miembro.nombre : ('#' + id);
      var quitar = document.createElement('button');
      quitar.type = 'button';
      quitar.className = 'btn-close btn-close-sm ms-1';
      quitar.setAttribute('aria-label', 'Quitar');
      quitar.addEventListener('click', function () { quitarMiembro(rol, id); });
      chip.appendChild(quitar);
      contenedor.appendChild(chip);
    });
  }

  function renderInputs(contenedor, name, rol) {
    contenedor.innerHTML = '';
    seleccion[rol].forEach(function (id) {
      var input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      input.value = String(id);
      contenedor.appendChild(input);
    });
  }

  function coincide(miembro, q) {
    if (!q) return true;
    var hay = (miembro.nombre + ' ' + miembro.cedula).toLowerCase();
    return hay.indexOf(q) !== -1;
  }

  function renderLista() {
    var q = (campoBuscar.value || '').trim().toLowerCase();
    lista.innerHTML = '';
    var visibles = miembros.filter(function (m) { return coincide(m, q); });
    if (!miembros.length) {
      lista.innerHTML = '<div class="text-muted small px-3 py-3">No hay miembros registrados.</div>';
      return;
    }
    if (!visibles.length) {
      lista.innerHTML = '<div class="text-muted small px-3 py-3">No hay miembros que coincidan.</div>';
      return;
    }
    visibles.forEach(function (m) {
      var fila = document.createElement('div');
      fila.className = 'cdv-miembros-picker__item';
      var datos = document.createElement('div');
      datos.innerHTML = '<div>' + m.nombre.replace(/</g, '&lt;') + '</div>'
        + (m.cedula ? '<div class="text-muted small">' + String(m.cedula).replace(/</g, '&lt;') + '</div>' : '');
      var acciones = document.createElement('div');
      acciones.className = 'd-flex flex-wrap gap-1';
      [['coordinador', 'Coord.'], ['encargado', 'Enc.']].forEach(function (par) {
        var rol = par[0];
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = seleccion[rol].indexOf(m.id) !== -1 ? 'btn btn-sm btn-primary' : 'btn btn-sm btn-outline-primary';
        btn.textContent = par[1];
        btn.addEventListener('click', function () {
          if (seleccion[rol].indexOf(m.id) !== -1) {
            quitarMiembro(rol, m.id);
          } else {
            agregarMiembro(rol, m.id);
          }
        });
        acciones.appendChild(btn);
      });
      fila.appendChild(datos);
      fila.appendChild(acciones);
      lista.appendChild(fila);
    });
  }

  function render() {
    renderChips(chipsCoord, 'coordinador');
    renderChips(chipsEnc, 'encargado');
    renderInputs(inputsCoord, 'coordinador_ids[]', 'coordinador');
    renderInputs(inputsEnc, 'encargado_ids[]', 'encargado');
    renderLista();
  }

  function agregarMiembro(rol, id) {
    var otro = rol === 'coordinador' ? 'encargado' : 'coordinador';
    seleccion[otro] = seleccion[otro].filter(function (actual) { return actual !== id; });
    if (seleccion[rol].indexOf(id) === -1) {
      seleccion[rol].push(id);
    }
    render();
  }

  function quitarMiembro(rol, id) {
    seleccion[rol] = seleccion[rol].filter(function (actual) { return actual !== id; });
    render();
  }

  function cargarTerritorio(datos) {
    campoId.value = String(datos.id || '');
    campoNombre.value = datos.nombre || '';
    campoBuscar.value = '';
    seleccion.coordinador = (datos.coordinador_ids || idsDeTexto(datos.coordinadores)).slice();
    seleccion.encargado = (datos.encargado_ids || idsDeTexto(datos.encargados)).slice();
    render();
  }

  if (campoBuscar) {
    campoBuscar.addEventListener('input', renderLista);
  }

  if (modal) {
    modal.addEventListener('show.bs.modal', function (evento) {
      var boton = evento.relatedTarget;
      if (!boton) {
        return;
      }
      cargarTerritorio({
        id: boton.getAttribute('data-id'),
        nombre: boton.getAttribute('data-nombre'),
        coordinadores: boton.getAttribute('data-coordinadores'),
        encargados: boton.getAttribute('data-encargados')
      });
    });
  }

  if (window.bootstrap) {
    if (<?= json_encode($modalEstructura === 'territorio-nuevo') ?> && modalNuevo) {
      bootstrap.Modal.getOrCreateInstance(modalNuevo).show();
    }
    if (<?= json_encode($modalEstructura === 'territorio') ?> && modal && edicion) {
      cargarTerritorio(edicion);
      bootstrap.Modal.getOrCreateInstance(modal).show();
    }
  }
});
</script>
<?php endif; ?>

<?php if ($pestaña === 'casas'): ?>
<?php $casaForm = ($modalEstructura === 'casa') ? $_POST : []; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <p class="text-muted small mb-0">
    Paso 3: cada casa de vida tiene líder y dirección. Colaborador y anfitrión son opcionales.
    La misma persona puede repetirse en más de un rol.
    <?php if (in_array('importar', $pestañasEstructura, true)): ?>
    También puedes
    <a href="estructura.php?pestaña=importar&amp;paso=casas">importarlas desde Excel o CSV</a>.
    <?php endif; ?>
  </p>
  <div class="d-flex flex-wrap gap-2">
    <?php if (!empty($puedeEliminar) && $totalCasasRegistradas > 0): ?>
    <form
      method="POST"
      action="estructura.php?pestaña=casas"
      class="d-inline js-form-confirmar"
      data-confirm-title="Eliminar todas las casas"
      data-confirm="Se eliminarán todas las casas de vida. ¿Continuar?"
    >
      <input type="hidden" name="accion" value="eliminar_todas_casas">
      <button type="submit" class="btn btn-outline-danger">
        <i class="bi bi-trash me-1"></i>Borrar todas
      </button>
    </form>
    <?php endif; ?>
    <?php if (empty($territorios) || empty($lideres)): ?>
    <?php if (empty($lideres) && in_array('lideres', $pestañasEstructura, true)): ?>
    <a class="btn btn-outline-primary" href="estructura.php?pestaña=lideres">Ir a miembros</a>
    <?php endif; ?>
    <?php if (empty($territorios) && in_array('territorios', $pestañasEstructura, true)): ?>
    <a class="btn btn-outline-primary" href="estructura.php?pestaña=territorios">Ir a territorios</a>
    <?php endif; ?>
    <?php else: ?>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaCasa">
      <i class="bi bi-plus-lg me-1"></i>Nueva casa
    </button>
    <?php endif; ?>
  </div>
</div>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" action="estructura.php" class="row g-2 align-items-end">
      <input type="hidden" name="pestaña" value="casas">
      <div class="col-md-6 col-lg-4">
        <label class="form-label small mb-1" for="buscar-casas">Buscar</label>
        <input
          type="search"
          class="form-control form-control-sm"
          id="buscar-casas"
          name="buscar"
          value="<?= htmlspecialchars((string) $buscarEstructura) ?>"
          placeholder="Casa, territorio, líder, dirección…"
        >
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-outline-primary">Buscar</button>
        <?php if ($buscarEstructura !== ''): ?>
        <a class="btn btn-sm btn-outline-secondary" href="estructura.php?pestaña=casas">Limpiar</a>
        <?php endif; ?>
      </div>
    </form>
    <p class="mb-0 mt-3">
      <?php if ($buscarEstructura !== ''): ?>
      <strong><?= (int) $totalCasas ?></strong> resultado(s) de <?= (int) $totalCasasRegistradas ?> casa(s)
      para «<?= htmlspecialchars((string) $buscarEstructura) ?>».
      <?php else: ?>
      Hay <strong><?= (int) $totalCasasRegistradas ?></strong> casa(s) de vida. Se muestran 20 por página.
      <?php endif; ?>
    </p>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-dashboard mb-0 align-middle">
        <thead class="table-light">
          <tr><th>Casa</th><th>Territorio</th><th>Líder</th><th>Colaborador</th><th>Anfitrión</th><th>Dirección</th><th>Acciones</th></tr>
        </thead>
        <tbody>
          <?php foreach ($casasPagina as $c): ?>
          <tr>
            <td><?= htmlspecialchars(nombreVisibleCasaVida($c)) ?></td>
            <td><?= htmlspecialchars((string) $c['territorio_nombre']) ?></td>
            <td><?= htmlspecialchars(trim($c['lider_nombre'] . ' ' . $c['lider_apellido'])) ?></td>
            <td><?= htmlspecialchars(trim(($c['colaborador_nombre'] ?? '') . ' ' . ($c['colaborador_apellido'] ?? '')) ?: '—') ?></td>
            <td><?= htmlspecialchars(trim(($c['anfitrion_nombre'] ?? '') . ' ' . ($c['anfitrion_apellido'] ?? '')) ?: '—') ?></td>
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
                <input type="hidden" name="redireccion" value="<?= htmlspecialchars(construirUrlRegistros('estructura.php', $filtrosListaEstructura, (int) $paginaCasas, 'casas')) ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($casasPagina)): ?>
          <tr>
            <td colspan="7" class="text-center text-muted py-4">
              <?= $buscarEstructura !== '' ? 'No se encontraron casas de vida para esa búsqueda.' : 'No hay casas de vida registradas.' ?>
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php
    $paginaActual = (int) $paginaCasas;
    $totalPaginas = (int) $totalPaginasCasas;
    $totalRegistros = (int) $totalCasas;
    $archivoPagina = 'estructura.php';
    $filtros = $filtrosListaEstructura;
    $pestañaPaginacion = 'casas';
    include __DIR__ . '/../partials/paginacion-registros.php';
    ?>
  </div>
</div>

<?php if (!empty($territorios) && !empty($lideres)): ?>
<div class="modal fade" id="modalNuevaCasa" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="estructura.php?pestaña=casas">
        <div class="modal-header">
          <h5 class="modal-title">Nueva casa de vida</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="accion" value="crear_casa">
          <div class="mb-3">
            <label class="form-label">Territorio</label>
            <select class="form-select" name="territorio_id" required>
              <?php foreach ($territorios as $t): ?>
              <option value="<?= (int) $t['id'] ?>" <?= ((int) ($casaForm['territorio_id'] ?? 0) === (int) $t['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars((string) $t['nombre']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Líder</label>
            <select class="form-select" name="lider_id" required>
              <option value="">Seleccione…</option>
              <?php foreach ($lideres as $l): ?>
              <option value="<?= (int) $l['id'] ?>" <?= ((int) ($casaForm['lider_id'] ?? 0) === (int) $l['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($l['nombre'] . ' ' . $l['apellido']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Colaborador</label>
            <select class="form-select" name="colaborador_id">
              <option value="">Ninguno</option>
              <?php foreach ($lideres as $l): ?>
              <option value="<?= (int) $l['id'] ?>" <?= ((int) ($casaForm['colaborador_id'] ?? 0) === (int) $l['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($l['nombre'] . ' ' . $l['apellido']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Anfitrión</label>
            <select class="form-select" name="anfitrion_id">
              <option value="">Ninguno</option>
              <?php foreach ($lideres as $l): ?>
              <option value="<?= (int) $l['id'] ?>" <?= ((int) ($casaForm['anfitrion_id'] ?? 0) === (int) $l['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($l['nombre'] . ' ' . $l['apellido']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Nombre casa</label>
            <input type="text" class="form-control" name="nombre" required value="<?= htmlspecialchars((string) ($casaForm['nombre'] ?? '')) ?>">
          </div>
          <div class="mb-0">
            <label class="form-label">Dirección</label>
            <input type="text" class="form-control" name="direccion" required value="<?= htmlspecialchars((string) ($casaForm['direccion'] ?? '')) ?>">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Crear casa</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php if ($modalEstructura === 'casa'): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var el = document.getElementById('modalNuevaCasa');
  if (el && window.bootstrap) {
    window.bootstrap.Modal.getOrCreateInstance(el).show();
  }
});
</script>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>

<?php if ($pestaña === 'importar'): ?>
<?php include __DIR__ . '/pestaña-importar.php'; ?>
<?php endif; ?>
