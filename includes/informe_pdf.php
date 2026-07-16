<?php

use Dompdf\Dompdf;
use Dompdf\Options;

function nombreArchivoInformePdf(array $informe, string $seccion = 'completo'): string
{
    $seccion = normalizarSeccionInforme($seccion);
    $prefijo = $seccion === 'completo' ? 'informe' : 'informe-' . $seccion;

    return $prefijo . '-' . $informe['fecha_desde'] . '-' . $informe['fecha_hasta'] . '.pdf';
}

function renderizarHtmlInformePdf(array $informe, string $seccion = 'completo'): string
{
    require_once __DIR__ . '/eventos.php';

    $informe['seccion_exportacion'] = normalizarSeccionInforme($seccion);

    ob_start();
    include __DIR__ . '/../views/informes/pdf.php';

    return (string) ob_get_clean();
}

function enviarInformePdf(array $informe, string $seccion = 'completo'): void
{
    $seccion = normalizarSeccionInforme($seccion);
    $autoload = __DIR__ . '/../vendor/autoload.php';

    if (!is_file($autoload)) {
        throw new RuntimeException(
            'Falta la librería PDF. Ejecuta «composer install» en la carpeta del Sistema Web.'
        );
    }

    require_once $autoload;

    $opciones = new Options();
    $opciones->set('isHtml5ParserEnabled', true);
    $opciones->set('isRemoteEnabled', false);
    $opciones->set('defaultFont', 'DejaVu Sans');

    $dompdf = new Dompdf($opciones);
    $dompdf->loadHtml(renderizarHtmlInformePdf($informe, $seccion));
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $dompdf->stream(nombreArchivoInformePdf($informe, $seccion), [
        'Attachment' => true,
    ]);
}
