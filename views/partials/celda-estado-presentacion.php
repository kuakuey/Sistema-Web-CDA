<?php
/**
 * Select de estado presentación niños.
 * Variables: $fila, $urlRedireccion, $estadosPresentacion, $etiquetasEstadosPresentacion
 */
?>
<form method="POST" action="acciones.php" class="m-0">
  <input type="hidden" name="accion" value="actualizar_estado_presentacion">
  <input type="hidden" name="id" value="<?= (int) $fila['id'] ?>">
  <input type="hidden" name="redireccion" value="<?= htmlspecialchars($urlRedireccion) ?>">
  <select name="estado" class="form-select form-select-sm" style="min-width: 8.5rem;" onchange="this.form.submit()">
    <?php foreach ($estadosPresentacion as $estadoOpcion): ?>
    <option value="<?= htmlspecialchars($estadoOpcion) ?>" <?= ($fila['estado'] ?? '') === $estadoOpcion ? 'selected' : '' ?>>
      <?= htmlspecialchars($etiquetasEstadosPresentacion[$estadoOpcion] ?? $estadoOpcion) ?>
    </option>
    <?php endforeach; ?>
  </select>
</form>
