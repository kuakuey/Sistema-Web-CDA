<?php
/**
 * Cabecera estándar de tablas de registros.
 *
 * Variables: $mostrarTipo, $mostrarValor, $mostrarObservacion, $mostrarEstado,
 *            $mostrarTelefono (bool, default true), $mostrarNumero (bool, default true),
 *            $layoutBautismo (bool), $layoutPresentacion (bool), $etiquetaFecha (string, default Fecha)
 */
$mostrarTipo = !empty($mostrarTipo);
$mostrarValor = !empty($mostrarValor);
$mostrarObservacion = !empty($mostrarObservacion);
$mostrarEstado = !empty($mostrarEstado);
$mostrarTelefono = !isset($mostrarTelefono) || !empty($mostrarTelefono);
$mostrarNumero = !isset($mostrarNumero) || !empty($mostrarNumero);
$layoutBautismo = !empty($layoutBautismo);
$layoutPresentacion = !empty($layoutPresentacion);
$etiquetaFecha = $etiquetaFecha ?? 'Fecha';
?>
<tr>
  <?php if ($mostrarNumero): ?><th class="text-center col-numero">#</th><?php endif; ?>
  <?php if ($mostrarTipo): ?><th>Tipo</th><?php endif; ?>
  <th>Nombre</th>
  <?php if ($layoutPresentacion): ?>
  <th>Contactos</th>
  <?php if ($mostrarEstado): ?><th>Estado</th><?php endif; ?>
  <th>Fecha de Presentación</th>
  <th><?= htmlspecialchars($etiquetaFecha) ?></th>
  <?php elseif ($layoutBautismo): ?>
  <?php if ($mostrarTelefono): ?><th>Teléfono</th><?php endif; ?>
  <?php if ($mostrarEstado): ?><th>Estado</th><?php endif; ?>
  <th>Fecha de bautizo</th>
  <th><?= htmlspecialchars($etiquetaFecha) ?></th>
  <?php else: ?>
  <th><?= htmlspecialchars($etiquetaFecha) ?></th>
  <?php if ($mostrarTelefono): ?><th>Teléfono</th><?php endif; ?>
  <?php if ($mostrarValor): ?><th>Valor</th><?php endif; ?>
  <?php if ($mostrarObservacion): ?><th>Obs.</th><?php endif; ?>
  <?php if ($mostrarEstado): ?><th>Estado</th><?php endif; ?>
  <?php endif; ?>
  <th class="text-end">Acciones</th>
</tr>
