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

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="plantilla-registros-eventos.xls"');
    header('Cache-Control: max-age=0');

    echo "\xEF\xBB\xBF";
    echo '<html><head><meta charset="UTF-8"></head><body>';
    echo '<h2>Plantilla · Registros de eventos</h2>';
    echo '<p><strong>Instrucciones:</strong></p>';
    echo '<ol>';
    echo '<li>Complete una fila por participante en la tabla «Datos» (no modifique los encabezados).</li>';
    echo '<li><strong>Evento</strong> y <strong>Tipo entrada</strong> deben coincidir exactamente con la hoja de referencia.</li>';
    echo '<li><strong>Fecha</strong> en formato AAAA-MM-DD (ej. ' . htmlspecialchars(date('Y-m-d')) . ').</li>';
    echo '<li><strong>Estado:</strong> por_cancelar o pagado. Si es por_cancelar, deje forma de pago en pendiente.</li>';
    echo '<li><strong>Forma de pago:</strong> pendiente, efectivo, transferencia o gratuito (entradas gratis).</li>';
    echo '<li>Guarde el archivo y súbalo en Avanzado → Importar registros.</li>';
    echo '</ol>';

    echo '<h3>Datos</h3>';
    echo '<table border="1" cellpadding="4" cellspacing="0">';
    echo '<tr>';
    foreach ($columnas as $etiqueta) {
        echo '<th>' . htmlspecialchars($etiqueta) . '</th>';
    }
    echo '</tr>';
    echo '<tr>';
    foreach (array_keys($columnas) as $clave) {
        $ejemplo = match ($clave) {
            'evento'       => 'Nombre del evento',
            'tipo_entrada' => 'General',
            'nombre'       => 'Juan Pérez',
            'telefono'     => '3001234567',
            'fecha'        => date('Y-m-d'),
            'valor'        => '50000',
            'numeracion'   => '',
            'estado_pago'  => 'por_cancelar',
            'forma_pago'   => 'pendiente',
            'observacion'  => '',
            default        => '',
        };
        echo '<td>' . htmlspecialchars($ejemplo) . '</td>';
    }
    echo '</tr>';
    echo '</table>';

    echo '<h3>Referencia · Eventos y tipos de entrada</h3>';
    echo '<table border="1" cellpadding="4" cellspacing="0">';
    echo '<tr><th>Evento</th><th>Tipo entrada</th><th>Valor catálogo</th><th>Gratis</th><th>Requiere numeración</th></tr>';
    if ($eventos === []) {
        echo '<tr><td colspan="5">No hay eventos registrados.</td></tr>';
    } else {
        foreach ($eventos as $evento) {
            $tipos = $evento['tipos_entrada'] ?? [];
            if ($tipos === []) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars((string) ($evento['nombre'] ?? '')) . '</td>';
                echo '<td colspan="4">Sin tipos de entrada</td>';
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
                echo '</tr>';
            }
        }
    }
    echo '</table>';
    echo '</body></html>';
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
