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

  <?php include __DIR__ . '/../partials/contenido-informe-evento.php'; ?>
</body>
</html>
