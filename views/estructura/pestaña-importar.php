<?php
$catalogoPasos = catalogoPasosImportEstructura();
$conteos = [
    'miembros'     => count($lideres ?? []),
    'territorios'  => count($territorios ?? []),
    'asignaciones' => count($parejasParentesco ?? []),
    'casas'        => count($casas ?? []),
];
$pasoActual = $catalogoPasos[$pasoImportar] ?? null;
$clavesPasos = array_values($pasosImportar);
$indicePaso = array_search($pasoImportar, $clavesPasos, true);
$pasoAnterior = ($indicePaso !== false && $indicePaso > 0) ? $clavesPasos[$indicePaso - 1] : null;
$pasoSiguiente = ($indicePaso !== false && isset($clavesPasos[$indicePaso + 1])) ? $clavesPasos[$indicePaso + 1] : null;
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <p class="text-muted small mb-0">
    Importa la estructura en este orden: miembros, territorios, asignaciones y casas de vida.
  </p>
  <div class="d-flex flex-wrap gap-2">
    <button
      type="button"
      class="btn btn-outline-primary"
      data-bs-toggle="modal"
      data-bs-target="#modalParentesco"
      <?= (count($miembrosMasculinos ?? []) < 1 || count($miembrosFemeninos ?? []) < 1) ? 'disabled' : '' ?>
    >
      <i class="bi bi-people me-1"></i>Conectar parentesco
    </button>
    <?php if (!empty($puedeEliminar) && !empty($lideres)): ?>
    <form
      method="POST"
      action="estructura.php?pestaña=importar"
      class="d-inline js-form-confirmar"
      data-confirm-title="Eliminar todos los miembros"
      data-confirm="Se eliminarán todos los miembros, sus parentescos, asignaciones a territorios y casas de vida. ¿Continuar?"
    >
      <input type="hidden" name="accion" value="eliminar_todos_lideres">
      <button type="submit" class="btn btn-outline-danger">
        <i class="bi bi-trash me-1"></i>Borrar todos
      </button>
    </form>
    <?php endif; ?>
  </div>
</div>

<div class="cdv-pasos mb-4" role="navigation" aria-label="Pasos de carga masiva">
  <?php foreach ($pasosImportar as $clavePaso): ?>
    <?php
    $infoPaso = $catalogoPasos[$clavePaso] ?? null;
    if ($infoPaso === null) {
        continue;
    }
    $activo = $clavePaso === $pasoImportar;
    $completo = ($conteos[$clavePaso] ?? 0) > 0;
    ?>
    <a
      class="cdv-paso<?= $activo ? ' is-active' : '' ?><?= $completo && !$activo ? ' is-done' : '' ?>"
      href="estructura.php?pestaña=importar&amp;paso=<?= urlencode($clavePaso) ?>"
    >
      <span class="cdv-paso__num"><?= (int) $infoPaso['numero'] ?></span>
      <span class="cdv-paso__texto">
        <span class="cdv-paso__label"><?= htmlspecialchars((string) $infoPaso['etiqueta']) ?></span>
        <span class="cdv-paso__meta"><?= (int) ($conteos[$clavePaso] ?? 0) ?> registro(s)</span>
      </span>
    </a>
  <?php endforeach; ?>
</div>

<?php if ($pasoActual === null): ?>
<div class="alert alert-warning">No tienes permiso para importar ningún paso de la estructura.</div>
<?php else: ?>

<?php if ($pasoImportar === 'asignaciones' && (empty($lideres) || empty($territorios))): ?>
<div class="alert alert-warning">
  Para asignar coordinadores y encargados primero deben existir miembros y territorios.
  <div class="mt-2 d-flex flex-wrap gap-2">
    <?php if (empty($lideres) && in_array('miembros', $pasosImportar, true)): ?>
    <a class="btn btn-sm btn-outline-primary" href="estructura.php?pestaña=importar&amp;paso=miembros">Paso 1 · Miembros</a>
    <?php endif; ?>
    <?php if (empty($territorios) && in_array('territorios', $pasosImportar, true)): ?>
    <a class="btn btn-sm btn-outline-primary" href="estructura.php?pestaña=importar&amp;paso=territorios">Paso 2 · Territorios</a>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<?php if ($pasoImportar === 'casas' && (empty($lideres) || empty($territorios))): ?>
<div class="alert alert-warning">
  Para importar casas de vida primero deben existir miembros y territorios.
  <div class="mt-2 d-flex flex-wrap gap-2">
    <?php if (empty($lideres) && in_array('miembros', $pasosImportar, true)): ?>
    <a class="btn btn-sm btn-outline-primary" href="estructura.php?pestaña=importar&amp;paso=miembros">Paso 1 · Miembros</a>
    <?php endif; ?>
    <?php if (empty($territorios) && in_array('territorios', $pasosImportar, true)): ?>
    <a class="btn btn-sm btn-outline-primary" href="estructura.php?pestaña=importar&amp;paso=territorios">Paso 2 · Territorios</a>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white py-3">
        <h3 class="h6 mb-0">
          <i class="bi bi-file-earmark-spreadsheet me-2"></i>
          Paso <?= (int) $pasoActual['numero'] ?> · Plantilla de <?= htmlspecialchars((string) $pasoActual['etiqueta']) ?>
        </h3>
      </div>
      <div class="card-body">
        <p class="text-muted small"><?= htmlspecialchars((string) $pasoActual['descripcion']) ?></p>
        <p class="text-muted small"><?= htmlspecialchars((string) $pasoActual['ayuda']) ?></p>
        <ul class="small text-muted mb-3">
          <?php foreach ($pasoActual['columnas'] as $claveCol => $etiquetaCol): ?>
          <li>
            <strong><?= htmlspecialchars((string) $etiquetaCol) ?></strong>
            <?php if (in_array($claveCol, $pasoActual['requeridas'], true)): ?>
            <span class="text-danger">*</span>
            <?php endif; ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <div class="d-flex flex-wrap gap-2">
          <a
            href="estructura.php?pestaña=importar&amp;paso=<?= urlencode($pasoImportar) ?>&amp;descargar=plantilla"
            class="btn btn-success"
          >
            <i class="bi bi-download me-1"></i>Excel
          </a>
          <a
            href="estructura.php?pestaña=importar&amp;paso=<?= urlencode($pasoImportar) ?>&amp;descargar=plantilla&amp;formato=csv"
            class="btn btn-outline-success"
          >
            <i class="bi bi-filetype-csv me-1"></i>CSV
          </a>
        </div>
        <p class="form-text mt-2 mb-0">Si Excel convierte el archivo a .xlsx, expórtalo como CSV UTF-8 o usa la plantilla .xls sin convertirla.</p>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white py-3">
        <h3 class="h6 mb-0"><i class="bi bi-upload me-2"></i>Subir archivo</h3>
      </div>
      <div class="card-body">
        <form
          method="POST"
          action="estructura.php?pestaña=importar&amp;paso=<?= urlencode($pasoImportar) ?>"
          enctype="multipart/form-data"
          class="js-form-confirmar"
          data-confirm-title="Importar <?= htmlspecialchars((string) $pasoActual['etiqueta']) ?>"
          data-confirm="¿Importar el archivo de <?= htmlspecialchars((string) $pasoActual['etiqueta']) ?>?"
        >
          <input type="hidden" name="accion" value="importar_estructura">
          <input type="hidden" name="paso" value="<?= htmlspecialchars($pasoImportar) ?>">
          <div class="mb-3">
            <label class="form-label" for="archivo_import_estructura">Archivo</label>
            <input
              type="file"
              class="form-control"
              id="archivo_import_estructura"
              name="archivo"
              accept=".csv,.xls,application/vnd.ms-excel,text/csv"
              required
            >
            <div class="form-text">Formatos: plantilla .xls descargada o .csv con las mismas columnas.</div>
          </div>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-cloud-upload me-1"></i>Importar <?= htmlspecialchars((string) $pasoActual['etiqueta']) ?>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php if ($resultadoImportEstructura !== null): ?>
<div class="card border-0 shadow-sm mt-4">
  <div class="card-header bg-white py-3">
    <h3 class="h6 mb-0"><i class="bi bi-clipboard-check me-2"></i>Resultado de la importación</h3>
  </div>
  <div class="card-body">
    <div class="d-flex flex-wrap gap-3 mb-3">
      <span class="badge bg-success fs-6"><?= (int) ($resultadoImportEstructura['importados'] ?? 0) ?> importado(s)</span>
      <?php if ((int) ($resultadoImportEstructura['duplicados'] ?? 0) > 0): ?>
      <span class="badge bg-secondary fs-6"><?= (int) $resultadoImportEstructura['duplicados'] ?> duplicado(s)</span>
      <?php endif; ?>
      <?php if (!empty($resultadoImportEstructura['errores'])): ?>
      <span class="badge bg-warning text-dark fs-6"><?= count($resultadoImportEstructura['errores']) ?> con error</span>
      <?php endif; ?>
    </div>

    <?php if (!empty($resultadoImportEstructura['errores'])): ?>
    <div class="table-responsive">
      <table class="table table-sm table-bordered mb-0">
        <thead class="table-light">
          <tr>
            <th>Fila</th>
            <th>Error</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($resultadoImportEstructura['errores'] as $errorFila): ?>
          <tr>
            <td class="text-nowrap"><?= (int) ($errorFila['fila'] ?? 0) ?></td>
            <td><?= htmlspecialchars((string) ($errorFila['mensaje'] ?? '')) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php elseif ((int) ($resultadoImportEstructura['importados'] ?? 0) > 0): ?>
    <p class="text-muted small mb-0">Los registros nuevos se importaron correctamente. Los duplicados se omitieron.</p>
    <?php else: ?>
    <p class="text-muted small mb-0">No se importó ningún registro nuevo. Revisa duplicados o el detalle de errores.</p>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<div class="d-flex flex-wrap justify-content-between gap-2 mt-4">
  <div>
    <?php if ($pasoAnterior !== null): ?>
    <a class="btn btn-outline-secondary" href="estructura.php?pestaña=importar&amp;paso=<?= urlencode($pasoAnterior) ?>">
      <i class="bi bi-arrow-left me-1"></i>Paso anterior
    </a>
    <?php endif; ?>
  </div>
  <div>
    <?php if ($pasoSiguiente !== null): ?>
    <a class="btn btn-primary" href="estructura.php?pestaña=importar&amp;paso=<?= urlencode($pasoSiguiente) ?>">
      Continuar al paso <?= (int) $catalogoPasos[$pasoSiguiente]['numero'] ?> · <?= htmlspecialchars((string) $catalogoPasos[$pasoSiguiente]['etiqueta']) ?>
      <i class="bi bi-arrow-right ms-1"></i>
    </a>
    <?php elseif ($pasoImportar === 'casas'): ?>
    <a class="btn btn-outline-primary" href="estructura.php?pestaña=casas">
      Ver casas de vida
      <i class="bi bi-arrow-right ms-1"></i>
    </a>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<div class="modal fade" id="modalParentesco" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="estructura.php?pestaña=importar">
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
              <?php foreach ($miembrosMasculinos ?? [] as $m): ?>
              <option value="<?= (int) $m['id'] ?>"><?= htmlspecialchars($m['nombre'] . ' ' . $m['apellido']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-0">
            <label class="form-label">Esposa</label>
            <select class="form-select" name="pariente_id" required>
              <option value="">Seleccione…</option>
              <?php foreach ($miembrosFemeninos ?? [] as $m): ?>
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
<?php if (($modalEstructura ?? null) === 'parentesco'): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var el = document.getElementById('modalParentesco');
  if (el && window.bootstrap) {
    window.bootstrap.Modal.getOrCreateInstance(el).show();
  }
});
</script>
<?php endif; ?>

