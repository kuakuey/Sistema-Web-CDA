<?php
/**
 * Modal de edición (solo superadmin).
 *
 * Variables: $modalEditarId, $tipoEditar, $filaEditar, $redireccionEditar
 * Contexto según tipo: $zonas, $estadosPresentacion, $casas, $tiposValor, $eventos, $tiposConsejeria
 */
$fila = $filaEditar;
?>
<div class="modal fade modal-editar-registro" id="<?= htmlspecialchars($modalEditarId) ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <form method="POST" action="acciones.php">
        <div class="modal-header">
          <h5 class="modal-title">Editar registro #<?= (int) $fila['id'] ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" value="<?= (int) $fila['id'] ?>">
          <input type="hidden" name="redireccion" value="<?= htmlspecialchars($redireccionEditar) ?>">

          <?php if ($tipoEditar === 'inscripcion'): ?>
          <input type="hidden" name="accion" value="actualizar_inscripcion">
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label class="form-label">Nombre <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nombre" required maxlength="100" value="<?= htmlspecialchars($fila['nombre'] ?? '') ?>">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Apellido <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="apellido" required maxlength="100" value="<?= htmlspecialchars($fila['apellido'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Teléfono <span class="text-danger">*</span></label>
              <input type="tel" class="form-control" name="celular" required maxlength="30" value="<?= htmlspecialchars($fila['celular'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" name="email" maxlength="100" value="<?= htmlspecialchars($fila['email'] ?? '') ?>">
            </div>
            <?php if (($fila['tipo_formulario'] ?? '') === 'conexion'): ?>
            <div class="col-md-6">
              <label class="form-label">Zona</label>
              <select class="form-select" name="zona">
                <option value="">—</option>
                <?php foreach ($zonas ?? [] as $claveZona => $etiquetaZona): ?>
                <option value="<?= htmlspecialchars($claveZona) ?>" <?= ($fila['zona'] ?? '') === $claveZona ? 'selected' : '' ?>>
                  <?= htmlspecialchars($etiquetaZona) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Dirección</label>
              <input type="text" class="form-control" name="direccion" maxlength="255" value="<?= htmlspecialchars($fila['direccion'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Estado</label>
              <select class="form-select" name="contactado">
                <option value="0" <?= empty($fila['contactado']) ? 'selected' : '' ?>>Recibido</option>
                <option value="1" <?= !empty($fila['contactado']) ? 'selected' : '' ?>>Contactado</option>
              </select>
            </div>
            <?php else: ?>
            <input type="hidden" name="zona" value="">
            <input type="hidden" name="direccion" value="">
            <input type="hidden" name="contactado" value="0">
            <?php endif; ?>
          </div>

          <?php elseif ($tipoEditar === 'presentacion'): ?>
          <input type="hidden" name="accion" value="actualizar_presentacion">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nombre del niño/a <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nombre_presentado" required maxlength="100" value="<?= htmlspecialchars($fila['nombre_presentado'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Fecha de nacimiento <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="fecha_nacimiento" required value="<?= htmlspecialchars($fila['fecha_nacimiento'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Nombre del padre <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nombre_padre" required maxlength="100" value="<?= htmlspecialchars($fila['nombre_padre'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Nombre de la madre <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nombre_madre" required maxlength="100" value="<?= htmlspecialchars($fila['nombre_madre'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Teléfono papá <span class="text-danger">*</span></label>
              <input type="tel" class="form-control" name="telefono_papa" required maxlength="30" value="<?= htmlspecialchars($fila['telefono_papa'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Teléfono mamá <span class="text-danger">*</span></label>
              <input type="tel" class="form-control" name="telefono_mama" required maxlength="30" value="<?= htmlspecialchars($fila['telefono_mama'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Estado <span class="text-danger">*</span></label>
              <select class="form-select" name="estado" required>
                <?php foreach ($estadosPresentacion ?? [] as $estado): ?>
                <option value="<?= htmlspecialchars($estado) ?>" <?= ($fila['estado'] ?? '') === $estado ? 'selected' : '' ?>>
                  <?= htmlspecialchars($etiquetasEstadosPresentacion[$estado] ?? $estado) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <?php elseif ($tipoEditar === 'ofrenda'): ?>
          <input type="hidden" name="accion" value="actualizar_ofrenda">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Casa de vida <span class="text-danger">*</span></label>
              <select class="form-select" name="casa_id" required>
                <option value="">Seleccione…</option>
                <?php foreach ($casas ?? [] as $casa): ?>
                <option value="<?= (int) $casa['id'] ?>" <?= (int) ($fila['casa_id'] ?? 0) === (int) $casa['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($casa['nombre']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Fecha de ofrenda <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="fecha_ofrenda" required value="<?= htmlspecialchars($fila['fecha_ofrenda'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Valor <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="monto" step="0.01" min="0.01" required value="<?= htmlspecialchars((string) ($fila['monto'] ?? '')) ?>">
            </div>
          </div>

          <?php elseif ($tipoEditar === 'valor_adicional'): ?>
          <input type="hidden" name="accion" value="actualizar_valor_adicional">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Tipo <span class="text-danger">*</span></label>
              <select class="form-select" name="tipo" required>
                <?php foreach ($tiposValor ?? [] as $clave => $etiqueta): ?>
                <option value="<?= htmlspecialchars($clave) ?>" <?= ($fila['tipo'] ?? '') === $clave ? 'selected' : '' ?>>
                  <?= htmlspecialchars($etiqueta) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="w-100"></div>
            <div class="col-md-6 col-lg-3">
              <label class="form-label">Nombre <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nombre" required maxlength="100" value="<?= htmlspecialchars($fila['nombre'] ?? '') ?>">
            </div>
            <div class="col-md-6 col-lg-3">
              <label class="form-label">Fecha <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="fecha" required value="<?= htmlspecialchars($fila['fecha'] ?? '') ?>">
            </div>
            <div class="col-md-6 col-lg-3">
              <label class="form-label">Teléfono <span class="text-danger">*</span></label>
              <input type="tel" class="form-control" name="telefono" required maxlength="30" value="<?= htmlspecialchars($fila['telefono'] ?? '') ?>">
            </div>
            <div class="col-md-6 col-lg-3">
              <label class="form-label">Valor <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="valor" step="0.01" min="0.01" required value="<?= htmlspecialchars((string) ($fila['valor'] ?? '')) ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Observación</label>
              <textarea class="form-control" name="observacion" rows="2" maxlength="1000"><?= htmlspecialchars($fila['observacion'] ?? '') ?></textarea>
            </div>
          </div>

          <?php elseif ($tipoEditar === 'registro_evento'): ?>
          <input type="hidden" name="accion" value="actualizar_registro_evento">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Evento <span class="text-danger">*</span></label>
              <select class="form-select" name="evento_id" required>
                <?php foreach ($eventos ?? [] as $eventoItem): ?>
                <option
                  value="<?= (int) $eventoItem['id'] ?>"
                  data-requiere-numeracion="<?= (int) ($eventoItem['requiere_numeracion'] ?? 0) ?>"
                  data-valor="<?= htmlspecialchars((string) ($eventoItem['valor'] ?? '0')) ?>"
                  <?= (int) ($fila['evento_id'] ?? 0) === (int) $eventoItem['id'] ? 'selected' : '' ?>
                >
                  <?= htmlspecialchars($eventoItem['nombre']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Nombre <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nombre" required maxlength="100" value="<?= htmlspecialchars($fila['nombre'] ?? '') ?>">
            </div>
            <div class="col-md-6 js-campo-numeracion-evento" style="<?= !empty($fila['requiere_numeracion']) || !empty($fila['numeracion']) ? '' : 'display:none' ?>">
              <label class="form-label">Numeración <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="numeracion" maxlength="30" value="<?= htmlspecialchars($fila['numeracion'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Fecha <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="fecha" required value="<?= htmlspecialchars($fila['fecha'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Teléfono <span class="text-danger">*</span></label>
              <input type="tel" class="form-control" name="telefono" required maxlength="30" value="<?= htmlspecialchars($fila['telefono'] ?? '') ?>">
            </div>
            <?php
            $valorEventoSeleccionado = 0.0;
            foreach ($eventos ?? [] as $eventoItem) {
                if ((int) ($fila['evento_id'] ?? 0) === (int) $eventoItem['id']) {
                    $valorEventoSeleccionado = (float) ($eventoItem['valor'] ?? 0);
                    break;
                }
            }
            $esGratuitoRegistro = $valorEventoSeleccionado <= 0;
            ?>
            <div class="col-12 js-bloque-pago-evento" style="<?= $esGratuitoRegistro ? 'display:none' : '' ?>">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Valor <span class="text-danger">*</span></label>
                  <input
                    type="number"
                    class="form-control js-valor-evento"
                    name="valor"
                    step="0.01"
                    min="0.01"
                    value="<?= htmlspecialchars((string) ($fila['valor'] ?? '0')) ?>"
                  >
                </div>
                <div class="col-12">
                  <label class="form-label d-block">Forma de pago <span class="text-danger">*</span></label>
                  <?php $formaPagoActual = in_array($fila['forma_pago'] ?? '', ['efectivo', 'transferencia'], true) ? $fila['forma_pago'] : 'efectivo'; ?>
                  <div class="form-check form-check-inline">
                    <input
                      class="form-check-input js-metodo-pago-evento"
                      type="radio"
                      name="forma_pago"
                      id="<?= htmlspecialchars($modalEditarId . '-pago-efectivo') ?>"
                      value="efectivo"
                      <?= $formaPagoActual === 'efectivo' ? 'checked' : '' ?>
                    >
                    <label class="form-check-label" for="<?= htmlspecialchars($modalEditarId . '-pago-efectivo') ?>">Efectivo</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input
                      class="form-check-input js-metodo-pago-evento"
                      type="radio"
                      name="forma_pago"
                      id="<?= htmlspecialchars($modalEditarId . '-pago-transferencia') ?>"
                      value="transferencia"
                      <?= $formaPagoActual === 'transferencia' ? 'checked' : '' ?>
                    >
                    <label class="form-check-label" for="<?= htmlspecialchars($modalEditarId . '-pago-transferencia') ?>">Transferencia</label>
                  </div>
                </div>
              </div>
            </div>
            <input type="hidden" name="forma_pago" class="js-forma-pago-gratuito" value="gratuito" <?= !$esGratuitoRegistro ? 'disabled' : '' ?>>
            <input type="hidden" name="valor" class="js-valor-gratuito" value="0" <?= !$esGratuitoRegistro ? 'disabled' : '' ?>>
            <div class="col-12">
              <label class="form-label">Observación</label>
              <textarea class="form-control" name="observacion" rows="2" maxlength="500"><?= htmlspecialchars($fila['observacion'] ?? '') ?></textarea>
            </div>
          </div>

          <?php elseif ($tipoEditar === 'consejeria'): ?>
          <input type="hidden" name="accion" value="actualizar_consejeria">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nombre_completo" required maxlength="200" value="<?= htmlspecialchars($fila['nombre_completo'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Teléfono <span class="text-danger">*</span></label>
              <input type="tel" class="form-control" name="telefono" required maxlength="30" value="<?= htmlspecialchars($fila['telefono'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Tipo de consejería <span class="text-danger">*</span></label>
              <select class="form-select" name="tipo_consejeria" required>
                <?php foreach ($tiposConsejeria ?? [] as $clave => $etiqueta): ?>
                <option value="<?= htmlspecialchars($clave) ?>" <?= ($fila['tipo_consejeria'] ?? '') === $clave ? 'selected' : '' ?>>
                  <?= htmlspecialchars($etiqueta) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Año en CDA <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="anio_en_cda" required min="1900" max="<?= (int) date('Y') ?>" value="<?= (int) ($fila['anio_en_cda'] ?? 0) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Primera consejería</label>
              <select class="form-select" name="primera_consejeria">
                <option value="1" <?= !empty($fila['primera_consejeria']) ? 'selected' : '' ?>>Sí</option>
                <option value="0" <?= empty($fila['primera_consejeria']) ? 'selected' : '' ?>>No</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Fecha de cita</label>
              <input type="date" class="form-control" name="cita_fecha" value="<?= htmlspecialchars($fila['cita_fecha'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Hora de cita</label>
              <input type="time" class="form-control" name="cita_hora" value="<?= htmlspecialchars(formatearHoraConsejeria($fila['cita_hora'] ?? '')) ?>">
            </div>
          </div>
          <?php endif; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-save me-1"></i>Guardar cambios
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
