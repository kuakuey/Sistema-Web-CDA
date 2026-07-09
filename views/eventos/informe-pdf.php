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
      font-weight: bold;
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
  </style>
</head>
<body>
  <h1>Informe de evento</h1>
  <p class="subtitulo"><?= htmlspecialchars($informe['evento']['nombre'] ?? 'Evento') ?> · Generado el <?= htmlspecialchars($informe['generado_en_etiqueta']) ?></p>

  <h2>Datos del evento</h2>
  <table class="meta">
    <tr>
      <td><span class="resumen-label">Nombre</span><span class="resumen-valor"><?= htmlspecialchars($informe['evento']['nombre'] ?? '—') ?></span></td>
      <td><span class="resumen-label">Fecha del evento</span><span class="resumen-valor"><?= htmlspecialchars($informe['evento_fecha_etiqueta']) ?></span></td>
    </tr>
    <tr>
      <td><span class="resumen-label">Tipo</span><span class="resumen-valor"><?= htmlspecialchars($informe['evento_tipo_etiqueta']) ?></span></td>
      <td><span class="resumen-label">Valor del evento</span><span class="resumen-valor"><?= htmlspecialchars($informe['evento_valor_etiqueta']) ?></span></td>
    </tr>
    <tr>
      <td><span class="resumen-label">Requiere numeración</span><span class="resumen-valor"><?= htmlspecialchars($informe['evento_numeracion_etiqueta']) ?></span></td>
      <td><span class="resumen-label">Estado</span><span class="resumen-valor"><?= htmlspecialchars($informe['evento_estado_etiqueta']) ?></span></td>
    </tr>
  </table>

  <h2>Resumen de participantes</h2>
  <table class="resumen">
    <tr>
      <td>
        <span class="resumen-label">Total participantes</span>
        <span class="resumen-valor"><?= (int) ($informe['resumen']['total_participantes'] ?? 0) ?></span>
      </td>
      <td>
        <span class="resumen-label">Total recaudado</span>
        <span class="resumen-valor"><?= htmlspecialchars(formatearMonto((float) ($informe['resumen']['total_monto'] ?? 0))) ?></span>
      </td>
      <td colspan="2">
        <span class="resumen-label">Por forma de pago</span>
        <span class="resumen-valor">
          <?php if (empty($informe['resumen']['por_forma_pago'])): ?>
          —
          <?php else: ?>
          <?php
          $partesFormaPago = [];
          foreach ($informe['resumen']['por_forma_pago'] as $formaPago => $datosPago) {
              $partesFormaPago[] = htmlspecialchars($formaPago)
                  . ': '
                  . (int) $datosPago['cantidad']
                  . ' ('
                  . htmlspecialchars(formatearMonto((float) $datosPago['monto']))
                  . ')';
          }
          echo implode(' · ', $partesFormaPago);
          ?>
          <?php endif; ?>
        </span>
      </td>
    </tr>
  </table>

  <h2>Participantes registrados</h2>
  <?php if (empty($informe['registros'])): ?>
  <p class="vacio">No hay participantes registrados para este evento.</p>
  <?php else: ?>
  <table class="datos">
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Numeración</th>
        <th>Fecha</th>
        <th>Teléfono</th>
        <th>Observación</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($informe['registros'] as $registro): ?>
      <tr>
        <td><?= htmlspecialchars($registro['nombre']) ?></td>
        <td><?= htmlspecialchars($registro['numeracion'] ?? '—') ?></td>
        <td><?= htmlspecialchars(formatearFechaInforme($registro['fecha'])) ?></td>
        <td><?= htmlspecialchars($registro['telefono']) ?></td>
        <td><?= !empty($registro['observacion']) ? htmlspecialchars($registro['observacion']) : '—' ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</body>
</html>
