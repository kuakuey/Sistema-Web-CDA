<?php

require_once __DIR__ . '/eventos.php';
require_once __DIR__ . '/valores_adicionales.php';
require_once __DIR__ . '/roles.php';
require_once __DIR__ . '/submissions.php';

/**
 * @return array<string, string>
 */
function columnasPlantillaImportEventos(): array
{
    return [
        'evento'       => 'Evento',
        'tipo_entrada' => 'Tipo entrada',
        'nombre'       => 'Nombre',
        'telefono'     => 'Teléfono',
        'fecha'        => 'Fecha',
        'valor'        => 'Valor',
        'numeracion'   => 'Numeración',
        'estado_pago'  => 'Estado',
        'forma_pago'   => 'Forma de pago',
        'observacion'  => 'Observación',
    ];
}

function enviarPlantillaImportEventos(): void
{
    $eventos = obtenerEventos();
    $ejemplos = obtenerFilasEjemploPlantillaImportEventos($eventos);
    $fechaHoy = date('Y-m-d');

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="plantilla-registros-eventos.xls"');
    header('Cache-Control: max-age=0');

    echo "\xEF\xBB\xBF";
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
    echo construirWorkbookXmlPlantillaImportEventos($eventos, $ejemplos, $fechaHoy);
}

function construirWorkbookXmlPlantillaImportEventos(array $eventos, array $ejemplos, string $fechaHoy): string
{
    $xml = '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
        . ' xmlns:o="urn:schemas-microsoft-com:office:office"'
        . ' xmlns:x="urn:schemas-microsoft-com:office:excel"'
        . ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"'
        . ' xmlns:html="http://www.w3.org/TR/REC-html40">';
    $xml .= '<Styles>';
    $xml .= '<Style ss:ID="Header"><Font ss:Bold="1"/></Style>';
    $xml .= '<Style ss:ID="Titulo"><Font ss:Bold="1" ss:Size="12"/></Style>';
    $xml .= '<Style ss:ID="Subtitulo"><Font ss:Bold="1" ss:Size="10"/></Style>';
    $xml .= '</Styles>';
    $xml .= construirHojaDatosXmlPlantillaImportEventos();
    $xml .= construirHojaGuiaXmlPlantillaImportEventos($eventos, $ejemplos, $fechaHoy);
    $xml .= '</Workbook>';

    return $xml;
}

function construirHojaDatosXmlPlantillaImportEventos(): string
{
    $columnas = columnasPlantillaImportEventos();
    $xml = '<Worksheet ss:Name="Datos"><Table>';
    $xml .= filaXmlExcel(array_values($columnas), 'Header');
    $xml .= '</Table></Worksheet>';

    return $xml;
}

/**
 * @param array<int, array<string, mixed>> $eventos
 * @param array<int, array<string, string>> $ejemplos
 */
function construirHojaGuiaXmlPlantillaImportEventos(array $eventos, array $ejemplos, string $fechaHoy): string
{
    $columnas = columnasPlantillaImportEventos();
    $claves = array_keys($columnas);

    $xml = '<Worksheet ss:Name="Guía y ejemplos"><Table>';

    $xml .= filaXmlExcel(['PLANTILLA · REGISTROS DE EVENTOS'], 'Titulo');
    $xml .= filaXmlExcel(['']);
    $xml .= filaXmlExcel(['Use la pestaña «Datos» para registrar participantes. Esta pestaña es solo guía.'], 'Subtitulo');
    $xml .= filaXmlExcel(['']);

    $xml .= filaXmlExcel(['INSTRUCCIONES GENERALES'], 'Subtitulo');
    foreach ([
        '1. Vaya a la pestaña «Datos» (primera pestaña).',
        '2. Complete una fila por participante debajo del encabezado.',
        '3. No modifique los nombres de las columnas en la fila 1.',
        '4. Copie nombres de Evento y Tipo entrada desde la tabla Referencia (abajo).',
        '5. Guarde el archivo y súbalo en Avanzado → Importar registros.',
        '6. El sistema importa únicamente la pestaña «Datos».',
    ] as $linea) {
        $xml .= filaXmlExcel([$linea]);
    }
    $xml .= filaXmlExcel(['']);

    $xml .= filaXmlExcel(['DESCRIPCIÓN DE COLUMNAS'], 'Subtitulo');
    $xml .= filaXmlExcel(['Columna', 'Obligatorio', 'Valores permitidos / Notas'], 'Header');
    foreach ([
        ['Evento', 'Sí', 'Nombre exacto del evento (ver Referencia).'],
        ['Tipo entrada', 'Sí', 'Nombre exacto del tipo dentro del evento.'],
        ['Nombre', 'Sí', 'Nombre completo del participante.'],
        ['Teléfono', 'Sí', 'Número de contacto.'],
        ['Fecha', 'Sí', 'Formato AAAA-MM-DD (ej. ' . $fechaHoy . ').'],
        ['Valor', 'Sí*', 'Monto numérico. En entradas gratis use 0.'],
        ['Numeración', 'Condicional', 'Obligatoria si el evento requiere numeración.'],
        ['Estado', 'Sí', 'por_cancelar o pagado. Gratis: pagado.'],
        ['Forma de pago', 'Sí', 'pendiente, efectivo, transferencia o gratuito.'],
        ['Observación', 'No', 'Texto libre opcional.'],
    ] as $fila) {
        $xml .= filaXmlExcel($fila);
    }
    $xml .= filaXmlExcel(['']);

    $xml .= filaXmlExcel(['REGLAS DE ESTADO Y FORMA DE PAGO'], 'Subtitulo');
    $xml .= filaXmlExcel(['Situación', 'Estado', 'Forma de pago', 'Notas'], 'Header');
    foreach ([
        ['Aún no ha pagado', 'por_cancelar', 'pendiente', 'Inscripción pendiente.'],
        ['Pagó en efectivo', 'pagado', 'efectivo', 'Indique el valor cobrado.'],
        ['Pagó por transferencia', 'pagado', 'transferencia', 'Indique el valor recibido.'],
        ['Entrada gratuita', 'pagado', 'gratuito', 'Valor 0. Estado: completado.'],
        ['Promoción / descuento', 'pagado o por_cancelar', 'según caso', 'Valor puede ser menor al catálogo.'],
    ] as $fila) {
        $xml .= filaXmlExcel($fila);
    }
    $xml .= filaXmlExcel(['']);

    $xml .= filaXmlExcel(['EJEMPLOS (copie el formato a la pestaña Datos)'], 'Subtitulo');
    $xml .= filaXmlExcel(array_values($columnas), 'Header');
    foreach ($ejemplos as $ejemplo) {
        $valores = [];
        foreach ($claves as $clave) {
            $valores[] = (string) ($ejemplo[$clave] ?? '');
        }
        $xml .= filaXmlExcel($valores);
    }
    $xml .= filaXmlExcel(['']);

    $xml .= filaXmlExcel(['REFERENCIA · EVENTOS Y TIPOS DE ENTRADA'], 'Subtitulo');
    $xml .= filaXmlExcel(['Evento', 'Tipo entrada', 'Valor catálogo', 'Gratis', 'Requiere numeración', 'Habilitado'], 'Header');
    if ($eventos === []) {
        $xml .= filaXmlExcel(['No hay eventos registrados. Cree eventos en el módulo Eventos antes de importar.']);
    } else {
        foreach ($eventos as $evento) {
            $tipos = $evento['tipos_entrada'] ?? [];
            if ($tipos === []) {
                $xml .= filaXmlExcel([
                    (string) ($evento['nombre'] ?? ''),
                    'Sin tipos de entrada',
                    '',
                    '',
                    '',
                    (int) ($evento['habilitado'] ?? 0) === 1 ? 'Sí' : 'No',
                ]);
                continue;
            }
            foreach ($tipos as $indice => $tipo) {
                $xml .= filaXmlExcel([
                    $indice === 0 ? (string) ($evento['nombre'] ?? '') : '',
                    (string) ($tipo['nombre'] ?? ''),
                    formatearMonto((float) ($tipo['valor'] ?? 0)),
                    (int) ($tipo['es_gratis'] ?? 0) === 1 ? 'Sí' : 'No',
                    (int) ($evento['requiere_numeracion'] ?? 0) === 1 ? 'Sí' : 'No',
                    (int) ($evento['habilitado'] ?? 0) === 1 ? 'Sí' : 'No',
                ]);
            }
        }
    }

    $xml .= '</Table></Worksheet>';

    return $xml;
}

/**
 * @param array<int, string|int|float> $valores
 */
function filaXmlExcel(array $valores, ?string $estiloId = null): string
{
    $attrs = $estiloId !== null ? ' ss:StyleID="' . escaparXmlExcel($estiloId) . '"' : '';
    $xml = '<Row' . $attrs . '>';
    foreach ($valores as $valor) {
        $xml .= celdaXmlExcel((string) $valor);
    }
    $xml .= '</Row>';

    return $xml;
}

function celdaXmlExcel(string $valor, string $tipo = 'String'): string
{
    return '<Cell><Data ss:Type="' . escaparXmlExcel($tipo) . '">'
        . escaparXmlExcel($valor)
        . '</Data></Cell>';
}

function escaparXmlExcel(string $texto): string
{
    return htmlspecialchars($texto, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

/**
 * @param array<int, array<string, mixed>> $eventos
 * @return array{
 *   evento_pago: string,
 *   tipo_pago: string,
 *   valor_pago: string,
 *   evento_gratis: string,
 *   tipo_gratis: string,
 *   evento_numeracion: string,
 *   tipo_numeracion: string,
 *   requiere_numeracion: bool
 * }
 */
function obtenerContextoEjemplosPlantillaImportEventos(array $eventos): array
{
    $contexto = [
        'evento_pago'         => 'Conferencia Anual',
        'tipo_pago'           => 'General',
        'valor_pago'          => '50000',
        'evento_gratis'       => 'Conferencia Anual',
        'tipo_gratis'         => 'Invitado',
        'evento_numeracion'   => 'Concierto',
        'tipo_numeracion'     => 'General',
        'requiere_numeracion' => true,
    ];

    $eventoPago = null;
    $tipoPago = null;
    $eventoGratis = null;
    $tipoGratis = null;
    $eventoNumeracion = null;
    $tipoNumeracion = null;

    foreach ($eventos as $evento) {
        foreach ($evento['tipos_entrada'] ?? [] as $tipo) {
            $esGratis = (int) ($tipo['es_gratis'] ?? 0) === 1;

            if (!$tipoPago && !$esGratis) {
                $eventoPago = $evento;
                $tipoPago = $tipo;
            }

            if (!$tipoGratis && $esGratis) {
                $eventoGratis = $evento;
                $tipoGratis = $tipo;
            }

            if (!$eventoNumeracion && (int) ($evento['requiere_numeracion'] ?? 0) === 1) {
                $eventoNumeracion = $evento;
                $tipoNumeracion = $tipo;
            }
        }
    }

    if ($eventoPago !== null && $tipoPago !== null) {
        $contexto['evento_pago'] = (string) ($eventoPago['nombre'] ?? $contexto['evento_pago']);
        $contexto['tipo_pago'] = (string) ($tipoPago['nombre'] ?? $contexto['tipo_pago']);
        $contexto['valor_pago'] = (string) ((float) ($tipoPago['valor'] ?? 50000));
    }

    if ($eventoGratis !== null && $tipoGratis !== null) {
        $contexto['evento_gratis'] = (string) ($eventoGratis['nombre'] ?? $contexto['evento_gratis']);
        $contexto['tipo_gratis'] = (string) ($tipoGratis['nombre'] ?? $contexto['tipo_gratis']);
    } elseif ($eventoPago !== null) {
        $contexto['evento_gratis'] = (string) ($eventoPago['nombre'] ?? $contexto['evento_gratis']);
    }

    if ($eventoNumeracion !== null && $tipoNumeracion !== null) {
        $contexto['evento_numeracion'] = (string) ($eventoNumeracion['nombre'] ?? $contexto['evento_numeracion']);
        $contexto['tipo_numeracion'] = (string) ($tipoNumeracion['nombre'] ?? $contexto['tipo_numeracion']);
        $contexto['requiere_numeracion'] = true;
    } else {
        $contexto['requiere_numeracion'] = false;
        $contexto['evento_numeracion'] = $contexto['evento_pago'];
        $contexto['tipo_numeracion'] = $contexto['tipo_pago'];
    }

    return $contexto;
}

/**
 * @param array<int, array<string, mixed>> $eventos
 * @return array<int, array<string, string>>
 */
function obtenerFilasEjemploPlantillaImportEventos(array $eventos): array
{
    $ctx = obtenerContextoEjemplosPlantillaImportEventos($eventos);
    $fecha = date('Y-m-d');
    $valorPago = $ctx['valor_pago'];
    $valorPromo = (string) max(0, (float) $valorPago - 10000);

    $filas = [
        [
            'evento'       => $ctx['evento_pago'],
            'tipo_entrada' => $ctx['tipo_pago'],
            'nombre'       => 'María González',
            'telefono'     => '3001112233',
            'fecha'        => $fecha,
            'valor'        => $valorPago,
            'numeracion'   => '',
            'estado_pago'  => 'por_cancelar',
            'forma_pago'   => 'pendiente',
            'observacion'  => 'EJEMPLO: Caso 1 — Inscripción pendiente de pago (estado por_cancelar + forma pendiente).',
        ],
        [
            'evento'       => $ctx['evento_pago'],
            'tipo_entrada' => $ctx['tipo_pago'],
            'nombre'       => 'Carlos Rodríguez',
            'telefono'     => '3104445566',
            'fecha'        => $fecha,
            'valor'        => $valorPago,
            'numeracion'   => '',
            'estado_pago'  => 'pagado',
            'forma_pago'   => 'efectivo',
            'observacion'  => 'EJEMPLO: Caso 2 — Pagado en efectivo (estado pagado + forma efectivo).',
        ],
        [
            'evento'       => $ctx['evento_pago'],
            'tipo_entrada' => $ctx['tipo_pago'],
            'nombre'       => 'Ana Lucía Vargas',
            'telefono'     => '3207778899',
            'fecha'        => $fecha,
            'valor'        => $valorPago,
            'numeracion'   => '',
            'estado_pago'  => 'pagado',
            'forma_pago'   => 'transferencia',
            'observacion'  => 'EJEMPLO: Caso 3 — Pagado por transferencia (estado pagado + forma transferencia).',
        ],
        [
            'evento'       => $ctx['evento_gratis'],
            'tipo_entrada' => $ctx['tipo_gratis'],
            'nombre'       => 'Pedro Martínez',
            'telefono'     => '3156667788',
            'fecha'        => $fecha,
            'valor'        => '0',
            'numeracion'   => '',
            'estado_pago'  => 'pagado',
            'forma_pago'   => 'gratuito',
            'observacion'  => 'EJEMPLO: Caso 4 — Entrada gratuita (valor 0, forma gratuito). Estado interno: completado.',
        ],
        [
            'evento'       => $ctx['evento_pago'],
            'tipo_entrada' => $ctx['tipo_pago'],
            'nombre'       => 'Laura Sánchez',
            'telefono'     => '3189990011',
            'fecha'        => $fecha,
            'valor'        => $valorPromo,
            'numeracion'   => '',
            'estado_pago'  => 'pagado',
            'forma_pago'   => 'efectivo',
            'observacion'  => 'EJEMPLO: Caso 5 — Valor con descuento/promoción (puede ser menor al catálogo).',
        ],
    ];

    if ($ctx['requiere_numeracion']) {
        $filas[] = [
            'evento'       => $ctx['evento_numeracion'],
            'tipo_entrada' => $ctx['tipo_numeracion'],
            'nombre'       => 'Diego Herrera',
            'telefono'     => '3012223344',
            'fecha'        => $fecha,
            'valor'        => $valorPago,
            'numeracion'   => 'A-015',
            'estado_pago'  => 'pagado',
            'forma_pago'   => 'efectivo',
            'observacion'  => 'EJEMPLO: Caso 6 — Evento con numeración obligatoria (llene la columna Numeración).',
        ];
    } else {
        $filas[] = [
            'evento'       => $ctx['evento_pago'],
            'tipo_entrada' => $ctx['tipo_pago'],
            'nombre'       => 'Sofía Ramírez',
            'telefono'     => '3145556677',
            'fecha'        => $fecha,
            'valor'        => $valorPago,
            'numeracion'   => 'B-042',
            'estado_pago'  => 'pagado',
            'forma_pago'   => 'transferencia',
            'observacion'  => 'EJEMPLO: Caso 6 — Con numeración opcional (solo si el evento la requiere; si no, déjela vacía).',
        ];
    }

    $filas[] = [
        'evento'       => $ctx['evento_pago'],
        'tipo_entrada' => $ctx['tipo_pago'],
        'nombre'       => 'Jorge Castillo',
        'telefono'     => '3178889900',
        'fecha'        => $fecha,
        'valor'        => $valorPago,
        'numeracion'   => '',
        'estado_pago'  => 'por_cancelar',
        'forma_pago'   => 'pendiente',
        'observacion'  => 'EJEMPLO: Caso 7 — Con observación personalizada para el equipo (texto libre).',
    ];

    return $filas;
}

function filaEsEjemploImportEvento(array $fila): bool
{
    $observacion = trim((string) ($fila['observacion'] ?? ''));

    return str_starts_with(mb_strtoupper($observacion), 'EJEMPLO:');
}

function normalizarEncabezadoImportEvento(string $encabezado): string
{
    $encabezado = trim(mb_strtolower($encabezado));
    $encabezado = str_replace(
        ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ'],
        ['a', 'e', 'i', 'o', 'u', 'u', 'n'],
        $encabezado
    );
    $encabezado = preg_replace('/[^a-z0-9]+/u', '_', $encabezado) ?? '';
    $encabezado = trim($encabezado, '_');

    return match ($encabezado) {
        'estado', 'estado_pago' => 'estado_pago',
        'forma', 'forma_de_pago', 'forma_pago' => 'forma_pago',
        'tipo', 'tipo_de_entrada', 'tipo_entrada' => 'tipo_entrada',
        'numeracion', 'numeracion_evento' => 'numeracion',
        'telefono', 'tel' => 'telefono',
        'observacion', 'obs' => 'observacion',
        default => $encabezado,
    };
}

/**
 * @return array{
 *   filas: array<int, array<string, string>>,
 *   diagnostico: array<string, mixed>
 * }
 */
function leerFilasArchivoImportEventos(string $rutaTemporal, string $nombreOriginal): array
{
    $diagnostico = [
        'archivo'              => $nombreOriginal,
        'formato'              => 'desconocido',
        'hojas_encontradas'    => [],
        'hoja_usada'           => '',
        'encabezados_archivo'  => [],
        'columnas_mapeadas'    => [],
        'columnas_faltantes'   => [],
        'filas_totales_hoja'   => 0,
        'filas_omitidas'       => [],
        'filas_validas'        => 0,
        'sugerencias'          => [],
    ];

    if ($rutaTemporal === '' || !is_readable($rutaTemporal)) {
        $diagnostico['sugerencias'][] = 'Verifica que el archivo se haya subido correctamente.';
        guardarDiagnosticoImportEventos($diagnostico);
        throw new InvalidArgumentException('No se pudo leer el archivo subido.');
    }

    $muestra = (string) file_get_contents($rutaTemporal, false, null, 0, 8192);
    if ($muestra === '') {
        $diagnostico['sugerencias'][] = 'El archivo está vacío. Agrega registros en la pestaña «Datos».';
        guardarDiagnosticoImportEventos($diagnostico);
        throw new InvalidArgumentException('El archivo está vacío.');
    }

    if (str_starts_with($muestra, 'PK')) {
        $diagnostico['formato'] = 'xlsx';
        $diagnostico['sugerencias'] = [
            'El archivo parece ser Excel .xlsx (formato binario).',
            'Descarga de nuevo la plantilla y guárdala como «Excel 97-2003 (.xls)» o expórtala como CSV.',
            'También puedes copiar la pestaña «Datos» a un archivo .csv sin cambiar los encabezados.',
        ];
        guardarDiagnosticoImportEventos($diagnostico);
        throw new InvalidArgumentException('Formato .xlsx no compatible. Guarda el archivo como .xls o .csv.');
    }

    if (str_starts_with($muestra, "\xD0\xCF\x11\xE0")) {
        $diagnostico['formato'] = 'xls_binario';
        $diagnostico['sugerencias'] = [
            'El archivo es un Excel binario (.xls) que no puede leerse directamente.',
            'En Excel: Archivo → Guardar como → «CSV UTF-8» o usa la plantilla descargada sin convertirla.',
            'Si descargaste la plantilla del sistema, súbela tal cual (.xls XML) o guarda solo como CSV.',
        ];
        guardarDiagnosticoImportEventos($diagnostico);
        throw new InvalidArgumentException('Formato Excel binario no compatible. Guarda como .csv o usa la plantilla .xls descargada.');
    }

    if (esArchivoSpreadsheetMlImportEventos($muestra)) {
        $diagnostico['formato'] = 'spreadsheetml';
        $resultado = leerFilasSpreadsheetMlImportEventos($rutaTemporal, $diagnostico);
        guardarDiagnosticoImportEventos($resultado['diagnostico']);

        return $resultado;
    }

    if (stripos($muestra, '<html') !== false || stripos($muestra, '<table') !== false) {
        $diagnostico['formato'] = 'html';
        $filas = leerFilasHtmlImportEventos($rutaTemporal, $diagnostico);
        $diagnostico['filas_validas'] = count($filas);
        guardarDiagnosticoImportEventos($diagnostico);

        return ['filas' => $filas, 'diagnostico' => $diagnostico];
    }

    $diagnostico['formato'] = 'csv';
    $filas = leerFilasCsvImportEventos($rutaTemporal, $diagnostico);
    $diagnostico['filas_validas'] = count($filas);
    guardarDiagnosticoImportEventos($diagnostico);

    return ['filas' => $filas, 'diagnostico' => $diagnostico];
}

/**
 * @param array<string, mixed> $diagnostico
 */
function guardarDiagnosticoImportEventos(array $diagnostico): void
{
    $GLOBALS['import_eventos_diagnostico'] = $diagnostico;
}

/**
 * @return array<string, mixed>
 */
function obtenerUltimoDiagnosticoImportEventos(): array
{
    return is_array($GLOBALS['import_eventos_diagnostico'] ?? null)
        ? $GLOBALS['import_eventos_diagnostico']
        : [];
}

/**
 * @param array<string, mixed> $diagnostico
 */
function construirMensajeErrorLecturaImportEventos(array $diagnostico): string
{
    $formato = (string) ($diagnostico['formato'] ?? 'desconocido');
    $filasOmitidas = (int) count($diagnostico['filas_omitidas'] ?? []);
    $filasTotales = (int) ($diagnostico['filas_totales_hoja'] ?? 0);
    $hoja = (string) ($diagnostico['hoja_usada'] ?? '');

    if ($filasTotales <= 1) {
        return 'La pestaña «Datos» solo tiene encabezados. Agrega al menos una fila con participantes.';
    }

    if ($filasOmitidas > 0 && (int) ($diagnostico['filas_validas'] ?? 0) === 0) {
        return 'Se encontraron filas en «Datos», pero ninguna es válida para importar. Revisa el detalle abajo.';
    }

    if ($formato === 'csv' && ($diagnostico['columnas_mapeadas'] ?? []) === []) {
        return 'No se reconocieron las columnas del CSV. Usa los mismos encabezados de la plantilla.';
    }

    if ($hoja !== '' && ($diagnostico['columnas_faltantes'] ?? []) !== []) {
        return 'Faltan columnas obligatorias en la hoja «' . $hoja . '». Revisa el detalle abajo.';
    }

    return 'El archivo no contiene filas de datos válidas para importar.';
}

/**
 * @param array<int, string> $encabezados
 * @param array<string, mixed> $diagnostico
 */
function completarDiagnosticoColumnasImportEventos(array $encabezados, array &$diagnostico): void
{
    $diagnostico['encabezados_archivo'] = $encabezados;
    $mapa = mapearEncabezadosImportEventos($encabezados);
    $diagnostico['columnas_mapeadas'] = array_keys($mapa);

    $requeridas = ['evento', 'nombre', 'telefono', 'fecha'];
    $diagnostico['columnas_faltantes'] = array_values(array_diff($requeridas, array_keys($mapa)));
}

function esArchivoSpreadsheetMlImportEventos(string $muestra): bool
{
    return stripos($muestra, 'urn:schemas-microsoft-com:office:spreadsheet') !== false
        || stripos($muestra, '<?mso-application') !== false
        || (stripos($muestra, '<Workbook') !== false && stripos($muestra, '<Worksheet') !== false);
}

/**
 * @param array<string, mixed> $diagnostico
 * @return array{filas: array<int, array<string, string>>, diagnostico: array<string, mixed>}
 */
function leerFilasSpreadsheetMlImportEventos(string $ruta, array $diagnostico): array
{
    $xml = file_get_contents($ruta);
    if ($xml === false) {
        $diagnostico['sugerencias'][] = 'No se pudo leer el contenido del archivo Excel.';
        throw new InvalidArgumentException('No se pudo leer el archivo Excel.');
    }

    if (str_starts_with($xml, "\xEF\xBB\xBF")) {
        $xml = substr($xml, 3);
    }

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    if (!$dom->loadXML($xml, LIBXML_NOERROR | LIBXML_NOWARNING)) {
        $diagnostico['sugerencias'][] = 'El XML del Excel está dañado. Descarga la plantilla nuevamente.';
        throw new InvalidArgumentException('El archivo Excel no tiene un formato válido.');
    }

    $xpath = new DOMXPath($dom);
    /** @var DOMNodeList<DOMElement> $hojas */
    $hojas = $xpath->query('//*[local-name()="Worksheet"]');
    if ($hojas === false || $hojas->length === 0) {
        $diagnostico['sugerencias'][] = 'No se encontraron hojas. Usa la plantilla descargada del sistema.';
        throw new InvalidArgumentException('No se encontró ninguna hoja en el archivo Excel.');
    }

    foreach ($hojas as $hoja) {
        if ($hoja instanceof DOMElement) {
            $diagnostico['hojas_encontradas'][] = obtenerNombreHojaSpreadsheetMl($hoja) ?: '(sin nombre)';
        }
    }

    $hojaDatos = null;
    foreach ($hojas as $hoja) {
        if (!$hoja instanceof DOMElement) {
            continue;
        }
        $nombre = obtenerNombreHojaSpreadsheetMl($hoja);
        if (strcasecmp($nombre, 'Datos') === 0) {
            $hojaDatos = $hoja;
            $diagnostico['hoja_usada'] = $nombre;
            break;
        }
    }

    if ($hojaDatos === null) {
        $primera = $hojas->item(0);
        $hojaDatos = $primera instanceof DOMElement ? $primera : null;
        if ($hojaDatos !== null) {
            $diagnostico['hoja_usada'] = obtenerNombreHojaSpreadsheetMl($hojaDatos) ?: 'Hoja 1';
            $diagnostico['sugerencias'][] = 'No se encontró la hoja «Datos». Se usó «' . $diagnostico['hoja_usada'] . '».';
        }
    }

    if ($hojaDatos === null) {
        throw new InvalidArgumentException('No se encontró la hoja «Datos» en el archivo.');
    }

    $filas = extraerFilasDatosSpreadsheetMl($hojaDatos, $diagnostico);
    $diagnostico['filas_validas'] = count($filas);

    if ($filas === []) {
        if ($diagnostico['sugerencias'] === []) {
            $diagnostico['sugerencias'][] = 'Agrega filas en la pestaña «Datos» debajo del encabezado.';
            $diagnostico['sugerencias'][] = 'Copia el formato desde «Guía y ejemplos» si necesitas un ejemplo.';
        }
    }

    return ['filas' => $filas, 'diagnostico' => $diagnostico];
}

function obtenerNombreHojaSpreadsheetMl(DOMElement $hoja): string
{
    if ($hoja->hasAttribute('ss:Name')) {
        return trim($hoja->getAttribute('ss:Name'));
    }

    if ($hoja->hasAttribute('Name')) {
        return trim($hoja->getAttribute('Name'));
    }

    foreach ($hoja->attributes ?? [] as $attr) {
        if (strcasecmp($attr->localName ?? '', 'Name') === 0) {
            return trim($attr->nodeValue ?? '');
        }
    }

    return '';
}

/**
 * @param array<string, mixed> $diagnostico
 * @return array<int, array<string, string>>
 */
function extraerFilasDatosSpreadsheetMl(DOMElement $hoja, array &$diagnostico): array
{
    $filasDom = [];
    foreach ($hoja->getElementsByTagName('*') as $nodo) {
        if ($nodo instanceof DOMElement && strcasecmp($nodo->localName ?? $nodo->tagName, 'Row') === 0) {
            $filasDom[] = $nodo;
        }
    }

    $diagnostico['filas_totales_hoja'] = count($filasDom);

    if ($filasDom === []) {
        $diagnostico['sugerencias'][] = 'La hoja «Datos» no tiene filas. Verifica que hayas editado la pestaña correcta.';
        return [];
    }

    $encabezados = [];
    $indiceEncabezado = -1;
    $celdasEncabezado = [];

    foreach ($filasDom as $indice => $fila) {
        $celdas = extraerCeldasFilaSpreadsheetMl($fila);
        $mapa = mapearEncabezadosImportEventos($celdas);
        if (isset($mapa['evento'], $mapa['nombre'])) {
            $encabezados = $mapa;
            $indiceEncabezado = $indice;
            $celdasEncabezado = $celdas;
            break;
        }
    }

    if ($encabezados === []) {
        $diagnostico['sugerencias'][] = 'No se encontró la fila de encabezados (Evento, Nombre, etc.). No modifiques la fila 1.';
        if ($filasDom !== []) {
            $primerasCeldas = extraerCeldasFilaSpreadsheetMl($filasDom[0]);
            $diagnostico['encabezados_archivo'] = $primerasCeldas;
        }
        return [];
    }

    completarDiagnosticoColumnasImportEventos($celdasEncabezado, $diagnostico);

    if ($diagnostico['columnas_faltantes'] !== []) {
        $diagnostico['sugerencias'][] = 'Faltan columnas: ' . implode(', ', $diagnostico['columnas_faltantes']) . '.';
    }

    $filas = [];
    for ($i = $indiceEncabezado + 1, $total = count($filasDom); $i < $total; $i++) {
        $numeroFila = $i + 1;
        $celdas = extraerCeldasFilaSpreadsheetMl($filasDom[$i]);
        $motivoOmitida = null;
        $datos = construirFilaImportEvento($encabezados, $celdas, $motivoOmitida);
        if ($datos !== null) {
            $datos['_fila_excel'] = $numeroFila;
            $filas[] = $datos;
            continue;
        }

        if ($motivoOmitida !== null) {
            $diagnostico['filas_omitidas'][] = [
                'fila'    => $numeroFila,
                'motivo'  => $motivoOmitida,
                'preview' => resumenFilaImportEvento($encabezados, $celdas),
            ];
        }
    }

    return $filas;
}

/**
 * @param array<string, int> $mapa
 * @param array<int, string> $valores
 */
function resumenFilaImportEvento(array $mapa, array $valores): string
{
    $partes = [];
    foreach (['evento', 'nombre', 'telefono'] as $clave) {
        if (!isset($mapa[$clave])) {
            continue;
        }
        $valor = trim((string) ($valores[$mapa[$clave]] ?? ''));
        if ($valor !== '') {
            $partes[] = $clave . ': ' . $valor;
        }
    }

    return $partes !== [] ? implode(' · ', $partes) : '(vacía)';
}

/**
 * @return array<int, string>
 */
function extraerCeldasFilaSpreadsheetMl(DOMElement $fila): array
{
    $valores = [];
    $columna = 0;

    foreach ($fila->childNodes as $nodo) {
        if (!$nodo instanceof DOMElement) {
            continue;
        }
        if (strcasecmp($nodo->localName ?? $nodo->tagName, 'Cell') !== 0) {
            continue;
        }

        $indiceAttr = '';
        if ($nodo->hasAttribute('ss:Index')) {
            $indiceAttr = $nodo->getAttribute('ss:Index');
        } elseif ($nodo->hasAttribute('Index')) {
            $indiceAttr = $nodo->getAttribute('Index');
        } else {
            foreach ($nodo->attributes ?? [] as $attr) {
                if (strcasecmp($attr->localName ?? '', 'Index') === 0) {
                    $indiceAttr = $attr->nodeValue ?? '';
                    break;
                }
            }
        }

        if ($indiceAttr !== '') {
            $columna = max(0, (int) $indiceAttr - 1);
        }

        $texto = '';
        foreach ($nodo->getElementsByTagName('*') as $hijo) {
            if ($hijo instanceof DOMElement && strcasecmp($hijo->localName ?? $hijo->tagName, 'Data') === 0) {
                $texto = trim(preg_replace('/\s+/u', ' ', $hijo->textContent ?? '') ?? '');
                break;
            }
        }

        $valores[$columna] = $texto;
        $columna++;
    }

    if ($valores === []) {
        return [];
    }

    $maximo = max(array_keys($valores));
    $densas = [];
    for ($i = 0; $i <= $maximo; $i++) {
        $densas[] = $valores[$i] ?? '';
    }

    return $densas;
}

/**
 * @param array<string, mixed> $diagnostico
 * @return array<int, array<string, string>>
 */
function leerFilasCsvImportEventos(string $ruta, array &$diagnostico): array
{
    $diagnostico['hoja_usada'] = 'CSV';

    $contenido = file_get_contents($ruta);
    if ($contenido === false) {
        throw new InvalidArgumentException('No se pudo leer el archivo CSV.');
    }

    if (str_starts_with($contenido, "\xEF\xBB\xBF")) {
        $contenido = substr($contenido, 3);
    }

    $lineas = preg_split('/\R/u', $contenido) ?: [];
    $lineas = array_values(array_filter($lineas, static fn (string $linea): bool => trim($linea) !== ''));

    $diagnostico['filas_totales_hoja'] = count($lineas);

    if ($lineas === []) {
        $diagnostico['sugerencias'][] = 'El CSV no tiene contenido.';
        return [];
    }

    $delimitador = substr_count($lineas[0], ';') > substr_count($lineas[0], ',') ? ';' : ',';
    $encabezados = str_getcsv($lineas[0], $delimitador);
    completarDiagnosticoColumnasImportEventos($encabezados, $diagnostico);

    $mapa = mapearEncabezadosImportEventos($encabezados);
    if ($mapa === []) {
        $diagnostico['sugerencias'][] = 'La primera fila del CSV no tiene encabezados reconocidos.';
        throw new InvalidArgumentException('No se encontraron columnas válidas en el archivo CSV.');
    }

    if ($diagnostico['columnas_faltantes'] !== []) {
        $diagnostico['sugerencias'][] = 'Faltan columnas obligatorias: ' . implode(', ', $diagnostico['columnas_faltantes']) . '.';
    }

    $filas = [];
    for ($i = 1, $total = count($lineas); $i < $total; $i++) {
        $numeroFila = $i + 1;
        $valores = str_getcsv($lineas[$i], $delimitador);
        $motivoOmitida = null;
        $fila = construirFilaImportEvento($mapa, $valores, $motivoOmitida);
        if ($fila !== null) {
            $fila['_fila_excel'] = $numeroFila;
            $filas[] = $fila;
            continue;
        }

        if ($motivoOmitida !== null) {
            $diagnostico['filas_omitidas'][] = [
                'fila'    => $numeroFila,
                'motivo'  => $motivoOmitida,
                'preview' => resumenFilaImportEvento($mapa, $valores),
            ];
        }
    }

    if ($filas === [] && count($lineas) <= 1) {
        $diagnostico['sugerencias'][] = 'El CSV solo tiene encabezados. Agrega filas con datos debajo.';
    }

    return $filas;
}

/**
 * @param array<string, mixed> $diagnostico
 * @return array<int, array<string, string>>
 */
function leerFilasHtmlImportEventos(string $ruta, array &$diagnostico): array
{
    $diagnostico['hoja_usada'] = 'HTML';

    $html = file_get_contents($ruta);
    if ($html === false) {
        throw new InvalidArgumentException('No se pudo leer el archivo Excel.');
    }

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    if (!$dom->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING)) {
        throw new InvalidArgumentException('El archivo Excel no tiene un formato válido.');
    }

    /** @var DOMNodeList<DOMElement> $tablas */
    $tablas = $dom->getElementsByTagName('table');
    foreach ($tablas as $tabla) {
        $filasTabla = extraerFilasTablaHtmlImportEventos($tabla, $diagnostico);
        if ($filasTabla !== []) {
            return $filasTabla;
        }
    }

    $diagnostico['sugerencias'][] = 'No se encontró una tabla con columnas Evento y Nombre.';
    throw new InvalidArgumentException('No se encontró la tabla de datos en el archivo.');
}

/**
 * @param array<string, mixed> $diagnostico
 * @return array<int, array<string, string>>
 */
function extraerFilasTablaHtmlImportEventos(DOMElement $tabla, array &$diagnostico): array
{
    $filasDom = $tabla->getElementsByTagName('tr');
    $diagnostico['filas_totales_hoja'] = $filasDom->length;

    if ($filasDom->length === 0) {
        return [];
    }

    $encabezados = [];
    $indiceEncabezado = -1;
    $celdasEncabezado = [];

    for ($i = 0; $i < $filasDom->length; $i++) {
        $fila = $filasDom->item($i);
        if (!$fila instanceof DOMElement) {
            continue;
        }

        $celdas = extraerCeldasFilaHtml($fila);
        $mapa = mapearEncabezadosImportEventos($celdas);
        if (isset($mapa['evento'], $mapa['nombre'])) {
            $encabezados = $mapa;
            $indiceEncabezado = $i;
            $celdasEncabezado = $celdas;
            break;
        }
    }

    if ($encabezados === []) {
        return [];
    }

    completarDiagnosticoColumnasImportEventos($celdasEncabezado, $diagnostico);

    $filas = [];
    for ($i = $indiceEncabezado + 1; $i < $filasDom->length; $i++) {
        $numeroFila = $i + 1;
        $fila = $filasDom->item($i);
        if (!$fila instanceof DOMElement) {
            continue;
        }

        $celdas = extraerCeldasFilaHtml($fila);
        $motivoOmitida = null;
        $datos = construirFilaImportEvento($encabezados, $celdas, $motivoOmitida);
        if ($datos !== null) {
            $datos['_fila_excel'] = $numeroFila;
            $filas[] = $datos;
            continue;
        }

        if ($motivoOmitida !== null) {
            $diagnostico['filas_omitidas'][] = [
                'fila'    => $numeroFila,
                'motivo'  => $motivoOmitida,
                'preview' => resumenFilaImportEvento($encabezados, $celdas),
            ];
        }
    }

    return $filas;
}

/**
 * @return array<int, string>
 */
function extraerCeldasFilaHtml(DOMElement $fila): array
{
    $celdas = [];
    foreach ($fila->childNodes as $nodo) {
        if (!$nodo instanceof DOMElement) {
            continue;
        }
        if (!in_array(strtolower($nodo->tagName), ['td', 'th'], true)) {
            continue;
        }
        $celdas[] = trim(preg_replace('/\s+/u', ' ', $nodo->textContent ?? '') ?? '');
    }

    return $celdas;
}

/**
 * @param array<int, string> $encabezados
 * @return array<string, int>
 */
function mapearEncabezadosImportEventos(array $encabezados): array
{
    $columnasValidas = array_keys(columnasPlantillaImportEventos());
    $mapa = [];

    foreach ($encabezados as $indice => $encabezado) {
        $clave = normalizarEncabezadoImportEvento($encabezado);
        if (in_array($clave, $columnasValidas, true)) {
            $mapa[$clave] = $indice;
        }
    }

    return $mapa;
}

/**
 * @param array<string, int> $mapa
 * @param array<int, string> $valores
 * @return array<string, string>|null
 */
function construirFilaImportEvento(array $mapa, array $valores, ?string &$motivoOmitida = null): ?array
{
    $motivoOmitida = null;
    $fila = [];
    foreach ($mapa as $clave => $indice) {
        $fila[$clave] = trim((string) ($valores[$indice] ?? ''));
    }

    if (($fila['evento'] ?? '') === '' && ($fila['nombre'] ?? '') === '') {
        $motivoOmitida = 'Fila vacía (sin evento ni nombre).';
        return null;
    }

    if (filaEsEjemploImportEvento($fila)) {
        $motivoOmitida = 'Fila de ejemplo (Observación empieza con EJEMPLO:).';
        return null;
    }

    $primeraCelda = mb_strtolower(trim((string) ($valores[0] ?? '')));
    if (str_contains($primeraCelda, 'nombre del evento') || str_contains($primeraCelda, 'instruc')) {
        $motivoOmitida = 'Fila instructiva de la plantilla.';
        return null;
    }

    if (($fila['evento'] ?? '') === '') {
        $motivoOmitida = 'Falta el nombre del evento.';
        return null;
    }

    if (($fila['nombre'] ?? '') === '') {
        $motivoOmitida = 'Falta el nombre del participante.';
        return null;
    }

    return $fila;
}

function resolverEventoImportPorNombre(string $nombreEvento): ?array
{
    $nombreEvento = trim($nombreEvento);
    if ($nombreEvento === '') {
        return null;
    }

    foreach (obtenerEventos() as $evento) {
        if (strcasecmp(trim((string) ($evento['nombre'] ?? '')), $nombreEvento) === 0) {
            if (!isset($evento['tipos_entrada'])) {
                $evento['tipos_entrada'] = obtenerTiposEntradaPorEvento((int) ($evento['id'] ?? 0));
            }

            return $evento;
        }
    }

    return null;
}

/**
 * @param array<string, mixed> $evento
 */
function resolverTipoEntradaImportPorNombre(array $evento, string $nombreTipo): ?array
{
    $nombreTipo = trim($nombreTipo);
    if ($nombreTipo === '') {
        return null;
    }

    foreach ($evento['tipos_entrada'] ?? [] as $tipo) {
        if (strcasecmp(trim((string) ($tipo['nombre'] ?? '')), $nombreTipo) === 0) {
            return $tipo;
        }
    }

    return null;
}

/**
 * @param array<string, string> $fila
 * @return array<string, mixed>
 */
function prepararEntradaImportEvento(array $fila, array $evento, array $tipoEntrada): array
{
    return [
        'evento_id'       => (int) ($evento['id'] ?? 0),
        'tipo_entrada_id' => (int) ($tipoEntrada['id'] ?? 0),
        'nombre'          => $fila['nombre'] ?? '',
        'telefono'        => $fila['telefono'] ?? '',
        'fecha'           => $fila['fecha'] ?? '',
        'valor'           => $fila['valor'] ?? '',
        'numeracion'      => $fila['numeracion'] ?? '',
        'estado_pago'     => $fila['estado_pago'] ?? '',
        'forma_pago'      => $fila['forma_pago'] ?? '',
        'observacion'     => $fila['observacion'] ?? '',
    ];
}

/**
 * @param array<string, mixed> $archivo
 * @param array<string, mixed> $usuario
 * @return array{importados: int, omitidos: int, errores: array<int, array{fila: int, mensaje: string}>}
 */
function procesarImportacionRegistrosEventos(array $archivo, array $usuario): array
{
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        throw new InvalidArgumentException('Selecciona un archivo para importar.');
    }

    if (($archivo['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('No se pudo subir el archivo. Intenta de nuevo.');
    }

    $nombreOriginal = (string) ($archivo['name'] ?? 'import.csv');
    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    if (!in_array($extension, ['csv', 'xls', 'xlsx'], true)) {
        throw new InvalidArgumentException('Formato no válido. Usa .csv, .xls o la plantilla descargada.');
    }

    $lectura = leerFilasArchivoImportEventos((string) ($archivo['tmp_name'] ?? ''), $nombreOriginal);
    $filas = $lectura['filas'];
    $diagnostico = $lectura['diagnostico'];

    if ($filas === []) {
        throw new InvalidArgumentException(construirMensajeErrorLecturaImportEventos($diagnostico));
    }

    $rol = (string) ($usuario['rol'] ?? ROL_SUPERADMIN);
    $importados = 0;
    $errores = [];
    $pdo = getConnection();
    $pdo->beginTransaction();

    try {
        foreach ($filas as $indice => $fila) {
            $numeroFila = (int) ($fila['_fila_excel'] ?? ($indice + 2));
            unset($fila['_fila_excel']);

            try {
                $evento = resolverEventoImportPorNombre($fila['evento'] ?? '');
                if (!$evento) {
                    throw new InvalidArgumentException('Evento no encontrado: «' . ($fila['evento'] ?? '') . '».');
                }

                $tipoEntrada = resolverTipoEntradaImportPorNombre($evento, $fila['tipo_entrada'] ?? '');
                if (!$tipoEntrada) {
                    throw new InvalidArgumentException('Tipo de entrada no encontrado: «' . ($fila['tipo_entrada'] ?? '') . '».');
                }

                $entrada = prepararEntradaImportEvento($fila, $evento, $tipoEntrada);
                $validados = validarDatosRegistroEvento($entrada, $evento, $rol, false);

                insertarValorAdicional([
                    'tipo'                  => TIPO_VALOR_EVENTOS_INTERNO,
                    'nombre'                => $validados['nombre'],
                    'fecha'                 => $validados['fecha'],
                    'telefono'              => $validados['telefono'],
                    'valor'                 => $validados['valor'],
                    'observacion'           => $validados['observacion'],
                    'evento_id'             => $validados['evento_id'],
                    'numeracion'            => $validados['numeracion'],
                    'forma_pago'            => $validados['forma_pago'],
                    'tipo_entrada_id'       => $validados['tipo_entrada_id'],
                    'tipo_entrada'          => $validados['tipo_entrada'],
                    'estado_pago'           => $validados['estado_pago'],
                    'info_adicional'        => $validados['info_adicional'] ?? null,
                    'registrado_por_id'     => (int) ($usuario['id'] ?? 0),
                    'registrado_por_nombre' => (string) ($usuario['nombre'] ?? $usuario['usuario'] ?? 'Importación'),
                ]);
                $importados++;
            } catch (InvalidArgumentException $e) {
                $errores[] = [
                    'fila'     => $numeroFila,
                    'mensaje'  => $e->getMessage(),
                ];
            }
        }

        if ($importados === 0) {
            $pdo->rollBack();
        } else {
            $pdo->commit();
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    return [
        'importados'  => $importados,
        'omitidos'    => count($errores),
        'errores'     => $errores,
        'diagnostico' => $diagnostico,
    ];
}
