<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/paginacion.php';

const TIPO_VALOR_EVENTOS_INTERNO = 'eventos';

function obtenerTiposValorAdicionalPorDefecto(): array
{
    return [
        'casa_vida_relampago' => 'Casa de vida relampago',
        'ofrenda_personal'     => 'Ofrenda Personal',
        'siembra'              => 'Siembra',
        'otros'                => 'Otros',
    ];
}

function sembrarTiposValorAdicionalPorDefecto(PDO $pdo): void
{
    if (!tablaExiste($pdo, 'tipos_valor_adicional')) {
        return;
    }

    $total = (int) $pdo->query('SELECT COUNT(*) FROM tipos_valor_adicional')->fetchColumn();

    if ($total > 0) {
        return;
    }

    $insertar = $pdo->prepare(
        'INSERT INTO tipos_valor_adicional (clave, etiqueta, creado_en) VALUES (?, ?, NOW())'
    );

    foreach (obtenerTiposValorAdicionalPorDefecto() as $clave => $etiqueta) {
        $insertar->execute([$clave, $etiqueta]);
    }
}

function asegurarTablaTiposValorAdicional(): void
{
    static $listo = false;

    if ($listo) {
        return;
    }

    require_once __DIR__ . '/../config/database.php';

    $pdo = getConnection();
    migrarTablaTiposValorAdicional($pdo);
    $listo = true;
}

function obtenerTiposValorAdicional(): array
{
    asegurarTablaTiposValorAdicional();

    $pdo = getConnection();
    $filas = $pdo->query(
        'SELECT clave, etiqueta FROM tipos_valor_adicional ORDER BY etiqueta ASC, id ASC'
    )->fetchAll();

    if ($filas === []) {
        return obtenerTiposValorAdicionalPorDefecto();
    }

    $tipos = [];

    foreach ($filas as $fila) {
        $tipos[(string) $fila['clave']] = (string) $fila['etiqueta'];
    }

    return $tipos;
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerFilasTiposValorAdicional(): array
{
    asegurarTablaTiposValorAdicional();

    $pdo = getConnection();

    return $pdo->query(
        'SELECT t.*,
                (SELECT COUNT(*) FROM valores_adicionales v WHERE v.tipo = t.clave) AS total_registros
         FROM tipos_valor_adicional t
         ORDER BY t.etiqueta ASC, t.id ASC'
    )->fetchAll();
}

function generarClaveTipoValor(string $etiqueta, ?int $excluirId = null): string
{
    $texto = strtolower(trim($etiqueta));
    $texto = strtr($texto, [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n',
        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ñ' => 'n',
    ]);
    $texto = preg_replace('/[^a-z0-9]+/', '_', $texto) ?? '';
    $texto = trim($texto, '_');

    if ($texto === '') {
        $texto = 'tipo';
    }

    $base = $texto;
    $contador = 2;
    $pdo = getConnection();

    while (true) {
        $sql = 'SELECT COUNT(*) FROM tipos_valor_adicional WHERE clave = ?';
        $parametros = [$texto];

        if ($excluirId !== null) {
            $sql .= ' AND id != ?';
            $parametros[] = $excluirId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametros);

        if ((int) $stmt->fetchColumn() === 0) {
            return $texto;
        }

        $texto = $base . '_' . $contador;
        $contador++;
    }
}

function crearTipoValorAdicional(string $etiqueta): int
{
    require_once __DIR__ . '/texto.php';
    asegurarTablaTiposValorAdicional();

    $etiqueta = normalizarTextoOrdenado($etiqueta);

    if ($etiqueta === '') {
        throw new InvalidArgumentException('La etiqueta del tipo es obligatoria.');
    }

    $clave = generarClaveTipoValor($etiqueta);
    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'INSERT INTO tipos_valor_adicional (clave, etiqueta, creado_en) VALUES (?, ?, NOW())'
    );
    $stmt->execute([$clave, $etiqueta]);

    return (int) $pdo->lastInsertId();
}

function actualizarTipoValorAdicional(int $id, string $etiqueta): bool
{
    require_once __DIR__ . '/texto.php';
    asegurarTablaTiposValorAdicional();

    $etiqueta = normalizarTextoOrdenado($etiqueta);

    if ($id <= 0 || $etiqueta === '') {
        throw new InvalidArgumentException('Datos de tipo inválidos.');
    }

    $pdo = getConnection();
    $stmt = $pdo->prepare('UPDATE tipos_valor_adicional SET etiqueta = ? WHERE id = ?');

    return $stmt->execute([$etiqueta, $id]);
}

function eliminarTipoValorAdicional(int $id): bool
{
    asegurarTablaTiposValorAdicional();

    $pdo = getConnection();
    $stmt = $pdo->prepare('SELECT clave FROM tipos_valor_adicional WHERE id = ?');
    $stmt->execute([$id]);
    $fila = $stmt->fetch();

    if (!$fila) {
        return false;
    }

    $uso = $pdo->prepare('SELECT COUNT(*) FROM valores_adicionales WHERE tipo = ?');
    $uso->execute([$fila['clave']]);

    if ((int) $uso->fetchColumn() > 0) {
        throw new InvalidArgumentException('No se puede eliminar un tipo con registros asociados.');
    }

    $eliminar = $pdo->prepare('DELETE FROM tipos_valor_adicional WHERE id = ?');

    return $eliminar->execute([$id]) && $eliminar->rowCount() > 0;
}

function esTipoValorAdicionalValido(string $tipo): bool
{
    if ($tipo === TIPO_VALOR_EVENTOS_INTERNO) {
        return false;
    }

    return array_key_exists($tipo, obtenerTiposValorAdicional());
}

function etiquetaTipoValorAdicional(string $tipo): string
{
    $tipos = obtenerTiposValorAdicional();

    if (array_key_exists($tipo, $tipos)) {
        return $tipos[$tipo];
    }

    if ($tipo === TIPO_VALOR_EVENTOS_INTERNO) {
        return 'Eventos';
    }

    return $tipo;
}

function esTipoValorAdicionalEventos(string $tipo): bool
{
    return $tipo === TIPO_VALOR_EVENTOS_INTERNO;
}

function nombreEventoValorAdicional(array $fila): string
{
    if (!esTipoValorAdicionalEventos((string) ($fila['tipo'] ?? ''))) {
        return '';
    }

    $eventoNombre = trim((string) ($fila['evento_nombre'] ?? ''));

    if ($eventoNombre !== '') {
        return $eventoNombre;
    }

    $eventoId = (int) ($fila['evento_id'] ?? 0);

    if ($eventoId > 0) {
        require_once __DIR__ . '/eventos.php';
        $evento = obtenerEvento($eventoId);

        if ($evento) {
            return trim((string) $evento['nombre']);
        }
    }

    return '';
}

function sqlSelectValoresAdicionales(): string
{
    return 'SELECT v.* FROM valores_adicionales v';
}

function insertarValorAdicional(array $datos): int
{
    require_once __DIR__ . '/texto.php';
    $datos = normalizarCamposTextoOrdenado($datos, ['nombre', 'observacion', 'registrado_por_nombre']);

    $pdo = getConnection();
    asegurarColumnasValoresAdicionales($pdo);

    $stmt = $pdo->prepare(
        'INSERT INTO valores_adicionales (
            tipo, nombre, fecha, telefono, valor, observacion, evento_id,
            numeracion, forma_pago, registrado_por_id, registrado_por_nombre, creado_en
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
    );

    $stmt->execute([
        $datos['tipo'],
        $datos['nombre'],
        $datos['fecha'],
        $datos['telefono'],
        $datos['valor'],
        $datos['observacion'] ?? '',
        isset($datos['evento_id']) && (int) $datos['evento_id'] > 0 ? (int) $datos['evento_id'] : null,
        $datos['numeracion'] ?? null,
        $datos['forma_pago'] ?? null,
        $datos['registrado_por_id'],
        $datos['registrado_por_nombre'],
    ]);

    return (int) $pdo->lastInsertId();
}

function eliminarValorAdicional(int $id): bool
{
    $pdo = getConnection();
    $stmt = $pdo->prepare('DELETE FROM valores_adicionales WHERE id = ?');

    return $stmt->execute([$id]) && $stmt->rowCount() > 0;
}

function actualizarValorAdicional(int $id, array $datos): bool
{
    require_once __DIR__ . '/texto.php';
    $datos = normalizarCamposTextoOrdenado($datos, ['nombre', 'observacion']);

    $pdo = getConnection();
    asegurarColumnasValoresAdicionales($pdo);

    $stmt = $pdo->prepare(
        'UPDATE valores_adicionales SET
            tipo = ?, nombre = ?, fecha = ?, telefono = ?, valor = ?, observacion = ?, evento_id = ?,
            numeracion = ?, forma_pago = ?
         WHERE id = ?'
    );

    return $stmt->execute([
        $datos['tipo'],
        $datos['nombre'],
        $datos['fecha'],
        $datos['telefono'],
        (float) $datos['valor'],
        $datos['observacion'] ?? '',
        isset($datos['evento_id']) && (int) $datos['evento_id'] > 0 ? (int) $datos['evento_id'] : null,
        $datos['numeracion'] ?? null,
        $datos['forma_pago'] ?? null,
        $id,
    ]);
}

function actualizarRegistroEvento(int $id, array $datos): bool
{
    require_once __DIR__ . '/eventos.php';

    $registro = obtenerRegistroEventoPorId($id);

    if (!$registro) {
        throw new InvalidArgumentException('Registro de evento no encontrado.');
    }

    $evento = obtenerEvento((int) ($datos['evento_id'] ?? $registro['evento_id'] ?? 0));

    if (!$evento) {
        throw new InvalidArgumentException('Selecciona un evento válido.');
    }

    $validados = validarDatosRegistroEvento($datos, $evento);

    return actualizarValorAdicional($id, [
        'tipo'        => TIPO_VALOR_EVENTOS_INTERNO,
        'nombre'      => $validados['nombre'],
        'fecha'       => $validados['fecha'],
        'telefono'    => $validados['telefono'],
        'valor'       => $validados['valor'],
        'observacion' => $validados['observacion'],
        'evento_id'   => $validados['evento_id'],
        'numeracion'  => $validados['numeracion'],
        'forma_pago'  => $validados['forma_pago'],
    ]);
}

function contarValoresAdicionales(): int
{
    $pdo = getConnection();

    return (int) $pdo->query(
        "SELECT COUNT(*) FROM valores_adicionales WHERE tipo != '" . TIPO_VALOR_EVENTOS_INTERNO . "'"
    )->fetchColumn();
}

/**
 * @return array{0: string, 1: array<int, mixed>}
 */
function construirSqlValoresAdicionales(array $filtros): array
{
    $condiciones = ["v.tipo != '" . TIPO_VALOR_EVENTOS_INTERNO . "'"];
    $parametros = [];

    if ($filtros['buscar'] !== '') {
        $busqueda = '%' . $filtros['buscar'] . '%';
        $condiciones[] = '(v.nombre LIKE ? OR v.telefono LIKE ? OR v.observacion LIKE ? OR v.registrado_por_nombre LIKE ?)';
        $parametros = array_merge($parametros, [$busqueda, $busqueda, $busqueda, $busqueda]);
    }

    if ($filtros['tipo_valor'] !== '') {
        $condiciones[] = 'v.tipo = ?';
        $parametros[] = $filtros['tipo_valor'];
    }

    if ($filtros['fecha_desde'] !== '') {
        $condiciones[] = 'v.fecha >= ?';
        $parametros[] = $filtros['fecha_desde'];
    }

    if ($filtros['fecha_hasta'] !== '') {
        $condiciones[] = 'v.fecha <= ?';
        $parametros[] = $filtros['fecha_hasta'];
    }

    if ($filtros['monto_min'] !== '' && is_numeric($filtros['monto_min'])) {
        $condiciones[] = 'v.valor >= ?';
        $parametros[] = (float) $filtros['monto_min'];
    }

    if ($filtros['monto_max'] !== '' && is_numeric($filtros['monto_max'])) {
        $condiciones[] = 'v.valor <= ?';
        $parametros[] = (float) $filtros['monto_max'];
    }

    $sql = sqlSelectValoresAdicionales() . ' WHERE ' . implode(' AND ', $condiciones)
        . ' ORDER BY v.creado_en DESC, v.id DESC';

    return [$sql, $parametros];
}

/**
 * @return array<int, array<string, mixed>>
 */
function buscarValoresAdicionales(
    array $filtros,
    int $limite = REGISTROS_POR_PAGINA,
    int $offset = 0
): array {
    $pdo = getConnection();
    [$sql, $parametros] = construirSqlValoresAdicionales($filtros);
    $limite = normalizarLimiteRegistros($limite);
    $sql .= ' LIMIT ' . $limite . ' OFFSET ' . $offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return $stmt->fetchAll();
}

function contarValoresAdicionalesFiltrados(array $filtros): int
{
    $pdo = getConnection();
    [$sql, $parametros] = construirSqlValoresAdicionales($filtros);
    $sqlConteo = preg_replace('/\s+ORDER BY.*$/i', '', $sql);
    $sqlConteo = preg_replace('/^SELECT v\.\* FROM valores_adicionales v/i', 'SELECT COUNT(*) FROM valores_adicionales v', $sqlConteo);

    $stmt = $pdo->prepare($sqlConteo);
    $stmt->execute($parametros);

    return (int) $stmt->fetchColumn();
}
