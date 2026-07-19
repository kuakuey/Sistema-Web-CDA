<?php

/**
 * Normaliza texto libre: minúsculas y primera letra de cada palabra en mayúscula.
 */
function normalizarTextoOrdenado(?string $texto): string
{
    $texto = trim((string) $texto);

    if ($texto === '') {
        return '';
    }

    if (function_exists('mb_convert_case') && function_exists('mb_strtolower')) {
        return mb_convert_case(mb_strtolower($texto, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    return ucwords(strtolower($texto));
}

function normalizarEmail(?string $email): string
{
    $email = trim((string) $email);

    if ($email === '') {
        return '';
    }

    return function_exists('mb_strtolower')
        ? mb_strtolower($email, 'UTF-8')
        : strtolower($email);
}

/**
 * @param array<string, mixed> $datos
 * @param list<string> $campos
 * @return array<string, mixed>
 */
function normalizarCamposTextoOrdenado(array $datos, array $campos): array
{
    foreach ($campos as $campo) {
        if (!array_key_exists($campo, $datos) || $datos[$campo] === null) {
            continue;
        }

        $datos[$campo] = normalizarTextoOrdenado((string) $datos[$campo]);
    }

    return $datos;
}

/**
 * @param array<string, mixed> $datos
 * @return array<string, mixed>
 */
function normalizarDatosPersona(array $datos): array
{
    $datos = normalizarCamposTextoOrdenado($datos, [
        'nombre',
        'apellido',
        'nombre_completo',
        'nombre_presentado',
        'nombre_padre',
        'nombre_madre',
        'direccion',
        'observacion',
        'notas',
    ]);

    if (array_key_exists('email', $datos) && $datos['email'] !== null && $datos['email'] !== '') {
        $datos['email'] = normalizarEmail((string) $datos['email']);
    }

    return $datos;
}
