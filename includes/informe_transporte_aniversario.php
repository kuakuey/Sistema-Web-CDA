<?php

use Dompdf\Dompdf;
use Dompdf\Options;

require_once __DIR__ . '/transporte_aniversario.php';
require_once __DIR__ . '/submissions.php';

function nombreArchivoInformeTransporteAniversario(string $extension): string
{
    return 'informe-transporte-aniversario-' . date('Y-m-d') . '.' . $extension;
}

/**
 * @param array<string, mixed> $reporte
 * @return array<string, mixed>
 */
function prepararInformeTransporteAniversario(array $reporte): array
{
    return array_merge($reporte, [
        'generado_en'          => date('Y-m-d H:i:s'),
        'generado_en_etiqueta' => formatearFechaHora(date('Y-m-d H:i:s')),
        'titulo'               => 'Transporte Aniversario',
    ]);
}

function etiquetaEstadoConductorInforme(array $conductor): string
{
    $restantes = (int) ($conductor['asientos_restantes'] ?? 0);

    return $restantes > 0
        ? $restantes . ' asiento(s) disponible(s)'
        : 'Sin cupo';
}

function listaPasajerosInforme(array $conductor): string
{
    $pasajeros = $conductor['pasajeros'] ?? [];

    if ($pasajeros === []) {
        return '—';
    }

    $nombres = array_map(
        static fn(array $pasajero): string => (string) ($pasajero['nombre_completo'] ?? ''),
        $pasajeros
    );

    return implode(', ', $nombres);
}

/**
 * @param array<string, mixed> $informe
 */
function renderizarHtmlInformeTransporteAniversarioPdf(array $informe): string
{
    ob_start();
    include __DIR__ . '/../views/transporte-aniversario/informe-pdf.php';

    return (string) ob_get_clean();
}

/**
 * @param array<string, mixed> $reporte
 */
function enviarInformeTransporteAniversarioPdf(array $reporte): void
{
    $autoload = __DIR__ . '/../vendor/autoload.php';

    if (!is_file($autoload)) {
        throw new RuntimeException(
            'Falta la librería PDF. Ejecuta «composer install» en la carpeta del Sistema Web.'
        );
    }

    require_once $autoload;

    $informe = prepararInformeTransporteAniversario($reporte);

    $opciones = new Options();
    $opciones->set('isHtml5ParserEnabled', true);
    $opciones->set('isRemoteEnabled', false);
    $opciones->set('defaultFont', 'DejaVu Sans');

    $dompdf = new Dompdf($opciones);
    $dompdf->loadHtml(renderizarHtmlInformeTransporteAniversarioPdf($informe));
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $dompdf->stream(nombreArchivoInformeTransporteAniversarioPdf($informe), [
        'Attachment' => true,
    ]);
}

/**
 * @param array<string, mixed> $informe
 */
function nombreArchivoInformeTransporteAniversarioPdf(array $informe): string
{
    return nombreArchivoInformeTransporteAniversario('pdf');
}

/**
 * @param array<string, mixed> $reporte
 */
function enviarInformeTransporteAniversarioExcel(array $reporte): void
{
    $informe = prepararInformeTransporteAniversario($reporte);
    $resumen = $informe['resumen'] ?? [];

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . nombreArchivoInformeTransporteAniversario('xls') . '"');
    header('Cache-Control: max-age=0');

    echo "\xEF\xBB\xBF";
    echo '<html><head><meta charset="UTF-8"></head><body>';
    echo '<h2>' . htmlspecialchars($informe['titulo']) . '</h2>';
    echo '<p>Generado el ' . htmlspecialchars($informe['generado_en_etiqueta']) . '</p>';

    echo '<h3>Resumen</h3>';
    echo '<table border="1" cellpadding="4" cellspacing="0">';
    echo '<tr><th>Registrados</th><th>Con carro</th><th>Necesitan transporte</th><th>Asientos ofrecidos</th><th>Asignados</th><th>Sin cupo</th></tr>';
    echo '<tr>';
    echo '<td>' . (int) ($resumen['total'] ?? 0) . '</td>';
    echo '<td>' . (int) ($resumen['con_carro'] ?? 0) . '</td>';
    echo '<td>' . (int) ($resumen['necesitan_transporte'] ?? 0) . '</td>';
    echo '<td>' . (int) ($resumen['asientos_ofrecidos'] ?? 0) . '</td>';
    echo '<td>' . (int) ($resumen['pasajeros_asignados'] ?? 0) . '</td>';
    echo '<td>' . (int) ($resumen['pasajeros_sin_cupo'] ?? 0) . '</td>';
    echo '</tr></table>';

    echo '<h3>Asignación por conductor</h3>';
    echo '<table border="1" cellpadding="4" cellspacing="0">';
    echo '<tr><th>Conductor</th><th>Edad</th><th>Zona</th><th>Teléfono</th><th>Asientos usados</th><th>Asientos totales</th><th>Estado</th><th>Pasajeros asignados</th></tr>';

    foreach ($informe['conductores'] ?? [] as $conductor) {
        $asignados = count($conductor['pasajeros'] ?? []);
        echo '<tr>';
        echo '<td>' . htmlspecialchars((string) ($conductor['nombre_completo'] ?? '')) . '</td>';
        echo '<td>' . htmlspecialchars(formatearEdadTransporteAniversario($conductor['edad'] ?? null)) . '</td>';
        echo '<td>' . htmlspecialchars(($conductor['zona'] ?? '') !== '' ? etiquetaZonaConexion($conductor['zona']) : '—') . '</td>';
        echo '<td>' . htmlspecialchars((string) ($conductor['telefono'] ?? '')) . '</td>';
        echo '<td>' . $asignados . '</td>';
        echo '<td>' . (int) ($conductor['asientos_total'] ?? 0) . '</td>';
        echo '<td>' . htmlspecialchars(etiquetaEstadoConductorInforme($conductor)) . '</td>';
        echo '<td>' . htmlspecialchars(listaPasajerosInforme($conductor)) . '</td>';
        echo '</tr>';
    }

    echo '</table>';

    echo '<h3>Personas sin cupo asignado</h3>';
    echo '<table border="1" cellpadding="4" cellspacing="0">';
    echo '<tr><th>Nombre completo</th><th>Edad</th><th>Zona</th><th>Teléfono</th></tr>';

    if (empty($informe['sin_asignar'])) {
        echo '<tr><td colspan="4">—</td></tr>';
    } else {
        foreach ($informe['sin_asignar'] as $pasajero) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars((string) ($pasajero['nombre_completo'] ?? '')) . '</td>';
            echo '<td>' . htmlspecialchars(formatearEdadTransporteAniversario($pasajero['edad'] ?? null)) . '</td>';
            echo '<td>' . htmlspecialchars(($pasajero['zona'] ?? '') !== '' ? etiquetaZonaConexion($pasajero['zona']) : '—') . '</td>';
            echo '<td>' . htmlspecialchars((string) ($pasajero['telefono'] ?? '')) . '</td>';
            echo '</tr>';
        }
    }

    echo '</table>';
    echo '</body></html>';
}
