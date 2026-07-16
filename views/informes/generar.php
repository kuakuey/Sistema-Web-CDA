<div class="informe-page">
  <div class="mb-3">
    <h2 class="h4 mb-1">Generar informe</h2>
    <p class="text-muted small mb-0">Descarga informes generales o por sección en PDF o Excel</p>
  </div>

  <?php if ($error): ?>
  <div class="alert alert-danger">
    <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?>
  </div>
  <?php endif; ?>

  <?php if ($errorBd): ?>
  <div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($errorBd) ?>
  </div>
  <?php endif; ?>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
      <h3 class="h6 mb-0"><i class="bi bi-calendar-range me-2"></i>Filtros del informe</h3>
    </div>
    <div class="card-body">
      <p class="text-muted small mb-3">
        El informe filtra por la fecha en que se registró cada ofrenda, evento o valor adicional en el sistema,
        no por la fecha de ofrenda o del evento.
      </p>
      <form method="GET" action="generar-informe.php" class="row g-3 align-items-end" id="formInformeFiltros">
        <input type="hidden" name="generar" value="1">
        <input type="hidden" name="seccion" id="informeSeccion" value="completo">
        <input type="hidden" name="formato" id="informeFormato" value="pdf">

        <div class="col-md-3">
          <label class="form-label" for="fecha_desde">Desde <span class="text-danger">*</span></label>
          <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" required value="<?= htmlspecialchars($fechaDesde) ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label" for="fecha_hasta">Hasta <span class="text-danger">*</span></label>
          <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" required value="<?= htmlspecialchars($fechaHasta) ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label" for="turno">Jornada</label>
          <select class="form-select" id="turno" name="turno">
            <?php foreach ($etiquetasTurno as $clave => $etiqueta): ?>
            <option value="<?= htmlspecialchars($clave) ?>" <?= $turno === $clave ? 'selected' : '' ?>>
              <?= htmlspecialchars($etiqueta) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label" for="estado">Estado (ofrendas)</label>
          <select class="form-select" id="estado" name="estado">
            <?php foreach ($etiquetasEstado as $clave => $etiqueta): ?>
            <option value="<?= htmlspecialchars($clave) ?>" <?= $estado === $clave ? 'selected' : '' ?>>
              <?= htmlspecialchars($etiqueta) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-6">
          <div class="form-check mt-2">
            <input
              class="form-check-input"
              type="checkbox"
              id="mostrar_sin_entregar"
              name="mostrar_sin_entregar"
              value="1"
              <?= !empty($mostrarSinEntregar) ? 'checked' : '' ?>
            >
            <label class="form-check-label" for="mostrar_sin_entregar">
              Incluir casas de vida que no entregaron (solo en informe de ofrendas)
            </label>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
      <h3 class="h6 mb-0"><i class="bi bi-download me-2"></i>Descargas</h3>
    </div>
    <div class="card-body">
      <div class="mb-4">
        <h4 class="h6 text-muted mb-2">Informe general</h4>
        <div class="d-flex flex-wrap gap-2">
          <button type="button" class="btn btn-primary btn-sm js-descargar-informe" data-seccion="completo" data-formato="pdf">
            <i class="bi bi-file-earmark-pdf me-1"></i>PDF completo
          </button>
          <button type="button" class="btn btn-success btn-sm js-descargar-informe" data-seccion="completo" data-formato="excel">
            <i class="bi bi-file-earmark-excel me-1"></i>Excel completo
          </button>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-md-4">
          <h4 class="h6 mb-2">Ofrendas</h4>
          <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm js-descargar-informe" data-seccion="ofrendas" data-formato="pdf">PDF</button>
            <button type="button" class="btn btn-outline-success btn-sm js-descargar-informe" data-seccion="ofrendas" data-formato="excel">Excel</button>
          </div>
        </div>
        <div class="col-md-4">
          <h4 class="h6 mb-2">Eventos</h4>
          <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm js-descargar-informe" data-seccion="eventos" data-formato="pdf">PDF</button>
            <button type="button" class="btn btn-outline-success btn-sm js-descargar-informe" data-seccion="eventos" data-formato="excel">Excel</button>
          </div>
        </div>
        <div class="col-md-4">
          <h4 class="h6 mb-2">Valores adicionales</h4>
          <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm js-descargar-informe" data-seccion="valores" data-formato="pdf">PDF</button>
            <button type="button" class="btn btn-outline-success btn-sm js-descargar-informe" data-seccion="valores" data-formato="excel">Excel</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var formulario = document.getElementById('formInformeFiltros');
  var campoSeccion = document.getElementById('informeSeccion');
  var campoFormato = document.getElementById('informeFormato');

  if (!formulario || !campoSeccion || !campoFormato) {
    return;
  }

  document.querySelectorAll('.js-descargar-informe').forEach(function (boton) {
    boton.addEventListener('click', function () {
      if (!formulario.reportValidity()) {
        return;
      }

      campoSeccion.value = boton.getAttribute('data-seccion') || 'completo';
      campoFormato.value = boton.getAttribute('data-formato') || 'pdf';
      formulario.submit();
    });
  });
})();
</script>
