<?php
/**
 * Botones de acción en tablas de registros.
 *
 * Variables: $modalId, $puedeEliminar, $eliminarAccion, $eliminarId,
 *            $eliminarRedireccion, $mensajeConfirmar (opcional)
 */
$mensajeConfirmar = $mensajeConfirmar ?? '¿Eliminar este registro?';
?>
<div class="d-flex gap-1 justify-content-end tabla-acciones">
  <button
    type="button"
    class="btn btn-sm btn-outline-secondary"
    data-bs-toggle="modal"
    data-bs-target="#<?= htmlspecialchars($modalId) ?>"
    title="Ver detalle"
  >
    <i class="bi bi-eye"></i>
  </button>
  <?php if (!empty($puedeEditar) && !empty($modalEditarId)): ?>
  <button
    type="button"
    class="btn btn-sm btn-outline-primary"
    data-bs-toggle="modal"
    data-bs-target="#<?= htmlspecialchars($modalEditarId) ?>"
    title="Editar"
  >
    <i class="bi bi-pencil"></i>
  </button>
  <?php endif; ?>
  <?php if (!empty($puedeEliminar)): ?>
  <form
    method="POST"
    action="acciones.php"
    class="d-inline js-form-confirmar"
    data-confirm-title="Eliminar registro"
    data-confirm="<?= htmlspecialchars($mensajeConfirmar, ENT_QUOTES, 'UTF-8') ?>"
  >
    <input type="hidden" name="accion" value="<?= htmlspecialchars($eliminarAccion) ?>">
    <input type="hidden" name="id" value="<?= (int) $eliminarId ?>">
    <input type="hidden" name="redireccion" value="<?= htmlspecialchars($eliminarRedireccion) ?>">
    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
      <i class="bi bi-trash"></i>
    </button>
  </form>
  <?php endif; ?>
</div>
