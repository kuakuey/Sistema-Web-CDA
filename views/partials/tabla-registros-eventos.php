<?php
/**
 * Tabla de registros de participantes en eventos.
 *
 * Variables: $registros, $filtros, $paginaActual, $pestañaPaginacion, $usuario, $puedeEditar,
 *            $archivoPagina (opcional), $mensajeVacio (opcional)
 */
$archivoPagina = $archivoPagina ?? 'eventos.php';
$mensajeVacio = $mensajeVacio ?? 'No hay registros de eventos.';
$modalesDetalle = [];
$modalesEditar = [];
$redireccionRegistros = construirUrlRegistros($archivoPagina, $filtros, $paginaActual, $pestañaPaginacion);
?>
<div class="table-responsive">
  <table class="table table-hover table-dashboard mb-0 align-middle">
    <thead class="table-light">
      <tr>
        <th>Nombre</th>
        <th>Evento</th>
        <th>Numeración</th>
        <th>Tipo entrada</th>
        <th>Valor</th>
        <th>Forma de pago</th>
        <th>Estado</th>
        <th class="text-end">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($registros)): ?>
      <tr>
        <td colspan="8" class="text-center text-muted py-5">
          <i class="bi bi-inbox display-6 d-block mb-2"></i>
          <?= htmlspecialchars($mensajeVacio) ?>
        </td>
      </tr>
      <?php else: ?>
      <?php foreach ($registros as $fila):
          $modalId = 'detalle-evento-' . (int) $fila['id'];
          $estadoPagoFila = normalizarEstadoPagoEvento((string) ($fila['estado_pago'] ?? 'por_cancelar')) ?: 'por_cancelar';
          $modalesDetalle[] = [
              'id'     => $modalId,
              'titulo' => 'Registro de evento #' . (int) $fila['id'],
              'filas'  => construirDetalleRegistroEvento($fila),
              'extra'  => '',
          ];
          if (!empty($puedeEditar)) {
              $modalesEditar[] = [
                  'id'         => 'editar-' . $modalId,
                  'tipo'       => 'registro_evento',
                  'fila'       => $fila,
                  'redireccion'=> $redireccionRegistros,
              ];
          }
      ?>
      <tr>
        <td><?= htmlspecialchars($fila['nombre']) ?></td>
        <td><span class="badge bg-secondary"><?= htmlspecialchars($fila['evento_nombre'] ?? '—') ?></span></td>
        <td><?= htmlspecialchars($fila['numeracion'] ?? '—') ?></td>
        <td><?= htmlspecialchars(trim((string) ($fila['tipo_entrada'] ?? '')) !== '' ? $fila['tipo_entrada'] : '—') ?></td>
        <td><strong><?= htmlspecialchars(formatearMonto((float) $fila['valor'])) ?></strong></td>
        <td><?= htmlspecialchars(etiquetaFormaPagoEvento($fila['forma_pago'] ?? null)) ?></td>
        <td>
          <?php
          $rolUsuario = (string) ($usuario['rol'] ?? '');
          $estadoPagoMostrar = registroEventoEsEntradaGratis($fila) ? 'pagado' : $estadoPagoFila;
          $mostrarComboboxEstadoPago = puedeCambiarEstadoPagoRegistroEvento($fila, $rolUsuario);
          ?>
          <?php if ($mostrarComboboxEstadoPago): ?>
          <form method="POST" action="acciones.php" class="m-0">
            <input type="hidden" name="accion" value="actualizar_estado_pago_evento">
            <input type="hidden" name="id" value="<?= (int) $fila['id'] ?>">
            <input type="hidden" name="redireccion" value="<?= htmlspecialchars($redireccionRegistros) ?>">
            <select
              class="form-select form-select-sm"
              name="estado_pago"
              onchange="this.form.submit()"
              aria-label="Estado de pago"
            >
              <?php foreach (obtenerEstadosPagoEvento() as $claveEstado => $etiquetaEstado): ?>
              <option value="<?= htmlspecialchars($claveEstado) ?>" <?= $estadoPagoFila === $claveEstado ? 'selected' : '' ?>>
                <?= htmlspecialchars($etiquetaEstado) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </form>
          <?php else: ?>
          <span class="badge <?= htmlspecialchars(claseBadgeEstadoPagoEvento($estadoPagoMostrar)) ?>">
            <?= htmlspecialchars(etiquetaEstadoPagoEvento($estadoPagoMostrar)) ?>
          </span>
          <?php endif; ?>
        </td>
        <td class="text-end">
          <?php
          $eliminarAccion = 'eliminar_registro_evento';
          $eliminarId = (int) $fila['id'];
          $eliminarRedireccion = $redireccionRegistros;
          $modalEditarId = !empty($puedeEditar) ? 'editar-' . $modalId : '';
          include __DIR__ . '/tabla-acciones-registro.php';
          ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/paginacion-registros.php'; ?>
<?php foreach ($modalesDetalle as $modal):
    $modalId = $modal['id'];
    $tituloModal = $modal['titulo'];
    $filasDetalle = $modal['filas'];
    $contenidoExtra = $modal['extra'];
    include __DIR__ . '/modal-detalle-registro.php';
endforeach;
include __DIR__ . '/modales-editar-registro.php';
