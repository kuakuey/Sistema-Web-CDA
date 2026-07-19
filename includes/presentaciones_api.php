<?php

require_once __DIR__ . '/submissions.php';
require_once __DIR__ . '/filters.php';
require_once __DIR__ . '/presentaciones.php';

/**
 * Lista pública: solo niños contactados o confirmados.
 */
function responderListaPresentacionesPublica(): void
{
    $registros = listarPresentacionesNinosPorEstados(['contactado', 'confirmado']);
    $lista = [];

    foreach ($registros as $fila) {
        $lista[] = [
            'representante_1'   => formatearNombreRepresentantePresentacion($fila, 1),
            'representante_2'   => tieneSegundoRepresentantePresentacion($fila)
                ? formatearNombreRepresentantePresentacion($fila, 2)
                : null,
            'nombre_presentado' => $fila['nombre_presentado'],
            'fecha_nacimiento'  => $fila['fecha_nacimiento'] ?? null,
            'fecha_nacimiento_etiqueta' => formatearFechaNacimiento($fila['fecha_nacimiento'] ?? null),
            'edad'              => calcularEdadDesdeFechaNacimiento($fila['fecha_nacimiento'] ?? null),
            'edad_etiqueta'     => formatearEdadPresentacion($fila['fecha_nacimiento'] ?? null),
            'estado'            => $fila['estado'],
            'estado_etiqueta'   => etiquetaEstadoPresentacion($fila['estado']),
        ];
    }

    echo json_encode([
        'exito'     => true,
        'registros' => $lista,
        'total'     => count($lista),
    ]);
}
