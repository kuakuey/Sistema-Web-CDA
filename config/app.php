<?php

date_default_timezone_set('America/Guayaquil');

if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

ini_set('default_charset', 'UTF-8');

/** Clave secreta para sincronizar tablas (index.php?m=CLAVE). Cámbiala en producción. */
const CDA_MANTENIMIENTO_CLAVE = 'cda-bd-v2-7f3a9e2c';

function esClaveMantenimientoBdValida(string $clave): bool
{
    return $clave !== '' && hash_equals(CDA_MANTENIMIENTO_CLAVE, $clave);
}

function obtenerUrlMantenimientoBd(): string
{
    return 'index.php?m=' . rawurlencode(CDA_MANTENIMIENTO_CLAVE);
}
