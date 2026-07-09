<?php
/**
 * Paginación de tablas de registros.
 *
 * Variables: $paginaActual, $totalPaginas, $totalRegistros, $archivoPagina, $filtros
 */
$paginaActual = (int) ($paginaActual ?? 1);
$totalPaginas = (int) ($totalPaginas ?? 1);
$totalRegistros = (int) ($totalRegistros ?? 0);
$archivoPagina = $archivoPagina ?? '';
$filtros = $filtros ?? [];
$pestañaPaginacion = $pestañaPaginacion ?? 'registros';
$registrosPorPagina = obtenerRegistrosPorPagina();

if ($totalRegistros <= 0) {
    return;
}

$rango = calcularRangoRegistros($paginaActual, $totalRegistros);
$inicioVentana = max(1, $paginaActual - 2);
$finVentana = min($totalPaginas, $paginaActual + 2);
?>
<nav class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-3 py-3 border-top bg-white" aria-label="Paginación de registros">
  <span class="text-muted small">
    Mostrando <?= (int) $rango['desde'] ?>–<?= (int) $rango['hasta'] ?> de <?= $totalRegistros ?>
    · <?= $registrosPorPagina ?> por página
  </span>
  <?php if ($totalPaginas > 1): ?>
  <ul class="pagination pagination-sm mb-0">
    <li class="page-item <?= $paginaActual <= 1 ? 'disabled' : '' ?>">
      <a
        class="page-link"
        href="<?= htmlspecialchars(construirUrlRegistros($archivoPagina, $filtros, max(1, $paginaActual - 1), $pestañaPaginacion)) ?>"
        aria-label="Anterior"
      >
        <i class="bi bi-chevron-left"></i>
      </a>
    </li>
    <?php for ($i = $inicioVentana; $i <= $finVentana; $i++): ?>
    <li class="page-item <?= $i === $paginaActual ? 'active' : '' ?>">
      <a class="page-link" href="<?= htmlspecialchars(construirUrlRegistros($archivoPagina, $filtros, $i, $pestañaPaginacion)) ?>">
        <?= $i ?>
      </a>
    </li>
    <?php endfor; ?>
    <li class="page-item <?= $paginaActual >= $totalPaginas ? 'disabled' : '' ?>">
      <a
        class="page-link"
        href="<?= htmlspecialchars(construirUrlRegistros($archivoPagina, $filtros, min($totalPaginas, $paginaActual + 1), $pestañaPaginacion)) ?>"
        aria-label="Siguiente"
      >
        <i class="bi bi-chevron-right"></i>
      </a>
    </li>
  </ul>
  <?php endif; ?>
</nav>
