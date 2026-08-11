<?php

require_once __DIR__ . '/calendario.php';

/**
 * Mes actual y siguiente (timezone de la app).
 *
 * @return array<int, array{anio: int, mes: int, etiqueta: string}>
 */
function obtenerMesesProximosCalendarioApi(): array
{
    $nombres = nombresMesesCalendarioApi();
    $hoy = new DateTime('today');
    $meses = [];

    for ($i = 0; $i < 2; $i++) {
        $ref = (clone $hoy)->modify('first day of this month')->modify('+' . $i . ' month');
        $mes = (int) $ref->format('n');
        $anio = (int) $ref->format('Y');
        $meses[] = [
            'anio'     => $anio,
            'mes'      => $mes,
            'etiqueta' => $nombres[$mes] ?? $ref->format('F'),
        ];
    }

    return $meses;
}

/**
 * @return array<int, string>
 */
function nombresMesesCalendarioApi(): array
{
    return [
        1  => 'Enero',
        2  => 'Febrero',
        3  => 'Marzo',
        4  => 'Abril',
        5  => 'Mayo',
        6  => 'Junio',
        7  => 'Julio',
        8  => 'Agosto',
        9  => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];
}

/**
 * Eventos activos del mes actual y el siguiente (solo desde hoy en adelante).
 *
 * @return array{
 *   meses: array<int, array{anio: int, mes: int, etiqueta: string, eventos: array<int, array<string, mixed>>}>,
 *   total: int
 * }
 */
function obtenerProximosEventosCalendarioApi(): array
{
    $hoy = (new DateTime('today'))->format('Y-m-d');
    $mesesMeta = obtenerMesesProximosCalendarioApi();
    $resultado = [];
    $total = 0;

    foreach ($mesesMeta as $meta) {
        $eventos = obtenerEventosCalendarioActivos($meta['anio'], $meta['mes']);
        $filtrados = [];

        foreach ($eventos as $evento) {
            $fecha = (string) ($evento['fecha'] ?? '');
            if ($fecha === '' || $fecha < $hoy) {
                continue;
            }

            $filtrados[] = [
                'id'     => (int) ($evento['id'] ?? 0),
                'titulo' => (string) ($evento['titulo'] ?? ''),
                'fecha'  => $fecha,
                'dia'    => (int) date('j', strtotime($fecha)),
                'activo' => 1,
            ];
            $total++;
        }

        $resultado[] = [
            'anio'     => $meta['anio'],
            'mes'      => $meta['mes'],
            'etiqueta' => $meta['etiqueta'],
            'eventos'  => $filtrados,
        ];
    }

    return [
        'meses' => $resultado,
        'total' => $total,
    ];
}

/**
 * HTML listo para embeber (shortcode calendario_text).
 */
function renderizarHtmlCalendarioTextoApi(): string
{
    $datos = obtenerProximosEventosCalendarioApi();
    $mesesConEventos = array_values(array_filter(
        $datos['meses'],
        static fn (array $mes): bool => !empty($mes['eventos'])
    ));

    ob_start();
    ?>
<style>
  .cda-events-section { margin: 0; padding: 0; }
  .cda-events-section .section-eyebrow { display: flex; align-items: center; gap: 10px; font-size: 12px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; color: #E85D24; margin-bottom: 8px; font-family: 'Lato', sans-serif; }
  .cda-events-section .section-eyebrow::after { content: ''; flex: 1; height: 1px; background: #E85D24; opacity: 0.3; }
  .cda-events-section .section-eyebrow svg { width: 16px; height: 16px; fill: none; stroke: #E85D24; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
  .cda-events-section .events-section-title { font-family: 'Bebas Neue', sans-serif; font-size: 48px; letter-spacing: 3px; color: #1a1a1a; margin-bottom: 40px; line-height: 1; }
  .cda-events-section .months-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 32px; }
  @media (max-width: 768px) {
    .cda-events-section .months-grid { grid-template-columns: 1fr; }
    .cda-events-section .events-section-title { font-size: 36px; }
  }
  .cda-events-section .month-header { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
  .cda-events-section .month-name { font-family: 'Bebas Neue', sans-serif; font-size: 30px; letter-spacing: 3px; color: #E85D24; line-height: 1; }
  .cda-events-section .month-line { flex: 1; height: 2px; background: linear-gradient(to right, #E85D24, transparent); }
  .cda-events-section .event-item { display: flex; align-items: flex-start; gap: 14px; padding: 12px 10px; border-radius: 10px; transition: background 0.15s; cursor: default; }
  .cda-events-section .event-item:hover { background: rgba(232, 93, 36, 0.06); }
  .cda-events-section .event-item + .event-item { border-top: 1px solid rgba(0,0,0,0.07); }
  .cda-events-section .event-date-badge { flex-shrink: 0; min-width: 80px; background: #E85D24; color: #fff; font-family: 'Bebas Neue', sans-serif; font-size: 17px; letter-spacing: 1px; text-align: center; padding: 6px 8px; border-radius: 8px; line-height: 1.2; }
  .cda-events-section .event-name { font-size: 13px; font-weight: 700; color: #1a1a1a; letter-spacing: 0.4px; text-transform: uppercase; line-height: 1.3; margin-bottom: 0; font-family: 'Lato', sans-serif; }
  .cda-events-section .events-empty { font-family: 'Lato', sans-serif; color: #777; font-size: 14px; }
</style>
<section class="cda-events-section events-section">
  <div class="section-eyebrow">
    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    Agenda
  </div>
  <h4 class="events-section-title">Próximos Eventos</h4>
  <?php if ($mesesConEventos === []): ?>
  <p class="events-empty">No hay eventos próximos en el calendario.</p>
  <?php else: ?>
  <div class="months-grid">
    <?php foreach ($mesesConEventos as $mes): ?>
    <div class="month-col">
      <div class="month-header">
        <span class="month-name"><?= htmlspecialchars($mes['etiqueta'], ENT_QUOTES, 'UTF-8') ?></span>
        <div class="month-line"></div>
      </div>
      <?php foreach ($mes['eventos'] as $evento): ?>
      <div class="event-item">
        <div class="event-date-badge"><?= (int) $evento['dia'] ?></div>
        <div class="event-info">
          <div class="event-name"><?= htmlspecialchars($evento['titulo'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
    <?php

    return trim((string) ob_get_clean());
}
