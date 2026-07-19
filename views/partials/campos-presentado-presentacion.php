<?php
/**
 * Bloque de un niño/a presentado.
 *
 * Variables:
 * - $indicePresentado (int)
 */
$indicePresentado = (int) ($indicePresentado ?? 0);
$prefijoCampo = 'presentados[' . $indicePresentado . ']';
$prefijoIds = 'presentado_' . $indicePresentado . '_';
$nombreCampo = $prefijoCampo . '[nombre]';
$nombreDia = $prefijoCampo . '[dia_nacimiento]';
$nombreMes = $prefijoCampo . '[mes_nacimiento]';
$nombreAnio = $prefijoCampo . '[anio_nacimiento]';
$mostrarAyudaEdad = $indicePresentado === 0;
?>
<div class="col-12 js-bloque-presentado">
  <div class="presentado-bloque border rounded p-3">
    <div class="row g-3">
      <div class="col-12 d-flex justify-content-between align-items-center">
        <p class="form-section-label mb-0 js-etiqueta-presentado">Presentado <?= $indicePresentado + 1 ?></p>
        <button type="button" class="btn btn-sm btn-outline-secondary js-quitar-presentado">
          <i class="bi bi-x-lg me-1"></i>Quitar
        </button>
      </div>

      <div class="col-md-6">
        <label class="form-label" for="<?= htmlspecialchars($prefijoIds) ?>nombre">Nombre del presentado <span class="text-danger">*</span></label>
        <input
          type="text"
          class="form-control js-campo-nombre-presentado"
          id="<?= htmlspecialchars($prefijoIds) ?>nombre"
          name="<?= htmlspecialchars($nombreCampo) ?>"
          required
          maxlength="100"
        >
      </div>

      <?php include __DIR__ . '/campos-fecha-nacimiento.php'; ?>
    </div>
  </div>
</div>
