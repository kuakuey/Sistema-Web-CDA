<?php
$miembro = $datosMiembro ?? $miembroDetalle ?? [];
$parentesco = $parentescoPorMiembro[(int) ($miembroDetalle['id'] ?? 0)] ?? null;
$asignacionesMiembro = $asignacionesMiembro ?? [];
$casasMiembro = $casasMiembro ?? [];
$paginaLista = (int) ($paginaMiembros ?? 1);
$urlLista = 'estructura.php?pestaña=lideres' . ($paginaLista > 1 ? '&pagina=' . $paginaLista : '');
$nombreCompleto = trim((string) ($miembroDetalle['nombre'] ?? '') . ' ' . (string) ($miembroDetalle['apellido'] ?? ''));
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div>
    <a class="small text-decoration-none" href="<?= htmlspecialchars($urlLista) ?>">
      <i class="bi bi-arrow-left me-1"></i>Volver a miembros
    </a>
    <h3 class="h5 mb-0 mt-2"><?= htmlspecialchars($nombreCompleto !== '' ? $nombreCompleto : 'Miembro') ?></h3>
    <p class="text-muted small mb-0">Ficha de la persona. Aquí se irá agregando más información.</p>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3">
        <h4 class="h6 mb-0">Datos personales</h4>
      </div>
      <div class="card-body">
        <form method="POST" action="<?= htmlspecialchars(urlFichaMiembro((int) ($miembroDetalle['id'] ?? 0))) ?>">
          <input type="hidden" name="accion" value="actualizar_lider">
          <input type="hidden" name="id" value="<?= (int) ($miembroDetalle['id'] ?? 0) ?>">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nombres <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nombre" required value="<?= htmlspecialchars((string) ($miembro['nombre'] ?? '')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Apellidos <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="apellido" required value="<?= htmlspecialchars((string) ($miembro['apellido'] ?? '')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Género <span class="text-danger">*</span></label>
              <select class="form-select" name="genero" required>
                <option value="">Seleccione…</option>
                <?php foreach (opcionesGeneroMiembro() as $clave => $etiqueta): ?>
                <option value="<?= htmlspecialchars($clave) ?>" <?= (($miembro['genero'] ?? '') === $clave) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($etiqueta) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Cédula</label>
              <input type="text" class="form-control" name="cedula" value="<?= htmlspecialchars((string) ($miembro['cedula'] ?? '')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Celular</label>
              <input type="tel" class="form-control" name="celular" value="<?= htmlspecialchars((string) ($miembro['celular'] ?? '')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" name="email" value="<?= htmlspecialchars((string) ($miembro['email'] ?? '')) ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Notas</label>
              <textarea class="form-control" name="notas" rows="3"><?= htmlspecialchars((string) ($miembro['notas'] ?? '')) ?></textarea>
            </div>
          </div>
          <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
            <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($urlLista) ?>">Cancelar</a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i>Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h4 class="h6 mb-0">En la estructura</h4>
      </div>
      <div class="card-body">
        <p class="small mb-2"><strong>Parentesco</strong></p>
        <?php if ($parentesco): ?>
        <p class="small mb-3">
          <?= htmlspecialchars(etiquetaParentescoMiembro((string) $parentesco['parentesco'])) ?>
          de
          <a href="<?= htmlspecialchars(urlFichaMiembro((int) $parentesco['pariente_id'])) ?>">
            <?= htmlspecialchars(trim($parentesco['pariente_nombre'] . ' ' . $parentesco['pariente_apellido'])) ?>
          </a>
        </p>
        <?php else: ?>
        <p class="text-muted small mb-3">Sin conectar</p>
        <?php endif; ?>

        <p class="small mb-2"><strong>Territorios</strong></p>
        <?php if ($asignacionesMiembro === []): ?>
        <p class="text-muted small mb-3">Sin asignar</p>
        <?php else: ?>
        <ul class="small mb-3 ps-3">
          <?php foreach ($asignacionesMiembro as $asignacion): ?>
          <li>
            <?= htmlspecialchars(etiquetaRolTerritorio((string) $asignacion['rol'])) ?>
            · <?= htmlspecialchars((string) ($asignacion['territorio_nombre'] ?? '')) ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <p class="small mb-2"><strong>Casas de vida</strong></p>
        <?php if ($casasMiembro === []): ?>
        <p class="text-muted small mb-0">Ninguna</p>
        <?php else: ?>
        <ul class="small mb-0 ps-3">
          <?php foreach ($casasMiembro as $casa): ?>
          <?php
          $rolesCasa = [];
          $miembroId = (int) ($miembroDetalle['id'] ?? 0);
          if ((int) ($casa['lider_id'] ?? 0) === $miembroId) {
              $rolesCasa[] = 'Líder';
          }
          if ((int) ($casa['colaborador_id'] ?? 0) === $miembroId) {
              $rolesCasa[] = 'Colaborador';
          }
          if ((int) ($casa['anfitrion_id'] ?? 0) === $miembroId) {
              $rolesCasa[] = 'Anfitrión';
          }
          ?>
          <li>
            <?= htmlspecialchars(implode(', ', $rolesCasa)) ?>
            · <?= htmlspecialchars((string) ($casa['direccion'] ?? nombreVisibleCasaVida($casa))) ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
