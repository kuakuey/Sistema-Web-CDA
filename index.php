<?php

require_once 'includes/auth.php';
require_once 'includes/view.php';

if (estaLogueado()) {
    header('Location: ' . obtenerUrlInicioPorRol($_SESSION['rol'] ?? ROL_ADMIN));
    exit;
}

$error = '';
$resultadoInstalacion = null;
$claveMantenimiento = trim((string) ($_GET['m'] ?? $_POST['m'] ?? ''));
$modoMantenimiento = esClaveMantenimientoBdValida($claveMantenimiento);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? 'iniciar';

    if ($accion === 'instalar') {
        $clavePost = trim((string) ($_POST['m'] ?? ''));

        if (!esClaveMantenimientoBdValida($clavePost)) {
            $error = 'Acceso no autorizado.';
        } else {
            $resultadoInstalacion = setupDatabase();
            $modoMantenimiento = true;
            $claveMantenimiento = $clavePost;
        }
    } elseif (!$modoMantenimiento) {
        $usuario = trim($_POST['usuario'] ?? '');
        $clave = $_POST['clave'] ?? '';

        if ($usuario !== '' && iniciarSesion($usuario, $clave)) {
            header('Location: ' . obtenerUrlInicioPorRol($_SESSION['rol'] ?? ROL_ADMIN));
            exit;
        }

        $error = 'Usuario o contraseña incorrectos.';
    }
}

if ($modoMantenimiento) {
    view('auth/mantenimiento-bd', [
        'tituloPagina'         => 'Mantenimiento BD',
        'resultadoInstalacion' => $resultadoInstalacion,
        'error'                => $error,
        'claveMantenimiento'   => $claveMantenimiento,
    ], 'login');
    exit;
}

view('auth/login', [
    'tituloPagina'         => 'Iniciar sesión',
    'error'                => $error,
    'resultadoInstalacion' => $resultadoInstalacion,
], 'login');
