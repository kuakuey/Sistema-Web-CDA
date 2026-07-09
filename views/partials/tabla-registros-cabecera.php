<?php
/**
 * Cabecera estándar de tablas de registros.
 *
 * Variables: $mostrarTipo, $mostrarValor, $mostrarObservacion, $mostrarEstado,
 *            $mostrarTelefono (bool, default true)
 */
$mostrarTipo = !empty($mostrarTipo);
$mostrarValor = !empty($mostrarValor);
$mostrarObservacion = !empty($mostrarObservacion);
$mostrarEstado = !empty($mostrarEstado);
$mostrarTelefono = !isset($mostrarTelefono) || !empty($mostrarTelefono);
?>
<tr>
  <th class="text-center col-numero">#</th>
  <?php if ($mostrarTipo): ?><th>Tipo</th><?php endif; ?>
  <th>Nombre</th>
  <th>Fecha</th>
  <?php if ($mostrarTelefono): ?><th>Teléfono</th><?php endif; ?>
  <?php if ($mostrarValor): ?><th>Valor</th><?php endif; ?>
  <?php if ($mostrarObservacion): ?><th>Obs.</th><?php endif; ?>
  <?php if ($mostrarEstado): ?><th>Estado</th><?php endif; ?>
  <th class="text-end">Acciones</th>
</tr>
