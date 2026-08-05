<?php
/** @var array<string, mixed> $estadoBd */
/** @var string|null $mensaje */
/** @var string|null $error */

$bdConectada = !empty($estadoBd['bd_conectada']);
$sistemaListo = !empty($estadoBd['sistema_listo']);
$hayPendientes = !$sistemaListo
    || (int) ($estadoBd['migraciones_aplicadas'] ?? 0) < (int) ($estadoBd['total_migraciones'] ?? 0);
$mostrarAccionesInstalacion = !$bdConectada;
$mostrarActualizarTablas = !$sistemaListo || $hayPendientes;
$mostrarPanelAcciones = $mostrarAccionesInstalacion || $mostrarActualizarTablas;
?>
<?php if (!empty($estadoBd['sistema_listo'])): ?>
<div class="alert alert-success mb-4" role="alert">
  <i class="bi bi-check-circle me-1"></i>
  <strong>Sistema listo.</strong> La base de datos está conectada y todas las tablas están instaladas.
</div>
<?php elseif (!empty($estadoBd['error'])): ?>
<div class="alert alert-danger mb-4" role="alert">
  <i class="bi bi-exclamation-triangle me-1"></i>
  <?= htmlspecialchars((string) $estadoBd['error']) ?>
</div>
<?php endif; ?>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="rounded-circle bg-<?= !empty($estadoBd['servidor_conectado']) ? 'success' : 'danger' ?>-subtle text-<?= !empty($estadoBd['servidor_conectado']) ? 'success' : 'danger' ?> d-flex align-items-center justify-content-center" style="width:3rem;height:3rem">
          <i class="bi bi-hdd-network fs-5"></i>
        </div>
        <div>
          <div class="fw-semibold"><?= !empty($estadoBd['servidor_conectado']) ? 'Conectado' : 'Sin conexión' ?></div>
          <div class="text-muted small">Servidor MySQL</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="rounded-circle bg-<?= !empty($estadoBd['bd_conectada']) ? 'success' : 'warning' ?>-subtle text-<?= !empty($estadoBd['bd_conectada']) ? 'success' : 'warning' ?> d-flex align-items-center justify-content-center" style="width:3rem;height:3rem">
          <i class="bi bi-database fs-5"></i>
        </div>
        <div>
          <div class="fw-semibold"><?= !empty($estadoBd['bd_conectada']) ? 'Conectada' : (!empty($estadoBd['bd_existe']) ? 'No accesible' : 'Pendiente') ?></div>
          <div class="text-muted small">Base de datos</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="rounded-circle bg-<?= (int) ($estadoBd['tablas_instaladas'] ?? 0) === (int) ($estadoBd['total_tablas'] ?? 0) && (int) ($estadoBd['total_tablas'] ?? 0) > 0 ? 'success' : 'secondary' ?>-subtle text-<?= (int) ($estadoBd['tablas_instaladas'] ?? 0) === (int) ($estadoBd['total_tablas'] ?? 0) && (int) ($estadoBd['total_tablas'] ?? 0) > 0 ? 'success' : 'secondary' ?> d-flex align-items-center justify-content-center" style="width:3rem;height:3rem">
          <i class="bi bi-table fs-5"></i>
        </div>
        <div>
          <div class="fw-semibold"><?= (int) ($estadoBd['tablas_instaladas'] ?? 0) ?>/<?= (int) ($estadoBd['total_tablas'] ?? 0) ?></div>
          <div class="text-muted small">Tablas instaladas</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-<?= $mostrarPanelAcciones ? '4' : '12' ?>">
    <?php if ($mostrarPanelAcciones): ?>
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h3 class="h6 mb-0"><i class="bi bi-lightning-charge me-2"></i>Acciones</h3>
      </div>
      <div class="card-body d-grid gap-2">
        <?php if ($mostrarAccionesInstalacion): ?>
        <form method="POST" action="avanzado.php?pestaña=bd" class="js-form-confirmar" data-confirm-title="Crear base de datos" data-confirm="¿Crear la base de datos si no existe?">
          <input type="hidden" name="accion" value="bd_crear">
          <button type="submit" class="btn btn-outline-primary w-100">
            <i class="bi bi-database-add me-1"></i>Crear base de datos
          </button>
        </form>
        <?php endif; ?>
        <?php if ($mostrarActualizarTablas): ?>
        <form method="POST" action="avanzado.php?pestaña=bd" class="js-form-confirmar" data-confirm-title="Actualizar tablas" data-confirm="¿Ejecutar migraciones y actualizar tablas pendientes?">
          <input type="hidden" name="accion" value="bd_actualizar">
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-arrow-repeat me-1"></i>Actualizar tablas
          </button>
        </form>
        <?php endif; ?>
        <?php if ($mostrarAccionesInstalacion): ?>
        <form method="POST" action="avanzado.php?pestaña=bd" class="js-form-confirmar" data-confirm-title="Instalación completa" data-confirm="¿Ejecutar instalación completa (BD + tablas + usuario admin)?">
          <input type="hidden" name="accion" value="bd_instalacion_completa">
          <button type="submit" class="btn btn-success w-100">
            <i class="bi bi-stars me-1"></i>Instalación completa (BD + tablas + admin)
          </button>
        </form>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm <?= !$mostrarPanelAcciones ? 'mb-4' : '' ?>">
      <div class="card-header bg-white py-3">
        <h3 class="h6 mb-0"><i class="bi bi-plug me-2"></i>Conexión</h3>
      </div>
      <div class="card-body">
        <dl class="row small mb-0">
          <dt class="col-5 text-muted">Host</dt>
          <dd class="col-7 fw-medium text-danger"><?= htmlspecialchars((string) ($estadoBd['host'] ?? '')) ?></dd>
          <dt class="col-5 text-muted">Base de datos</dt>
          <dd class="col-7 fw-medium text-danger"><?= htmlspecialchars((string) ($estadoBd['base_datos'] ?? '')) ?></dd>
          <dt class="col-5 text-muted">Usuario</dt>
          <dd class="col-7 fw-medium text-danger"><?= htmlspecialchars((string) ($estadoBd['usuario'] ?? '')) ?></dd>
          <dt class="col-5 text-muted">Servidor</dt>
          <dd class="col-7">
            <span class="badge bg-<?= !empty($estadoBd['servidor_conectado']) ? 'success' : 'danger' ?>">
              <?= !empty($estadoBd['servidor_conectado']) ? 'Conectado' : 'Desconectado' ?>
            </span>
          </dd>
          <dt class="col-5 text-muted">Base de datos</dt>
          <dd class="col-7">
            <span class="badge bg-<?= !empty($estadoBd['bd_conectada']) ? 'success' : 'secondary' ?>">
              <?= !empty($estadoBd['bd_conectada']) ? 'Conectada' : 'No conectada' ?>
            </span>
          </dd>
          <?php if (!empty($estadoBd['version_mysql'])): ?>
          <dt class="col-5 text-muted">MySQL</dt>
          <dd class="col-7"><?= htmlspecialchars((string) $estadoBd['version_mysql']) ?></dd>
          <?php endif; ?>
        </dl>
      </div>
    </div>
  </div>

  <?php if ($mostrarPanelAcciones): ?>
  <div class="col-lg-8">
  <?php else: ?>
  <div class="col-12">
  <?php endif; ?>
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h3 class="h6 mb-0"><i class="bi bi-file-earmark-code me-2"></i>Migraciones</h3>
        <span class="badge bg-primary"><?= (int) ($estadoBd['migraciones_aplicadas'] ?? 0) ?> aplicadas</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-hover mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th>Archivo</th>
                <th>Estado</th>
                <th>Fecha</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($estadoBd['migraciones'] ?? [] as $migracion): ?>
              <tr>
                <td>
                  <div class="fw-medium"><?= htmlspecialchars((string) ($migracion['archivo'] ?? '')) ?></div>
                  <div class="text-muted small"><?= htmlspecialchars((string) ($migracion['etiqueta'] ?? '')) ?></div>
                </td>
                <td>
                  <?php if (!empty($migracion['aplicada'])): ?>
                  <span class="badge bg-success">Aplicada</span>
                  <?php else: ?>
                  <span class="badge bg-warning text-dark">Pendiente</span>
                  <?php endif; ?>
                </td>
                <td class="text-muted small"><?= htmlspecialchars((string) ($migracion['fecha'] ?? '—')) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3">
        <h3 class="h6 mb-0"><i class="bi bi-table me-2"></i>Tablas</h3>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-hover mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th>Tabla</th>
                <th>Estado</th>
                <th class="text-end">Registros</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($estadoBd['tablas'] ?? [] as $tabla): ?>
              <tr>
                <td class="text-danger fw-medium"><?= htmlspecialchars((string) ($tabla['nombre'] ?? '')) ?></td>
                <td>
                  <?php if (!empty($tabla['instalada'])): ?>
                  <span class="badge bg-success">OK</span>
                  <?php else: ?>
                  <span class="badge bg-danger">Falta</span>
                  <?php endif; ?>
                </td>
                <td class="text-end text-muted"><?= $tabla['registros'] !== null ? (int) $tabla['registros'] : '—' ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
