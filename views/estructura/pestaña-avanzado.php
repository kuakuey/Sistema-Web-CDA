<?php
$seccionesAvanzado = [];

$rolAvanzado = (string) ($usuario['rol'] ?? '');
if (puedeGestionarEstructuraPestana($rolAvanzado, 'lideres')) {
    $seccionesAvanzado[] = [
        'clave'     => 'miembros',
        'titulo'    => 'Miembros',
        'icono'     => 'bi-people',
        'conteo'    => (int) ($totalMiembrosRegistrados ?? count($lideres ?? [])),
        'unidad'    => 'miembro(s)',
        'exportar'  => 'estructura.php?pestaña=avanzado&descargar=miembros',
        'eliminar'  => 'eliminar_todos_lideres',
        'confirm'   => 'Se eliminarán todos los miembros, sus parentescos, asignaciones a territorios y casas de vida. ¿Continuar?',
        'tituloConfirm' => 'Eliminar todos los miembros',
    ];
}

if (puedeGestionarEstructuraPestana($rolAvanzado, 'territorios')) {
    $seccionesAvanzado[] = [
        'clave'     => 'territorios',
        'titulo'    => 'Territorios',
        'icono'     => 'bi-map',
        'conteo'    => count($territorios ?? []),
        'unidad'    => 'territorio(s)',
        'exportar'  => 'estructura.php?pestaña=avanzado&descargar=territorios',
        'eliminar'  => 'eliminar_todos_territorios',
        'confirm'   => 'Se eliminarán todos los territorios, sus asignaciones y las casas de vida asociadas. ¿Continuar?',
        'tituloConfirm' => 'Eliminar todos los territorios',
    ];
}

if (puedeGestionarEstructuraPestana($rolAvanzado, 'casas')) {
    $seccionesAvanzado[] = [
        'clave'     => 'casas',
        'titulo'    => 'Casas de vida',
        'icono'     => 'bi-house-heart',
        'conteo'    => (int) ($totalCasasRegistradas ?? count($casas ?? [])),
        'unidad'    => 'casa(s)',
        'exportar'  => 'estructura.php?pestaña=avanzado&descargar=casas',
        'eliminar'  => 'eliminar_todas_casas',
        'confirm'   => 'Se eliminarán todas las casas de vida. Los miembros y territorios no se tocan. ¿Continuar?',
        'tituloConfirm' => 'Eliminar todas las casas de vida',
    ];
}
?>
<div class="mb-4">
  <p class="text-muted small mb-0">
    Exporta o elimina todos los registros de cada sección.
  </p>
</div>

<div class="row g-4">
  <?php foreach ($seccionesAvanzado as $seccion): ?>
  <div class="col-md-6 col-xl-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <h3 class="h6 mb-2">
          <i class="bi <?= htmlspecialchars((string) $seccion['icono']) ?> me-1"></i>
          <?= htmlspecialchars((string) $seccion['titulo']) ?>
        </h3>
        <p class="text-muted small mb-3">
          <?= (int) $seccion['conteo'] ?> <?= htmlspecialchars((string) $seccion['unidad']) ?>
        </p>
        <div class="d-flex flex-wrap gap-2">
          <?php if ((int) $seccion['conteo'] > 0): ?>
          <a class="btn btn-outline-success" href="<?= htmlspecialchars((string) $seccion['exportar']) ?>">
            <i class="bi bi-download me-1"></i>Exportar
          </a>
          <?php else: ?>
          <button type="button" class="btn btn-outline-success" disabled>
            <i class="bi bi-download me-1"></i>Exportar
          </button>
          <?php endif; ?>
          <?php if (!empty($puedeEliminar) && (int) $seccion['conteo'] > 0): ?>
          <form
            method="POST"
            action="estructura.php?pestaña=avanzado"
            class="d-inline js-form-confirmar"
            data-confirm-title="<?= htmlspecialchars((string) $seccion['tituloConfirm']) ?>"
            data-confirm="<?= htmlspecialchars((string) $seccion['confirm']) ?>"
          >
            <input type="hidden" name="accion" value="<?= htmlspecialchars((string) $seccion['eliminar']) ?>">
            <button type="submit" class="btn btn-outline-danger">
              <i class="bi bi-trash me-1"></i>Eliminar registros
            </button>
          </form>
          <?php elseif (!empty($puedeEliminar)): ?>
          <button type="button" class="btn btn-outline-danger" disabled>
            <i class="bi bi-trash me-1"></i>Eliminar registros
          </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
