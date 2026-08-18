<?php

require_once __DIR__ . '/esquema.php';
require_once __DIR__ . '/paginacion.php';
require_once __DIR__ . '/valores_adicionales.php';

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerEventos(): array
{
    $pdo = getConnection();
    asegurarColumnasEventos($pdo);

    $eventos = $pdo->query(
        'SELECT e.*,
                (SELECT COUNT(*)
                 FROM valores_adicionales v
                 WHERE v.evento_id = e.id AND v.tipo = "' . TIPO_VALOR_EVENTOS_INTERNO . '") AS total_registros
         FROM eventos e
         ORDER BY e.nombre ASC, e.id ASC'
    )->fetchAll();

    return adjuntarCamposAdicionalesAEventos(adjuntarTiposEntradaAEventos($eventos));
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerEventosHabilitados(): array
{
    $pdo = getConnection();
    asegurarColumnasEventos($pdo);

    $eventos = $pdo->query(
        'SELECT * FROM eventos WHERE habilitado = 1 ORDER BY nombre ASC, id ASC'
    )->fetchAll();

    return adjuntarCamposAdicionalesAEventos(adjuntarTiposEntradaAEventos($eventos));
}

function obtenerEvento(int $id): ?array
{
    $pdo = getConnection();
    asegurarColumnasEventos($pdo);
    $stmt = $pdo->prepare('SELECT * FROM eventos WHERE id = ?');
    $stmt->execute([$id]);
    $evento = $stmt->fetch();

    if (!$evento) {
        return null;
    }

    $evento['tipos_entrada'] = obtenerTiposEntradaPorEvento((int) $evento['id']);
    $evento['campos_adicionales'] = obtenerCamposAdicionalesPorEvento((int) $evento['id']);

    return $evento;
}

/**
 * @param array<int, array<string, mixed>> $eventos
 * @return array<int, array<string, mixed>>
 */
function adjuntarTiposEntradaAEventos(array $eventos): array
{
    if ($eventos === []) {
        return [];
    }

    $ids = array_map(static fn (array $evento): int => (int) $evento['id'], $eventos);
    $tiposPorEvento = obtenerTiposEntradaPorEventos($ids);

    foreach ($eventos as &$evento) {
        $eventoId = (int) $evento['id'];
        $evento['tipos_entrada'] = $tiposPorEvento[$eventoId] ?? [];
    }
    unset($evento);

    return $eventos;
}

/**
 * @param array<int, array<string, mixed>> $eventos
 * @return array<int, array<string, mixed>>
 */
function adjuntarCamposAdicionalesAEventos(array $eventos): array
{
    if ($eventos === []) {
        return [];
    }

    $ids = array_map(static fn (array $evento): int => (int) $evento['id'], $eventos);
    $camposPorEvento = obtenerCamposAdicionalesPorEventos($ids);

    foreach ($eventos as &$evento) {
        $eventoId = (int) $evento['id'];
        $evento['campos_adicionales'] = $camposPorEvento[$eventoId] ?? [];
    }
    unset($evento);

    return $eventos;
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerCamposAdicionalesPorEvento(int $eventoId): array
{
    if ($eventoId <= 0) {
        return [];
    }

    $porEvento = obtenerCamposAdicionalesPorEventos([$eventoId]);

    return $porEvento[$eventoId] ?? [];
}

/**
 * @param array<int, int> $eventoIds
 * @return array<int, array<int, array<string, mixed>>>
 */
function obtenerCamposAdicionalesPorEventos(array $eventoIds): array
{
    $eventoIds = array_values(array_unique(array_filter(array_map('intval', $eventoIds))));

    if ($eventoIds === []) {
        return [];
    }

    $pdo = getConnection();
    asegurarColumnasEventos($pdo);

    $placeholders = implode(',', array_fill(0, count($eventoIds), '?'));
    $stmt = $pdo->prepare(
        "SELECT id, evento_id, etiqueta, tipo, opciones, obligatorio, orden
         FROM eventos_campos_adicionales
         WHERE evento_id IN ($placeholders)
         ORDER BY orden ASC, id ASC"
    );
    $stmt->execute($eventoIds);

    $resultado = [];
    foreach ($stmt->fetchAll() as $fila) {
        $eventoId = (int) $fila['evento_id'];
        $fila['tipo'] = normalizarTipoCampoAdicionalEvento($fila['tipo'] ?? 'texto');
        $fila['opciones'] = decodificarOpcionesCampoAdicional($fila['opciones'] ?? null);
        $resultado[$eventoId][] = $fila;
    }

    return $resultado;
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerTiposEntradaPorEvento(int $eventoId): array
{
    if ($eventoId <= 0) {
        return [];
    }

    $porEvento = obtenerTiposEntradaPorEventos([$eventoId]);

    return $porEvento[$eventoId] ?? [];
}

/**
 * @param array<int, int> $eventoIds
 * @return array<int, array<int, array<string, mixed>>>
 */
function obtenerTiposEntradaPorEventos(array $eventoIds): array
{
    $eventoIds = array_values(array_unique(array_filter(array_map('intval', $eventoIds))));

    if ($eventoIds === []) {
        return [];
    }

    $pdo = getConnection();
    asegurarColumnasEventos($pdo);

    $placeholders = implode(',', array_fill(0, count($eventoIds), '?'));
    $stmt = $pdo->prepare(
        "SELECT id, evento_id, nombre, valor, orden, visible_publico, es_gratis
         FROM eventos_tipos_entrada
         WHERE evento_id IN ($placeholders)
         ORDER BY orden ASC, id ASC"
    );
    $stmt->execute($eventoIds);

    $resultado = [];
    foreach ($stmt->fetchAll() as $fila) {
        $eventoId = (int) $fila['evento_id'];
        $resultado[$eventoId][] = $fila;
    }

    return $resultado;
}

function obtenerTipoEntradaPorId(int $id, ?int $eventoId = null): ?array
{
    if ($id <= 0) {
        return null;
    }

    $pdo = getConnection();
    asegurarColumnasEventos($pdo);

    if ($eventoId !== null && $eventoId > 0) {
        $stmt = $pdo->prepare(
            'SELECT id, evento_id, nombre, valor, orden, visible_publico, es_gratis
             FROM eventos_tipos_entrada
             WHERE id = ? AND evento_id = ?
             LIMIT 1'
        );
        $stmt->execute([$id, $eventoId]);
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, evento_id, nombre, valor, orden, visible_publico, es_gratis
             FROM eventos_tipos_entrada
             WHERE id = ?
             LIMIT 1'
        );
        $stmt->execute([$id]);
    }

    $fila = $stmt->fetch();

    return $fila ?: null;
}

/**
 * @param array<int, array<string, mixed>>|array<string, mixed> $tiposEntrada
 * @return array<int, array{nombre: string, valor: float, visible_publico: int, es_gratis: int}>
 */
function normalizarTiposEntradaCatalogo($tiposEntrada): array
{
    require_once __DIR__ . '/texto.php';

    if (!is_array($tiposEntrada)) {
        $tiposEntrada = [];
    }

    // Soporta arrays indexados o campos POST tipo_entrada[nombre][] / tipo_entrada[valor][].
    if (isset($tiposEntrada['nombre']) || isset($tiposEntrada['valor'])) {
        $nombres = $tiposEntrada['nombre'] ?? [];
        $valores = $tiposEntrada['valor'] ?? [];
        $visiblesPublico = $tiposEntrada['visible_publico'] ?? [];
        $esGratisLista = $tiposEntrada['es_gratis'] ?? [];
        $tiposEntrada = [];

        if (!is_array($nombres)) {
            $nombres = [$nombres];
        }
        if (!is_array($valores)) {
            $valores = [$valores];
        }
        if (!is_array($visiblesPublico)) {
            $visiblesPublico = [$visiblesPublico];
        }
        if (!is_array($esGratisLista)) {
            $esGratisLista = [$esGratisLista];
        }

        $total = max(count($nombres), count($valores), count($visiblesPublico), count($esGratisLista));
        for ($i = 0; $i < $total; $i++) {
            $tiposEntrada[] = [
                'nombre'          => $nombres[$i] ?? '',
                'valor'           => $valores[$i] ?? 0,
                'visible_publico' => $visiblesPublico[$i] ?? 1,
                'es_gratis'       => $esGratisLista[$i] ?? 0,
            ];
        }
    }

    $normalizados = [];

    foreach ($tiposEntrada as $tipo) {
        if (!is_array($tipo)) {
            continue;
        }

        $nombre = normalizarTextoOrdenado($tipo['nombre'] ?? '');
        if ($nombre === '') {
            continue;
        }

        $esGratis = !empty($tipo['es_gratis']);
        $valor = isset($tipo['valor']) ? (float) $tipo['valor'] : 0.0;

        if ($esGratis || $valor < 0) {
            $valor = 0.0;
        }

        $normalizados[] = [
            'nombre'          => $nombre,
            'valor'           => $valor,
            'visible_publico' => !empty($tipo['visible_publico']) ? 1 : 0,
            'es_gratis'       => $esGratis ? 1 : 0,
        ];
    }

    if ($normalizados === []) {
        throw new InvalidArgumentException('Agrega al menos un tipo de entrada.');
    }

    return $normalizados;
}

/**
 * @param array<int, array{nombre: string, valor: float, visible_publico: int, es_gratis: int}> $tiposEntrada
 */
function guardarTiposEntradaEvento(int $eventoId, array $tiposEntrada): void
{
    if ($eventoId <= 0) {
        throw new InvalidArgumentException('Evento inválido.');
    }

    // No llamar migraciones/DDL aquí: si se usa dentro de una transacción,
    // MySQL hace COMMIT implícito y el commit/rollback posterior falla.
    $pdo = getConnection();

    $pdo->prepare('DELETE FROM eventos_tipos_entrada WHERE evento_id = ?')->execute([$eventoId]);

    $stmt = $pdo->prepare(
        'INSERT INTO eventos_tipos_entrada (evento_id, nombre, valor, orden, visible_publico, es_gratis, creado_en)
         VALUES (?, ?, ?, ?, ?, ?, NOW())'
    );

    foreach ($tiposEntrada as $orden => $tipo) {
        $stmt->execute([
            $eventoId,
            $tipo['nombre'],
            $tipo['valor'],
            (int) $orden,
            (int) ($tipo['visible_publico'] ?? 1),
            (int) ($tipo['es_gratis'] ?? 0),
        ]);
    }
}

/**
 * @return array<string, string>
 */
function obtenerTiposCampoAdicionalEvento(): array
{
    return [
        'texto'  => 'Texto',
        'lista'  => 'Lista desplegable',
        'numero' => 'Número',
        'fecha'  => 'Fecha',
    ];
}

function normalizarTipoCampoAdicionalEvento($tipo): string
{
    $tipo = trim((string) $tipo);
    $tipos = obtenerTiposCampoAdicionalEvento();

    return array_key_exists($tipo, $tipos) ? $tipo : 'texto';
}

/**
 * @param mixed $opciones
 * @return array<int, string>
 */
function decodificarOpcionesCampoAdicional($opciones): array
{
    if (is_array($opciones)) {
        $lista = $opciones;
    } else {
        $raw = trim((string) $opciones);
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $lista = $decoded;
        } else {
            $lista = preg_split('/\r\n|\r|\n|,/', $raw) ?: [];
        }
    }

    $resultado = [];
    $vistos = [];
    foreach ($lista as $opcion) {
        $texto = trim((string) $opcion);
        if ($texto === '') {
            continue;
        }

        $longitud = function_exists('mb_strlen') ? mb_strlen($texto) : strlen($texto);
        if ($longitud > 80) {
            $texto = function_exists('mb_substr') ? mb_substr($texto, 0, 80) : substr($texto, 0, 80);
        }

        $clave = function_exists('mb_strtolower') ? mb_strtolower($texto, 'UTF-8') : strtolower($texto);
        if (isset($vistos[$clave])) {
            continue;
        }
        $vistos[$clave] = true;
        $resultado[] = $texto;
    }

    return $resultado;
}

function formatearOpcionesCampoAdicionalParaTextarea($opciones): string
{
    return implode("\n", decodificarOpcionesCampoAdicional($opciones));
}

/**
 * @return array<string, mixed>
 */
function serializarCampoAdicionalEventoParaJs(array $campo): array
{
    return [
        'id'          => (int) ($campo['id'] ?? 0),
        'etiqueta'    => (string) ($campo['etiqueta'] ?? ''),
        'obligatorio' => (int) ($campo['obligatorio'] ?? 1),
        'tipo'        => normalizarTipoCampoAdicionalEvento($campo['tipo'] ?? 'texto'),
        'opciones'    => decodificarOpcionesCampoAdicional($campo['opciones'] ?? []),
    ];
}

/**
 * @param array<int, array<string, mixed>>|array<string, mixed> $camposAdicionales
 * @return array<int, array{etiqueta: string, tipo: string, opciones: array<int, string>, obligatorio: int}>
 */
function normalizarCamposAdicionalesCatalogo($camposAdicionales): array
{
    require_once __DIR__ . '/texto.php';

    if (!is_array($camposAdicionales)) {
        return [];
    }

    if (isset($camposAdicionales['etiqueta']) || isset($camposAdicionales['obligatorio']) || isset($camposAdicionales['tipo'])) {
        $etiquetas = $camposAdicionales['etiqueta'] ?? [];
        $obligatorios = $camposAdicionales['obligatorio'] ?? [];
        $tipos = $camposAdicionales['tipo'] ?? [];
        $opcionesLista = $camposAdicionales['opciones'] ?? [];

        if (!is_array($etiquetas)) {
            $etiquetas = [$etiquetas];
        }
        if (!is_array($obligatorios)) {
            $obligatorios = [$obligatorios];
        }
        if (!is_array($tipos)) {
            $tipos = [$tipos];
        }
        if (!is_array($opcionesLista)) {
            $opcionesLista = [$opcionesLista];
        }

        $camposAdicionales = [];
        $total = max(count($etiquetas), count($obligatorios), count($tipos), count($opcionesLista));
        for ($i = 0; $i < $total; $i++) {
            $camposAdicionales[] = [
                'etiqueta'    => $etiquetas[$i] ?? '',
                'obligatorio' => $obligatorios[$i] ?? 1,
                'tipo'        => $tipos[$i] ?? 'texto',
                'opciones'    => $opcionesLista[$i] ?? '',
            ];
        }
    }

    $normalizados = [];
    $etiquetasVistas = [];

    foreach ($camposAdicionales as $campo) {
        if (!is_array($campo)) {
            continue;
        }

        $etiqueta = normalizarTextoOrdenado($campo['etiqueta'] ?? '');
        if ($etiqueta === '') {
            continue;
        }

        if (function_exists('mb_strlen') ? mb_strlen($etiqueta) > 100 : strlen($etiqueta) > 100) {
            throw new InvalidArgumentException('Cada dato adicional debe tener máximo 100 caracteres.');
        }

        $clave = function_exists('mb_strtolower') ? mb_strtolower($etiqueta, 'UTF-8') : strtolower($etiqueta);
        if (isset($etiquetasVistas[$clave])) {
            throw new InvalidArgumentException('No repitas el mismo dato adicional: ' . $etiqueta . '.');
        }
        $etiquetasVistas[$clave] = true;

        $tipo = normalizarTipoCampoAdicionalEvento($campo['tipo'] ?? 'texto');
        $opciones = $tipo === 'lista' ? decodificarOpcionesCampoAdicional($campo['opciones'] ?? '') : [];

        if ($tipo === 'lista' && count($opciones) < 2) {
            throw new InvalidArgumentException('La lista «' . $etiqueta . '» necesita al menos 2 opciones (una por línea).');
        }

        $normalizados[] = [
            'etiqueta'    => $etiqueta,
            'tipo'        => $tipo,
            'opciones'    => $opciones,
            'obligatorio' => !empty($campo['obligatorio']) ? 1 : 0,
        ];
    }

    if (count($normalizados) > 10) {
        throw new InvalidArgumentException('Puedes solicitar como máximo 10 datos adicionales por evento.');
    }

    return $normalizados;
}

/**
 * @param array<int, array{etiqueta: string, tipo: string, opciones: array<int, string>, obligatorio: int}> $camposAdicionales
 */
function guardarCamposAdicionalesEvento(int $eventoId, array $camposAdicionales): void
{
    if ($eventoId <= 0) {
        throw new InvalidArgumentException('Evento inválido.');
    }

    $pdo = getConnection();
    $pdo->prepare('DELETE FROM eventos_campos_adicionales WHERE evento_id = ?')->execute([$eventoId]);

    if ($camposAdicionales === []) {
        return;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO eventos_campos_adicionales (evento_id, etiqueta, tipo, opciones, obligatorio, orden, creado_en)
         VALUES (?, ?, ?, ?, ?, ?, NOW())'
    );

    foreach ($camposAdicionales as $orden => $campo) {
        $tipo = normalizarTipoCampoAdicionalEvento($campo['tipo'] ?? 'texto');
        $opciones = $tipo === 'lista' ? decodificarOpcionesCampoAdicional($campo['opciones'] ?? []) : [];

        $stmt->execute([
            $eventoId,
            $campo['etiqueta'],
            $tipo,
            $opciones === [] ? null : json_encode($opciones, JSON_UNESCAPED_UNICODE),
            (int) ($campo['obligatorio'] ?? 1),
            (int) $orden,
        ]);
    }
}

/**
 * @return array<int, array{id: int, etiqueta: string, valor: string}>
 */
function decodificarInfoAdicionalRegistro($infoAdicional): array
{
    if (is_array($infoAdicional)) {
        $decoded = $infoAdicional;
    } else {
        $raw = trim((string) $infoAdicional);
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }
    }

    $resultado = [];
    foreach ($decoded as $item) {
        if (!is_array($item)) {
            continue;
        }

        $etiqueta = trim((string) ($item['etiqueta'] ?? ''));
        $valor = trim((string) ($item['valor'] ?? ''));
        if ($etiqueta === '' && $valor === '') {
            continue;
        }

        $resultado[] = [
            'id'       => (int) ($item['id'] ?? 0),
            'etiqueta' => $etiqueta,
            'valor'    => $valor,
        ];
    }

    return $resultado;
}

function codificarInfoAdicionalRegistro(array $respuestas): ?string
{
    if ($respuestas === []) {
        return null;
    }

    return json_encode($respuestas, JSON_UNESCAPED_UNICODE);
}

function formatearInfoAdicionalRegistro($infoAdicional): string
{
    $respuestas = decodificarInfoAdicionalRegistro($infoAdicional);
    if ($respuestas === []) {
        return '';
    }

    $partes = [];
    foreach ($respuestas as $item) {
        $etiqueta = $item['etiqueta'] !== '' ? $item['etiqueta'] : 'Dato';
        $valor = $item['valor'] !== '' ? $item['valor'] : '—';
        $partes[] = $etiqueta . ': ' . $valor;
    }

    return implode(' · ', $partes);
}

/**
 * @return array<int, array{id: int, etiqueta: string}>
 */
function obtenerCamposAdicionalesParaInformeEvento(array $informe): array
{
    $vistos = [];
    $columnas = [];

    foreach (($informe['evento']['campos_adicionales'] ?? []) as $campo) {
        $etiqueta = trim((string) ($campo['etiqueta'] ?? ''));
        if ($etiqueta === '') {
            continue;
        }

        $clave = mb_strtolower($etiqueta);
        $vistos[$clave] = true;
        $columnas[] = [
            'id'       => (int) ($campo['id'] ?? 0),
            'etiqueta' => $etiqueta,
        ];
    }

    if ($columnas !== []) {
        return $columnas;
    }

    foreach ($informe['registros'] ?? [] as $registro) {
        foreach (decodificarInfoAdicionalRegistro($registro['info_adicional'] ?? '') as $item) {
            $etiqueta = $item['etiqueta'] !== '' ? $item['etiqueta'] : 'Dato';
            $clave = mb_strtolower($etiqueta);
            if (isset($vistos[$clave])) {
                continue;
            }

            $vistos[$clave] = true;
            $columnas[] = [
                'id'       => (int) ($item['id'] ?? 0),
                'etiqueta' => $etiqueta,
            ];
        }
    }

    return $columnas;
}

/**
 * @param array{id?: int, etiqueta?: string} $campo
 */
function valorInfoAdicionalPorCampoInforme($infoAdicional, array $campo): string
{
    $respuestas = decodificarInfoAdicionalRegistro($infoAdicional);
    $campoId = (int) ($campo['id'] ?? 0);
    $etiqueta = trim((string) ($campo['etiqueta'] ?? ''));

    foreach ($respuestas as $item) {
        if ($campoId > 0 && (int) ($item['id'] ?? 0) === $campoId) {
            return $item['valor'] !== '' ? $item['valor'] : '—';
        }
    }

    if ($etiqueta !== '') {
        foreach ($respuestas as $item) {
            if (strcasecmp((string) ($item['etiqueta'] ?? ''), $etiqueta) === 0) {
                return $item['valor'] !== '' ? $item['valor'] : '—';
            }
        }
    }

    return '—';
}

/**
 * @param array<string, mixed> $entrada
 * @param array<int, array<string, mixed>> $campos
 * @return array<int, array{id: int, etiqueta: string, valor: string}>
 */
function validarRespuestasCamposAdicionalesEvento(array $entrada, array $campos, bool $exigirObligatorios = true): array
{
    require_once __DIR__ . '/texto.php';

    if ($campos === []) {
        return [];
    }

    $valoresPost = $entrada['info_adicional'] ?? [];
    if (!is_array($valoresPost)) {
        $valoresPost = [];
    }

    $respuestas = [];
    foreach ($campos as $campo) {
        $campoId = (int) ($campo['id'] ?? 0);
        $etiqueta = trim((string) ($campo['etiqueta'] ?? ''));
        if ($etiqueta === '') {
            continue;
        }

        $valorBruto = '';
        if ($campoId > 0 && array_key_exists($campoId, $valoresPost)) {
            $valorBruto = (string) $valoresPost[$campoId];
        } elseif ($campoId > 0 && array_key_exists((string) $campoId, $valoresPost)) {
            $valorBruto = (string) $valoresPost[(string) $campoId];
        } elseif (array_key_exists($etiqueta, $valoresPost)) {
            $valorBruto = (string) $valoresPost[$etiqueta];
        }

        $valorBruto = trim($valorBruto);
        $tipo = normalizarTipoCampoAdicionalEvento($campo['tipo'] ?? 'texto');
        $valor = '';

        if ($tipo === 'lista') {
            $opciones = decodificarOpcionesCampoAdicional($campo['opciones'] ?? []);
            if ($valorBruto !== '') {
                foreach ($opciones as $opcion) {
                    if (strcasecmp($opcion, $valorBruto) === 0) {
                        $valor = $opcion;
                        break;
                    }
                }
                if ($valor === '') {
                    throw new InvalidArgumentException('Selecciona una opción válida para «' . $etiqueta . '».');
                }
            }
        } elseif ($tipo === 'numero') {
            if ($valorBruto !== '') {
                if (!is_numeric($valorBruto)) {
                    throw new InvalidArgumentException('El dato «' . $etiqueta . '» debe ser un número.');
                }
                $valor = $valorBruto;
            }
        } elseif ($tipo === 'fecha') {
            if ($valorBruto !== '') {
                $fechaObj = DateTime::createFromFormat('Y-m-d', $valorBruto);
                if (!$fechaObj || $fechaObj->format('Y-m-d') !== $valorBruto) {
                    throw new InvalidArgumentException('El dato «' . $etiqueta . '» debe ser una fecha válida.');
                }
                $valor = $valorBruto;
            }
        } else {
            $valor = normalizarTextoOrdenado($valorBruto);
            $longitud = function_exists('mb_strlen') ? mb_strlen($valor) : strlen($valor);
            if ($longitud > 255) {
                throw new InvalidArgumentException('El dato «' . $etiqueta . '» no puede superar 255 caracteres.');
            }
        }

        if ($exigirObligatorios && !empty($campo['obligatorio']) && $valor === '') {
            throw new InvalidArgumentException('Completa el dato adicional: ' . $etiqueta . '.');
        }

        $respuestas[] = [
            'id'       => $campoId,
            'etiqueta' => $etiqueta,
            'valor'    => $valor,
        ];
    }

    return $respuestas;
}

/**
 * @param array<int, array{nombre: string, valor: float}> $tiposEntrada
 */
function valorCatalogoDesdeTiposEntrada(array $tiposEntrada): float
{
    if ($tiposEntrada === []) {
        return 0.0;
    }

    $valores = array_map(static fn (array $tipo): float => (float) $tipo['valor'], $tiposEntrada);
    $maximo = max($valores);

    // Si todos son gratuitos, el catálogo queda en 0; si hay pagos, usa el menor pago.
    if ($maximo <= 0) {
        return 0.0;
    }

    $pagos = array_values(array_filter($valores, static fn (float $valor): bool => $valor > 0));

    return $pagos === [] ? 0.0 : min($pagos);
}

function eventoEsGratuitoCatalogo(array $evento): bool
{
    $tipos = $evento['tipos_entrada'] ?? [];

    if (!is_array($tipos) || $tipos === []) {
        return (float) ($evento['valor'] ?? 0) <= 0;
    }

    foreach ($tipos as $tipo) {
        if (!tipoEntradaEsGratis($tipo)) {
            return false;
        }
    }

    return true;
}

function tipoEntradaEsGratis(array $tipo): bool
{
    return (int) ($tipo['es_gratis'] ?? 0) === 1;
}

function registroEventoEsEntradaGratis(array $registro): bool
{
    if (normalizarFormaPagoEvento((string) ($registro['forma_pago'] ?? '')) === 'gratuito') {
        return true;
    }

    $tipoEntradaId = (int) ($registro['tipo_entrada_id'] ?? 0);
    if ($tipoEntradaId <= 0) {
        return false;
    }

    $tipoEntrada = obtenerTipoEntradaPorId($tipoEntradaId);

    return $tipoEntrada !== null && tipoEntradaEsGratis($tipoEntrada);
}

function puedeCambiarEstadoPagoRegistroEvento(array $registro, string $rol): bool
{
    if (registroEventoEsEntradaGratis($registro)) {
        return false;
    }

    $estadoActual = normalizarEstadoPagoEvento((string) ($registro['estado_pago'] ?? '')) ?: 'por_cancelar';

    return puedeMostrarComboboxEstadoPagoEvento($rol, $estadoActual);
}

function tipoEntradaEsVisiblePublico(array $tipo): bool
{
    return (int) ($tipo['visible_publico'] ?? 1) === 1;
}

/**
 * @param array<int, array<string, mixed>> $tipos
 * @return array<int, array<string, mixed>>
 */
function filtrarTiposEntradaEventoPorRol(array $tipos, string $rol): array
{
    require_once __DIR__ . '/roles.php';

    if (esRolConControlTotal($rol)) {
        return array_values($tipos);
    }

    return array_values(array_filter($tipos, 'tipoEntradaEsVisiblePublico'));
}

function formatearTiposEntradaEvento(array $evento): string
{
    $tipos = $evento['tipos_entrada'] ?? [];

    if (!is_array($tipos) || $tipos === []) {
        $valor = (float) ($evento['valor'] ?? 0);

        return $valor <= 0 ? 'Gratuito' : formatearMonto($valor);
    }

    $partes = [];
    foreach ($tipos as $tipo) {
        $nombre = trim((string) ($tipo['nombre'] ?? ''));
        $valor = (float) ($tipo['valor'] ?? 0);
        if ($nombre === '') {
            continue;
        }

        if (tipoEntradaEsGratis($tipo)) {
            $etiquetaValor = 'Gratuito';
        } elseif ($valor <= 0) {
            $etiquetaValor = 'Pendiente de pago';
        } else {
            $etiquetaValor = formatearMonto($valor);
        }

        $texto = $nombre . ': ' . $etiquetaValor;
        if (!tipoEntradaEsVisiblePublico($tipo)) {
            $texto .= ' (solo admin)';
        }

        $partes[] = $texto;
    }

    return $partes === [] ? '—' : implode(' · ', $partes);
}

function obtenerEventoHabilitado(int $id): ?array
{
    $evento = obtenerEvento($id);

    if (!$evento || (int) ($evento['habilitado'] ?? 0) !== 1) {
        return null;
    }

    return $evento;
}

/**
 * @return array<string, string>
 */
function obtenerFormasPagoEvento(): array
{
    return [
        'pago'          => 'Pago',
        'gratuito'      => 'Gratuito',
        'pendiente'     => 'Pendiente',
        'efectivo'      => 'Efectivo',
        'transferencia' => 'Transferencia',
    ];
}

/**
 * @return array<string, string>
 */
function obtenerEstadosPagoEvento(): array
{
    return [
        'por_cancelar' => 'Por cancelar',
        'pagado'       => 'Pagado',
    ];
}

function normalizarEstadoPagoEvento(string $estado): string
{
    $estado = trim(mb_strtolower($estado));

    return array_key_exists($estado, obtenerEstadosPagoEvento()) ? $estado : '';
}

function etiquetaEstadoPagoEvento(?string $estado): string
{
    if ($estado === null || $estado === '') {
        return '—';
    }

    $estados = obtenerEstadosPagoEvento();

    return $estados[normalizarEstadoPagoEvento($estado)] ?? $estado;
}

function etiquetaEstadoPagoRegistroEvento(array $registro): string
{
    if (registroEventoEsEntradaGratis($registro)) {
        return 'Completado';
    }

    return etiquetaEstadoPagoEvento($registro['estado_pago'] ?? null);
}

function claseBadgeEstadoPagoRegistroEvento(array $registro): string
{
    if (registroEventoEsEntradaGratis($registro)) {
        return 'bg-success';
    }

    return claseBadgeEstadoPagoEvento($registro['estado_pago'] ?? null);
}

function claseBadgeEstadoPagoEvento(?string $estado): string
{
    return normalizarEstadoPagoEvento((string) $estado) === 'pagado'
        ? 'bg-success'
        : 'bg-warning text-dark';
}

function puedeRevertirEstadoPagoEvento(string $rol): bool
{
    require_once __DIR__ . '/roles.php';

    return esRolConControlTotal($rol);
}

function puedeCrearRegistroEventoPendiente(string $rol): bool
{
    require_once __DIR__ . '/roles.php';

    return esRolConControlTotal($rol);
}

function registroEventoRequiereMetodoPagoInmediato(string $rol): bool
{
    require_once __DIR__ . '/roles.php';

    return $rol === ROL_CONTADOR;
}

function puedeMostrarComboboxEstadoPagoEvento(string $rol, string $estadoActual): bool
{
    require_once __DIR__ . '/roles.php';

    $estadoActual = normalizarEstadoPagoEvento($estadoActual) ?: 'por_cancelar';

    if ($estadoActual === 'pagado') {
        return puedeRevertirEstadoPagoEvento($rol);
    }

    return esRolConControlTotal($rol) || puedeRegistrarEventos($rol);
}

function validarAccesoRegistroEventoPorEstadoEvento(array $registro, string $rol): void
{
    require_once __DIR__ . '/roles.php';

    $eventoId = (int) ($registro['evento_id'] ?? 0);

    if ($eventoId <= 0) {
        return;
    }

    $evento = obtenerEvento($eventoId);

    if (!$evento || (int) ($evento['habilitado'] ?? 0) === 1) {
        return;
    }

    if (!esRolConControlTotal($rol)) {
        throw new InvalidArgumentException('No tienes acceso a registros de eventos deshabilitados.');
    }
}

/**
 * @return array<string, mixed>
 */
function parsearFiltrosRegistrosEventos(array $entrada): array
{
    require_once __DIR__ . '/filters.php';

    $filtros = parsearFiltrosRegistros($entrada);
    $filtros['evento_id'] = max(0, (int) ($entrada['evento_id'] ?? 0));

    return $filtros;
}

function validarCambioEstadoPagoEvento(string $estadoActual, string $estadoNuevo, string $rol): void
{
    require_once __DIR__ . '/roles.php';

    $estadoActual = normalizarEstadoPagoEvento($estadoActual) ?: 'por_cancelar';
    $estadoNuevo = normalizarEstadoPagoEvento($estadoNuevo);

    if ($estadoNuevo === '') {
        throw new InvalidArgumentException('Estado de pago no válido.');
    }

    if ($estadoActual === $estadoNuevo) {
        return;
    }

    if ($estadoActual === 'pagado') {
        if (!puedeRevertirEstadoPagoEvento($rol)) {
            throw new InvalidArgumentException('No puedes modificar un registro ya pagado.');
        }

        return;
    }

    if (!esRolConControlTotal($rol) && !puedeRegistrarEventos($rol)) {
        throw new InvalidArgumentException('No tienes permiso para cambiar el estado de pago.');
    }
}

function normalizarFormaPagoEvento(string $formaPago): string
{
    $formaPago = trim(mb_strtolower($formaPago));

    return array_key_exists($formaPago, obtenerFormasPagoEvento()) ? $formaPago : '';
}

function etiquetaFormaPagoEvento(?string $formaPago): string
{
    if ($formaPago === null || $formaPago === '') {
        return '—';
    }

    $formas = obtenerFormasPagoEvento();

    return $formas[normalizarFormaPagoEvento($formaPago)] ?? $formaPago;
}

/**
 * @param array<string, mixed> $datos
 * @return array{
 *   nombre: string,
 *   fecha: string,
 *   valor: float,
 *   habilitado: int,
 *   requiere_numeracion: int,
 *   tipos_entrada: array<int, array{nombre: string, valor: float}>
 * }
 */
function normalizarDatosEventoCatalogo(array $datos): array
{
    require_once __DIR__ . '/texto.php';

    $nombre = normalizarTextoOrdenado($datos['nombre'] ?? '');
    $fecha = trim((string) ($datos['fecha'] ?? ''));
    $habilitado = !empty($datos['habilitado']) ? 1 : 0;
    $requiereNumeracion = !empty($datos['requiere_numeracion']) ? 1 : 0;

    if ($nombre === '' || $fecha === '') {
        throw new InvalidArgumentException('Nombre y fecha son obligatorios.');
    }

    $tiposEntrada = $datos['tipos_entrada'] ?? null;

    // Compatibilidad: si no vienen tipos, arma uno desde valor suelto (permite 0).
    if ($tiposEntrada === null || $tiposEntrada === []) {
        $valorLegacy = isset($datos['valor']) ? (float) $datos['valor'] : 0.0;
        if ($valorLegacy < 0) {
            $valorLegacy = 0.0;
        }
        $tiposEntrada = [[
            'nombre'    => 'General',
            'valor'     => $valorLegacy,
            'es_gratis' => $valorLegacy <= 0 ? 1 : 0,
        ]];
    }

    $tiposEntrada = normalizarTiposEntradaCatalogo($tiposEntrada);
    $valor = valorCatalogoDesdeTiposEntrada($tiposEntrada);
    $camposAdicionales = normalizarCamposAdicionalesCatalogo($datos['campos_adicionales'] ?? []);

    validarFechaEvento($fecha);

    return [
        'nombre'               => $nombre,
        'fecha'                => $fecha,
        'valor'                => $valor,
        'habilitado'           => $habilitado,
        'requiere_numeracion'  => $requiereNumeracion,
        'tipos_entrada'        => $tiposEntrada,
        'campos_adicionales'   => $camposAdicionales,
    ];
}

/**
 * @param array<string, mixed> $entrada
 * @return array<string, mixed>
 */
function validarDatosRegistroEvento(array $entrada, ?array $evento = null, ?string $rol = null, bool $exigirCamposAdicionales = true): array
{
    require_once __DIR__ . '/texto.php';
    require_once __DIR__ . '/roles.php';

    if ($rol === null) {
        require_once __DIR__ . '/auth.php';
        $rol = (string) (obtenerUsuarioActual()['rol'] ?? '');
    }

    $eventoId = isset($entrada['evento_id']) ? (int) $entrada['evento_id'] : 0;
    $nombre = normalizarTextoOrdenado($entrada['nombre'] ?? '');
    $fecha = trim((string) ($entrada['fecha'] ?? ''));
    $telefono = trim((string) ($entrada['telefono'] ?? ''));
    $valor = isset($entrada['valor']) ? (float) $entrada['valor'] : 0;
    $observacion = normalizarTextoOrdenado($entrada['observacion'] ?? '');
    $numeracion = trim((string) ($entrada['numeracion'] ?? ''));
    $formaPago = normalizarFormaPagoEvento((string) ($entrada['forma_pago'] ?? ''));
    $tipoEntradaId = isset($entrada['tipo_entrada_id']) ? (int) $entrada['tipo_entrada_id'] : 0;
    $estadoPago = normalizarEstadoPagoEvento((string) ($entrada['estado_pago'] ?? ''));

    if ($evento === null) {
        $evento = $eventoId > 0 ? obtenerEventoHabilitado($eventoId) : null;
    } else {
        if (!isset($evento['tipos_entrada'])) {
            $evento['tipos_entrada'] = obtenerTiposEntradaPorEvento((int) ($evento['id'] ?? 0));
        }
        if (!isset($evento['campos_adicionales'])) {
            $evento['campos_adicionales'] = obtenerCamposAdicionalesPorEvento((int) ($evento['id'] ?? 0));
        }
    }

    if (!$evento) {
        throw new InvalidArgumentException('Selecciona un evento habilitado.');
    }

    $tiposEntrada = $evento['tipos_entrada'] ?? [];
    $tipoEntrada = null;
    $tipoEntradaNombre = null;

    if (is_array($tiposEntrada) && $tiposEntrada !== []) {
        if ($tipoEntradaId <= 0) {
            throw new InvalidArgumentException('Selecciona un tipo de entrada.');
        }

        $tipoEntrada = obtenerTipoEntradaPorId($tipoEntradaId, (int) $evento['id']);
        if (!$tipoEntrada) {
            throw new InvalidArgumentException('Tipo de entrada no válido para este evento.');
        }

        if (!tipoEntradaEsVisiblePublico($tipoEntrada) && !esRolConControlTotal($rol)) {
            throw new InvalidArgumentException('No tienes permiso para usar este tipo de entrada.');
        }

        $tipoEntradaNombre = (string) $tipoEntrada['nombre'];
        $valorTipoCatalogo = (float) $tipoEntrada['valor'];

        // Se respeta el valor enviado (permite 0 / promociones); si no viene, usa el del catálogo.
        if (!isset($entrada['valor']) || $entrada['valor'] === '' || $entrada['valor'] === null) {
            $valor = $valorTipoCatalogo;
        }
    }

    if ($valor < 0) {
        throw new InvalidArgumentException('El valor no puede ser negativo.');
    }

    $tipoEsGratis = $tipoEntrada !== null && tipoEntradaEsGratis($tipoEntrada);

    if ($tipoEsGratis) {
        $formaPago = 'gratuito';
        $valor = 0;
        $estadoPago = 'pagado';
    } elseif (registroEventoRequiereMetodoPagoInmediato($rol)) {
        $estadoPago = 'pagado';

        if (!in_array($formaPago, ['efectivo', 'transferencia'], true)) {
            throw new InvalidArgumentException('Selecciona Efectivo o Transferencia.');
        }
    } elseif (!puedeCrearRegistroEventoPendiente($rol)) {
        $estadoPago = 'por_cancelar';
        $formaPago = 'pendiente';
    } else {
        if ($estadoPago === '') {
            $estadoPago = 'por_cancelar';
        }

        if (!array_key_exists($estadoPago, obtenerEstadosPagoEvento())) {
            throw new InvalidArgumentException('Selecciona un estado válido: Por cancelar o Pagado.');
        }

        if ($estadoPago === 'pagado') {
            if (!in_array($formaPago, ['efectivo', 'transferencia'], true)) {
                throw new InvalidArgumentException('Selecciona Efectivo o Transferencia.');
            }
        } else {
            $estadoPago = 'por_cancelar';
            $formaPago = 'pendiente';
        }
    }

    if ($nombre === '' || $fecha === '' || $telefono === '') {
        throw new InvalidArgumentException('Completa todos los campos obligatorios.');
    }

    $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
        throw new InvalidArgumentException('Fecha no válida.');
    }

    if ((int) ($evento['requiere_numeracion'] ?? 0) === 1 && $numeracion === '') {
        throw new InvalidArgumentException('La numeración es obligatoria para este evento.');
    }

    $camposAdicionales = $evento['campos_adicionales'] ?? [];
    if (!is_array($camposAdicionales)) {
        $camposAdicionales = [];
    }
    $infoAdicional = validarRespuestasCamposAdicionalesEvento($entrada, $camposAdicionales, $exigirCamposAdicionales);

    return [
        'evento_id'        => (int) $evento['id'],
        'nombre'           => $nombre,
        'fecha'            => $fecha,
        'telefono'         => $telefono,
        'valor'            => $valor,
        'observacion'      => $observacion,
        'numeracion'       => $numeracion !== '' ? $numeracion : null,
        'forma_pago'       => $formaPago,
        'tipo_entrada_id'  => $tipoEntrada !== null ? (int) $tipoEntrada['id'] : null,
        'tipo_entrada'     => $tipoEntradaNombre,
        'estado_pago'      => $estadoPago,
        'info_adicional'   => $infoAdicional,
    ];
}

function actualizarEstadoPagoRegistroEvento(int $id, string $estadoPago, string $rol): bool
{
    $estadoPago = normalizarEstadoPagoEvento($estadoPago);

    if ($id <= 0 || $estadoPago === '') {
        throw new InvalidArgumentException('Estado de pago no válido.');
    }

    $registro = obtenerRegistroEventoPorId($id);

    if (!$registro) {
        throw new InvalidArgumentException('Registro de evento no encontrado.');
    }

    validarAccesoRegistroEventoPorEstadoEvento($registro, $rol);

    if (registroEventoEsEntradaGratis($registro)) {
        throw new InvalidArgumentException('Las entradas gratuitas siempre tienen estado Completado.');
    }

    $estadoActual = normalizarEstadoPagoEvento((string) ($registro['estado_pago'] ?? '')) ?: 'por_cancelar';
    validarCambioEstadoPagoEvento($estadoActual, $estadoPago, $rol);

    if ($estadoActual === $estadoPago) {
        return true;
    }

    $pdo = getConnection();
    asegurarColumnasValoresAdicionales($pdo);
    $stmt = $pdo->prepare(
        'UPDATE valores_adicionales SET estado_pago = ? WHERE id = ? AND tipo = ?'
    );

    return $stmt->execute([$estadoPago, $id, TIPO_VALOR_EVENTOS_INTERNO]) && $stmt->rowCount() > 0;
}

function obtenerRegistroEventoPorId(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }

    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'SELECT v.*, e.nombre AS evento_nombre, e.requiere_numeracion
         FROM valores_adicionales v
         LEFT JOIN eventos e ON e.id = v.evento_id
         WHERE v.id = ? AND v.tipo = ?
         LIMIT 1'
    );
    $stmt->execute([$id, TIPO_VALOR_EVENTOS_INTERNO]);

    $fila = $stmt->fetch();

    return $fila ?: null;
}

/**
 * @param array{nombre: string, fecha: string, valor?: float|int|string, habilitado?: bool|int, requiere_numeracion?: bool|int, tipos_entrada?: array} $datos
 */
function crearEvento(array $datos): int
{
    $datos = normalizarDatosEventoCatalogo($datos);

    $pdo = getConnection();
    asegurarColumnasEventos($pdo);

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO eventos (nombre, fecha, valor, habilitado, requiere_numeracion, creado_en)
             VALUES (?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([
            $datos['nombre'],
            $datos['fecha'],
            $datos['valor'],
            $datos['habilitado'],
            $datos['requiere_numeracion'],
        ]);

        $eventoId = (int) $pdo->lastInsertId();
        guardarTiposEntradaEvento($eventoId, $datos['tipos_entrada']);
        guardarCamposAdicionalesEvento($eventoId, $datos['campos_adicionales']);

        if ($pdo->inTransaction()) {
            $pdo->commit();
        }

        return $eventoId;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

/**
 * @param array{nombre: string, fecha: string, valor?: float|int|string, habilitado?: bool|int, requiere_numeracion?: bool|int, tipos_entrada?: array} $datos
 */
function actualizarEventoCatalogo(int $id, array $datos): bool
{
    if ($id <= 0) {
        throw new InvalidArgumentException('Datos de evento inválidos.');
    }

    $datos = normalizarDatosEventoCatalogo($datos);

    $pdo = getConnection();
    asegurarColumnasEventos($pdo);

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare(
            'UPDATE eventos
             SET nombre = ?, fecha = ?, valor = ?, habilitado = ?, requiere_numeracion = ?
             WHERE id = ?'
        );
        $ok = $stmt->execute([
            $datos['nombre'],
            $datos['fecha'],
            $datos['valor'],
            $datos['habilitado'],
            $datos['requiere_numeracion'],
            $id,
        ]);

        guardarTiposEntradaEvento($id, $datos['tipos_entrada']);
        guardarCamposAdicionalesEvento($id, $datos['campos_adicionales']);

        if ($pdo->inTransaction()) {
            $pdo->commit();
        }

        return $ok;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function validarFechaEvento(string $fecha): void
{
    $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);

    if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
        throw new InvalidArgumentException('Fecha no válida.');
    }
}

function eliminarEvento(int $id): bool
{
    $pdo = getConnection();
    asegurarColumnasEventos($pdo);

    $pdo->beginTransaction();
    try {
        $pdo->prepare('DELETE FROM eventos_tipos_entrada WHERE evento_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM eventos_campos_adicionales WHERE evento_id = ?')->execute([$id]);
        $stmt = $pdo->prepare('DELETE FROM eventos WHERE id = ?');
        $ok = $stmt->execute([$id]) && $stmt->rowCount() > 0;

        if ($pdo->inTransaction()) {
            $pdo->commit();
        }

        return $ok;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function contarEventos(): int
{
    $pdo = getConnection();

    return (int) $pdo->query('SELECT COUNT(*) FROM eventos')->fetchColumn();
}

/**
 * @return array{0: string, 1: array<int, mixed>}
 */
function construirSqlRegistrosEventos(array $filtros): array
{
    $condiciones = ['v.tipo = ?'];
    $parametros = [TIPO_VALOR_EVENTOS_INTERNO];

    if (($filtros['evento_id'] ?? 0) > 0) {
        $condiciones[] = 'v.evento_id = ?';
        $parametros[] = (int) $filtros['evento_id'];
    } else {
        $condiciones[] = 'e.habilitado = 1';
    }

    if ($filtros['buscar'] !== '') {
        $termino = trim((string) $filtros['buscar']);
        $busqueda = '%' . $termino . '%';
        $condiciones[] = '(v.nombre LIKE ? OR v.telefono LIKE ? OR v.observacion LIKE ? OR v.numeracion = ? OR e.nombre LIKE ?)';
        $parametros = array_merge($parametros, [$busqueda, $busqueda, $busqueda, $termino, $busqueda]);
    }

    if ($filtros['fecha_desde'] !== '') {
        $condiciones[] = 'v.fecha >= ?';
        $parametros[] = $filtros['fecha_desde'];
    }

    if ($filtros['fecha_hasta'] !== '') {
        $condiciones[] = 'v.fecha <= ?';
        $parametros[] = $filtros['fecha_hasta'];
    }

    $sql = 'SELECT v.*, e.nombre AS evento_nombre, e.requiere_numeracion
            FROM valores_adicionales v
            LEFT JOIN eventos e ON e.id = v.evento_id
            WHERE ' . implode(' AND ', $condiciones)
        . ' ORDER BY v.creado_en DESC, v.id DESC';

    return [$sql, $parametros];
}

/**
 * @return array<int, array<string, mixed>>
 */
function buscarRegistrosEventos(
    array $filtros,
    int $limite = REGISTROS_POR_PAGINA,
    int $offset = 0
): array {
    $pdo = getConnection();
    [$sql, $parametros] = construirSqlRegistrosEventos($filtros);
    $limite = normalizarLimiteRegistros($limite);
    $sql .= ' LIMIT ' . $limite . ' OFFSET ' . $offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return $stmt->fetchAll();
}

function contarRegistrosEventos(array $filtros): int
{
    $pdo = getConnection();
    [$sql, $parametros] = construirSqlRegistrosEventos($filtros);
    $sqlConteo = preg_replace('/\s+ORDER BY.*$/i', '', $sql);
    $sqlConteo = preg_replace(
        '/SELECT v\.\*.*?FROM/is',
        'SELECT COUNT(*) FROM',
        $sqlConteo,
        1
    );

    $stmt = $pdo->prepare($sqlConteo);
    $stmt->execute($parametros);

    return (int) $stmt->fetchColumn();
}

function etiquetaEstadoEvento(int $habilitado): string
{
    return $habilitado === 1 ? 'Habilitado' : 'Deshabilitado';
}

function obtenerEventoPorId(int $id): ?array
{
    return obtenerEvento($id);
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerRegistrosPorEvento(int $eventoId): array
{
    if ($eventoId <= 0) {
        return [];
    }

    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'SELECT v.*, e.nombre AS evento_nombre, e.requiere_numeracion
         FROM valores_adicionales v
         LEFT JOIN eventos e ON e.id = v.evento_id
         WHERE v.tipo = ? AND v.evento_id = ?
         ORDER BY v.nombre ASC, v.id ASC'
    );
    $stmt->execute([TIPO_VALOR_EVENTOS_INTERNO, $eventoId]);

    return $stmt->fetchAll();
}

function etiquetaTipoEventoCatalogo(array $evento): string
{
    return (float) ($evento['valor'] ?? 0) <= 0 ? 'Gratuito' : 'Pago';
}

/**
 * @param array<int, array<string, mixed>> $registros
 * @return array{monto_por_cancelar: float, monto_recaudado: float, monto_total: float}
 */
function calcularResumenFinancieroInformeEvento(array $registros): array
{
    $porCancelar = 0.0;
    $recaudado = 0.0;

    foreach ($registros as $registro) {
        if (registroEventoEsEntradaGratis($registro)) {
            continue;
        }

        $valor = (float) ($registro['valor'] ?? 0);
        $estado = normalizarEstadoPagoEvento((string) ($registro['estado_pago'] ?? '')) ?: 'por_cancelar';
        $forma = normalizarFormaPagoEvento((string) ($registro['forma_pago'] ?? ''));

        if ($estado === 'pagado' && in_array($forma, ['efectivo', 'transferencia'], true)) {
            $recaudado += $valor;
        } else {
            $porCancelar += $valor;
        }
    }

    return [
        'monto_por_cancelar' => $porCancelar,
        'monto_recaudado'    => $recaudado,
        'monto_total'        => $porCancelar + $recaudado,
    ];
}

/**
 * @param array<int, array<string, mixed>> $registros
 * @return array<int, array{nombre: string, cantidad: int, valor_catalogo: string}>
 */
function construirResumenTiposEntradaInformeEvento(array $registros, array $evento): array
{
    $conteo = [];

    foreach ($registros as $registro) {
        $tipoId = (int) ($registro['tipo_entrada_id'] ?? 0);
        $nombre = trim((string) ($registro['tipo_entrada'] ?? ''));

        if (!isset($conteo[$tipoId])) {
            $conteo[$tipoId] = [
                'nombre'   => $nombre !== '' ? $nombre : 'Sin tipo',
                'cantidad' => 0,
            ];
        }

        $conteo[$tipoId]['cantidad']++;
    }

    $resumen = [];
    $tiposVistos = [];

    foreach ($evento['tipos_entrada'] ?? [] as $tipo) {
        $tipoId = (int) ($tipo['id'] ?? 0);
        $tiposVistos[$tipoId] = true;
        $nombre = trim((string) ($tipo['nombre'] ?? ''));

        if (tipoEntradaEsGratis($tipo)) {
            $valorCatalogo = 'Gratuito';
        } elseif ((float) ($tipo['valor'] ?? 0) <= 0) {
            $valorCatalogo = 'Pendiente de pago';
        } else {
            $valorCatalogo = formatearMonto((float) ($tipo['valor'] ?? 0));
        }

        $resumen[] = [
            'nombre'         => $nombre !== '' ? $nombre : 'Sin nombre',
            'cantidad'       => (int) ($conteo[$tipoId]['cantidad'] ?? 0),
            'valor_catalogo' => $valorCatalogo,
        ];
    }

    foreach ($conteo as $tipoId => $datos) {
        if (isset($tiposVistos[(int) $tipoId])) {
            continue;
        }

        $resumen[] = [
            'nombre'         => (string) ($datos['nombre'] ?? 'Sin tipo'),
            'cantidad'       => (int) ($datos['cantidad'] ?? 0),
            'valor_catalogo' => '—',
        ];
    }

    return $resumen;
}

function compararRegistrosInformeEventoPorNumeracion(array $a, array $b): int
{
    $numeracionA = trim((string) ($a['numeracion'] ?? ''));
    $numeracionB = trim((string) ($b['numeracion'] ?? ''));

    if ($numeracionA === '' && $numeracionB === '') {
        return strcasecmp((string) ($a['nombre'] ?? ''), (string) ($b['nombre'] ?? ''));
    }

    if ($numeracionA === '') {
        return 1;
    }

    if ($numeracionB === '') {
        return -1;
    }

    $comparacion = strnatcasecmp($numeracionA, $numeracionB);

    return $comparacion !== 0
        ? $comparacion
        : strcasecmp((string) ($a['nombre'] ?? ''), (string) ($b['nombre'] ?? ''));
}

/**
 * @param array<int, array<string, mixed>> $registros
 * @return array<int, array{tipo_entrada_id: int, tipo_entrada: string, registros: array<int, array<string, mixed>>}>
 */
function agruparRegistrosInformeEventoPorTipoEntrada(array $registros, array $evento): array
{
    $grupos = [];

    foreach ($evento['tipos_entrada'] ?? [] as $tipo) {
        $tipoId = (int) ($tipo['id'] ?? 0);
        $nombre = trim((string) ($tipo['nombre'] ?? ''));

        $grupos[$tipoId] = [
            'tipo_entrada_id' => $tipoId,
            'tipo_entrada'    => $nombre !== '' ? $nombre : 'Sin nombre',
            'registros'       => [],
        ];
    }

    $grupoSinTipo = [
        'tipo_entrada_id' => 0,
        'tipo_entrada'    => 'Sin tipo de entrada',
        'registros'       => [],
    ];

    foreach ($registros as $registro) {
        $tipoId = (int) ($registro['tipo_entrada_id'] ?? 0);

        if ($tipoId > 0 && isset($grupos[$tipoId])) {
            $grupos[$tipoId]['registros'][] = $registro;
            continue;
        }

        if ($tipoId > 0) {
            if (!isset($grupos[$tipoId])) {
                $grupos[$tipoId] = [
                    'tipo_entrada_id' => $tipoId,
                    'tipo_entrada'    => trim((string) ($registro['tipo_entrada'] ?? 'Entrada')),
                    'registros'       => [],
                ];
            }
            $grupos[$tipoId]['registros'][] = $registro;
            continue;
        }

        $grupoSinTipo['registros'][] = $registro;
    }

    $resultado = [];

    foreach ($grupos as $grupo) {
        if ($grupo['registros'] === []) {
            continue;
        }

        usort($grupo['registros'], 'compararRegistrosInformeEventoPorNumeracion');
        $resultado[] = $grupo;
    }

    if ($grupoSinTipo['registros'] !== []) {
        usort($grupoSinTipo['registros'], 'compararRegistrosInformeEventoPorNumeracion');
        $resultado[] = $grupoSinTipo;
    }

    return $resultado;
}

/**
 * @return array<string, mixed>
 */
function generarInformeEvento(
    int $eventoId,
    string $fechaDesde = '',
    string $fechaHasta = '',
    string $turno = 'todos'
): array {
    require_once __DIR__ . '/informes.php';

    $evento = obtenerEventoPorId($eventoId);

    if (!$evento) {
        throw new InvalidArgumentException('Evento no encontrado.');
    }

    $rango = resolverRangoFechasInforme($fechaDesde, $fechaHasta);
    $turno = normalizarTurnoInforme($turno);
    $registros = obtenerRegistrosEventosPorRangoRegistro(
        $rango['desde'],
        $rango['hasta'],
        $turno,
        $eventoId
    );
    $resumenFinanciero = calcularResumenFinancieroInformeEvento($registros);
    $resumenTiposEntrada = construirResumenTiposEntradaInformeEvento($registros, $evento);
    $registrosPorTipo = agruparRegistrosInformeEventoPorTipoEntrada($registros, $evento);
    $totalEntradas = array_sum(array_column($resumenTiposEntrada, 'cantidad'));
    $generadoEn = date('Y-m-d H:i:s');

    return [
        'evento' => $evento,
        'registros' => $registros,
        'registros_por_tipo' => $registrosPorTipo,
        'resumen' => [
            'total_participantes' => count($registros),
            'total_entradas'      => $totalEntradas,
            'monto_por_cancelar'  => $resumenFinanciero['monto_por_cancelar'],
            'monto_recaudado'     => $resumenFinanciero['monto_recaudado'],
            'monto_total'         => $resumenFinanciero['monto_total'],
            'por_tipo_entrada'    => $resumenTiposEntrada,
        ],
        'fecha_desde'                => $rango['desde'] ?? 'todo',
        'fecha_hasta'                => $rango['hasta'] ?? 'todo',
        'fecha_desde_etiqueta'       => $rango['fecha_desde_etiqueta'],
        'fecha_hasta_etiqueta'       => $rango['fecha_hasta_etiqueta'],
        'periodo_etiqueta'           => $rango['periodo_etiqueta'],
        'sin_filtro_fecha'           => $rango['sin_filtro_fecha'],
        'turno'                      => $turno,
        'turno_etiqueta'             => etiquetaTurnoInforme($turno),
        'evento_id'                  => $eventoId,
        'evento_etiqueta'            => (string) ($evento['nombre'] ?? 'Evento #' . $eventoId),
        'evento_tipo_etiqueta'       => etiquetaTipoEventoCatalogo($evento),
        'evento_fecha_etiqueta'      => formatearFechaInforme($evento['fecha'] ?? null),
        'evento_valor_etiqueta'      => formatearTiposEntradaEvento($evento),
        'evento_numeracion_etiqueta' => (int) ($evento['requiere_numeracion'] ?? 0) === 1 ? 'Sí' : 'No',
        'evento_estado_etiqueta'     => etiquetaEstadoEvento((int) ($evento['habilitado'] ?? 0)),
        'generado_en'                => date('d/m/Y H:i', strtotime($generadoEn)),
        'generado_en_etiqueta'       => formatearFechaHora($generadoEn),
    ];
}

function renderizarContenidoInformeEventoHtml(array $informe): string
{
    ob_start();
    include __DIR__ . '/../views/partials/contenido-informe-evento.php';

    return (string) ob_get_clean();
}
