<?php
/**
 * Sidebar drawer (menú lateral). Definir $sidebarVariant = 'mobile' antes de incluir.
 */

$sidebarVariant = $sidebarVariant ?? 'mobile';
$sidebarEsMobile = $sidebarVariant === 'mobile';
$sidebarId = $sidebarEsMobile ? 'appSidebarMobile' : '';
$sidebarClases = 'app-sidebar app-sidebar--drawer';

$rolSidebar = $usuario['rol'] ?? '';
$itemsMenu = obtenerItemsMenuSidebar($rolSidebar);
$etiquetasMenu = obtenerEtiquetasSecciones();
$iconosMenu = obtenerIconosSecciones();
$seccionActual = $seccionActiva ?? ($seccion ?? '');
?>
<aside
  class="<?= htmlspecialchars($sidebarClases) ?>"
  <?= $sidebarId !== '' ? 'id="' . htmlspecialchars($sidebarId) . '"' : '' ?>
>
  <nav class="nav flex-column sidebar-nav">
    <?php foreach ($itemsMenu as $claveSeccion):
        $icono = $iconosMenu[$claveSeccion] ?? 'bi-file-text';
        $claseEnlace = 'nav-link';
        if ($seccionActual === $claveSeccion) {
            $claseEnlace .= ' active';
        }
    ?>
    <a
      class="<?= htmlspecialchars($claseEnlace) ?>"
      href="<?= htmlspecialchars(obtenerUrlMenuSidebar($claveSeccion)) ?>"
    >
      <i class="bi <?= htmlspecialchars($icono) ?> me-2"></i>
      <?= htmlspecialchars($etiquetasMenu[$claveSeccion] ?? $claveSeccion) ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-footer__user">
      <i class="bi bi-person-circle"></i>
      <div>
        <div class="sidebar-footer__name">
          <?= htmlspecialchars($usuario['nombre'] ?? $usuario['usuario']) ?>
        </div>
        <?php if (!empty($etiquetasRoles[$usuario['rol'] ?? ''])): ?>
        <span class="badge bg-primary mt-1"><?= htmlspecialchars($etiquetasRoles[$usuario['rol']]) ?></span>
        <?php endif; ?>
      </div>
    </div>
    <a class="btn btn-outline-danger btn-sm sidebar-footer__logout" href="logout.php">
      <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión
    </a>
  </div>
</aside>
