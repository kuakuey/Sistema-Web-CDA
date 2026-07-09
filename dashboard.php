<?php

require_once 'includes/auth.php';
require_once 'includes/rutas.php';

requerirSesion();

$usuario = obtenerUsuarioActual();
$rol = $usuario['rol'];

if (isset($_GET['seccion'])) {
    $seccion = trim((string) $_GET['seccion']);

    if (puedeVerSeccion($rol, $seccion)) {
        header('Location: ' . obtenerUrlSeccion($seccion));
        exit;
    }
}

header('Location: ' . obtenerUrlInicioPorRol($rol));
exit;
