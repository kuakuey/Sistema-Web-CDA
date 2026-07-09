<form method="POST" action="acciones.php" class="row g-3 js-form-registro" id="formNuevoPresentacion" data-mensaje-exito="Presentación registrada correctamente.">
  <input type="hidden" name="accion" value="crear_presentacion">

  <div class="col-12">
    <p class="form-section-label mb-0">Datos del Representante Legal</p>
  </div>

  <div class="col-md-6">
    <label class="form-label" for="nombre_padre">Nombre Papá <span class="text-danger">*</span> <span class="text-muted fw-normal">(obligatorio)</span></label>
    <input type="text" class="form-control" id="nombre_padre" name="nombre_padre" required maxlength="100">
  </div>

  <div class="col-md-6">
    <label class="form-label" for="telefono_papa">Teléfono Papá</label>
    <input type="tel" class="form-control" id="telefono_papa" name="telefono_papa" required maxlength="30">
  </div>

  <div class="col-12">
    <p class="form-section-label mb-0 mt-2">Datos del segundo representante legal</p>
  </div>

  <div class="col-md-6">
    <label class="form-label" for="nombre_madre">Nombre Mamá</label>
    <input type="text" class="form-control" id="nombre_madre" name="nombre_madre" required maxlength="100">
  </div>

  <div class="col-md-6">
    <label class="form-label" for="telefono_mama">Teléfono Mamá</label>
    <input type="tel" class="form-control" id="telefono_mama" name="telefono_mama" required maxlength="30">
  </div>

  <div class="col-12">
    <p class="form-section-label mb-0 mt-2">Datos del Presentado</p>
  </div>

  <div class="col-md-6">
    <label class="form-label" for="nombre_presentado">Nombre del presentado</label>
    <input type="text" class="form-control" id="nombre_presentado" name="nombre_presentado" required maxlength="100">
  </div>

  <?php include __DIR__ . '/campos-fecha-nacimiento.php'; ?>

  <div class="col-12">
    <button type="submit" class="btn btn-primary">
      <i class="bi bi-save me-1"></i>Guardar
    </button>
  </div>
</form>
