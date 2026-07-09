<?php if (tieneObservacionRegistro($observacion ?? null)): ?>
<span class="badge bg-success">Sí</span>
<?php else: ?>
<span class="badge bg-secondary">No</span>
<?php endif; ?>
