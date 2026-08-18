<?php
/** @var array<int, array<string, mixed>> $camposAdicionalesEvento */
/** @var string $prefijoId */

if (!isset($camposAdicionalesEvento) || !is_array($camposAdicionalesEvento) || $camposAdicionalesEvento === []) {
    $camposAdicionalesEvento = [[
        'etiqueta'    => '',
        'tipo'        => 'texto',
        'opciones'    => [],
        'obligatorio' => 1,
    ]];
}

$prefijoId = $prefijoId ?? 'campo_adicional';
$tiposCampoAdicional = obtenerTiposCampoAdicionalEvento();
?>
<div class="js-campos-adicionales-catalogo">
  <div class="d-flex align-items-center justify-content-between mb-2">
    <label class="form-label mb-0">Información adicional</label>
    <button type="button" class="btn btn-sm btn-outline-primary js-agregar-campo-adicional">
      <i class="bi bi-plus-lg me-1"></i>Agregar dato
    </button>
  </div>
  <p class="text-muted small mb-2">Define qué otros datos se pedirán al registrar un participante. Puedes usar texto, lista desplegable, número o fecha. Déjalo vacío si no aplica.</p>
  <div class="js-campos-adicionales-lista">
    <?php foreach ($camposAdicionalesEvento as $indiceCampo => $campoAdicional):
      $esObligatorio = (int) ($campoAdicional['obligatorio'] ?? 1) === 1;
      $tipoCampo = normalizarTipoCampoAdicionalEvento($campoAdicional['tipo'] ?? 'texto');
      $esLista = $tipoCampo === 'lista';
      $idFila = $prefijoId . '_' . $indiceCampo;
    ?>
    <div class="js-campo-adicional-fila border rounded p-2 mb-2">
      <div class="row g-2 align-items-end">
        <div class="col-md-5">
          <label class="form-label small mb-1">Dato a solicitar</label>
          <input
            type="text"
            class="form-control form-control-sm js-etiqueta-campo-adicional"
            name="campo_adicional[etiqueta][]"
            maxlength="100"
            placeholder="Ej. Iglesia, talla, alergias…"
            value="<?= htmlspecialchars((string) ($campoAdicional['etiqueta'] ?? '')) ?>"
          >
        </div>
        <div class="col-md-3">
          <label class="form-label small mb-1">Tipo</label>
          <select class="form-select form-select-sm js-tipo-campo-adicional" name="campo_adicional[tipo][]">
            <?php foreach ($tiposCampoAdicional as $claveTipo => $etiquetaTipo): ?>
            <option value="<?= htmlspecialchars($claveTipo) ?>" <?= $tipoCampo === $claveTipo ? 'selected' : '' ?>>
              <?= htmlspecialchars($etiquetaTipo) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <div class="d-flex align-items-center gap-2 flex-wrap pb-1">
            <button
              type="button"
              class="btn btn-sm btn-outline-danger js-quitar-campo-adicional"
              title="Eliminar"
            >
              <i class="bi bi-trash"></i>
            </button>
            <input type="hidden" class="js-obligatorio-valor" name="campo_adicional[obligatorio][]" value="<?= $esObligatorio ? '1' : '0' ?>">
            <div class="form-check mb-0" title="El dato será obligatorio al registrar">
              <input
                class="form-check-input js-obligatorio-check"
                type="checkbox"
                id="obligatorio_<?= htmlspecialchars($idFila) ?>"
                <?= $esObligatorio ? 'checked' : '' ?>
              >
              <label class="form-check-label small" for="obligatorio_<?= htmlspecialchars($idFila) ?>">Obligatorio</label>
            </div>
          </div>
        </div>
        <div class="col-12 js-bloque-opciones-campo" <?= $esLista ? '' : 'style="display:none"' ?>>
          <label class="form-label small mb-1">Opciones de la lista</label>
          <textarea
            class="form-control form-control-sm js-opciones-campo-adicional"
            name="campo_adicional[opciones][]"
            rows="3"
            placeholder="Una opción por línea. Ej.&#10;S&#10;M&#10;L&#10;XL"
          ><?= htmlspecialchars(formatearOpcionesCampoAdicionalParaTextarea($campoAdicional['opciones'] ?? [])) ?></textarea>
          <p class="text-muted small mb-0">Escribe una opción por línea. Se mostrarán en un combobox al registrar.</p>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
