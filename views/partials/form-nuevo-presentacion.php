<form method="POST" action="acciones.php" class="row g-3 js-form-registro" id="formNuevoPresentacion" data-mensaje-exito="Presentación registrada correctamente.">
  <input type="hidden" name="accion" value="crear_presentacion">

  <?php
  $numeroRepresentante = 1;
  $esObligatorio = true;
  include __DIR__ . '/campos-representante-presentacion.php';

  $numeroRepresentante = 2;
  $esObligatorio = false;
  include __DIR__ . '/campos-representante-presentacion.php';
  ?>

  <div class="col-12">
    <p class="form-section-label mb-0 mt-2">Niños/as a presentar</p>
    <div class="form-text">Puedes registrar varios hermanos/as con los mismos representantes.</div>
  </div>

  <div id="presentadosPresentacion" class="col-12">
    <div class="row g-3">
      <?php
      $indicePresentado = 0;
      include __DIR__ . '/campos-presentado-presentacion.php';
      ?>
    </div>
  </div>

  <div class="col-12">
    <button type="button" class="btn btn-outline-primary btn-sm js-agregar-presentado">
      <i class="bi bi-plus-lg me-1"></i>Agregar otro presentado
    </button>
  </div>

  <div class="col-12">
    <button type="submit" class="btn btn-primary">
      <i class="bi bi-save me-1"></i>Guardar
    </button>
  </div>
</form>
<?php
$presentacionJs = dirname(__DIR__, 2) . '/js/presentacion-ninos.js';
$presentacionJsVersion = is_file($presentacionJs) ? (string) filemtime($presentacionJs) : '1';
?>
<script src="js/presentacion-ninos.js?v=<?= htmlspecialchars($presentacionJsVersion) ?>"></script>
