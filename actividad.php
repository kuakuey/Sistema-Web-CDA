<?php

require_once 'includes/auth.php';

requerirSesion();

$parametros = ['pestaña' => 'logs'];

foreach (['buscar', 'accion', 'seccion', 'fecha_desde', 'fecha_hasta', 'pagina', 'ok', 'error', 'limpiados'] as $clave) {
    if (isset($_GET[$clave]) && (string) $_GET[$clave] !== '') {
        $parametros[$clave] = (string) $_GET[$clave];
    }
}

header('Location: avanzado.php?' . http_build_query($parametros));
exit;
