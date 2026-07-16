<?php
/**
 * Estado de bautismo en tabla: combobox solo en Ingresado; Bautizado muestra etiqueta con fecha.
 * Variables: $fila, $urlRedireccion, $puedeEditarEstado
 */
require_once __DIR__ . '/../../includes/filters.php';

$puedeEditarEstado = !empty($puedeEditarEstado);
$estado = (string) ($fila['estado_bautismo'] ?? 'ingresado');
$bloqueado = !empty($fila['estado_bautismo_bloqueado']);
$puedeUsarCombobox = $puedeEditarEstado && $estado === 'ingresado' && !$bloqueado;
?>
<?php if ($puedeUsarCombobox): ?>
<form method="POST" action="acciones.php" class="m-0" novalidate data-sin-bloqueo="1">
  <input type="hidden" name="accion" value="actualizar_estado_bautismo">
  <input type="hidden" name="id" value="<?= (int) $fila['id'] ?>">
  <input type="hidden" name="redireccion" value="<?= htmlspecialchars($urlRedireccion) ?>">
  <select
    name="estado_bautismo"
    class="form-select form-select-sm"
    style="min-width: 9rem;"
    onchange="if (this.value === 'bautizado') { this.form.submit(); }"
  >
    <option value="ingresado" selected>Ingresado</option>
    <option value="bautizado">Bautizado</option>
  </select>
</form>
<?php elseif ($estado === 'bautizado'): ?>
<span class="badge bg-success"><?= htmlspecialchars(etiquetaEstadoBautismoRegistro($fila)) ?></span>
<?php else: ?>
<span class="badge bg-secondary">Ingresado</span>
<?php endif; ?>
