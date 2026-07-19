<?php
/**
 * Campos día / mes / año de nacimiento.
 * Opcional: $prefijoIds, $nombreDia, $nombreMes, $nombreAnio, $mostrarAyudaEdad.
 */
$prefijoIds = $prefijoIds ?? '';
$nombreDia = $nombreDia ?? 'dia_nacimiento';
$nombreMes = $nombreMes ?? 'mes_nacimiento';
$nombreAnio = $nombreAnio ?? 'anio_nacimiento';
$mostrarAyudaEdad = !isset($mostrarAyudaEdad) || !empty($mostrarAyudaEdad);
$meses = obtenerMesesCalendario();
?>
<div class="col-12">
  <label class="form-label">Fecha de nacimiento <span class="text-danger">*</span></label>
</div>

<div class="col-md-4 col-lg-3">
  <label class="form-label small" for="<?= htmlspecialchars($prefijoIds) ?>dia_nacimiento">Día</label>
  <input
    type="number"
    class="form-control"
    id="<?= htmlspecialchars($prefijoIds) ?>dia_nacimiento"
    name="<?= htmlspecialchars($nombreDia) ?>"
    required
    min="1"
    max="31"
    step="1"
    placeholder="DD"
  >
</div>

<div class="col-md-4 col-lg-3">
  <label class="form-label small" for="<?= htmlspecialchars($prefijoIds) ?>mes_nacimiento">Mes</label>
  <select class="form-select" id="<?= htmlspecialchars($prefijoIds) ?>mes_nacimiento" name="<?= htmlspecialchars($nombreMes) ?>" required>
    <option value="">Mes…</option>
    <?php foreach ($meses as $numero => $nombre): ?>
    <option value="<?= (int) $numero ?>"><?= htmlspecialchars($nombre) ?></option>
    <?php endforeach; ?>
  </select>
</div>

<div class="col-md-4 col-lg-3">
  <label class="form-label small" for="<?= htmlspecialchars($prefijoIds) ?>anio_nacimiento">Año</label>
  <input
    type="number"
    class="form-control"
    id="<?= htmlspecialchars($prefijoIds) ?>anio_nacimiento"
    name="<?= htmlspecialchars($nombreAnio) ?>"
    required
    min="1900"
    max="<?= (int) date('Y') ?>"
    step="1"
    placeholder="AAAA"
  >
</div>

<?php if ($mostrarAyudaEdad): ?>
<div class="col-12">
  <div class="form-text">La edad se calcula con día, mes y año completos.</div>
</div>
<?php endif; ?>
