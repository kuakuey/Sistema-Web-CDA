<?php
/** @var array{importados?: int, omitidos?: int, errores?: array<int, array{fila: int, mensaje: string}>}|null $resultadoImportEventos */
$resultadoImportEventos = $resultadoImportEventos ?? null;
?>
<div class="row g-4">
  <div class="col-lg-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white py-3">
        <h3 class="h6 mb-0"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Plantilla</h3>
      </div>
      <div class="card-body">
        <p class="text-muted small">
          El archivo tiene <strong>dos pestañas</strong>: «Datos» (solo encabezados, para llenar) y
          «Guía y ejemplos» (instrucciones, casos de uso y referencia de eventos).
        </p>
        <ul class="small text-muted mb-4">
          <li>Complete sus registros en la pestaña <strong>Datos</strong>.</li>
          <li>Consulte la pestaña <strong>Guía y ejemplos</strong> para ver 7 casos y la tabla de referencia.</li>
          <li>Al importar, el sistema lee únicamente la pestaña «Datos».</li>
          <li>También puede subir un archivo <code>.csv</code> con las mismas columnas.</li>
        </ul>
        <a href="avanzado.php?pestaña=importar&amp;descargar=plantilla" class="btn btn-success">
          <i class="bi bi-download me-1"></i>Descargar plantilla Excel
        </a>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white py-3">
        <h3 class="h6 mb-0"><i class="bi bi-upload me-2"></i>Subir registros</h3>
      </div>
      <div class="card-body">
        <p class="text-muted small mb-3">
          Importa participantes de eventos desde la plantilla (.xls) o un archivo CSV con las mismas columnas.
        </p>
        <form method="POST" action="avanzado.php?pestaña=importar" enctype="multipart/form-data" class="js-form-confirmar" data-confirm-title="Importar registros" data-confirm="¿Importar los registros del archivo seleccionado?">
          <input type="hidden" name="accion" value="importar_registros_eventos">
          <div class="mb-3">
            <label class="form-label" for="archivo_import_eventos">Archivo</label>
            <input
              type="file"
              class="form-control"
              id="archivo_import_eventos"
              name="archivo"
              accept=".csv,.xls,.xlsx,application/vnd.ms-excel,text/csv"
              required
            >
            <div class="form-text">Formatos: .xls (plantilla), .csv</div>
          </div>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-cloud-upload me-1"></i>Importar registros
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php if ($resultadoImportEventos !== null): ?>
<div class="card border-0 shadow-sm mt-4">
  <div class="card-header bg-white py-3">
    <h3 class="h6 mb-0"><i class="bi bi-clipboard-check me-2"></i>Resultado de la importación</h3>
  </div>
  <div class="card-body">
    <div class="d-flex flex-wrap gap-3 mb-3">
      <span class="badge bg-success fs-6"><?= (int) ($resultadoImportEventos['importados'] ?? 0) ?> importado(s)</span>
      <?php if ((int) ($resultadoImportEventos['omitidos'] ?? 0) > 0): ?>
      <span class="badge bg-warning text-dark fs-6"><?= (int) ($resultadoImportEventos['omitidos'] ?? 0) ?> con error</span>
      <?php endif; ?>
    </div>

    <?php if (!empty($resultadoImportEventos['errores'])): ?>
    <div class="table-responsive">
      <table class="table table-sm table-bordered mb-0">
        <thead class="table-light">
          <tr>
            <th>Fila</th>
            <th>Error</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($resultadoImportEventos['errores'] as $errorFila): ?>
          <tr>
            <td class="text-nowrap"><?= (int) ($errorFila['fila'] ?? 0) ?></td>
            <td><?= htmlspecialchars((string) ($errorFila['mensaje'] ?? '')) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php elseif ((int) ($resultadoImportEventos['importados'] ?? 0) > 0): ?>
    <p class="text-muted small mb-0">Todos los registros del archivo se importaron correctamente.</p>
    <?php else: ?>
    <p class="text-muted small mb-0">No se importó ningún registro. Revise los errores del archivo.</p>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>
