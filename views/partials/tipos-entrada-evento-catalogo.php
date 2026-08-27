<?php
/** @var array<int, array<string, mixed>> $tiposEntradaEvento */
/** @var string $prefijoId */

if (!isset($tiposEntradaEvento) || !is_array($tiposEntradaEvento) || $tiposEntradaEvento === []) {
    $tiposEntradaEvento = [[
        'nombre'          => '',
        'valor'           => 0,
        'visible_publico' => 1,
        'es_gratis'       => 0,
    ]];
}

$prefijoId = $prefijoId ?? 'tipo_entrada';
$totalFilas = count($tiposEntradaEvento);
?>
<div class="d-flex align-items-center justify-content-between mb-2">
  <label class="form-label mb-0">Tipos de entrada <span class="text-danger">*</span></label>
  <button type="button" class="btn btn-sm btn-outline-primary js-agregar-tipo-entrada">
    <i class="bi bi-plus-lg me-1"></i>Agregar tipo
  </button>
</div>
<div class="table-responsive border rounded">
  <table class="table table-sm align-middle mb-0 js-tabla-tipos-entrada">
    <thead class="table-light">
      <tr>
        <th>Tipo de entrada</th>
        <th style="width:7.5rem">Prefijo</th>
        <th style="width:9rem">Valor</th>
        <th class="text-center" style="width:14rem">Acciones</th>
      </tr>
    </thead>
    <tbody class="js-tipos-entrada-lista">
      <?php foreach ($tiposEntradaEvento as $indiceTipo => $tipoEntrada):
        $visiblePublico = (int) ($tipoEntrada['visible_publico'] ?? 1) === 1;
        $esGratis = (int) ($tipoEntrada['es_gratis'] ?? 0) === 1;
        $idFila = $prefijoId . '_' . $indiceTipo;
      ?>
      <tr class="js-tipo-entrada-fila">
        <td>
          <input
            type="text"
            class="form-control form-control-sm"
            name="tipo_entrada[nombre][]"
            required
            maxlength="100"
            placeholder="Ej. General, VIP…"
            value="<?= htmlspecialchars((string) ($tipoEntrada['nombre'] ?? '')) ?>"
          >
        </td>
        <td>
          <input
            type="text"
            class="form-control form-control-sm js-prefijo-tipo-entrada"
            name="tipo_entrada[prefijo][]"
            maxlength="10"
            placeholder="VIP"
            value="<?= htmlspecialchars(normalizarPrefijoTipoEntrada($tipoEntrada['prefijo'] ?? '')) ?>"
            autocomplete="off"
          >
        </td>
        <td>
          <input
            type="number"
            class="form-control form-control-sm js-valor-tipo-entrada"
            name="tipo_entrada[valor][]"
            min="0"
            step="0.01"
            value="<?= htmlspecialchars((string) ((float) ($tipoEntrada['valor'] ?? 0))) ?>"
            <?= $esGratis ? 'disabled' : 'required' ?>
          >
        </td>
        <td>
          <div class="d-flex align-items-center justify-content-center gap-1 flex-wrap">
            <button
              type="button"
              class="btn btn-sm btn-outline-danger js-quitar-tipo-entrada"
              title="Eliminar"
              <?= $totalFilas <= 1 ? 'disabled' : '' ?>
            >
              <i class="bi bi-trash"></i>
            </button>
            <input type="hidden" class="js-visible-publico-valor" name="tipo_entrada[visible_publico][]" value="<?= $visiblePublico ? '1' : '0' ?>">
            <div class="form-check form-check-inline mb-0" title="Visible al público">
              <input
                class="form-check-input js-visible-publico-check"
                type="checkbox"
                id="visible_<?= htmlspecialchars($idFila) ?>"
                <?= $visiblePublico ? 'checked' : '' ?>
              >
              <label class="form-check-label small" for="visible_<?= htmlspecialchars($idFila) ?>">Visible</label>
            </div>
            <input type="hidden" class="js-es-gratis-valor" name="tipo_entrada[es_gratis][]" value="<?= $esGratis ? '1' : '0' ?>">
            <div class="form-check form-check-inline mb-0" title="Entrada gratuita">
              <input
                class="form-check-input js-es-gratis-check"
                type="checkbox"
                id="gratis_<?= htmlspecialchars($idFila) ?>"
                <?= $esGratis ? 'checked' : '' ?>
              >
              <label class="form-check-label small" for="gratis_<?= htmlspecialchars($idFila) ?>">Gratis</label>
            </div>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<p class="text-muted small mt-2 mb-0">El prefijo se concatena con la numeración (G + 203 = G203) para no mezclar tipos. Valor 0 sin «Gratis» = pendiente de pago. Sin «Visible» = solo admin y superadmin.</p>
