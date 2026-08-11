<?php
$nombresMeses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
];
$diasSemana = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

$mesAnterior = $mes - 1;
$anioAnterior = $anio;
if ($mesAnterior < 1) {
    $mesAnterior = 12;
    $anioAnterior--;
}
$mesSiguiente = $mes + 1;
$anioSiguiente = $anio;
if ($mesSiguiente > 12) {
    $mesSiguiente = 1;
    $anioSiguiente++;
}

$primerDia = new DateTime(sprintf('%04d-%02d-01', $anio, $mes));
$diasEnMes = (int) $primerDia->format('t');
$offsetInicio = ((int) $primerDia->format('N')) - 1; // 0=lunes
$hoy = date('Y-m-d');
$totalEventosVista = $pestaña === 'calendario' ? count($eventosMes) : count($eventosTodos);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h2 class="h4 mb-1">Calendario</h2>
    <p class="text-muted small mb-0">Eventos del calendario con foto, título, fecha y estado</p>
  </div>
  <span class="badge bg-primary fs-6"><?= (int) $totalEventosVista ?> evento(s)</span>
</div>

<?php if ($mensaje): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($mensaje) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
</div>
<?php endif; ?>

<?php if (!empty($errorBd)): ?>
<div class="alert alert-warning" role="alert">
  <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($errorBd) ?>
</div>
<?php else: ?>

<ul class="nav nav-tabs mb-4" role="tablist">
  <li class="nav-item" role="presentation">
    <a class="nav-link <?= $pestaña === 'calendario' ? 'active' : '' ?>" href="calendario.php?pestaña=calendario&amp;anio=<?= (int) $anio ?>&amp;mes=<?= (int) $mes ?>" role="tab">
      <i class="bi bi-calendar3 me-1"></i>Calendario
    </a>
  </li>
  <li class="nav-item" role="presentation">
    <a class="nav-link <?= $pestaña === 'gestionar' ? 'active' : '' ?>" href="calendario.php?pestaña=gestionar" role="tab">
      <i class="bi bi-list-ul me-1"></i>Gestionar
    </a>
  </li>
  <li class="nav-item" role="presentation">
    <a class="nav-link <?= $pestaña === 'nuevo' ? 'active' : '' ?>" href="calendario.php?pestaña=nuevo" role="tab">
      <i class="bi bi-plus-circle me-1"></i>Nuevo evento
    </a>
  </li>
</ul>

<?php if ($pestaña === 'calendario'): ?>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <h3 class="h6 mb-0">
      <i class="bi bi-calendar3 me-2"></i><?= htmlspecialchars($nombresMeses[$mes] . ' ' . $anio) ?>
    </h3>
    <div class="d-flex gap-2">
      <a class="btn btn-sm btn-outline-secondary" href="calendario.php?pestaña=calendario&amp;anio=<?= (int) $anioAnterior ?>&amp;mes=<?= (int) $mesAnterior ?>">
        <i class="bi bi-chevron-left"></i>
      </a>
      <a class="btn btn-sm btn-outline-secondary" href="calendario.php?pestaña=calendario&amp;anio=<?= (int) date('Y') ?>&amp;mes=<?= (int) date('n') ?>">Hoy</a>
      <a class="btn btn-sm btn-outline-secondary" href="calendario.php?pestaña=calendario&amp;anio=<?= (int) $anioSiguiente ?>&amp;mes=<?= (int) $mesSiguiente ?>">
        <i class="bi bi-chevron-right"></i>
      </a>
    </div>
  </div>
  <div class="card-body p-2 p-md-3">
    <div class="table-responsive">
      <table class="table table-bordered mb-0 calendario-mes align-middle">
        <thead class="table-light">
          <tr>
            <?php foreach ($diasSemana as $dia): ?>
            <th class="text-center small"><?= htmlspecialchars($dia) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php
          $celdas = $offsetInicio + $diasEnMes;
          $filas = (int) ceil($celdas / 7);
          $diaActual = 1;
          for ($fila = 0; $fila < $filas; $fila++):
          ?>
          <tr>
            <?php for ($col = 0; $col < 7; $col++):
              $indice = $fila * 7 + $col;
              if ($indice < $offsetInicio || $diaActual > $diasEnMes):
            ?>
            <td class="bg-light" style="min-height: 7rem; height: 7rem; vertical-align: top; width: 14.28%;"></td>
            <?php else:
              $fechaCelda = sprintf('%04d-%02d-%02d', $anio, $mes, $diaActual);
              $eventosDia = $eventosPorFecha[$fechaCelda] ?? [];
              $esHoy = $fechaCelda === $hoy;
            ?>
            <td class="<?= $esHoy ? 'table-primary' : '' ?>" style="min-height: 7rem; height: 7rem; vertical-align: top; width: 14.28%;">
              <div class="small fw-semibold mb-1"><?= (int) $diaActual ?></div>
              <?php foreach ($eventosDia as $eventoDia):
                $activo = (int) ($eventoDia['activo'] ?? 0) === 1;
                $fotoUrl = urlFotoEventoCalendario($eventoDia['foto'] ?? '');
              ?>
              <div class="d-flex align-items-center gap-1 mb-1 small rounded px-1 py-1 <?= $activo ? 'bg-success-subtle' : 'bg-secondary-subtle' ?>">
                <?php if ($fotoUrl !== ''): ?>
                <img src="<?= htmlspecialchars($fotoUrl) ?>" alt="" width="22" height="22" class="rounded object-fit-cover flex-shrink-0" style="object-fit: cover;">
                <?php endif; ?>
                <span class="text-truncate" title="<?= htmlspecialchars($eventoDia['titulo'] ?? '') ?>">
                  <?= htmlspecialchars($eventoDia['titulo'] ?? '') ?>
                </span>
              </div>
              <?php endforeach; ?>
            </td>
            <?php
              $diaActual++;
              endif;
            endfor; ?>
          </tr>
          <?php endfor; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3">
    <h3 class="h6 mb-0"><i class="bi bi-images me-2"></i>Eventos de <?= htmlspecialchars($nombresMeses[$mes]) ?></h3>
  </div>
  <div class="card-body">
    <?php if (empty($eventosMes)): ?>
    <p class="text-muted mb-0 text-center py-4">No hay eventos en este mes.</p>
    <?php else: ?>
    <div class="row g-3">
      <?php foreach ($eventosMes as $evento):
        $fotoUrl = urlFotoEventoCalendario($evento['foto'] ?? '');
        $activo = (int) ($evento['activo'] ?? 0) === 1;
      ?>
      <div class="col-sm-6 col-lg-4 col-xl-3">
        <div class="border rounded-3 h-100 overflow-hidden">
          <?php if ($fotoUrl !== ''): ?>
          <img src="<?= htmlspecialchars($fotoUrl) ?>" alt="<?= htmlspecialchars($evento['titulo'] ?? '') ?>" class="w-100" style="height: 160px; object-fit: cover;">
          <?php else: ?>
          <div class="bg-light d-flex align-items-center justify-content-center" style="height: 160px;">
            <i class="bi bi-image text-muted fs-2"></i>
          </div>
          <?php endif; ?>
          <div class="p-3">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
              <h4 class="h6 mb-0"><?= htmlspecialchars($evento['titulo'] ?? '') ?></h4>
              <?php if ($activo): ?>
              <span class="badge bg-success">Activo</span>
              <?php else: ?>
              <span class="badge bg-secondary">Inactivo</span>
              <?php endif; ?>
            </div>
            <p class="small text-muted mb-0">
              <i class="bi bi-calendar-event me-1"></i><?= htmlspecialchars(formatearFechaTabla($evento['fecha'] ?? '')) ?>
            </p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php elseif ($pestaña === 'nuevo'): ?>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3">
    <h3 class="h6 mb-0"><i class="bi bi-plus-circle me-2"></i>Nuevo evento del calendario</h3>
  </div>
  <div class="card-body">
    <form method="POST" action="calendario.php?pestaña=nuevo" enctype="multipart/form-data" class="row g-3">
      <input type="hidden" name="accion" value="crear_evento_calendario">
      <div class="col-md-6">
        <label class="form-label" for="titulo_nuevo">Título <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="titulo_nuevo" name="titulo" required maxlength="150" value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label" for="fecha_nueva">Fecha <span class="text-danger">*</span></label>
        <input type="date" class="form-control" id="fecha_nueva" name="fecha" required value="<?= htmlspecialchars($_POST['fecha'] ?? date('Y-m-d')) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label" for="estado_nuevo">Estado <span class="text-danger">*</span></label>
        <select class="form-select" id="estado_nuevo" name="estado" required>
          <option value="activo" <?= (($_POST['estado'] ?? 'activo') === 'activo') ? 'selected' : '' ?>>Activo</option>
          <option value="inactivo" <?= (($_POST['estado'] ?? '') === 'inactivo') ? 'selected' : '' ?>>Inactivo</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label" for="foto_nueva">Foto <span class="text-danger">*</span></label>
        <input type="file" class="form-control" id="foto_nueva" name="foto" accept="image/jpeg,image/png,image/webp,image/gif" required>
        <div class="form-text">JPG, PNG, WEBP o GIF. Máximo 5 MB.</div>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Guardar evento</button>
      </div>
    </form>
  </div>
</div>

<?php elseif ($pestaña === 'gestionar'): ?>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3">
    <h3 class="h6 mb-0"><i class="bi bi-list-ul me-2"></i>Eventos registrados</h3>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-dashboard mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th class="text-center col-numero">#</th>
            <th>Foto</th>
            <th>Título</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($eventosTodos)): ?>
          <tr>
            <td colspan="6" class="text-center text-muted py-5">No hay eventos en el calendario.</td>
          </tr>
          <?php else: ?>
          <?php foreach ($eventosTodos as $indice => $evento):
            $fotoUrl = urlFotoEventoCalendario($evento['foto'] ?? '');
            $activo = (int) ($evento['activo'] ?? 0) === 1;
          ?>
          <tr>
            <td class="text-center text-muted"><?= $indice + 1 ?></td>
            <td>
              <?php if ($fotoUrl !== ''): ?>
              <img src="<?= htmlspecialchars($fotoUrl) ?>" alt="" width="56" height="56" class="rounded" style="object-fit: cover;">
              <?php else: ?>
              <span class="text-muted">—</span>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($evento['titulo'] ?? '') ?></td>
            <td><?= htmlspecialchars(formatearFechaTabla($evento['fecha'] ?? '')) ?></td>
            <td>
              <?php if ($activo): ?>
              <span class="badge bg-success">Activo</span>
              <?php else: ?>
              <span class="badge bg-secondary">Inactivo</span>
              <?php endif; ?>
            </td>
            <td class="text-end">
              <?php if (!empty($puedeEditar)): ?>
              <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditarCalendario<?= (int) $evento['id'] ?>" title="Editar">
                <i class="bi bi-pencil"></i>
              </button>
              <?php endif; ?>
              <?php if (!empty($puedeEliminar)): ?>
              <form
                method="POST"
                action="acciones.php"
                class="d-inline js-form-confirmar"
                data-confirm-title="Eliminar evento"
                data-confirm="¿Eliminar este evento del calendario?"
              >
                <input type="hidden" name="accion" value="eliminar_evento_calendario">
                <input type="hidden" name="id" value="<?= (int) $evento['id'] ?>">
                <input type="hidden" name="redireccion" value="calendario.php?pestaña=gestionar">
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if (!empty($puedeEditar)): ?>
<?php foreach ($eventosTodos as $evento):
  $fotoUrl = urlFotoEventoCalendario($evento['foto'] ?? '');
  $activo = (int) ($evento['activo'] ?? 0) === 1;
?>
<div class="modal fade" id="modalEditarCalendario<?= (int) $evento['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="calendario.php?pestaña=gestionar" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="actualizar_evento_calendario">
        <input type="hidden" name="id" value="<?= (int) $evento['id'] ?>">
        <div class="modal-header">
          <h5 class="modal-title">Editar evento</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Título <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="titulo" required maxlength="150" value="<?= htmlspecialchars($evento['titulo'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Fecha <span class="text-danger">*</span></label>
            <input type="date" class="form-control" name="fecha" required value="<?= htmlspecialchars($evento['fecha'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Estado <span class="text-danger">*</span></label>
            <select class="form-select" name="estado" required>
              <option value="activo" <?= $activo ? 'selected' : '' ?>>Activo</option>
              <option value="inactivo" <?= !$activo ? 'selected' : '' ?>>Inactivo</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Foto</label>
            <?php if ($fotoUrl !== ''): ?>
            <div class="mb-2">
              <img src="<?= htmlspecialchars($fotoUrl) ?>" alt="" width="96" height="96" class="rounded" style="object-fit: cover;">
            </div>
            <?php endif; ?>
            <input type="file" class="form-control" name="foto" accept="image/jpeg,image/png,image/webp,image/gif">
            <div class="form-text">Deja vacío para mantener la foto actual.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php endif; ?>

<?php endif; ?>
