<?php

use Dompdf\Dompdf;
use Dompdf\Options;

function nombreArchivoInformeEventoPdf(array $informe): string
{
    $nombre = preg_replace('/[^a-z0-9]+/i', '-', strtolower((string) ($informe['evento']['nombre'] ?? 'evento')));
    $nombre = trim($nombre, '-');

    if ($nombre === '') {
        $nombre = 'evento';
    }

    return 'informe-evento-' . $nombre . '-' . (int) ($informe['evento']['id'] ?? 0) . '.pdf';
}

function renderizarHtmlInformeEventoPdf(array $informe): string
{
    require_once __DIR__ . '/eventos.php';

    ob_start();
    include __DIR__ . '/../views/eventos/informe-pdf.php';

    return (string) ob_get_clean();
}

function enviarInformeEventoPdf(array $informe): void
{
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
    $dompdf->loadHtml(renderizarHtmlInformeEventoPdf($informe));
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $dompdf->stream(nombreArchivoInformeEventoPdf($informe), [
        'Attachment' => true,
    ]);
}
