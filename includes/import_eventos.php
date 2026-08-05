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
    $columnas = columnasPlantillaImportEventos();
    $eventos = obtenerEventos();
    $ejemplos = obtenerFilasEjemploPlantillaImportEventos($eventos);
    $fechaHoy = date('Y-m-d');

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="plantilla-registros-eventos.xls"');
    header('Cache-Control: max-age=0');

    echo "\xEF\xBB\xBF";
    echo '<html><head><meta charset="UTF-8"></head><body>';
    echo '<h2>Plantilla · Registros de eventos</h2>';

    echo '<h3>Instrucciones generales</h3>';
    echo '<ol>';
    echo '<li>Use la tabla <strong>Datos</strong>: una fila por participante. <strong>No modifique los encabezados</strong> de la primera fila.</li>';
    echo '<li>Las filas que empiezan con <strong>EJEMPLO:</strong> en Observación son solo referencia; <strong>el sistema las omite al importar</strong>. Puede borrarlas o dejarlas.</li>';
    echo '<li>Agregue sus registros reales debajo de los ejemplos o reemplace los ejemplos por sus datos.</li>';
    echo '<li>Consulte la tabla <strong>Referencia</strong> al final para copiar nombres exactos de evento y tipo de entrada.</li>';
    echo '<li>Guarde el archivo y súbalo en <strong>Avanzado → Importar registros</strong>.</li>';
    echo '</ol>';

    echo '<h3>Descripción de columnas</h3>';
    echo '<table border="1" cellpadding="4" cellspacing="0">';
    echo '<tr><th>Columna</th><th>Obligatorio</th><th>Valores permitidos / Notas</th></tr>';
    echo '<tr><td>Evento</td><td>Sí</td><td>Nombre exacto del evento (ver Referencia).</td></tr>';
    echo '<tr><td>Tipo entrada</td><td>Sí</td><td>Nombre exacto del tipo dentro del evento (ver Referencia).</td></tr>';
    echo '<tr><td>Nombre</td><td>Sí</td><td>Nombre completo del participante.</td></tr>';
    echo '<tr><td>Teléfono</td><td>Sí</td><td>Número de contacto.</td></tr>';
    echo '<tr><td>Fecha</td><td>Sí</td><td>Formato AAAA-MM-DD (ej. ' . htmlspecialchars($fechaHoy) . ').</td></tr>';
    echo '<tr><td>Valor</td><td>Sí*</td><td>Monto numérico. En entradas gratis use 0. Puede diferir del catálogo (promociones).</td></tr>';
    echo '<tr><td>Numeración</td><td>Condicional</td><td>Obligatoria si el evento requiere numeración (ver Referencia).</td></tr>';
    echo '<tr><td>Estado</td><td>Sí</td><td><code>por_cancelar</code> o <code>pagado</code>. Entradas gratis: use <code>pagado</code>.</td></tr>';
    echo '<tr><td>Forma de pago</td><td>Sí</td><td><code>pendiente</code>, <code>efectivo</code>, <code>transferencia</code> o <code>gratuito</code>.</td></tr>';
    echo '<tr><td>Observación</td><td>No</td><td>Texto libre. No use el prefijo EJEMPLO: en registros reales.</td></tr>';
    echo '</table>';

    echo '<h3>Reglas de estado y forma de pago</h3>';
    echo '<table border="1" cellpadding="4" cellspacing="0">';
    echo '<tr><th>Situación</th><th>Estado</th><th>Forma de pago</th><th>Notas</th></tr>';
    echo '<tr><td>Aún no ha pagado</td><td>por_cancelar</td><td>pendiente</td><td>Caso más común para inscripciones pendientes.</td></tr>';
    echo '<tr><td>Pagó en efectivo</td><td>pagado</td><td>efectivo</td><td>Indique el valor cobrado.</td></tr>';
    echo '<tr><td>Pagó por transferencia</td><td>pagado</td><td>transferencia</td><td>Indique el valor recibido.</td></tr>';
    echo '<tr><td>Entrada gratuita</td><td>pagado</td><td>gratuito</td><td>Valor 0. El sistema marca el registro como completado.</td></tr>';
    echo '<tr><td>Promoción / descuento</td><td>pagado o por_cancelar</td><td>según corresponda</td><td>Valor puede ser menor al del catálogo.</td></tr>';
    echo '</table>';

    echo '<h3>Datos (encabezados + ejemplos)</h3>';
    echo '<p><em>Copie una fila de ejemplo como base o agregue filas nuevas con el mismo formato.</em></p>';
    echo '<table border="1" cellpadding="4" cellspacing="0">';
    echo '<tr>';
    foreach ($columnas as $etiqueta) {
        echo '<th>' . htmlspecialchars($etiqueta) . '</th>';
    }
    echo '</tr>';

    foreach ($ejemplos as $ejemplo) {
        echo '<tr>';
        foreach (array_keys($columnas) as $clave) {
            echo '<td>' . htmlspecialchars((string) ($ejemplo[$clave] ?? '')) . '</td>';
        }
        echo '</tr>';
    }

    echo '</table>';

    echo '<h3>Referencia · Eventos y tipos de entrada del sistema</h3>';
    echo '<p><em>Copie estos nombres tal cual aparecen en las columnas Evento y Tipo entrada.</em></p>';
    echo '<table border="1" cellpadding="4" cellspacing="0">';
    echo '<tr><th>Evento</th><th>Tipo entrada</th><th>Valor catálogo</th><th>Gratis</th><th>Requiere numeración</th><th>Habilitado</th></tr>';
    if ($eventos === []) {
        echo '<tr><td colspan="6">No hay eventos registrados. Cree eventos en el módulo Eventos antes de importar.</td></tr>';
    } else {
        foreach ($eventos as $evento) {
            $tipos = $evento['tipos_entrada'] ?? [];
            if ($tipos === []) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars((string) ($evento['nombre'] ?? '')) . '</td>';
                echo '<td colspan="5">Sin tipos de entrada configurados</td>';
                echo '</tr>';
                continue;
            }
            foreach ($tipos as $indice => $tipo) {
                echo '<tr>';
                echo '<td>' . ($indice === 0 ? htmlspecialchars((string) ($evento['nombre'] ?? '')) : '') . '</td>';
                echo '<td>' . htmlspecialchars((string) ($tipo['nombre'] ?? '')) . '</td>';
                echo '<td>' . htmlspecialchars(formatearMonto((float) ($tipo['valor'] ?? 0))) . '</td>';
                echo '<td>' . ((int) ($tipo['es_gratis'] ?? 0) === 1 ? 'Sí' : 'No') . '</td>';
                echo '<td>' . ((int) ($evento['requiere_numeracion'] ?? 0) === 1 ? 'Sí' : 'No') . '</td>';
                echo '<td>' . ((int) ($evento['habilitado'] ?? 0) === 1 ? 'Sí' : 'No') . '</td>';
                echo '</tr>';
            }
        }
    }
    echo '</table>';
    echo '</body></html>';
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
 * @return array<int, array<string, string>>
 */
function leerFilasArchivoImportEventos(string $rutaTemporal, string $nombreOriginal): array
{
    if ($rutaTemporal === '' || !is_readable($rutaTemporal)) {
        throw new InvalidArgumentException('No se pudo leer el archivo subido.');
    }

    $muestra = (string) file_get_contents($rutaTemporal, false, null, 0, 4096);
    if ($muestra === '') {
        throw new InvalidArgumentException('El archivo está vacío.');
    }

    if (stripos($muestra, '<html') !== false || stripos($muestra, '<table') !== false) {
        return leerFilasHtmlImportEventos($rutaTemporal);
    }

    return leerFilasCsvImportEventos($rutaTemporal);
}

/**
 * @return array<int, array<string, string>>
 */
function leerFilasCsvImportEventos(string $ruta): array
{
    $contenido = file_get_contents($ruta);
    if ($contenido === false) {
        throw new InvalidArgumentException('No se pudo leer el archivo CSV.');
    }

    if (str_starts_with($contenido, "\xEF\xBB\xBF")) {
        $contenido = substr($contenido, 3);
    }

    $lineas = preg_split('/\R/u', $contenido) ?: [];
    $lineas = array_values(array_filter($lineas, static fn (string $linea): bool => trim($linea) !== ''));

    if ($lineas === []) {
        return [];
    }

    $delimitador = substr_count($lineas[0], ';') > substr_count($lineas[0], ',') ? ';' : ',';
    $encabezados = str_getcsv($lineas[0], $delimitador);
    $mapa = mapearEncabezadosImportEventos($encabezados);

    if ($mapa === []) {
        throw new InvalidArgumentException('No se encontraron columnas válidas en el archivo.');
    }

    $filas = [];
    for ($i = 1, $total = count($lineas); $i < $total; $i++) {
        $valores = str_getcsv($lineas[$i], $delimitador);
        $fila = construirFilaImportEvento($mapa, $valores);
        if ($fila !== null) {
            $filas[] = $fila;
        }
    }

    return $filas;
}

/**
 * @return array<int, array<string, string>>
 */
function leerFilasHtmlImportEventos(string $ruta): array
{
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
        $filasTabla = extraerFilasTablaHtmlImportEventos($tabla);
        if ($filasTabla !== []) {
            return $filasTabla;
        }
    }

    throw new InvalidArgumentException('No se encontró la tabla de datos en el archivo.');
}

/**
 * @return array<int, array<string, string>>
 */
function extraerFilasTablaHtmlImportEventos(DOMElement $tabla): array
{
    $filasDom = $tabla->getElementsByTagName('tr');
    if ($filasDom->length === 0) {
        return [];
    }

    $encabezados = [];
    $indiceEncabezado = -1;

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
            break;
        }
    }

    if ($encabezados === []) {
        return [];
    }

    $filas = [];
    for ($i = $indiceEncabezado + 1; $i < $filasDom->length; $i++) {
        $fila = $filasDom->item($i);
        if (!$fila instanceof DOMElement) {
            continue;
        }

        $celdas = extraerCeldasFilaHtml($fila);
        $datos = construirFilaImportEvento($encabezados, $celdas);
        if ($datos !== null) {
            $filas[] = $datos;
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
function construirFilaImportEvento(array $mapa, array $valores): ?array
{
    $fila = [];
    foreach ($mapa as $clave => $indice) {
        $fila[$clave] = trim((string) ($valores[$indice] ?? ''));
    }

    if (($fila['evento'] ?? '') === '' && ($fila['nombre'] ?? '') === '') {
        return null;
    }

    if (filaEsEjemploImportEvento($fila)) {
        return null;
    }

    $primeraCelda = mb_strtolower(trim((string) ($valores[0] ?? '')));
    if (str_contains($primeraCelda, 'nombre del evento') || str_contains($primeraCelda, 'instruc')) {
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

    $filas = leerFilasArchivoImportEventos((string) ($archivo['tmp_name'] ?? ''), $nombreOriginal);
    if ($filas === []) {
        throw new InvalidArgumentException('El archivo no contiene filas de datos para importar.');
    }

    $rol = (string) ($usuario['rol'] ?? ROL_SUPERADMIN);
    $importados = 0;
    $errores = [];
    $pdo = getConnection();
    $pdo->beginTransaction();

    try {
        foreach ($filas as $indice => $fila) {
            $numeroFila = $indice + 2;

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
                $validados = validarDatosRegistroEvento($entrada, $evento, $rol);

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
        'importados' => $importados,
        'omitidos'   => count($errores),
        'errores'    => $errores,
    ];
}
