<?php
/**
 * Modal con detalle completo de un registro.
 *
 * Variables: $modalId, $tituloModal, $filasDetalle, $contenidoExtra (opcional)
 */
?>
<div class="modal fade modal-detalle-registro" id="<?= htmlspecialchars($modalId) ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= htmlspecialchars($tituloModal) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <dl class="detalle-registro-list mb-0">
          <?php foreach ($filasDetalle as $item): ?>
          <div class="detalle-registro-list__row">
            <dt><?= htmlspecialchars($item['etiqueta']) ?></dt>
            <dd><?= !empty($item['html']) ? $item['valor'] : nl2br(htmlspecialchars($item['valor'])) ?></dd>
          </div>
          <?php endforeach; ?>
        </dl>
        <?php if (!empty($contenidoExtra)): ?>
        <div class="detalle-registro-extra mt-3 pt-3 border-top">
          <?= $contenidoExtra ?>
        </div>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
