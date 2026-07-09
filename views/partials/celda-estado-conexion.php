<?php
/**
 * Select de estado Conexión (Recibido / Contactado).
 * Variables: $fila, $urlRedireccion, $puedeEditarEstado
 */
$puedeEditarEstado = !empty($puedeEditarEstado);
$contactado = !empty($fila['contactado']);
?>
<?php if ($puedeEditarEstado): ?>
<form method="POST" action="acciones.php" class="m-0">
  <input type="hidden" name="accion" value="actualizar_estado_conexion">
  <input type="hidden" name="id" value="<?= (int) $fila['id'] ?>">
  <input type="hidden" name="redireccion" value="<?= htmlspecialchars($urlRedireccion) ?>">
  <select name="contactado" class="form-select form-select-sm" style="min-width: 8.5rem;" onchange="this.form.submit()">
    <option value="0" <?= !$contactado ? 'selected' : '' ?>>Recibido</option>
    <option value="1" <?= $contactado ? 'selected' : '' ?>>Contactado</option>
  </select>
</form>
<?php else: ?>
<?php if ($contactado): ?>
<span class="badge bg-success">Contactado</span>
<?php else: ?>
<span class="badge bg-secondary">Recibido</span>
<?php endif; ?>
<?php endif; ?>
