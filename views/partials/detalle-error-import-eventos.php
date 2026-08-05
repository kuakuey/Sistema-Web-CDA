<?php
/** @var array{mensaje?: string, diagnostico?: array<string, mixed>}|null $errorImportEventos */
/** @var array{diagnostico?: array<string, mixed>}|null $resultadoImportEventos */
$diagnostico = $errorImportEventos['diagnostico'] ?? ($resultadoImportEventos['diagnostico'] ?? []);
if (!is_array($diagnostico)) {
    $diagnostico = [];
}

$formato = (string) ($diagnostico['formato'] ?? '—');
$hojaUsada = (string) ($diagnostico['hoja_usada'] ?? '—');
$archivo = (string) ($diagnostico['archivo'] ?? '—');
$hojas = $diagnostico['hojas_encontradas'] ?? [];
$encabezados = $diagnostico['encabezados_archivo'] ?? [];
$columnasMapeadas = $diagnostico['columnas_mapeadas'] ?? [];
$columnasFaltantes = $diagnostico['columnas_faltantes'] ?? [];
$filasTotales = (int) ($diagnostico['filas_totales_hoja'] ?? 0);
$filasValidas = (int) ($diagnostico['filas_validas'] ?? 0);
$filasOmitidas = $diagnostico['filas_omitidas'] ?? [];
$sugerencias = $diagnostico['sugerencias'] ?? [];
?>
<div class="card border-danger border-0 shadow-sm mt-4">
  <div class="card-header bg-danger-subtle py-3">
    <h3 class="h6 mb-0 text-danger"><i class="bi bi-bug me-2"></i>Detalle del error de importación</h3>
  </div>
  <div class="card-body">
    <?php if (!empty($errorImportEventos['mensaje'])): ?>
    <p class="mb-3"><strong><?= htmlspecialchars((string) $errorImportEventos['mensaje']) ?></strong></p>
    <?php endif; ?>

    <div class="row g-3 mb-4">
      <div class="col-md-6 col-lg-4">
        <div class="small text-muted">Archivo</div>
        <div><?= htmlspecialchars($archivo) ?></div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="small text-muted">Formato detectado</div>
        <div><code><?= htmlspecialchars($formato) ?></code></div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="small text-muted">Hoja leída</div>
        <div><?= htmlspecialchars($hojaUsada !== '' ? $hojaUsada : '—') ?></div>
      </div>
      <?php if (is_array($hojas) && $hojas !== []): ?>
      <div class="col-12">
        <div class="small text-muted">Pestañas encontradas</div>
        <div><?= htmlspecialchars(implode(', ', array_map('strval', $hojas))) ?></div>
      </div>
      <?php endif; ?>
      <div class="col-md-4">
        <div class="small text-muted">Filas en la hoja</div>
        <div><?= $filasTotales ?></div>
      </div>
      <div class="col-md-4">
        <div class="small text-muted">Filas válidas</div>
        <div><?= $filasValidas ?></div>
      </div>
      <div class="col-md-4">
        <div class="small text-muted">Filas omitidas al leer</div>
        <div><?= is_array($filasOmitidas) ? count($filasOmitidas) : 0 ?></div>
      </div>
    </div>

    <?php if (is_array($encabezados) && $encabezados !== []): ?>
    <div class="mb-4">
      <h4 class="h6">Encabezados detectados</h4>
      <p class="small text-muted mb-2"><?= htmlspecialchars(implode(' · ', array_map('strval', $encabezados))) ?></p>
      <?php if (is_array($columnasMapeadas) && $columnasMapeadas !== []): ?>
      <p class="small mb-0">Columnas reconocidas: <code><?= htmlspecialchars(implode(', ', array_map('strval', $columnasMapeadas))) ?></code></p>
      <?php endif; ?>
      <?php if (is_array($columnasFaltantes) && $columnasFaltantes !== []): ?>
      <p class="small text-danger mb-0">Columnas faltantes: <code><?= htmlspecialchars(implode(', ', array_map('strval', $columnasFaltantes))) ?></code></p>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (is_array($sugerencias) && $sugerencias !== []): ?>
    <div class="mb-4">
      <h4 class="h6">Sugerencias</h4>
      <ul class="small mb-0">
        <?php foreach ($sugerencias as $sugerencia): ?>
        <li><?= htmlspecialchars((string) $sugerencia) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <?php if (is_array($filasOmitidas) && $filasOmitidas !== []): ?>
    <div class="table-responsive">
      <h4 class="h6">Filas omitidas al leer el archivo</h4>
      <table class="table table-sm table-bordered mb-0">
        <thead class="table-light">
          <tr>
            <th>Fila</th>
            <th>Motivo</th>
            <th>Vista previa</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($filasOmitidas as $filaOmitida): ?>
          <tr>
            <td class="text-nowrap"><?= (int) ($filaOmitida['fila'] ?? 0) ?></td>
            <td><?= htmlspecialchars((string) ($filaOmitida['motivo'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string) ($filaOmitida['preview'] ?? '')) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
