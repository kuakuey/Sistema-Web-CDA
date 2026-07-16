<div class="informe-page">
  <div class="mb-3">
    <h2 class="h4 mb-1">Generar informe</h2>
    <p class="text-muted small mb-0">Configura los filtros, elige el tipo de informe y descárgalo en PDF o Excel</p>
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

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
      <h3 class="h6 mb-0"><i class="bi bi-file-earmark-bar-graph me-2"></i>Descargar informe</h3>
    </div>
    <div class="card-body">
      <p class="text-muted small mb-4">
        El informe filtra por la fecha en que se registró cada ofrenda, evento o valor adicional en el sistema,
        no por la fecha de ofrenda o del evento. Si dejas vacías las fechas, se incluirán todos los registros.
      </p>

      <form method="GET" action="generar-informe.php" class="row g-3" id="formInformeFiltros">
        <input type="hidden" name="generar" value="1">
        <input type="hidden" name="formato" id="informeFormato" value="pdf">

        <div class="col-12">
          <h4 class="h6 text-muted mb-0">Filtros</h4>
        </div>

        <div class="col-md-4">
          <label class="form-label" for="fecha_desde">Desde</label>
          <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" value="<?= htmlspecialchars($fechaDesde) ?>">
        </div>

        <div class="col-md-4">
          <label class="form-label" for="fecha_hasta">Hasta</label>
          <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" value="<?= htmlspecialchars($fechaHasta) ?>">
        </div>

        <div class="col-md-4">
          <label class="form-label" for="turno">Jornada</label>
          <select class="form-select" id="turno" name="turno">
            <?php foreach ($etiquetasTurno as $clave => $etiqueta): ?>
            <option value="<?= htmlspecialchars($clave) ?>" <?= $turno === $clave ? 'selected' : '' ?>>
              <?= htmlspecialchars($etiqueta) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-12 js-campo-sin-entregar" style="<?= $seccion === 'ofrendas' ? '' : 'display:none' ?>">
          <div class="form-check">
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

        <div class="col-12">
          <hr class="my-1">
        </div>

        <div class="col-md-4">
          <label class="form-label" for="seccion">Informe</label>
          <select class="form-select" id="seccion" name="seccion">
            <?php foreach ($etiquetasSeccionInforme as $clave => $etiqueta): ?>
            <option value="<?= htmlspecialchars($clave) ?>" <?= $seccion === $clave ? 'selected' : '' ?>>
              <?= htmlspecialchars($etiqueta) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-4 js-campo-evento" style="<?= $seccion === 'eventos' ? '' : 'display:none' ?>">
          <label class="form-label" for="evento_id">Evento</label>
          <select class="form-select" id="evento_id" name="evento_id">
            <option value="">Todos los eventos</option>
            <?php foreach ($eventos ?? [] as $evento): ?>
            <option
              value="<?= (int) $evento['id'] ?>"
              <?= (int) ($eventoId ?? 0) === (int) $evento['id'] ? 'selected' : '' ?>
            >
              <?= htmlspecialchars($evento['nombre']) ?> (<?= (int) ($evento['total_registros'] ?? 0) ?> registro(s))
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-12">
          <div class="d-flex flex-wrap gap-2 pt-2">
            <button type="button" class="btn btn-primary js-descargar-informe" data-formato="pdf">
              <i class="bi bi-file-earmark-pdf me-1"></i>PDF
            </button>
            <button type="button" class="btn btn-success js-descargar-informe" data-formato="excel">
              <i class="bi bi-file-earmark-excel me-1"></i>Excel
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function () {
  var formulario = document.getElementById('formInformeFiltros');
  var campoFormato = document.getElementById('informeFormato');
  var selectorSeccion = document.getElementById('seccion');
  var campoEvento = document.querySelector('.js-campo-evento');
  var campoSinEntregar = document.querySelector('.js-campo-sin-entregar');
  var checkboxSinEntregar = document.getElementById('mostrar_sin_entregar');

  if (!formulario || !campoFormato || !selectorSeccion) {
    return;
  }

  function actualizarCamposDependientes() {
    var seccion = selectorSeccion.value;

    if (campoEvento) {
      campoEvento.style.display = seccion === 'eventos' ? '' : 'none';
    }

    if (campoSinEntregar) {
      var mostrarOfrendas = seccion === 'ofrendas';
      campoSinEntregar.style.display = mostrarOfrendas ? '' : 'none';

      if (!mostrarOfrendas && checkboxSinEntregar) {
        checkboxSinEntregar.checked = false;
      }
    }
  }

  selectorSeccion.addEventListener('change', actualizarCamposDependientes);
  actualizarCamposDependientes();

  document.querySelectorAll('.js-descargar-informe').forEach(function (boton) {
    boton.addEventListener('click', function () {
      campoFormato.value = boton.getAttribute('data-formato') || 'pdf';
      formulario.submit();
    });
  });
})();
</script>
