<?php
$catalogoPasos = catalogoPasosImportEstructura();
$pasosImportar = $pasosImportar ?? [];
$rolAvanzado = (string) ($usuario['rol'] ?? '');
$totalAsignaciones = 0;
foreach ($territorios ?? [] as $territorioAsig) {
    $totalAsignaciones += count($territorioAsig['asignaciones']['coordinador'] ?? []);
    $totalAsignaciones += count($territorioAsig['asignaciones']['encargado'] ?? []);
}

$seccionesAvanzado = [];

if (puedeGestionarEstructuraPestana($rolAvanzado, 'lideres')) {
    $seccionesAvanzado[] = [
        'clave'         => 'miembros',
        'paso'          => 'miembros',
        'titulo'        => 'Miembros',
        'icono'         => 'bi-people',
        'conteo'        => (int) ($totalMiembrosRegistrados ?? count($lideres ?? [])),
        'unidad'        => 'miembro(s)',
        'exportar'      => 'estructura.php?pestaña=avanzado&descargar=miembros',
        'eliminar'      => 'eliminar_todos_lideres',
        'confirm'       => 'Se eliminarán todos los miembros, sus parentescos, asignaciones a territorios y casas de vida. ¿Continuar?',
        'tituloConfirm' => 'Eliminar todos los miembros',
    ];
}

if (puedeGestionarEstructuraPestana($rolAvanzado, 'territorios')) {
    $seccionesAvanzado[] = [
        'clave'         => 'territorios',
        'paso'          => 'territorios',
        'titulo'        => 'Territorios',
        'icono'         => 'bi-map',
        'conteo'        => count($territorios ?? []),
        'unidad'        => 'territorio(s)',
        'exportar'      => 'estructura.php?pestaña=avanzado&descargar=territorios',
        'eliminar'      => 'eliminar_todos_territorios',
        'confirm'       => 'Se eliminarán todos los territorios, sus asignaciones y las casas de vida asociadas. ¿Continuar?',
        'tituloConfirm' => 'Eliminar todos los territorios',
    ];
    $seccionesAvanzado[] = [
        'clave'         => 'asignaciones',
        'paso'          => 'asignaciones',
        'titulo'        => 'Asignaciones',
        'icono'         => 'bi-person-badge',
        'conteo'        => $totalAsignaciones,
        'unidad'        => 'asignación(es)',
        'exportar'      => '',
        'eliminar'      => '',
        'confirm'       => '',
        'tituloConfirm' => '',
    ];
}

if (puedeGestionarEstructuraPestana($rolAvanzado, 'casas')) {
    $seccionesAvanzado[] = [
        'clave'         => 'casas',
        'paso'          => 'casas',
        'titulo'        => 'Casas de vida',
        'icono'         => 'bi-house-heart',
        'conteo'        => (int) ($totalCasasRegistradas ?? count($casas ?? [])),
        'unidad'        => 'casa(s)',
        'exportar'      => 'estructura.php?pestaña=avanzado&descargar=casas',
        'eliminar'      => 'eliminar_todas_casas',
        'confirm'       => 'Se eliminarán todas las casas de vida. Los miembros y territorios no se tocan. ¿Continuar?',
        'tituloConfirm' => 'Eliminar todas las casas de vida',
    ];
}
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <p class="text-muted small mb-0">
    Exporta, importa o elimina los registros de cada sección.
  </p>
  <?php if (puedeGestionarEstructuraPestana($rolAvanzado, 'lideres')): ?>
  <button
    type="button"
    class="btn btn-outline-primary"
    data-bs-toggle="modal"
    data-bs-target="#modalParentesco"
    <?= (count($miembrosMasculinos ?? []) < 1 || count($miembrosFemeninos ?? []) < 1) ? 'disabled' : '' ?>
  >
    <i class="bi bi-people me-1"></i>Conectar parentesco
  </button>
  <?php endif; ?>
</div>

<div class="row g-4">
  <?php foreach ($seccionesAvanzado as $seccion): ?>
  <?php
  $pasoSeccion = (string) $seccion['paso'];
  $infoPaso = $catalogoPasos[$pasoSeccion] ?? null;
  $puedeImportar = $infoPaso !== null && in_array($pasoSeccion, $pasosImportar, true);
  $idModal = 'modalImportar' . ucfirst($pasoSeccion);
  ?>
  <div class="col-md-6 col-xl-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <h3 class="h6 mb-2">
          <i class="bi <?= htmlspecialchars((string) $seccion['icono']) ?> me-1"></i>
          <?= htmlspecialchars((string) $seccion['titulo']) ?>
        </h3>
        <p class="text-muted small mb-3">
          <?= (int) $seccion['conteo'] ?> <?= htmlspecialchars((string) $seccion['unidad']) ?>
        </p>
        <div class="d-flex flex-wrap gap-2">
          <?php if ($seccion['exportar'] !== ''): ?>
            <?php if ((int) $seccion['conteo'] > 0): ?>
            <a class="btn btn-outline-success" href="<?= htmlspecialchars((string) $seccion['exportar']) ?>">
              <i class="bi bi-download me-1"></i>Exportar
            </a>
            <?php else: ?>
            <button type="button" class="btn btn-outline-success" disabled>
              <i class="bi bi-download me-1"></i>Exportar
            </button>
            <?php endif; ?>
          <?php endif; ?>

          <?php if ($puedeImportar): ?>
          <a
            class="btn btn-outline-secondary"
            href="estructura.php?pestaña=avanzado&amp;paso=<?= urlencode($pasoSeccion) ?>&amp;descargar=plantilla"
          >
            <i class="bi bi-file-earmark-spreadsheet me-1"></i>Descargar plantilla
          </a>
          <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#<?= htmlspecialchars($idModal) ?>">
            <i class="bi bi-upload me-1"></i>Importar
          </button>
          <?php endif; ?>

          <?php if ($seccion['eliminar'] !== '' && !empty($puedeEliminar) && (int) $seccion['conteo'] > 0): ?>
          <form
            method="POST"
            action="estructura.php?pestaña=avanzado"
            class="d-inline js-form-confirmar"
            data-confirm-title="<?= htmlspecialchars((string) $seccion['tituloConfirm']) ?>"
            data-confirm="<?= htmlspecialchars((string) $seccion['confirm']) ?>"
          >
            <input type="hidden" name="accion" value="<?= htmlspecialchars((string) $seccion['eliminar']) ?>">
            <button type="submit" class="btn btn-outline-danger">
              <i class="bi bi-trash me-1"></i>Eliminar registros
            </button>
          </form>
          <?php elseif ($seccion['eliminar'] !== '' && !empty($puedeEliminar)): ?>
          <button type="button" class="btn btn-outline-danger" disabled>
            <i class="bi bi-trash me-1"></i>Eliminar registros
          </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
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

<?php foreach ($seccionesAvanzado as $seccion): ?>
<?php
$pasoSeccion = (string) $seccion['paso'];
$infoPaso = $catalogoPasos[$pasoSeccion] ?? null;
if ($infoPaso === null || !in_array($pasoSeccion, $pasosImportar, true)) {
    continue;
}
$idModal = 'modalImportar' . ucfirst($pasoSeccion);
?>
<div class="modal fade" id="<?= htmlspecialchars($idModal) ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form
        method="POST"
        action="estructura.php?pestaña=avanzado&amp;paso=<?= urlencode($pasoSeccion) ?>"
        enctype="multipart/form-data"
        class="js-form-confirmar"
        data-confirm-title="Importar <?= htmlspecialchars((string) $infoPaso['etiqueta']) ?>"
        data-confirm="¿Importar el archivo de <?= htmlspecialchars((string) $infoPaso['etiqueta']) ?>?"
      >
        <div class="modal-header">
          <h5 class="modal-title">Importar <?= htmlspecialchars((string) $infoPaso['etiqueta']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="accion" value="importar_estructura">
          <input type="hidden" name="paso" value="<?= htmlspecialchars($pasoSeccion) ?>">
          <p class="text-muted small"><?= htmlspecialchars((string) $infoPaso['ayuda']) ?></p>
          <div class="mb-0">
            <label class="form-label" for="archivo_<?= htmlspecialchars($pasoSeccion) ?>">Archivo</label>
            <input
              type="file"
              class="form-control"
              id="archivo_<?= htmlspecialchars($pasoSeccion) ?>"
              name="archivo"
              accept=".csv,.xls,application/vnd.ms-excel,text/csv"
              required
            >
            <div class="form-text">Usa la plantilla .xls o un CSV con las mismas columnas.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-cloud-upload me-1"></i>Importar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>

<div class="modal fade" id="modalParentesco" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="estructura.php?pestaña=avanzado">
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
