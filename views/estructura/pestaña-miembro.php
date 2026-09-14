<?php
$miembro = $datosMiembro ?? $miembroDetalle ?? [];
$familiares = $parentescoPorMiembro[(int) ($miembroDetalle['id'] ?? 0)] ?? [];
if (!is_array($familiares) || (isset($familiares['id']) && !isset($familiares[0]))) {
    $familiares = $familiares ? [$familiares] : [];
}
$asignacionesMiembro = $asignacionesMiembro ?? [];
$casasMiembro = $casasMiembro ?? [];
$cursosMiembro = $cursosMiembro ?? [];
$cursosMiembroForm = $cursosMiembroForm ?? $cursosMiembro;
$editarFicha = !empty($editarFicha);
$paginaLista = (int) ($paginaMiembros ?? 1);
$buscarLista = trim((string) ($buscarEstructura ?? ''));
$urlLista = construirUrlRegistros('estructura.php', $buscarLista !== '' ? ['buscar' => $buscarLista] : [], $paginaLista, 'lideres');
$urlFicha = urlFichaMiembro((int) ($miembroDetalle['id'] ?? 0), $paginaLista, $buscarLista);
$urlFichaEditar = urlFichaMiembro((int) ($miembroDetalle['id'] ?? 0), $paginaLista, $buscarLista, true);
$nombreCompleto = trim((string) ($miembroDetalle['nombre'] ?? '') . ' ' . (string) ($miembroDetalle['apellido'] ?? ''));
$idsFamilia = [(int) ($miembroDetalle['id'] ?? 0)];
foreach ($familiares as $familiar) {
    $idsFamilia[] = (int) ($familiar['pariente_id'] ?? 0);
}
$miembrosFamiliaBusqueda = [];
foreach ($lideres ?? [] as $candidato) {
    if (in_array((int) ($candidato['id'] ?? 0), $idsFamilia, true)) {
        continue;
    }
    $miembrosFamiliaBusqueda[] = [
        'id'     => (int) $candidato['id'],
        'nombre' => trim((string) $candidato['nombre'] . ' ' . (string) $candidato['apellido']),
        'cedula' => (string) ($candidato['cedula'] ?? ''),
        'genero' => (string) ($candidato['genero'] ?? ''),
    ];
}

$textoFicha = static function ($valor): string {
    $texto = trim((string) $valor);

    return $texto !== '' ? $texto : '—';
};
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
  <div>
    <a class="small text-decoration-none" href="<?= htmlspecialchars($urlLista) ?>">
      <i class="bi bi-arrow-left me-1"></i>Volver a miembros
    </a>
    <h3 class="h5 mb-0 mt-2"><?= htmlspecialchars($nombreCompleto !== '' ? $nombreCompleto : 'Miembro') ?></h3>
    <p class="text-muted small mb-0">
      <?= $editarFicha ? 'Editando ficha del miembro.' : 'Datos personales, ministeriales y familiares.' ?>
    </p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <?php if ($editarFicha): ?>
    <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars($urlFicha) ?>">
      Cancelar
    </a>
    <?php else: ?>
    <a class="btn btn-primary btn-sm" href="<?= htmlspecialchars($urlFichaEditar) ?>">
      <i class="bi bi-pencil me-1"></i>Editar
    </a>
    <?php endif; ?>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <?php if (!$editarFicha): ?>
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h4 class="h6 mb-0">Datos personales</h4>
      </div>
      <div class="card-body">
        <dl class="detalle-registro-list mb-0">
          <div class="detalle-registro-list__row">
            <dt>Nombres</dt>
            <dd><?= htmlspecialchars($textoFicha($miembro['nombre'] ?? '')) ?></dd>
          </div>
          <div class="detalle-registro-list__row">
            <dt>Apellidos</dt>
            <dd><?= htmlspecialchars($textoFicha($miembro['apellido'] ?? '')) ?></dd>
          </div>
          <div class="detalle-registro-list__row">
            <dt>Género</dt>
            <dd><?= htmlspecialchars(etiquetaGeneroMiembro($miembro['genero'] ?? '')) ?></dd>
          </div>
          <div class="detalle-registro-list__row">
            <dt>Cédula</dt>
            <dd><?= htmlspecialchars($textoFicha($miembro['cedula'] ?? '')) ?></dd>
          </div>
          <div class="detalle-registro-list__row">
            <dt>Celular</dt>
            <dd><?= htmlspecialchars($textoFicha($miembro['celular'] ?? '')) ?></dd>
          </div>
          <div class="detalle-registro-list__row">
            <dt>Correo</dt>
            <dd><?= htmlspecialchars($textoFicha($miembro['email'] ?? '')) ?></dd>
          </div>
          <div class="detalle-registro-list__row">
            <dt>Notas</dt>
            <dd><?= nl2br(htmlspecialchars($textoFicha($miembro['notas'] ?? ''))) ?></dd>
          </div>
        </dl>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h4 class="h6 mb-0">Datos ministeriales</h4>
      </div>
      <div class="card-body">
        <dl class="detalle-registro-list mb-3">
          <div class="detalle-registro-list__row">
            <dt>Fecha de bautismo</dt>
            <dd><?= htmlspecialchars(formatearFechaMiembro($miembro['fecha_bautismo'] ?? '')) ?></dd>
          </div>
        </dl>
        <p class="small mb-2"><strong>Cursos realizados</strong></p>
        <?php if ($cursosMiembro === []): ?>
        <p class="text-muted small mb-0">Sin cursos registrados.</p>
        <?php else: ?>
        <ul class="small mb-0 ps-3">
          <?php foreach ($cursosMiembro as $cursoFila): ?>
          <li>
            <?= htmlspecialchars(etiquetaCursoMinisterial($cursoFila['curso'] ?? '')) ?>
            · <?= htmlspecialchars(formatearFechaMiembro($cursoFila['fecha_culminacion'] ?? '')) ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h4 class="h6 mb-0">Familiares</h4>
      </div>
      <div class="card-body">
        <?php if ($familiares === []): ?>
        <p class="text-muted small mb-0">Sin familiares conectados.</p>
        <?php else: ?>
        <dl class="detalle-registro-list mb-0">
          <?php foreach ($familiares as $familiar): ?>
          <div class="detalle-registro-list__row">
            <dt><?= htmlspecialchars(etiquetaParentescoDesdePariente($familiar)) ?></dt>
            <dd>
              <a href="<?= htmlspecialchars(urlFichaMiembro((int) $familiar['pariente_id'], $paginaLista, $buscarLista)) ?>">
                <?= htmlspecialchars($textoFicha(trim((string) ($familiar['pariente_nombre'] ?? '') . ' ' . (string) ($familiar['pariente_apellido'] ?? '')))) ?>
              </a>
            </dd>
          </div>
          <?php endforeach; ?>
        </dl>
        <?php endif; ?>
      </div>
    </div>

    <?php else: ?>
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h4 class="h6 mb-0">Datos personales</h4>
      </div>
      <div class="card-body">
        <form method="POST" action="<?= htmlspecialchars($urlFichaEditar) ?>">
          <input type="hidden" name="accion" value="actualizar_lider">
          <input type="hidden" name="id" value="<?= (int) ($miembroDetalle['id'] ?? 0) ?>">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nombres <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nombre" required value="<?= htmlspecialchars((string) ($miembro['nombre'] ?? '')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Apellidos <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="apellido" required value="<?= htmlspecialchars((string) ($miembro['apellido'] ?? '')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Género <span class="text-danger">*</span></label>
              <select class="form-select" name="genero" required>
                <option value="">Seleccione…</option>
                <?php foreach (opcionesGeneroMiembro() as $clave => $etiqueta): ?>
                <option value="<?= htmlspecialchars($clave) ?>" <?= (($miembro['genero'] ?? '') === $clave) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($etiqueta) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Cédula</label>
              <input type="text" class="form-control" name="cedula" value="<?= htmlspecialchars((string) ($miembro['cedula'] ?? '')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Celular</label>
              <input type="tel" class="form-control" name="celular" value="<?= htmlspecialchars((string) ($miembro['celular'] ?? '')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Correo</label>
              <input type="email" class="form-control" name="email" value="<?= htmlspecialchars((string) ($miembro['email'] ?? '')) ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Notas</label>
              <textarea class="form-control" name="notas" rows="3"><?= htmlspecialchars((string) ($miembro['notas'] ?? '')) ?></textarea>
            </div>
          </div>
          <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
            <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($urlFicha) ?>">Cancelar</a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i>Guardar
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h4 class="h6 mb-0">Datos ministeriales</h4>
      </div>
      <div class="card-body">
        <form method="POST" action="<?= htmlspecialchars($urlFichaEditar) ?>" id="formDatosMinisteriales">
          <input type="hidden" name="accion" value="guardar_datos_ministeriales">
          <input type="hidden" name="id" value="<?= (int) ($miembroDetalle['id'] ?? 0) ?>">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label" for="fecha_bautismo">Fecha de bautismo</label>
              <input
                type="date"
                class="form-control"
                id="fecha_bautismo"
                name="fecha_bautismo"
                value="<?= htmlspecialchars((string) ($miembro['fecha_bautismo'] ?? '')) ?>"
              >
            </div>
          </div>

          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <label class="form-label mb-0">Cursos realizados</label>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAnadirCurso">
              <i class="bi bi-plus-lg me-1"></i>Añadir curso
            </button>
          </div>
          <p class="text-muted small mb-3">Honra, Escol o Academy, con la fecha en que se graduó o culminó.</p>
          <div id="listaCursosMiembro">
            <?php foreach ($cursosMiembroForm as $cursoFila): ?>
            <div class="row g-2 align-items-end js-curso-fila mb-2">
              <div class="col-md-6">
                <label class="form-label small mb-1">Curso</label>
                <select class="form-select form-select-sm" name="cursos[curso][]" required>
                  <option value="">Seleccione…</option>
                  <?php foreach (opcionesCursoMinisterial() as $claveCurso => $etiquetaCurso): ?>
                  <option value="<?= htmlspecialchars($claveCurso) ?>" <?= ((string) ($cursoFila['curso'] ?? '') === $claveCurso) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($etiquetaCurso) ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label small mb-1">Fecha de culminación</label>
                <input
                  type="date"
                  class="form-control form-control-sm"
                  name="cursos[fecha][]"
                  required
                  value="<?= htmlspecialchars((string) ($cursoFila['fecha_culminacion'] ?? $cursoFila['fecha'] ?? '')) ?>"
                >
              </div>
              <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-outline-danger w-100 js-quitar-curso">Quitar</button>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <p class="text-muted small mb-0 <?= $cursosMiembroForm === [] ? '' : 'd-none' ?>" id="cursosVacios">
            Aún no hay cursos. Pulsa «Añadir curso» para registrar uno.
          </p>
          <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
            <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($urlFicha) ?>">Cancelar</a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i>Guardar
            </button>
          </div>
        </form>
        <template id="plantillaCursoMiembro">
          <div class="row g-2 align-items-end js-curso-fila mb-2">
            <div class="col-md-6">
              <label class="form-label small mb-1">Curso</label>
              <select class="form-select form-select-sm" name="cursos[curso][]" required>
                <option value="">Seleccione…</option>
                <?php foreach (opcionesCursoMinisterial() as $claveCurso => $etiquetaCurso): ?>
                <option value="<?= htmlspecialchars($claveCurso) ?>"><?= htmlspecialchars($etiquetaCurso) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small mb-1">Fecha de culminación</label>
              <input type="date" class="form-control form-control-sm" name="cursos[fecha][]" required>
            </div>
            <div class="col-md-2">
              <button type="button" class="btn btn-sm btn-outline-danger w-100 js-quitar-curso">Quitar</button>
            </div>
          </div>
        </template>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h4 class="h6 mb-0">Familiares</h4>
      </div>
      <div class="card-body">
        <?php if ($familiares === []): ?>
        <p class="text-muted small mb-3">Aún no hay familiares conectados.</p>
        <?php else: ?>
        <ul class="list-unstyled mb-4">
          <?php foreach ($familiares as $familiar): ?>
          <li class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom py-2">
            <div>
              <a href="<?= htmlspecialchars(urlFichaMiembro((int) $familiar['pariente_id'], $paginaLista, $buscarLista)) ?>">
                <?= htmlspecialchars(trim((string) ($familiar['pariente_nombre'] ?? '') . ' ' . (string) ($familiar['pariente_apellido'] ?? ''))) ?>
              </a>
              <span class="text-muted small">· <?= htmlspecialchars(etiquetaParentescoDesdePariente($familiar)) ?></span>
            </div>
            <form
              method="POST"
              action="<?= htmlspecialchars($urlFichaEditar) ?>"
              class="d-inline js-form-confirmar"
              data-confirm-title="Quitar familiar"
              data-confirm="¿Quitar este familiar?"
            >
              <input type="hidden" name="accion" value="eliminar_parentesco">
              <input type="hidden" name="miembro_id" value="<?= (int) ($miembroDetalle['id'] ?? 0) ?>">
              <input type="hidden" name="pariente_id" value="<?= (int) $familiar['pariente_id'] ?>">
              <button type="submit" class="btn btn-sm btn-outline-danger" title="Quitar">
                <i class="bi bi-trash"></i>
              </button>
            </form>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <form method="POST" action="<?= htmlspecialchars($urlFichaEditar) ?>" id="formAgregarFamiliar">
          <input type="hidden" name="accion" value="agregar_familiar">
          <input type="hidden" name="miembro_id" value="<?= (int) ($miembroDetalle['id'] ?? 0) ?>">
          <input type="hidden" name="pariente_id" id="familiarParienteId" value="">
          <div class="row g-2 align-items-end mb-3">
            <div class="col-md-6">
              <label class="form-label small mb-1" for="familiarBuscar">Buscar miembro</label>
              <input type="search" class="form-control form-control-sm" id="familiarBuscar" placeholder="Nombre o cédula" autocomplete="off">
            </div>
            <div class="col-md-6">
              <label class="form-label small mb-1" for="familiarParentesco">Parentesco</label>
              <select class="form-select form-select-sm" name="parentesco" id="familiarParentesco" required>
                <option value="">Seleccione…</option>
                <?php foreach (opcionesParentescoMiembro() as $claveParentesco => $etiquetaParentesco): ?>
                <option value="<?= htmlspecialchars($claveParentesco) ?>"><?= htmlspecialchars($etiquetaParentesco) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="cdv-miembros-picker border rounded" id="familiarListaMiembros"></div>
          <p class="text-muted small mt-2 mb-0">Busca a la persona, elige el parentesco y pulsa Añadir.</p>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="col-lg-4">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h4 class="h6 mb-0">En la estructura</h4>
      </div>
      <div class="card-body">
        <p class="small mb-2"><strong>Territorios</strong></p>
        <?php if ($asignacionesMiembro === []): ?>
        <p class="text-muted small mb-3">Sin asignar</p>
        <?php else: ?>
        <ul class="small mb-3 ps-3">
          <?php foreach ($asignacionesMiembro as $asignacion): ?>
          <li>
            <?= htmlspecialchars(etiquetaRolTerritorio((string) $asignacion['rol'])) ?>
            · <?= htmlspecialchars((string) ($asignacion['territorio_nombre'] ?? '')) ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <p class="small mb-2"><strong>Casas de vida</strong></p>
        <?php if ($casasMiembro === []): ?>
        <p class="text-muted small mb-0">Ninguna</p>
        <?php else: ?>
        <ul class="small mb-0 ps-3">
          <?php foreach ($casasMiembro as $casa): ?>
          <?php
          $rolesCasa = [];
          $miembroId = (int) ($miembroDetalle['id'] ?? 0);
          if ((int) ($casa['lider_id'] ?? 0) === $miembroId) {
              $rolesCasa[] = 'Líder';
          }
          if ((int) ($casa['colaborador_id'] ?? 0) === $miembroId) {
              $rolesCasa[] = 'Colaborador';
          }
          if ((int) ($casa['anfitrion_id'] ?? 0) === $miembroId) {
              $rolesCasa[] = 'Anfitrión';
          }
          ?>
          <li>
            <?= htmlspecialchars(implode(', ', $rolesCasa)) ?>
            · <?= htmlspecialchars(nombreVisibleCasaVida($casa)) ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php if ($editarFicha): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var listaCursos = document.getElementById('listaCursosMiembro');
  var plantillaCurso = document.getElementById('plantillaCursoMiembro');
  var btnAnadirCurso = document.getElementById('btnAnadirCurso');
  var vacioCursos = document.getElementById('cursosVacios');
  var miembros = <?= json_encode($miembrosFamiliaBusqueda, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
  var campoBuscar = document.getElementById('familiarBuscar');
  var listaFamilia = document.getElementById('familiarListaMiembros');
  var campoPariente = document.getElementById('familiarParienteId');
  var formFamiliar = document.getElementById('formAgregarFamiliar');

  function actualizarVacioCursos() {
    if (!vacioCursos || !listaCursos) {
      return;
    }
    vacioCursos.classList.toggle('d-none', listaCursos.querySelectorAll('.js-curso-fila').length > 0);
  }

  if (btnAnadirCurso && plantillaCurso && listaCursos) {
    btnAnadirCurso.addEventListener('click', function () {
      listaCursos.appendChild(plantillaCurso.content.cloneNode(true));
      actualizarVacioCursos();
    });
    listaCursos.addEventListener('click', function (evento) {
      var boton = evento.target.closest('.js-quitar-curso');
      if (!boton) {
        return;
      }
      var fila = boton.closest('.js-curso-fila');
      if (fila) {
        fila.remove();
        actualizarVacioCursos();
      }
    });
  }

  function coincide(miembro, q) {
    if (!q) {
      return true;
    }
    return (miembro.nombre + ' ' + miembro.cedula).toLowerCase().indexOf(q) !== -1;
  }

  function renderFamilia() {
    if (!listaFamilia) {
      return;
    }
    var q = ((campoBuscar && campoBuscar.value) || '').trim().toLowerCase();
    listaFamilia.innerHTML = '';
    if (!miembros.length) {
      listaFamilia.innerHTML = '<div class="text-muted small px-3 py-3">No hay más miembros para agregar.</div>';
      return;
    }
    var visibles = miembros.filter(function (m) { return coincide(m, q); });
    if (!visibles.length) {
      listaFamilia.innerHTML = '<div class="text-muted small px-3 py-3">No hay miembros que coincidan.</div>';
      return;
    }
    visibles.forEach(function (m) {
      var fila = document.createElement('div');
      fila.className = 'cdv-miembros-picker__item';
      var datos = document.createElement('div');
      datos.innerHTML = '<div>' + m.nombre.replace(/</g, '&lt;') + '</div>'
        + (m.cedula ? '<div class="text-muted small">' + String(m.cedula).replace(/</g, '&lt;') + '</div>' : '');
      var boton = document.createElement('button');
      boton.type = 'button';
      boton.className = 'btn btn-sm btn-outline-primary';
      boton.textContent = 'Añadir';
      boton.addEventListener('click', function () {
        if (!formFamiliar.reportValidity()) {
          return;
        }
        campoPariente.value = String(m.id);
        formFamiliar.submit();
      });
      fila.appendChild(datos);
      fila.appendChild(boton);
      listaFamilia.appendChild(fila);
    });
  }

  if (campoBuscar) {
    campoBuscar.addEventListener('input', renderFamilia);
  }
  renderFamilia();
});
</script>
<?php endif; ?>
