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
      width: 16.66%;
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
  </style>
</head>
<body>
  <?php $resumen = $informe['resumen'] ?? []; ?>

  <h1>Reporte de Transporte Aniversario</h1>
  <p class="subtitulo">Generado el <?= htmlspecialchars($informe['generado_en_etiqueta'] ?? '') ?></p>

  <h2>Resumen</h2>
  <table class="resumen">
    <tr>
      <td>
        <span class="resumen-label">Registrados</span>
        <span class="resumen-valor"><?= (int) ($resumen['total'] ?? 0) ?></span>
      </td>
      <td>
        <span class="resumen-label">Con carro</span>
        <span class="resumen-valor"><?= (int) ($resumen['con_carro'] ?? 0) ?></span>
      </td>
      <td>
        <span class="resumen-label">Necesitan transporte</span>
        <span class="resumen-valor"><?= (int) ($resumen['necesitan_transporte'] ?? 0) ?></span>
      </td>
      <td>
        <span class="resumen-label">Asientos ofrecidos</span>
        <span class="resumen-valor"><?= (int) ($resumen['asientos_ofrecidos'] ?? 0) ?></span>
      </td>
      <td>
        <span class="resumen-label">Asignados</span>
        <span class="resumen-valor"><?= (int) ($resumen['pasajeros_asignados'] ?? 0) ?></span>
      </td>
      <td>
        <span class="resumen-label">Sin cupo</span>
        <span class="resumen-valor"><?= (int) ($resumen['pasajeros_sin_cupo'] ?? 0) ?></span>
      </td>
    </tr>
  </table>

  <h2>Asignación por conductor</h2>
  <?php if (empty($informe['conductores'])): ?>
  <p class="vacio">No hay personas registradas con movilización propia.</p>
  <?php else: ?>
  <table class="datos">
    <thead>
      <tr>
        <th>Conductor</th>
        <th>Teléfono</th>
        <th>Asientos</th>
        <th>Estado</th>
        <th>Pasajeros asignados</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($informe['conductores'] as $conductor): ?>
      <?php $asignados = count($conductor['pasajeros'] ?? []); ?>
      <tr>
        <td><?= htmlspecialchars((string) ($conductor['nombre_completo'] ?? '')) ?></td>
        <td><?= htmlspecialchars((string) ($conductor['telefono'] ?? '')) ?></td>
        <td><?= $asignados ?> / <?= (int) ($conductor['asientos_total'] ?? 0) ?></td>
        <td><?= htmlspecialchars(etiquetaEstadoConductorInforme($conductor)) ?></td>
        <td><?= htmlspecialchars(listaPasajerosInforme($conductor)) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>

  <h2>Personas sin cupo asignado</h2>
  <?php if (empty($informe['sin_asignar'])): ?>
  <p class="vacio">Todas las personas que necesitan transporte tienen cupo asignado.</p>
  <?php else: ?>
  <table class="datos">
    <thead>
      <tr>
        <th>Nombre completo</th>
        <th>Teléfono</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($informe['sin_asignar'] as $pasajero): ?>
      <tr>
        <td><?= htmlspecialchars((string) ($pasajero['nombre_completo'] ?? '')) ?></td>
        <td><?= htmlspecialchars((string) ($pasajero['telefono'] ?? '')) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</body>
</html>
