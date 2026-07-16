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
      width: 33.33%;
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

    .vacio {
      color: #6c757d;
      font-style: italic;
    }

    .estado-pendiente {
      color: #856404;
    }
  </style>
</head>
<body>
<?php
$resumen = $informe['resumen'];
$seccionExportacion = normalizarSeccionInforme($informe['seccion_exportacion'] ?? 'completo');
$incluirOfrendas = in_array($seccionExportacion, ['completo', 'ofrendas'], true);
$incluirEventos = in_array($seccionExportacion, ['completo', 'eventos'], true);
$incluirValores = in_array($seccionExportacion, ['completo', 'valores'], true);
?>

  <h1><?= htmlspecialchars(tituloSeccionInforme($seccionExportacion)) ?></h1>
  <p class="subtitulo">
    Periodo de registro: <?= htmlspecialchars($informe['periodo_etiqueta'] ?? ($informe['fecha_desde_etiqueta'] . ' al ' . $informe['fecha_hasta_etiqueta'])) ?>
    · Jornada: <?= htmlspecialchars($informe['turno_etiqueta']) ?>
    <?php if ($incluirEventos && ($informe['evento_id'] ?? 0) > 0): ?>
    · Evento: <?= htmlspecialchars($informe['evento_etiqueta'] ?? '—') ?>
    <?php endif; ?>
    · Generado: <?= htmlspecialchars($informe['generado_en']) ?>
  </p>

  <?php if ($seccionExportacion === 'completo'): ?>
  <table class="resumen">
    <tr>
      <td>
        <span class="resumen-label">Casas de vida</span>
        <span class="resumen-valor"><?= (int) $resumen['total_casas'] ?></span>
      </td>
      <td>
        <span class="resumen-label">Dieron ofrenda</span>
        <span class="resumen-valor"><?= (int) $resumen['casas_dieron'] ?></span>
      </td>
      <td>
        <span class="resumen-label">Aún no dieron</span>
        <span class="resumen-valor"><?= (int) $resumen['casas_no_dieron'] ?></span>
      </td>
    </tr>
    <tr>
      <td>
        <span class="resumen-label">Registros de ofrenda</span>
        <span class="resumen-valor"><?= (int) $resumen['cantidad_registros_ofrendas'] ?></span>
      </td>
      <td>
        <span class="resumen-label">Total ofrendas</span>
        <span class="resumen-valor"><strong><?= htmlspecialchars(formatearMonto((float) $resumen['total_monto_ofrendas'])) ?></strong></span>
      </td>
      <td>
        <span class="resumen-label">Total eventos</span>
        <span class="resumen-valor"><strong><?= htmlspecialchars(formatearMonto((float) ($resumen['total_monto_eventos'] ?? 0))) ?></strong></span>
      </td>
    </tr>
    <tr>
      <td>
        <span class="resumen-label">Registros de eventos</span>
        <span class="resumen-valor"><?= (int) ($resumen['cantidad_registros_eventos'] ?? 0) ?></span>
      </td>
      <td>
        <span class="resumen-label">Valores adicionales</span>
        <span class="resumen-valor"><strong><?= htmlspecialchars(formatearMonto((float) $resumen['total_monto_valores'])) ?></strong></span>
      </td>
      <td>
        <span class="resumen-label">Total general</span>
        <span class="resumen-valor"><strong><?= htmlspecialchars(formatearMonto((float) $resumen['total_general'])) ?></strong></span>
      </td>
    </tr>
  </table>
  <?php elseif ($incluirOfrendas): ?>
  <table class="resumen">
    <tr>
      <td>
        <span class="resumen-label">Casas de vida</span>
        <span class="resumen-valor"><?= (int) $resumen['total_casas'] ?></span>
      </td>
      <td>
        <span class="resumen-label">Dieron ofrenda</span>
        <span class="resumen-valor"><?= (int) $resumen['casas_dieron'] ?></span>
      </td>
      <td>
        <span class="resumen-label">Total ofrendas</span>
        <span class="resumen-valor"><strong><?= htmlspecialchars(formatearMonto((float) $resumen['total_monto_ofrendas'])) ?></strong></span>
      </td>
    </tr>
  </table>
  <?php elseif ($incluirEventos): ?>
  <table class="resumen">
    <tr>
      <td>
        <span class="resumen-label">Registros de eventos</span>
        <span class="resumen-valor"><?= (int) ($resumen['cantidad_registros_eventos'] ?? 0) ?></span>
      </td>
      <td>
        <span class="resumen-label">Total recaudado</span>
        <span class="resumen-valor"><strong><?= htmlspecialchars(formatearMonto((float) ($resumen['total_monto_eventos'] ?? 0))) ?></strong></span>
      </td>
    </tr>
  </table>
  <?php elseif ($incluirValores): ?>
  <table class="resumen">
    <tr>
      <td>
        <span class="resumen-label">Registros</span>
        <span class="resumen-valor"><?= (int) ($resumen['cantidad_valores_adicionales'] ?? 0) ?></span>
      </td>
      <td>
        <span class="resumen-label">Total valores</span>
        <span class="resumen-valor"><strong><?= htmlspecialchars(formatearMonto((float) $resumen['total_monto_valores'])) ?></strong></span>
      </td>
    </tr>
  </table>
  <?php endif; ?>

  <?php if ($incluirOfrendas): ?>
  <h2>Ofrendas por fecha</h2>
  <?php if (empty($informe['ofrendas_por_fecha'])): ?>
  <p class="vacio">No hay ofrendas registradas en este periodo.</p>
  <?php else: ?>
    <?php foreach ($informe['ofrendas_por_fecha'] as $grupo): ?>
    <h3>Fecha: <?= htmlspecialchars($grupo['fecha_etiqueta']) ?></h3>
    <table class="datos">
      <thead>
        <tr>
          <th>Casa de vida</th>
          <th>Territorio</th>
          <th>Líder</th>
          <th>Monto</th>
          <th>Registró</th>
          <th>Registrado el</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($grupo['ofrendas'] as $ofrenda): ?>
        <tr>
          <td><?= htmlspecialchars($ofrenda['casa_vida'] ?? '—') ?></td>
          <td><?= htmlspecialchars($ofrenda['territorio'] ?? '—') ?></td>
          <td><?= htmlspecialchars($ofrenda['lider'] ?? '—') ?></td>
          <td><strong><?= htmlspecialchars(formatearMonto((float) $ofrenda['monto'])) ?></strong></td>
          <td><?= htmlspecialchars($ofrenda['registrado_por_nombre'] ?? '—') ?></td>
          <td><?= htmlspecialchars(formatearFechaHora($ofrenda['creado_en'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if (!empty($informe['mostrar_sin_entregar'])): ?>
  <h2>Casas de vida que no entregaron</h2>
  <?php if (empty($informe['casas_no_dieron'])): ?>
  <p class="vacio">Todas las casas registraron al menos una ofrenda en este periodo.</p>
  <?php else: ?>
  <table class="datos">
    <thead>
      <tr>
        <th>Casa de vida</th>
        <th>Territorio</th>
        <th>Líder</th>
        <th>Estado</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($informe['casas_no_dieron'] as $casa): ?>
      <tr>
        <td><?= htmlspecialchars($casa['nombre']) ?></td>
        <td><?= htmlspecialchars($casa['territorio']) ?></td>
        <td><?= htmlspecialchars($casa['lider']) ?></td>
        <td class="estado-pendiente">Sin ofrenda</td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
  <?php endif; ?>
  <?php endif; ?>

  <?php if ($incluirEventos): ?>
  <h2>Eventos</h2>
  <?php if (empty($informe['registros_eventos'])): ?>
  <p class="vacio">No hay registros de eventos en este periodo.</p>
  <?php else: ?>
  <table class="datos">
    <thead>
      <tr>
        <th>Evento</th>
        <th>Nombre</th>
        <th>Numeración</th>
        <th>Fecha</th>
        <th>Teléfono</th>
        <th>Forma de pago</th>
        <th>Valor</th>
        <th>Observación</th>
        <th>Registró</th>
        <th>Registrado el</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($informe['registros_eventos'] as $registroEvento): ?>
      <tr>
        <td><?= htmlspecialchars($registroEvento['evento_nombre'] ?? '—') ?></td>
        <td><?= htmlspecialchars($registroEvento['nombre']) ?></td>
        <td><?= htmlspecialchars($registroEvento['numeracion'] ?? '—') ?></td>
        <td><?= htmlspecialchars(formatearFechaInforme($registroEvento['fecha'])) ?></td>
        <td><?= htmlspecialchars($registroEvento['telefono']) ?></td>
        <td><?= htmlspecialchars(etiquetaFormaPagoEvento($registroEvento['forma_pago'] ?? null)) ?></td>
        <td><strong><?= htmlspecialchars(formatearMonto((float) $registroEvento['valor'])) ?></strong></td>
        <td><?= $registroEvento['observacion'] ? htmlspecialchars($registroEvento['observacion']) : '—' ?></td>
        <td><?= htmlspecialchars($registroEvento['registrado_por_nombre'] ?? '—') ?></td>
        <td><?= htmlspecialchars(formatearFechaHora($registroEvento['creado_en'])) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
  <?php endif; ?>

  <?php if ($incluirValores): ?>
  <h2>Valores adicionales</h2>
  <?php if (empty($informe['valores_adicionales'])): ?>
  <p class="vacio">No hay valores adicionales registrados en este periodo.</p>
  <?php else: ?>
  <table class="datos">
    <thead>
      <tr>
        <th>Tipo</th>
        <th>Nombre</th>
        <th>Fecha</th>
        <th>Teléfono</th>
        <th>Valor</th>
        <th>Observación</th>
        <th>Registró</th>
        <th>Registrado el</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($informe['valores_adicionales'] as $valor): ?>
      <tr>
        <td><?= htmlspecialchars(etiquetaTipoValorAdicional($valor['tipo'])) ?></td>
        <td><?= htmlspecialchars($valor['nombre']) ?></td>
        <td><?= htmlspecialchars(formatearFechaInforme($valor['fecha'])) ?></td>
        <td><?= htmlspecialchars($valor['telefono']) ?></td>
        <td><strong><?= htmlspecialchars(formatearMonto((float) $valor['valor'])) ?></strong></td>
        <td><?= $valor['observacion'] ? htmlspecialchars($valor['observacion']) : '—' ?></td>
        <td><?= htmlspecialchars($valor['registrado_por_nombre'] ?? '—') ?></td>
        <td><?= htmlspecialchars(formatearFechaHora($valor['creado_en'])) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
  <?php endif; ?>
</body>
</html>
