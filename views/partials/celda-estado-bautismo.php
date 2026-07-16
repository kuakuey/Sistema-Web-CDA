<?php
/**
 * Estado de bautismo (Ingresado / Bautizado + fecha).
 * Variables: $fila, $urlRedireccion, $puedeEditarEstado, $esSuperadmin
 */
$puedeEditarEstado = !empty($puedeEditarEstado);
$esSuperadmin = !empty($esSuperadmin);
$estado = (string) ($fila['estado_bautismo'] ?? 'ingresado');
$bloqueado = !empty($fila['estado_bautismo_bloqueado']);
$puedeCambiar = $puedeEditarEstado && ($esSuperadmin || !$bloqueado);
$formId = 'estado-bautismo-' . (int) $fila['id'];
$puedeVolverIngresado = $esSuperadmin || $estado !== 'bautizado';
?>
<?php if ($puedeCambiar): ?>
<form method="POST" action="acciones.php" class="m-0 js-form-estado-bautismo" id="<?= htmlspecialchars($formId) ?>" novalidate data-sin-bloqueo="1">
  <input type="hidden" name="accion" value="actualizar_estado_bautismo">
  <input type="hidden" name="id" value="<?= (int) $fila['id'] ?>">
  <input type="hidden" name="redireccion" value="<?= htmlspecialchars($urlRedireccion) ?>">
  <div class="d-flex flex-column gap-1" style="min-width: 10rem;">
    <select
      name="estado_bautismo"
      class="form-select form-select-sm js-estado-bautismo-select"
      onchange="cdaEstadoBautismoCambio(this)"
    >
      <?php if ($puedeVolverIngresado): ?>
      <option value="ingresado" <?= $estado === 'ingresado' ? 'selected' : '' ?>>Ingresado</option>
      <?php endif; ?>
      <option value="bautizado" <?= $estado === 'bautizado' ? 'selected' : '' ?>>Bautizado</option>
    </select>
    <input
      type="date"
      name="fecha_bautismo"
      class="form-control form-control-sm js-fecha-bautismo"
      value="<?= htmlspecialchars((string) ($fila['fecha_bautismo'] ?? '')) ?>"
      max="<?= date('Y-m-d') ?>"
      style="<?= $estado === 'bautizado' ? '' : 'display:none' ?>"
      onchange="cdaFechaBautismoCambio(this)"
      oninput="cdaFechaBautismoCambio(this)"
    >
  </div>
</form>
<?php else: ?>
<?php if ($estado === 'bautizado'): ?>
<span class="badge bg-success">Bautizado</span>
<?php if (!empty($fila['fecha_bautismo'])): ?>
<div class="small text-muted mt-1"><?= htmlspecialchars(formatearFechaTabla($fila['fecha_bautismo'])) ?></div>
<?php endif; ?>
<?php else: ?>
<span class="badge bg-secondary">Ingresado</span>
<?php endif; ?>
<?php endif; ?>
