<h2>Datos del evento</h2>
<table class="meta">
  <tr>
    <td><span class="resumen-label">Fecha del evento</span><span class="resumen-valor"><?= htmlspecialchars($informe['evento_fecha_etiqueta']) ?></span></td>
    <td><span class="resumen-label">Estado</span><span class="resumen-valor"><?= htmlspecialchars($informe['evento_estado_etiqueta']) ?></span></td>
  </tr>
  <tr>
    <td><span class="resumen-label">Requiere numeración</span><span class="resumen-valor"><?= htmlspecialchars($informe['evento_numeracion_etiqueta']) ?></span></td>
    <td><span class="resumen-label">Total participantes</span><span class="resumen-valor"><?= (int) ($informe['resumen']['total_participantes'] ?? 0) ?></span></td>
  </tr>
</table>

<h2>Resumen financiero</h2>
<table class="resumen resumen-evento">
  <tr>
    <td>
      <span class="resumen-label">Falta por cancelar</span>
      <span class="resumen-valor destacado"><?= htmlspecialchars(formatearMonto((float) ($informe['resumen']['monto_por_cancelar'] ?? 0))) ?></span>
    </td>
    <td>
      <span class="resumen-label">Recaudado</span>
      <span class="resumen-valor destacado"><?= htmlspecialchars(formatearMonto((float) ($informe['resumen']['monto_recaudado'] ?? 0))) ?></span>
    </td>
    <td>
      <span class="resumen-label">Total</span>
      <span class="resumen-valor destacado"><?= htmlspecialchars(formatearMonto((float) ($informe['resumen']['monto_total'] ?? 0))) ?></span>
    </td>
    <td>
      <span class="resumen-label">Entradas registradas</span>
      <span class="resumen-valor"><?= (int) ($informe['resumen']['total_entradas'] ?? 0) ?></span>
    </td>
  </tr>
</table>

<h2>Entradas por tipo</h2>
<?php if (empty($informe['resumen']['por_tipo_entrada'])): ?>
<p class="vacio">No hay tipos de entrada configurados.</p>
<?php else: ?>
<table class="datos">
  <thead>
    <tr>
      <th>Tipo de entrada</th>
      <th>Cantidad vendida</th>
      <th>Valor catálogo</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($informe['resumen']['por_tipo_entrada'] as $tipoResumen): ?>
    <tr>
      <td><?= htmlspecialchars($tipoResumen['nombre'] ?? '—') ?></td>
      <td><strong><?= (int) ($tipoResumen['cantidad'] ?? 0) ?></strong></td>
      <td><?= htmlspecialchars($tipoResumen['valor_catalogo'] ?? '—') ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<h2>Participantes registrados</h2>
<?php if (empty($informe['registros_por_tipo'])): ?>
<p class="vacio">No hay participantes registrados para este evento.</p>
<?php else: ?>
  <?php
  $camposAdicionalesInforme = obtenerCamposAdicionalesParaInformeEvento($informe);
  foreach ($informe['registros_por_tipo'] as $grupo):
  ?>
  <h3><?= htmlspecialchars($grupo['tipo_entrada'] ?? 'Entrada') ?> (<?= count($grupo['registros'] ?? []) ?>)</h3>
  <table class="datos">
    <thead>
      <tr>
        <th>Numeración</th>
        <th>Nombre</th>
        <th>Valor</th>
        <th>Estado</th>
        <th>Forma de pago</th>
        <th>Fecha</th>
        <?php foreach ($camposAdicionalesInforme as $campoAdicional): ?>
        <th><?= htmlspecialchars($campoAdicional['etiqueta']) ?></th>
        <?php endforeach; ?>
        <th>Observación</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($grupo['registros'] as $registro): ?>
      <tr>
        <td><?= htmlspecialchars(trim((string) ($registro['numeracion'] ?? '')) !== '' ? $registro['numeracion'] : '—') ?></td>
        <td><?= htmlspecialchars($registro['nombre']) ?></td>
        <td><strong><?= htmlspecialchars(formatearMonto((float) ($registro['valor'] ?? 0))) ?></strong></td>
        <td><?= htmlspecialchars(etiquetaEstadoPagoRegistroEvento($registro)) ?></td>
        <td><?= htmlspecialchars(etiquetaFormaPagoEvento($registro['forma_pago'] ?? null)) ?></td>
        <td><?= htmlspecialchars(formatearFechaInforme($registro['fecha'])) ?></td>
        <?php foreach ($camposAdicionalesInforme as $campoAdicional): ?>
        <td><?= htmlspecialchars(valorInfoAdicionalPorCampoInforme($registro['info_adicional'] ?? '', $campoAdicional)) ?></td>
        <?php endforeach; ?>
        <td><?= !empty($registro['observacion']) ? htmlspecialchars($registro['observacion']) : '—' ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endforeach; ?>
<?php endif; ?>
