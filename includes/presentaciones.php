<?php

/**
 * Opciones de parentesco para representantes legales en presentación de niños.
 *
 * @return array<string, string>
 */
function opcionesParentescoRepresentante(): array
{
    return [
        'padre'       => 'Padre',
        'madre'       => 'Madre',
        'abuelo'      => 'Abuelo',
        'abuela'      => 'Abuela',
        'tio'         => 'Tío',
        'tia'         => 'Tía',
        'tutor_legal' => 'Tutor legal',
        'otro'        => 'Otro',
    ];
}

function etiquetaParentescoRepresentante(?string $parentesco): string
{
    $parentesco = trim((string) $parentesco);
    if ($parentesco === '') {
        return 'Representante';
    }

    $opciones = opcionesParentescoRepresentante();

    return $opciones[$parentesco] ?? $parentesco;
}

function esParentescoRepresentanteValido(?string $parentesco): bool
{
    $parentesco = trim((string) $parentesco);

    return $parentesco !== '' && array_key_exists($parentesco, opcionesParentescoRepresentante());
}

/**
 * @return array<string, string|null>
 */
function representantePresentacionDesdeFila(array $fila, int $numero): array
{
    if ($numero === 1) {
        return [
            'parentesco' => trim((string) ($fila['parentesco_representante_1'] ?? '')),
            'nombre'     => trim((string) ($fila['nombre_padre'] ?? '')),
            'telefono'   => trim((string) ($fila['telefono_papa'] ?? '')),
        ];
    }

    return [
        'parentesco' => trim((string) ($fila['parentesco_representante_2'] ?? '')),
        'nombre'     => trim((string) ($fila['nombre_madre'] ?? '')),
        'telefono'   => trim((string) ($fila['telefono_mama'] ?? '')),
    ];
}

function tieneSegundoRepresentantePresentacion(array $fila): bool
{
    return representantePresentacionDesdeFila($fila, 2)['nombre'] !== '';
}

function mapearCamposLegacyRepresentantesPresentacion(array $datos): array
{
    if (isset($datos['representantes']) && is_array($datos['representantes'])) {
        $representantes = array_values($datos['representantes']);

        if (isset($representantes[0]) && is_array($representantes[0])) {
            $datos['representante_1_parentesco'] = $representantes[0]['parentesco'] ?? '';
            $datos['representante_1_nombre'] = $representantes[0]['nombre'] ?? '';
            $datos['representante_1_telefono'] = $representantes[0]['telefono'] ?? '';
        }

        if (isset($representantes[1]) && is_array($representantes[1])) {
            $datos['representante_2_parentesco'] = $representantes[1]['parentesco'] ?? '';
            $datos['representante_2_nombre'] = $representantes[1]['nombre'] ?? '';
            $datos['representante_2_telefono'] = $representantes[1]['telefono'] ?? '';
        }
    }

    if (trim((string) ($datos['representante_1_nombre'] ?? '')) === '' && trim((string) ($datos['nombre_padre'] ?? '')) !== '') {
        $datos['representante_1_nombre'] = $datos['nombre_padre'];
        $datos['representante_1_telefono'] = $datos['telefono_papa'] ?? '';
        if (trim((string) ($datos['representante_1_parentesco'] ?? '')) === '') {
            $datos['representante_1_parentesco'] = 'padre';
        }
    }

    if (trim((string) ($datos['representante_2_nombre'] ?? '')) === '' && trim((string) ($datos['nombre_madre'] ?? '')) !== '') {
        $datos['representante_2_nombre'] = $datos['nombre_madre'];
        $datos['representante_2_telefono'] = $datos['telefono_mama'] ?? '';
        if (trim((string) ($datos['representante_2_parentesco'] ?? '')) === '') {
            $datos['representante_2_parentesco'] = 'madre';
        }
    }

    return $datos;
}

/**
 * @param array<string, mixed> $datos
 * @return array<string, string|null>
 */
function normalizarRepresentantesPresentacion(array $datos): array
{
    $datos = mapearCamposLegacyRepresentantesPresentacion($datos);
    $rep1 = [
        'parentesco' => trim((string) ($datos['representante_1_parentesco'] ?? '')),
        'nombre'     => trim((string) ($datos['representante_1_nombre'] ?? '')),
        'telefono'   => trim((string) ($datos['representante_1_telefono'] ?? '')),
    ];
    $rep2 = [
        'parentesco' => trim((string) ($datos['representante_2_parentesco'] ?? '')),
        'nombre'     => trim((string) ($datos['representante_2_nombre'] ?? '')),
        'telefono'   => trim((string) ($datos['representante_2_telefono'] ?? '')),
    ];

    if (!esParentescoRepresentanteValido($rep1['parentesco']) || $rep1['nombre'] === '' || $rep1['telefono'] === '') {
        throw new InvalidArgumentException('Completa los datos del representante legal.');
    }

    $segundoRepresentante = $rep2['nombre'] !== '';

    if ($segundoRepresentante) {
        if (!esParentescoRepresentanteValido($rep2['parentesco']) || $rep2['telefono'] === '') {
            throw new InvalidArgumentException('Completa los datos del segundo representante o déjalo vacío.');
        }
    }

    return [
        'parentesco_representante_1' => $rep1['parentesco'],
        'nombre_padre'               => $rep1['nombre'],
        'telefono_papa'              => $rep1['telefono'],
        'parentesco_representante_2' => $segundoRepresentante ? $rep2['parentesco'] : null,
        'nombre_madre'               => $segundoRepresentante ? $rep2['nombre'] : null,
        'telefono_mama'              => $segundoRepresentante ? $rep2['telefono'] : null,
    ];
}

function formatearNombreRepresentantePresentacion(array $fila, int $numero): string
{
    $representante = representantePresentacionDesdeFila($fila, $numero);
    if ($representante['nombre'] === '') {
        return '—';
    }

    $parentesco = etiquetaParentescoRepresentante($representante['parentesco']);

    return $parentesco . ': ' . $representante['nombre'];
}

function primerNombrePersona(?string $nombre): string
{
    $nombre = trim((string) $nombre);
    if ($nombre === '') {
        return '';
    }

    $partes = preg_split('/\s+/u', $nombre, 2);

    return $partes[0] ?? $nombre;
}

/**
 * @param array<string, mixed>|null $representante
 * @return array<string, mixed>|null
 */
function acortarRepresentantePresentacionPublico(?array $representante): ?array
{
    if ($representante === null) {
        return null;
    }

    $nombreCorto = primerNombrePersona($representante['nombre'] ?? '');
    $parentescoEtiqueta = (string) ($representante['parentesco_etiqueta'] ?? 'Representante');
    $representante['nombre'] = $nombreCorto;
    $representante['etiqueta'] = $nombreCorto !== ''
        ? $parentescoEtiqueta . ': ' . $nombreCorto
        : '—';

    return $representante;
}

/**
 * @param array<string, mixed> $datos
 * @return array<int, array{nombre_presentado: string, fecha_nacimiento: string}>
 */
function parsearPresentadosPresentacion(array $datos): array
{
    require_once __DIR__ . '/submissions.php';

    if (isset($datos['presentados']) && is_array($datos['presentados'])) {
        $presentados = [];
        $numero = 1;

        foreach ($datos['presentados'] as $item) {
            if (!is_array($item)) {
                $numero++;
                continue;
            }

            $nombre = trim((string) ($item['nombre'] ?? $item['nombre_presentado'] ?? ''));
            if ($nombre === '') {
                $numero++;
                continue;
            }

            $fecha = parsearFechaNacimientoPresentacion($item);
            if ($fecha === null) {
                throw new InvalidArgumentException(
                    'Ingresa una fecha de nacimiento válida para el presentado ' . $numero . '.'
                );
            }

            $presentados[] = [
                'nombre_presentado' => $nombre,
                'fecha_nacimiento'  => $fecha,
            ];
            $numero++;
        }

        if ($presentados === []) {
            throw new InvalidArgumentException('Agrega al menos un presentado con nombre y fecha de nacimiento.');
        }

        return $presentados;
    }

    $nombre = trim((string) ($datos['nombre_presentado'] ?? ''));
    if ($nombre === '') {
        throw new InvalidArgumentException('Agrega al menos un presentado con nombre y fecha de nacimiento.');
    }

    $fecha = parsearFechaNacimientoPresentacion($datos);
    if ($fecha === null) {
        throw new InvalidArgumentException('Ingresa una fecha de nacimiento válida (día, mes y año).');
    }

    return [[
        'nombre_presentado' => $nombre,
        'fecha_nacimiento'  => $fecha,
    ]];
}
