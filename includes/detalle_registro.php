<?php

require_once __DIR__ . '/submissions.php';
require_once __DIR__ . '/filters.php';
require_once __DIR__ . '/valores_adicionales.php';
require_once __DIR__ . '/consejerias.php';
require_once __DIR__ . '/transporte_aniversario.php';

function tieneObservacionRegistro(?string $texto): bool
{
    return trim((string) $texto) !== '';
}

/**
 * @return array<int, array{etiqueta: string, valor: string}>
 */
function filasMetaRegistro(array $fila): array
{
    $registrado = trim((string) ($fila['registrado_por_nombre'] ?? ''));
    if ($registrado === '' && !empty($fila['agente_usuario'])) {
        $registrado = trim((string) $fila['agente_usuario']);
    }

    return [
        [
            'etiqueta' => 'Registrado por',
            'valor'    => $registrado !== '' ? $registrado : '—',
        ],
        [
            'etiqueta' => 'Fecha y hora de registro',
            'valor'    => formatearFechaHora($fila['creado_en'] ?? null),
        ],
    ];
}

/**
 * @return array<int, array{etiqueta: string, valor: string}>
 */
function construirDetalleInscripcion(array $fila, array $etiquetasFormulario): array
{
    $filas = [];

    $filas[] = [
        'etiqueta' => 'Tipo',
        'valor'    => $etiquetasFormulario[$fila['tipo_formulario']] ?? ($fila['tipo_formulario'] ?? '—'),
    ];
    $filas[] = [
        'etiqueta' => 'Nombre completo',
        'valor'    => trim(($fila['nombre'] ?? '') . ' ' . ($fila['apellido'] ?? '')),
    ];
    $filas[] = filaDetalleHtml('Teléfono', enlaceWhatsApp($fila['celular'] ?? null));
    $filas[] = ['etiqueta' => 'Email', 'valor' => (string) ($fila['email'] ?? '—')];

    if (($fila['tipo_formulario'] ?? '') === 'conexion' || ($fila['zona'] ?? '') !== '') {
        $filas[] = [
            'etiqueta' => 'Zona',
            'valor'    => ($fila['zona'] ?? '') !== ''
                ? etiquetaZonaConexion((string) $fila['zona'])
                : '—',
        ];
        $filas[] = ['etiqueta' => 'Dirección', 'valor' => (string) ($fila['direccion'] ?? '—')];
    }

    if (($fila['tipo_formulario'] ?? '') === 'conexion') {
        $filas[] = [
            'etiqueta' => 'Estado',
            'valor'    => etiquetaEstadoConexionInscripcion($fila),
        ];
    }

    if (($fila['tipo_formulario'] ?? '') === 'bautismo') {
        $filas[] = [
            'etiqueta' => 'Estado',
            'valor'    => etiquetaEstadoBautismo((string) ($fila['estado_bautismo'] ?? 'ingresado')),
        ];
        $filas[] = [
            'etiqueta' => 'Fecha de bautismo',
            'valor'    => formatearFechaTabla($fila['fecha_bautismo'] ?? null),
        ];
    }

    return array_merge($filas, filasMetaRegistro($fila));
}

/**
 * @return array<int, array{etiqueta: string, valor: string}>
 */
function construirDetallePresentacion(array $fila, array $etiquetasEstados): array
{
    $filas = [
        ['etiqueta' => 'Nombre del niño/a', 'valor' => (string) ($fila['nombre_presentado'] ?? '—')],
        [
            'etiqueta' => 'Fecha de nacimiento',
            'valor'    => formatearFechaNacimiento($fila['fecha_nacimiento'] ?? null),
        ],
        [
            'etiqueta' => 'Edad',
            'valor'    => formatearEdadPresentacion($fila['fecha_nacimiento'] ?? null),
        ],
        ['etiqueta' => 'Nombre del padre', 'valor' => (string) ($fila['nombre_padre'] ?? '—')],
        ['etiqueta' => 'Nombre de la madre', 'valor' => (string) ($fila['nombre_madre'] ?? '—')],
        filaDetalleHtml('Teléfonos', enlacesWhatsAppPresentacion($fila)),
        [
            'etiqueta' => 'Estado',
            'valor'    => $etiquetasEstados[$fila['estado'] ?? ''] ?? ($fila['estado'] ?? '—'),
        ],
    ];

    return array_merge($filas, filasMetaRegistro($fila));
}

/**
 * @return array<int, array{etiqueta: string, valor: string}>
 */
function construirDetalleOfrenda(array $fila): array
{
    $filas = [
        ['etiqueta' => 'Casa de vida', 'valor' => (string) ($fila['casa_vida'] ?? '—')],
        ['etiqueta' => 'Líder', 'valor' => (string) ($fila['lider'] ?? '—')],
        ['etiqueta' => 'Fecha de ofrenda', 'valor' => (string) ($fila['fecha_ofrenda'] ?? '—')],
        ['etiqueta' => 'Monto', 'valor' => formatearMonto((float) ($fila['monto'] ?? 0))],
    ];

    return array_merge($filas, filasMetaRegistro($fila));
}

function construirDetalleRegistroEvento(array $fila): array
{
    require_once __DIR__ . '/eventos.php';

    $observacion = trim((string) ($fila['observacion'] ?? ''));
    $numeracion = trim((string) ($fila['numeracion'] ?? ''));

    $filas = [
        ['etiqueta' => 'Evento', 'valor' => nombreEventoValorAdicional($fila) ?: '—'],
        ['etiqueta' => 'Nombre', 'valor' => (string) ($fila['nombre'] ?? '—')],
        ['etiqueta' => 'Fecha', 'valor' => formatearFechaTabla($fila['fecha'] ?? null)],
        filaDetalleHtml('Teléfono', enlaceWhatsApp($fila['telefono'] ?? null)),
        ['etiqueta' => 'Forma de pago', 'valor' => etiquetaFormaPagoEvento($fila['forma_pago'] ?? null)],
        ['etiqueta' => 'Valor', 'valor' => formatearMonto((float) ($fila['valor'] ?? 0))],
    ];

    if ($numeracion !== '' || !empty($fila['requiere_numeracion'])) {
        $filas[] = ['etiqueta' => 'Numeración', 'valor' => $numeracion !== '' ? $numeracion : '—'];
    }

    $filas[] = [
        'etiqueta' => 'Observación',
        'valor'    => $observacion !== '' ? $observacion : '—',
    ];

    return array_merge($filas, filasMetaRegistro($fila));
}

/**
 * @return array<int, array{etiqueta: string, valor: string}>
 */
function construirDetalleValorAdicional(array $fila): array
{
    if (esTipoValorAdicionalEventos((string) ($fila['tipo'] ?? ''))) {
        return construirDetalleRegistroEvento($fila);
    }
    $observacion = trim((string) ($fila['observacion'] ?? ''));
    $tipo = (string) ($fila['tipo'] ?? '');

    $filas = [
        ['etiqueta' => 'Tipo', 'valor' => etiquetaTipoValorAdicional($tipo)],
    ];

    if (esTipoValorAdicionalEventos($tipo)) {
        $filas[] = ['etiqueta' => 'Evento', 'valor' => nombreEventoValorAdicional($fila) ?: '—'];
    }

    $filas[] = ['etiqueta' => 'Nombre', 'valor' => (string) ($fila['nombre'] ?? '—')];

    $filas = array_merge($filas, [
        ['etiqueta' => 'Fecha', 'valor' => (string) ($fila['fecha'] ?? '—')],
        filaDetalleHtml('Teléfono', enlaceWhatsApp($fila['telefono'] ?? null)),
        ['etiqueta' => 'Valor', 'valor' => formatearMonto((float) ($fila['valor'] ?? 0))],
        [
            'etiqueta' => 'Observación',
            'valor'    => $observacion !== '' ? $observacion : '—',
        ],
    ]);

    return array_merge($filas, filasMetaRegistro($fila));
}

/**
 * @return array<int, array{etiqueta: string, valor: string}>
 */
function construirDetalleConsejeria(array $fila): array
{
    $filas = [
        ['etiqueta' => 'Nombre completo', 'valor' => (string) ($fila['nombre_completo'] ?? '—')],
        filaDetalleHtml('Teléfono', enlaceWhatsApp($fila['telefono'] ?? null)),
        [
            'etiqueta' => 'Tipo de consejería',
            'valor'    => etiquetaTipoConsejeria((string) ($fila['tipo_consejeria'] ?? '')),
        ],
        ['etiqueta' => 'Año en CDA', 'valor' => (string) ((int) ($fila['anio_en_cda'] ?? 0))],
        [
            'etiqueta' => 'Primera consejería',
            'valor'    => ((int) ($fila['primera_consejeria'] ?? 0)) === 1 ? 'Sí' : 'No',
        ],
        [
            'etiqueta' => 'Cita asignada',
            'valor'    => formatearCitaConsejeria($fila['cita_fecha'] ?? null, $fila['cita_hora'] ?? null),
        ],
    ];

    return array_merge($filas, filasMetaRegistro($fila));
}

/**
 * @return array<int, array{etiqueta: string, valor: string}>
 */
function construirDetalleTransporteAniversario(array $fila): array
{
    $poseeMovilizacion = !empty($fila['posee_movilizacion']);

    $filas = [
        ['etiqueta' => 'Nombre completo', 'valor' => (string) ($fila['nombre_completo'] ?? '—')],
        ['etiqueta' => 'Edad', 'valor' => formatearEdadTransporteAniversario($fila['edad'] ?? null)],
        [
            'etiqueta' => 'Tipo',
            'valor'    => etiquetaTipoTransporteAniversario($poseeMovilizacion),
        ],
        filaDetalleHtml('Teléfono', enlaceWhatsApp($fila['telefono'] ?? null)),
    ];

    $observacion = trim((string) ($fila['observacion'] ?? ''));
    $filas[] = [
        'etiqueta' => 'Observación',
        'valor'    => $observacion !== '' ? $observacion : '—',
    ];

    if ($poseeMovilizacion) {
        $filas[] = [
            'etiqueta' => 'Asientos disponibles',
            'valor'    => (string) ((int) ($fila['asientos_disponibles'] ?? 0)),
        ];
    }

    return array_merge($filas, filasMetaRegistro($fila));
}

function formatearFechaTabla(?string $fecha): string
{
    if ($fecha === null || $fecha === '') {
        return '—';
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        $dt = DateTime::createFromFormat('Y-m-d', $fecha);

        return $dt ? $dt->format('d/m/Y') : $fecha;
    }

    return formatearFechaHora($fecha);
}

function formatearTelefonosPresentacion(array $fila): string
{
    $partes = [];
    $papa = trim((string) ($fila['telefono_papa'] ?? ''));
    $mama = trim((string) ($fila['telefono_mama'] ?? ''));

    if ($papa !== '') {
        $partes[] = $papa;
    }
    if ($mama !== '') {
        $partes[] = $mama;
    }

    return $partes ? implode(' / ', $partes) : '—';
}

function normalizarTelefonoWhatsApp(string $telefono): string
{
    $digitos = preg_replace('/\D+/', '', $telefono);
    if ($digitos === '') {
        return '';
    }

    if (str_starts_with($digitos, '593')) {
        return $digitos;
    }

    if (str_starts_with($digitos, '0')) {
        return '593' . substr($digitos, 1);
    }

    if (strlen($digitos) === 9) {
        return '593' . $digitos;
    }

    return $digitos;
}

function enlaceWhatsApp(?string $telefono, ?string $etiqueta = null): string
{
    $telefono = trim((string) $telefono);
    if ($telefono === '') {
        return '—';
    }

    $wa = normalizarTelefonoWhatsApp($telefono);
    if ($wa === '') {
        return htmlspecialchars($telefono);
    }

    $texto = $etiqueta ?? $telefono;
    $url = 'https://wa.me/' . $wa;

    return '<a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener noreferrer" class="link-whatsapp">'
        . htmlspecialchars($texto)
        . ' <i class="bi bi-whatsapp" aria-hidden="true"></i></a>';
}

function enlacesWhatsAppPresentacion(array $fila): string
{
    $partes = [];
    $papa = trim((string) ($fila['telefono_papa'] ?? ''));
    $mama = trim((string) ($fila['telefono_mama'] ?? ''));

    if ($papa !== '') {
        $partes[] = enlaceWhatsApp($papa, 'Papá: ' . $papa);
    }
    if ($mama !== '') {
        $partes[] = enlaceWhatsApp($mama, 'Mamá: ' . $mama);
    }

    return $partes ? implode('<br>', $partes) : '—';
}

function etiquetaEstadoConexionInscripcion(array $fila): string
{
    return !empty($fila['contactado']) ? 'Contactado' : 'Recibido';
}

function filaDetalleHtml(string $etiqueta, string $html): array
{
    return [
        'etiqueta' => $etiqueta,
        'valor'    => $html,
        'html'     => true,
    ];
}
