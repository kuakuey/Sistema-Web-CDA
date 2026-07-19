<?php
/**
 * Campos de un representante legal para presentación de niños.
 *
 * Variables:
 * - $numeroRepresentante (int): 1 o 2
 * - $representante (array{parentesco:string,nombre:string,telefono:string}|null)
 * - $esObligatorio (bool)
 */
require_once __DIR__ . '/../../includes/presentaciones.php';

$numeroRepresentante = (int) ($numeroRepresentante ?? 1);
$esObligatorio = !empty($esObligatorio);
$representante = $representante ?? ['parentesco' => '', 'nombre' => '', 'telefono' => ''];
$prefijo = 'representante_' . $numeroRepresentante . '_';
$idPrefijo = 'representante_' . $numeroRepresentante . '_';
$opcionesParentesco = opcionesParentescoRepresentante();
$titulo = $numeroRepresentante === 1
    ? 'Representante legal'
    : 'Segundo representante legal (opcional)';
?>
<div class="col-12">
  <p class="form-section-label mb-0<?= $numeroRepresentante === 2 ? ' mt-2' : '' ?>"><?= htmlspecialchars($titulo) ?></p>
</div>

<div class="col-md-4">
  <label class="form-label" for="<?= htmlspecialchars($idPrefijo) ?>parentesco">
    Parentesco<?= $esObligatorio ? ' <span class="text-danger">*</span>' : '' ?>
  </label>
  <select
    class="form-select"
    id="<?= htmlspecialchars($idPrefijo) ?>parentesco"
    name="<?= htmlspecialchars($prefijo) ?>parentesco"
    <?= $esObligatorio ? 'required' : '' ?>
  >
    <option value="">Seleccionar…</option>
    <?php foreach ($opcionesParentesco as $valor => $etiqueta): ?>
    <option value="<?= htmlspecialchars($valor) ?>"<?= ($representante['parentesco'] ?? '') === $valor ? ' selected' : '' ?>>
      <?= htmlspecialchars($etiqueta) ?>
    </option>
    <?php endforeach; ?>
  </select>
</div>

<div class="col-md-4">
  <label class="form-label" for="<?= htmlspecialchars($idPrefijo) ?>nombre">
    Nombre<?= $esObligatorio ? ' <span class="text-danger">*</span>' : '' ?>
  </label>
  <input
    type="text"
    class="form-control"
    id="<?= htmlspecialchars($idPrefijo) ?>nombre"
    name="<?= htmlspecialchars($prefijo) ?>nombre"
    maxlength="100"
    value="<?= htmlspecialchars($representante['nombre'] ?? '') ?>"
    <?= $esObligatorio ? 'required' : '' ?>
  >
</div>

<div class="col-md-4">
  <label class="form-label" for="<?= htmlspecialchars($idPrefijo) ?>telefono">
    Teléfono<?= $esObligatorio ? ' <span class="text-danger">*</span>' : '' ?>
  </label>
  <input
    type="tel"
    class="form-control"
    id="<?= htmlspecialchars($idPrefijo) ?>telefono"
    name="<?= htmlspecialchars($prefijo) ?>telefono"
    maxlength="30"
    value="<?= htmlspecialchars($representante['telefono'] ?? '') ?>"
    <?= $esObligatorio ? 'required' : '' ?>
  >
</div>
