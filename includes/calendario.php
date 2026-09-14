<?php

require_once __DIR__ . '/esquema.php';

const CALENDARIO_UPLOAD_DIR = 'uploads/calendario';
const CALENDARIO_MAX_BYTES = 5242880; // 5 MB

function asegurarTablaCalendarioEventos(?PDO $pdo = null): void
{
    static $listo = false;

    if ($listo) {
        return;
    }

    $pdo = $pdo ?? getConnection();
    migrarTablaCalendarioEventos($pdo);
    $listo = true;
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerEventosCalendario(?int $anio = null, ?int $mes = null): array
{
    asegurarTablaCalendarioEventos();
    $pdo = getConnection();

    if ($anio !== null && $mes !== null && $anio > 0 && $mes >= 1 && $mes <= 12) {
        $inicio = sprintf('%04d-%02d-01', $anio, $mes);
        $fin = date('Y-m-t', strtotime($inicio));
        $stmt = $pdo->prepare(
            'SELECT * FROM calendario_eventos
             WHERE fecha <= ?
               AND COALESCE(fecha_fin, fecha) >= ?
             ORDER BY fecha ASC, titulo ASC, id ASC'
        );
        $stmt->execute([$fin, $inicio]);

        return $stmt->fetchAll();
    }

    return $pdo->query(
        'SELECT * FROM calendario_eventos ORDER BY fecha DESC, titulo ASC, id ASC'
    )->fetchAll();
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerEventosCalendarioActivos(?int $anio = null, ?int $mes = null): array
{
    $eventos = obtenerEventosCalendario($anio, $mes);

    return array_values(array_filter(
        $eventos,
        static fn (array $evento): bool => (int) ($evento['activo'] ?? 0) === 1
    ));
}

function obtenerEventoCalendario(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }

    asegurarTablaCalendarioEventos();
    $pdo = getConnection();
    $stmt = $pdo->prepare('SELECT * FROM calendario_eventos WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $fila = $stmt->fetch();

    return $fila ?: null;
}

function contarEventosCalendario(): int
{
    asegurarTablaCalendarioEventos();
    $pdo = getConnection();

    return (int) $pdo->query('SELECT COUNT(*) FROM calendario_eventos')->fetchColumn();
}

/**
 * @param array<string, mixed> $datos
 * @param array<string, mixed>|null $archivo
 */
function crearEventoCalendario(array $datos, ?array $archivo = null): int
{
    asegurarTablaCalendarioEventos();
    $normalizado = normalizarDatosEventoCalendario($datos, true);
    $foto = guardarFotoEventoCalendario($archivo) ?? '';

    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO calendario_eventos (titulo, descripcion, fecha, fecha_fin, foto, activo)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $normalizado['titulo'],
            $normalizado['descripcion'],
            $normalizado['fecha'],
            $normalizado['fecha_fin'],
            $foto,
            $normalizado['activo'],
        ]);

        return (int) $pdo->lastInsertId();
    } catch (Throwable $e) {
        if ($foto !== '') {
            eliminarArchivoFotoCalendario($foto);
        }
        throw $e;
    }
}

/**
 * @param array<string, mixed> $datos
 * @param array<string, mixed>|null $archivo
 */
function actualizarEventoCalendario(int $id, array $datos, ?array $archivo = null): bool
{
    if ($id <= 0) {
        throw new InvalidArgumentException('Evento no válido.');
    }

    $actual = obtenerEventoCalendario($id);

    if (!$actual) {
        throw new InvalidArgumentException('Evento no encontrado.');
    }

    $normalizado = normalizarDatosEventoCalendario($datos, false);
    $fotoActual = trim((string) ($actual['foto'] ?? ''));
    $fotoNueva = null;

    if ($archivo !== null && ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $fotoNueva = guardarFotoEventoCalendario($archivo);
    }

    $fotoFinal = $fotoNueva ?? $fotoActual;

    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'UPDATE calendario_eventos
         SET titulo = ?, descripcion = ?, fecha = ?, fecha_fin = ?, foto = ?, activo = ?
         WHERE id = ?'
    );
    $ok = $stmt->execute([
        $normalizado['titulo'],
        $normalizado['descripcion'],
        $normalizado['fecha'],
        $normalizado['fecha_fin'],
        $fotoFinal,
        $normalizado['activo'],
        $id,
    ]);

    if ($ok && $fotoNueva !== null && $fotoActual !== '' && $fotoActual !== $fotoNueva) {
        eliminarArchivoFotoCalendario($fotoActual);
    }

    return $ok;
}

function eliminarEventoCalendario(int $id): bool
{
    $evento = obtenerEventoCalendario($id);

    if (!$evento) {
        return false;
    }

    $pdo = getConnection();
    $stmt = $pdo->prepare('DELETE FROM calendario_eventos WHERE id = ?');
    $ok = $stmt->execute([$id]) && $stmt->rowCount() > 0;

    if ($ok) {
        eliminarArchivoFotoCalendario((string) ($evento['foto'] ?? ''));
    }

    return $ok;
}

/**
 * @param array<string, mixed> $datos
 * @return array{titulo: string, descripcion: string, fecha: string, fecha_fin: ?string, activo: int}
 */
function normalizarDatosEventoCalendario(array $datos, bool $esNuevo): array
{
    require_once __DIR__ . '/texto.php';

    $titulo = normalizarTextoOrdenado((string) ($datos['titulo'] ?? ''));
    if (function_exists('mb_substr')) {
        $titulo = mb_substr($titulo, 0, 150, 'UTF-8');
    } else {
        $titulo = substr($titulo, 0, 150);
    }

    $descripcion = trim((string) ($datos['descripcion'] ?? ''));
    $descripcion = preg_replace('/\s+/u', ' ', $descripcion) ?? $descripcion;
    if (function_exists('mb_substr')) {
        $descripcion = mb_substr($descripcion, 0, 255, 'UTF-8');
    } else {
        $descripcion = substr($descripcion, 0, 255);
    }

    $fecha = trim((string) ($datos['fecha'] ?? ''));
    $fechaFin = trim((string) ($datos['fecha_fin'] ?? ''));
    $estado = strtolower(trim((string) ($datos['estado'] ?? ($datos['activo'] ?? ''))));

    if ($titulo === '') {
        throw new InvalidArgumentException('El título es obligatorio.');
    }

    if ($descripcion === '') {
        throw new InvalidArgumentException('La descripción breve es obligatoria.');
    }

    validarFechaCalendario($fecha);

    if ($fechaFin === '' || $fechaFin === $fecha) {
        $fechaFinNormalizada = null;
    } else {
        validarFechaCalendario($fechaFin);
        if ($fechaFin < $fecha) {
            throw new InvalidArgumentException('La fecha de fin no puede ser anterior a la fecha de inicio.');
        }
        $fechaFinNormalizada = $fechaFin;
    }

    if (in_array($estado, ['1', 'activo', 'si', 'sí', 'true'], true)) {
        $activo = 1;
    } elseif (in_array($estado, ['0', 'inactivo', 'no', 'false'], true)) {
        $activo = 0;
    } elseif (isset($datos['activo'])) {
        $activo = !empty($datos['activo']) ? 1 : 0;
    } else {
        $activo = $esNuevo ? 1 : 0;
    }

    return [
        'titulo'      => $titulo,
        'descripcion' => $descripcion,
        'fecha'       => $fecha,
        'fecha_fin'   => $fechaFinNormalizada,
        'activo'      => $activo,
    ];
}

function validarFechaCalendario(string $fecha): void
{
    $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);

    if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
        throw new InvalidArgumentException('Fecha no válida.');
    }
}

function mensajeErrorSubidaFotoCalendario(int $error): string
{
    switch ($error) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'La foto supera el tamaño máximo permitido (5 MB).';
        case UPLOAD_ERR_PARTIAL:
            return 'La foto se subió incompleta. Intenta de nuevo.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'El servidor no tiene carpeta temporal para subir fotos.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'No se pudo escribir la foto en el servidor.';
        case UPLOAD_ERR_EXTENSION:
            return 'Una extensión de PHP bloqueó la subida de la foto.';
        default:
            return 'No se pudo subir la foto. Intenta de nuevo.';
    }
}

/**
 * Detecta la extensión real de la imagen (JPG, PNG, WEBP o GIF).
 * Usa getimagesize para no depender de la extensión fileinfo.
 */
function extensionFotoCalendario(string $tmp): string
{
    $info = @getimagesize($tmp);
    $tipo = is_array($info) ? (int) ($info[2] ?? 0) : 0;

    $mapa = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_GIF  => 'gif',
    ];

    if (defined('IMAGETYPE_WEBP')) {
        $mapa[IMAGETYPE_WEBP] = 'webp';
    }

    if (isset($mapa[$tipo])) {
        return $mapa[$tipo];
    }

    throw new InvalidArgumentException('Formato de imagen no permitido. Usa JPG, PNG, WEBP o GIF.');
}

function asegurarDirectorioFotosCalendario(): string
{
    $directorio = rutaAbsolutaCalendarioUpload();

    if (!is_dir($directorio) && !@mkdir($directorio, 0775, true) && !is_dir($directorio)) {
        throw new InvalidArgumentException(
            'No se pudo crear el directorio de fotos del calendario. Verifica permisos de uploads/calendario.'
        );
    }

    if (!is_writable($directorio)) {
        @chmod($directorio, 0775);
    }

    if (!is_writable($directorio)) {
        @chmod($directorio, 0777);
    }

    if (!is_writable($directorio)) {
        throw new InvalidArgumentException(
            'El directorio de fotos no tiene permiso de escritura. Ajusta uploads/calendario.'
        );
    }

    return $directorio;
}

function guardarFotoEventoCalendario(?array $archivo): ?string
{
    if ($archivo === null) {
        return null;
    }

    $error = (int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($error === UPLOAD_ERR_NO_FILE || trim((string) ($archivo['name'] ?? '')) === '') {
        return null;
    }

    if ($error !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException(mensajeErrorSubidaFotoCalendario($error));
    }

    $tmp = (string) ($archivo['tmp_name'] ?? '');
    $tamano = (int) ($archivo['size'] ?? 0);

    if ($tmp === '' || !is_file($tmp)) {
        throw new InvalidArgumentException('Archivo de foto no válido.');
    }

    if (function_exists('is_uploaded_file') && !is_uploaded_file($tmp)) {
        throw new InvalidArgumentException('Archivo de foto no válido.');
    }

    if ($tamano <= 0 || $tamano > CALENDARIO_MAX_BYTES) {
        throw new InvalidArgumentException('La foto debe pesar como máximo 5 MB.');
    }

    $extension = extensionFotoCalendario($tmp);
    $directorioAbsoluto = asegurarDirectorioFotosCalendario();

    $nombre = date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $destinoAbsoluto = $directorioAbsoluto . DIRECTORY_SEPARATOR . $nombre;
    $rutaRelativa = CALENDARIO_UPLOAD_DIR . '/' . $nombre;

    $movido = @move_uploaded_file($tmp, $destinoAbsoluto);
    if (!$movido) {
        $movido = @copy($tmp, $destinoAbsoluto);
        if ($movido) {
            @unlink($tmp);
        }
    }

    if (!$movido || !is_file($destinoAbsoluto)) {
        throw new InvalidArgumentException(
            'No se pudo guardar la foto del evento. Verifica permisos de uploads/calendario.'
        );
    }

    @chmod($destinoAbsoluto, 0644);

    return $rutaRelativa;
}

function rutaAbsolutaCalendarioUpload(): string
{
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, CALENDARIO_UPLOAD_DIR);
}

function eliminarArchivoFotoCalendario(string $rutaRelativa): void
{
    $rutaRelativa = str_replace('\\', '/', trim($rutaRelativa));

    if ($rutaRelativa === '' || strpos($rutaRelativa, '..') !== false) {
        return;
    }

    if (strpos($rutaRelativa, CALENDARIO_UPLOAD_DIR . '/') !== 0) {
        return;
    }

    $absoluta = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rutaRelativa);

    if (is_file($absoluta)) {
        @unlink($absoluta);
    }
}

function urlFotoEventoCalendario(?string $rutaRelativa): string
{
    $rutaRelativa = str_replace('\\', '/', trim((string) $rutaRelativa));

    if ($rutaRelativa === '' || strpos($rutaRelativa, '..') !== false) {
        return '';
    }

    return $rutaRelativa;
}

/**
 * @return array{anio: int, mes: int}
 */
function parsearMesCalendario(array $entrada): array
{
    $anio = isset($entrada['anio']) ? (int) $entrada['anio'] : (int) date('Y');
    $mes = isset($entrada['mes']) ? (int) $entrada['mes'] : (int) date('n');

    if ($anio < 2000 || $anio > 2100) {
        $anio = (int) date('Y');
    }

    if ($mes < 1 || $mes > 12) {
        $mes = (int) date('n');
    }

    return ['anio' => $anio, 'mes' => $mes];
}

/**
 * @param array<int, array<string, mixed>> $eventos
 * @return array<string, array<int, array<string, mixed>>>
 */
function agruparEventosCalendarioPorFecha(array $eventos): array
{
    $porFecha = [];

    foreach ($eventos as $evento) {
        $fechaInicio = (string) ($evento['fecha'] ?? '');
        if ($fechaInicio === '') {
            continue;
        }

        $fechaFin = trim((string) ($evento['fecha_fin'] ?? ''));
        if ($fechaFin === '' || $fechaFin < $fechaInicio) {
            $fechaFin = $fechaInicio;
        }

        $cursor = DateTime::createFromFormat('Y-m-d', $fechaInicio);
        $fin = DateTime::createFromFormat('Y-m-d', $fechaFin);

        if (!$cursor || !$fin) {
            $porFecha[$fechaInicio][] = $evento;
            continue;
        }

        while ($cursor <= $fin) {
            $clave = $cursor->format('Y-m-d');
            $porFecha[$clave][] = $evento;
            $cursor->modify('+1 day');
        }
    }

    return $porFecha;
}

function fechaFinEfectivaEventoCalendario(array $evento): string
{
    $fecha = trim((string) ($evento['fecha'] ?? ''));
    $fechaFin = trim((string) ($evento['fecha_fin'] ?? ''));

    if ($fechaFin !== '' && $fechaFin >= $fecha) {
        return $fechaFin;
    }

    return $fecha;
}

/**
 * Badge público: "11" o "28–29" (mismo mes) o "30 Jul–2 Ago".
 */
function formatearBadgeFechaEventoCalendario(string $fecha, ?string $fechaFin = null): string
{
    $fecha = trim($fecha);
    $fechaFin = trim((string) $fechaFin);

    if ($fecha === '') {
        return '';
    }

    $inicio = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$inicio) {
        return $fecha;
    }

    if ($fechaFin === '' || $fechaFin === $fecha) {
        return (string) (int) $inicio->format('j');
    }

    $fin = DateTime::createFromFormat('Y-m-d', $fechaFin);
    if (!$fin || $fechaFin < $fecha) {
        return (string) (int) $inicio->format('j');
    }

    $nombresCortos = [
        1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
        5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
        9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic',
    ];

    $diaInicio = (int) $inicio->format('j');
    $diaFin = (int) $fin->format('j');

    if ($inicio->format('Y-m') === $fin->format('Y-m')) {
        return $diaInicio . '–' . $diaFin;
    }

    $mesInicio = $nombresCortos[(int) $inicio->format('n')] ?? $inicio->format('M');
    $mesFin = $nombresCortos[(int) $fin->format('n')] ?? $fin->format('M');

    return $diaInicio . ' ' . $mesInicio . '–' . $diaFin . ' ' . $mesFin;
}

/**
 * Texto para tablas del panel: "11/08/2026" o "11/08/2026 – 13/08/2026".
 */
function formatearRangoFechaTablaCalendario(array $evento): string
{
    require_once __DIR__ . '/detalle_registro.php';

    $inicio = formatearFechaTabla($evento['fecha'] ?? '');
    $fechaFin = trim((string) ($evento['fecha_fin'] ?? ''));

    if ($fechaFin === '' || $fechaFin === (string) ($evento['fecha'] ?? '')) {
        return $inicio;
    }

    return $inicio . ' – ' . formatearFechaTabla($fechaFin);
}

function etiquetaEstadoEventoCalendario(int $activo): string
{
    return $activo === 1 ? 'Activo' : 'Inactivo';
}

/**
 * URL base pública del Sistema Web (para fotos vía API).
 */
function obtenerUrlBasePublicaSistema(): string
{
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $https = strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https';
    }

    $scheme = $https ? 'https' : 'http';
    $host = trim((string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));

    // .../api/calendario.php → directorio raíz del sistema
    $basePath = dirname(dirname($script));
    if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
        $basePath = '';
    }

    return rtrim($scheme . '://' . $host . $basePath, '/');
}

/**
 * Convierte ruta relativa de foto en URL absoluta.
 */
function urlAbsolutaFotoEventoCalendario(?string $rutaRelativa): string
{
    $relativa = urlFotoEventoCalendario($rutaRelativa);

    if ($relativa === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $relativa)) {
        return $relativa;
    }

    return obtenerUrlBasePublicaSistema() . '/' . ltrim($relativa, '/');
}
