<?php
/**
 * Estado de presentación: combobox hasta Presentado; luego solo etiqueta.
 * Variables: $fila, $urlRedireccion, $estadosPresentacion, $etiquetasEstadosPresentacion, $puedeEditarEstado
 */
require_once __DIR__ . '/../../includes/filters.php';

$puedeEditarEstado = !empty($puedeEditarEstado);
$estado = (string) ($fila['estado'] ?? 'recibido');
$bloqueado = !empty($fila['estado_bloqueado']);
$puedeUsarCombobox = $puedeEditarEstado && $estado !== 'presentado' && !$bloqueado;
?>
<?php if ($puedeUsarCombobox): ?>
<form method="POST" action="acciones.php" class="m-0" novalidate data-sin-bloqueo="1">
  <input type="hidden" name="accion" value="actualizar_estado_presentacion">
  <input type="hidden" name="id" value="<?= (int) $fila['id'] ?>">
  <input type="hidden" name="redireccion" value="<?= htmlspecialchars($urlRedireccion) ?>">
  <select name="estado" class="form-select form-select-sm" style="min-width: 8.5rem;" onchange="this.form.submit()">
    <?php foreach ($estadosPresentacion as $estadoOpcion): ?>
    <option value="<?= htmlspecialchars($estadoOpcion) ?>" <?= $estado === $estadoOpcion ? 'selected' : '' ?>>
      <?= htmlspecialchars($etiquetasEstadosPresentacion[$estadoOpcion] ?? $estadoOpcion) ?>
    </option>
    <?php endforeach; ?>
  </select>
</form>
<?php elseif ($estado === 'presentado'): ?>
<span class="badge bg-success">Presentado</span>
<?php else: ?>
<span class="badge bg-secondary"><?= htmlspecialchars($etiquetasEstadosPresentacion[$estado] ?? $estado) ?></span>
<?php endif; ?>
