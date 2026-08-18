<?php
/** @var array<int, array<string, mixed>> $camposAdicionalesEvento */
/** @var string $prefijoId */

if (!isset($camposAdicionalesEvento) || !is_array($camposAdicionalesEvento) || $camposAdicionalesEvento === []) {
    $camposAdicionalesEvento = [[
        'etiqueta'    => '',
        'obligatorio' => 1,
    ]];
}

$prefijoId = $prefijoId ?? 'campo_adicional';
?>
<div class="js-campos-adicionales-catalogo">
  <div class="d-flex align-items-center justify-content-between mb-2">
    <label class="form-label mb-0">Información adicional</label>
    <button type="button" class="btn btn-sm btn-outline-primary js-agregar-campo-adicional">
      <i class="bi bi-plus-lg me-1"></i>Agregar dato
    </button>
  </div>
  <p class="text-muted small mb-2">Define qué otros datos se pedirán al registrar un participante de este evento. Déjalo vacío si no aplica.</p>
  <div class="table-responsive border rounded">
    <table class="table table-sm align-middle mb-0 js-tabla-campos-adicionales">
      <thead class="table-light">
        <tr>
          <th>Dato a solicitar</th>
          <th class="text-center" style="width:12rem">Acciones</th>
        </tr>
      </thead>
      <tbody class="js-campos-adicionales-lista">
        <?php foreach ($camposAdicionalesEvento as $indiceCampo => $campoAdicional):
          $esObligatorio = (int) ($campoAdicional['obligatorio'] ?? 1) === 1;
          $idFila = $prefijoId . '_' . $indiceCampo;
        ?>
        <tr class="js-campo-adicional-fila">
          <td>
            <input
              type="text"
              class="form-control form-control-sm"
              name="campo_adicional[etiqueta][]"
              maxlength="100"
              placeholder="Ej. Iglesia, talla de camiseta, alergias…"
              value="<?= htmlspecialchars((string) ($campoAdicional['etiqueta'] ?? '')) ?>"
            >
          </td>
          <td>
            <div class="d-flex align-items-center justify-content-center gap-1 flex-wrap">
              <button
                type="button"
                class="btn btn-sm btn-outline-danger js-quitar-campo-adicional"
                title="Eliminar"
              >
                <i class="bi bi-trash"></i>
              </button>
              <input type="hidden" class="js-obligatorio-valor" name="campo_adicional[obligatorio][]" value="<?= $esObligatorio ? '1' : '0' ?>">
              <div class="form-check form-check-inline mb-0" title="El dato será obligatorio al registrar">
                <input
                  class="form-check-input js-obligatorio-check"
                  type="checkbox"
                  id="obligatorio_<?= htmlspecialchars($idFila) ?>"
                  <?= $esObligatorio ? 'checked' : '' ?>
                >
                <label class="form-check-label small" for="obligatorio_<?= htmlspecialchars($idFila) ?>">Obligatorio</label>
              </div>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
