<?php

require_once 'includes/auth.php';

requerirSesion();

$pestañaLegacy = isset($_GET['pestaña']) ? trim((string) $_GET['pestaña']) : 'lista';
$mapaPestañas = [
    'lista'     => 'usuarios',
    'registrar' => 'usuarios',
    'permisos'  => 'permisos',
];

$pestaña = $mapaPestañas[$pestañaLegacy] ?? 'usuarios';
$parametros = ['pestaña' => $pestaña];

if ($pestaña === 'permisos' && isset($_GET['rol']) && trim((string) $_GET['rol']) !== '') {
    $parametros['rol'] = trim((string) $_GET['rol']);
}

foreach (['ok', 'error', 'clave'] as $clave) {
    if (isset($_GET[$clave])) {
        $parametros[$clave] = (string) $_GET[$clave];
    }
}

header('Location: avanzado.php?' . http_build_query($parametros));
exit;
