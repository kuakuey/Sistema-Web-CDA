<?php
/**
 * Formulario interno de ingreso. Variables: $seccion, $tipoRegistro, $archivoPagina,
 * $tiposPermitidos, $etiquetasFormulario, $zonas.
 */
$esConexion = $seccion === 'conexion' || ($seccion === 'generales' && ($tipoFormularioNuevo ?? '') === 'conexion');
?>
<form method="POST" action="acciones.php" class="row g-3 js-form-registro" id="formNuevoInscripcion" data-mensaje-exito="Registro guardado correctamente.">
  <input type="hidden" name="accion" value="crear_inscripcion">
  <input type="hidden" name="seccion" value="<?= htmlspecialchars($seccion) ?>">

  <?php if ($seccion === 'generales'): ?>
  <div class="col-md-6 col-lg-4">
    <label class="form-label" for="tipo_formulario">Tipo <span class="text-danger">*</span></label>
    <select class="form-select" id="tipo_formulario" name="tipo_formulario" required>
      <option value="">Seleccione…</option>
      <?php foreach ($tiposPermitidos as $tipo): ?>
      <option value="<?= htmlspecialchars($tipo) ?>">
        <?= htmlspecialchars($etiquetasFormulario[$tipo] ?? $tipo) ?>
      </option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php else: ?>
  <input type="hidden" name="tipo_formulario" value="<?= htmlspecialchars($seccion) ?>">
  <?php endif; ?>

  <div class="col-12 col-md-6">
    <label class="form-label" for="nombre">Nombre <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="nombre" name="nombre" required maxlength="100">
  </div>

  <div class="col-12 col-md-6">
    <label class="form-label" for="apellido">Apellido <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="apellido" name="apellido" required maxlength="100">
  </div>

  <div class="col-12">
    <label class="form-label" for="celular">Teléfono <span class="text-danger">*</span></label>
    <input type="tel" class="form-control" id="celular" name="celular" required maxlength="30">
  </div>

  <div class="col-12">
    <label class="form-label" for="email">Email</label>
    <input type="email" class="form-control" id="email" name="email" maxlength="100">
  </div>

  <div class="col-md-6 col-lg-4 js-campo-conexion" style="<?= $seccion === 'conexion' ? '' : 'display:none' ?>">
    <label class="form-label" for="zona">Zona <span class="text-danger">*</span></label>
    <select class="form-select" id="zona" name="zona" <?= $seccion === 'conexion' ? 'required' : '' ?>>
      <option value="">Seleccione…</option>
      <?php foreach ($zonas as $slug => $etiqueta): ?>
      <option value="<?= htmlspecialchars($slug) ?>"><?= htmlspecialchars($etiqueta) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-12 js-campo-conexion" style="<?= $seccion === 'conexion' ? '' : 'display:none' ?>">
    <label class="form-label" for="direccion">Dirección <span class="text-danger">*</span></label>
    <textarea class="form-control" id="direccion" name="direccion" rows="2" maxlength="500" <?= $seccion === 'conexion' ? 'required' : '' ?>></textarea>
  </div>

  <div class="col-12">
    <button type="submit" class="btn btn-primary">
      <i class="bi bi-save me-1"></i>Guardar
    </button>
  </div>
</form>
<?php if ($seccion === 'generales'): ?>
<script>
(function () {
  var tipoSelect = document.getElementById('tipo_formulario');
  var camposConexion = document.querySelectorAll('.js-campo-conexion');
  var zona = document.getElementById('zona');
  var direccion = document.getElementById('direccion');

  if (!tipoSelect) {
    return;
  }

  function actualizarCamposConexion() {
    var esConexion = tipoSelect.value === 'conexion';
    camposConexion.forEach(function (el) {
      el.style.display = esConexion ? '' : 'none';
    });
    if (zona) {
      zona.required = esConexion;
      if (!esConexion) {
        zona.value = '';
      }
    }
    if (direccion) {
      direccion.required = esConexion;
      if (!esConexion) {
        direccion.value = '';
      }
    }
  }

  tipoSelect.addEventListener('change', actualizarCamposConexion);
  actualizarCamposConexion();
})();
</script>
<?php endif; ?>
