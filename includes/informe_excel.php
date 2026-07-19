<?php

require_once __DIR__ . '/informes.php';
require_once __DIR__ . '/submissions.php';
require_once __DIR__ . '/valores_adicionales.php';

function nombreArchivoInformeExcel(array $informe, string $seccion): string
{
    $seccion = normalizarSeccionInforme($seccion);
    $prefijo = $seccion === 'completo' ? 'informe' : 'informe-' . $seccion;

    return $prefijo . '-' . $informe['fecha_desde'] . '-' . $informe['fecha_hasta'] . '.xls';
}

function enviarInformeExcel(array $informe, string $seccion = 'completo'): void
{
    require_once __DIR__ . '/eventos.php';

    $seccion = normalizarSeccionInforme($seccion);
    $informe['seccion_exportacion'] = $seccion;
    $resumen = $informe['resumen'] ?? [];

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . nombreArchivoInformeExcel($informe, $seccion) . '"');
    header('Cache-Control: max-age=0');

    echo "\xEF\xBB\xBF";
    echo '<html><head><meta charset="UTF-8"></head><body>';
    echo '<h2>' . htmlspecialchars(tituloSeccionInforme($seccion)) . '</h2>';
    echo '<p>Periodo de registro: ' . htmlspecialchars($informe['periodo_etiqueta'] ?? ($informe['fecha_desde_etiqueta'] . ' al ' . $informe['fecha_hasta_etiqueta']))
        . ' · Jornada: ' . htmlspecialchars($informe['turno_etiqueta']);
    if ($seccion === 'eventos' && ($informe['evento_id'] ?? 0) > 0) {
        echo ' · Evento: ' . htmlspecialchars($informe['evento_etiqueta'] ?? '—');
    }
    if ($seccion === 'presentaciones') {
        echo ' · Estados: ' . htmlspecialchars($informe['estados_presentacion_etiqueta'] ?? '—');
    }
    echo ' · Generado: ' . htmlspecialchars($informe['generado_en']) . '</p>';

    if (in_array($seccion, ['completo', 'ofrendas'], true)) {
        echo '<h3>Resumen de ofrendas</h3>';
        echo '<table border="1" cellpadding="4" cellspacing="0">';
        echo '<tr><th>Casas de vida</th><th>Dieron ofrenda</th><th>Aún no dieron</th><th>Registros</th><th>Total ofrendas</th></tr>';
        echo '<tr>';
        echo '<td>' . (int) ($resumen['total_casas'] ?? 0) . '</td>';
        echo '<td>' . (int) ($resumen['casas_dieron'] ?? 0) . '</td>';
        echo '<td>' . (int) ($resumen['casas_no_dieron'] ?? 0) . '</td>';
        echo '<td>' . (int) ($resumen['cantidad_registros_ofrendas'] ?? 0) . '</td>';
        echo '<td>' . htmlspecialchars(formatearMonto((float) ($resumen['total_monto_ofrendas'] ?? 0))) . '</td>';
        echo '</tr></table>';

        echo '<h3>Ofrendas por fecha</h3>';
        if (empty($informe['ofrendas_por_fecha'])) {
            echo '<p>—</p>';
        } else {
            foreach ($informe['ofrendas_por_fecha'] as $grupo) {
                echo '<h4>' . htmlspecialchars($grupo['fecha_etiqueta']) . '</h4>';
                echo '<table border="1" cellpadding="4" cellspacing="0">';
                echo '<tr><th>Casa de vida</th><th>Territorio</th><th>Líder</th><th>Monto</th><th>Registró</th><th>Registrado el</th></tr>';
                foreach ($grupo['ofrendas'] as $ofrenda) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($ofrenda['casa_vida'] ?? '—') . '</td>';
                    echo '<td>' . htmlspecialchars($ofrenda['territorio'] ?? '—') . '</td>';
                    echo '<td>' . htmlspecialchars($ofrenda['lider'] ?? '—') . '</td>';
                    echo '<td>' . htmlspecialchars(formatearMonto((float) $ofrenda['monto'])) . '</td>';
                    echo '<td>' . htmlspecialchars($ofrenda['registrado_por_nombre'] ?? '—') . '</td>';
                    echo '<td>' . htmlspecialchars(formatearFechaHora($ofrenda['creado_en'])) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
        }

        if (!empty($informe['mostrar_sin_entregar'])) {
            echo '<h3>Casas de vida que no entregaron</h3>';
            echo '<table border="1" cellpadding="4" cellspacing="0">';
            echo '<tr><th>Casa de vida</th><th>Territorio</th><th>Líder</th><th>Estado</th></tr>';
            if (empty($informe['casas_no_dieron'])) {
                echo '<tr><td colspan="4">—</td></tr>';
            } else {
                foreach ($informe['casas_no_dieron'] as $casa) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($casa['nombre']) . '</td>';
                    echo '<td>' . htmlspecialchars($casa['territorio']) . '</td>';
                    echo '<td>' . htmlspecialchars($casa['lider']) . '</td>';
                    echo '<td>Sin ofrenda</td>';
                    echo '</tr>';
                }
            }
            echo '</table>';
        }
    }

    if (in_array($seccion, ['completo', 'eventos'], true)) {
        echo '<h3>Eventos</h3>';
        echo '<table border="1" cellpadding="4" cellspacing="0">';
        echo '<tr><th>Evento</th><th>Nombre</th><th>Numeración</th><th>Fecha</th><th>Teléfono</th><th>Forma de pago</th><th>Valor</th><th>Observación</th><th>Registró</th><th>Registrado el</th></tr>';
        if (empty($informe['registros_eventos'])) {
            echo '<tr><td colspan="10">—</td></tr>';
        } else {
            foreach ($informe['registros_eventos'] as $registroEvento) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($registroEvento['evento_nombre'] ?? '—') . '</td>';
                echo '<td>' . htmlspecialchars($registroEvento['nombre']) . '</td>';
                echo '<td>' . htmlspecialchars($registroEvento['numeracion'] ?? '—') . '</td>';
                echo '<td>' . htmlspecialchars(formatearFechaInforme($registroEvento['fecha'])) . '</td>';
                echo '<td>' . htmlspecialchars($registroEvento['telefono']) . '</td>';
                echo '<td>' . htmlspecialchars(etiquetaFormaPagoEvento($registroEvento['forma_pago'] ?? null)) . '</td>';
                echo '<td>' . htmlspecialchars(formatearMonto((float) $registroEvento['valor'])) . '</td>';
                echo '<td>' . ($registroEvento['observacion'] ? htmlspecialchars($registroEvento['observacion']) : '—') . '</td>';
                echo '<td>' . htmlspecialchars($registroEvento['registrado_por_nombre'] ?? '—') . '</td>';
                echo '<td>' . htmlspecialchars(formatearFechaHora($registroEvento['creado_en'])) . '</td>';
                echo '</tr>';
            }
        }
        echo '</table>';
    }

    if (in_array($seccion, ['completo', 'valores'], true)) {
        echo '<h3>Valores adicionales</h3>';
        echo '<table border="1" cellpadding="4" cellspacing="0">';
        echo '<tr><th>Tipo</th><th>Nombre</th><th>Fecha</th><th>Teléfono</th><th>Valor</th><th>Observación</th><th>Registró</th><th>Registrado el</th></tr>';
        if (empty($informe['valores_adicionales'])) {
            echo '<tr><td colspan="8">—</td></tr>';
        } else {
            foreach ($informe['valores_adicionales'] as $valor) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars(etiquetaTipoValorAdicional($valor['tipo'])) . '</td>';
                echo '<td>' . htmlspecialchars($valor['nombre']) . '</td>';
                echo '<td>' . htmlspecialchars(formatearFechaInforme($valor['fecha'])) . '</td>';
                echo '<td>' . htmlspecialchars($valor['telefono']) . '</td>';
                echo '<td>' . htmlspecialchars(formatearMonto((float) $valor['valor'])) . '</td>';
                echo '<td>' . ($valor['observacion'] ? htmlspecialchars($valor['observacion']) : '—') . '</td>';
                echo '<td>' . htmlspecialchars($valor['registrado_por_nombre'] ?? '—') . '</td>';
                echo '<td>' . htmlspecialchars(formatearFechaHora($valor['creado_en'])) . '</td>';
                echo '</tr>';
            }
        }
        echo '</table>';
    }

    if ($seccion === 'presentaciones') {
        echo '<h3>Presentación de niños</h3>';
        echo '<p>Registros: ' . (int) ($resumen['cantidad_presentaciones'] ?? 0) . '</p>';
        echo '<table border="1" cellpadding="4" cellspacing="0">';
        echo '<tr><th>Nombre niño/a</th><th>Edad</th><th>Representante 1</th><th>Representante 2</th></tr>';
        if (empty($informe['presentaciones'])) {
            echo '<tr><td colspan="4">—</td></tr>';
        } else {
            require_once __DIR__ . '/presentaciones.php';
            foreach ($informe['presentaciones'] as $presentacion) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($presentacion['nombre_presentado'] ?? '—') . '</td>';
                echo '<td>' . htmlspecialchars($presentacion['edad_etiqueta'] ?? formatearEdadPresentacion($presentacion['fecha_nacimiento'] ?? null)) . '</td>';
                echo '<td>' . htmlspecialchars(formatearNombreRepresentantePresentacion($presentacion, 1)) . '</td>';
                echo '<td>' . htmlspecialchars(tieneSegundoRepresentantePresentacion($presentacion) ? formatearNombreRepresentantePresentacion($presentacion, 2) : '—') . '</td>';
                echo '</tr>';
            }
        }
        echo '</table>';
    }

    echo '</body></html>';
}
