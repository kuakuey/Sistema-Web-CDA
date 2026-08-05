<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <style>
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 10px;
      color: #212529;
      line-height: 1.35;
    }

    h1 {
      font-size: 16px;
      margin: 0 0 6px;
      text-align: center;
    }

    .subtitulo {
      text-align: center;
      color: #6c757d;
      margin: 0 0 16px;
    }

    h2 {
      font-size: 12px;
      margin: 18px 0 8px;
      padding-bottom: 4px;
      border-bottom: 1px solid #dee2e6;
    }

    h3 {
      font-size: 11px;
      margin: 14px 0 6px;
      color: #495057;
    }

    .resumen {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 12px;
    }

    .resumen td {
      width: 25%;
      border: 1px solid #dee2e6;
      padding: 8px;
      vertical-align: top;
    }

    .resumen-label {
      display: block;
      font-size: 9px;
      color: #6c757d;
      margin-bottom: 3px;
    }

    .resumen-valor {
      font-size: 12px;
    }

    table.datos {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }

    table.datos th,
    table.datos td {
      border: 1px solid #dee2e6;
      padding: 5px 6px;
      text-align: left;
      vertical-align: top;
    }

    table.datos th {
      background: #f8f9fa;
      font-size: 9px;
      text-transform: uppercase;
    }

    table.meta {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 14px;
    }

    table.meta td {
      border: 1px solid #dee2e6;
      padding: 6px 8px;
      width: 50%;
    }

    .vacio {
      color: #6c757d;
      font-style: italic;
    }

    .destacado {
      font-weight: bold;
    }
  </style>
</head>
<body>
  <h1>Informe de evento</h1>
  <p class="subtitulo"><?= htmlspecialchars($informe['evento']['nombre'] ?? 'Evento') ?> · Generado el <?= htmlspecialchars($informe['generado_en_etiqueta']) ?></p>

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
  <table class="resumen">
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
    <?php foreach ($informe['registros_por_tipo'] as $grupo): ?>
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
          <td><?= !empty($registro['observacion']) ? htmlspecialchars($registro['observacion']) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endforeach; ?>
  <?php endif; ?>
</body>
</html>
