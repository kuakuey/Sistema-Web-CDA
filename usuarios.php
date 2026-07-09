<?php

require_once 'includes/auth.php';
require_once 'includes/view.php';
require_once 'includes/submissions.php';
require_once 'includes/users.php';
require_once 'includes/permisos.php';

requerirSuperadmin();

$usuario = obtenerUsuarioActual();
$rol = $usuario['rol'];
$mensaje = null;
$error = null;

$pestaña = isset($_GET['pestaña']) ? trim((string) $_GET['pestaña']) : 'lista';
if (!in_array($pestaña, ['lista', 'registrar', 'permisos'], true)) {
    $pestaña = 'lista';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'crear_usuario') {
    $resultado = crearUsuario(
        $_POST['usuario'] ?? '',
        $_POST['clave'] ?? '',
        $_POST['nombre'] ?? '',
        $_POST['rol'] ?? ''
    );

    if ($resultado['exito']) {
        header('Location: usuarios.php?pestaña=registrar&ok=1');
        exit;
    }

    header('Location: usuarios.php?pestaña=registrar&error=' . urlencode($resultado['mensaje']));
    exit;
}

if (isset($_GET['ok'])) {
    $mensaje = $pestaña === 'permisos'
        ? 'Permisos actualizados correctamente.'
        : 'Usuario creado correctamente.';
}

$usuarios = obtenerTodosUsuarios();
$etiquetasRoles = obtenerEtiquetasRoles();
$seccionesPermitidas = obtenerSeccionesPermitidas($rol);
$etiquetasSecciones = obtenerEtiquetasSecciones();
$seccionesPermisos = obtenerSeccionesConfigurablesPermisos();
$catalogoPermisos = obtenerCatalogoPermisosDetallados();
$rolesPermisos = obtenerRolesConfigurablesPermisos();
$matrizPermisos = cargarMatrizPermisosRoles();
$rolPermisosActivo = isset($_GET['rol']) ? trim((string) $_GET['rol']) : ROL_ADMIN;

if (!array_key_exists($rolPermisosActivo, $rolesPermisos)) {
    $rolPermisosActivo = ROL_ADMIN;
}

$permisosActivosRol = normalizarPermisosParaUi($matrizPermisos[$rolPermisosActivo] ?? []);

$estadisticas = [];

try {
    $estadisticas = obtenerEstadisticasPorRol($rol);
} catch (PDOException $e) {
    // Sidebar sin contadores si falla la BD
}

view('usuarios/index', [
    'tituloPagina'        => 'Usuarios',
    'usuario'             => $usuario,
    'seccionActiva'       => 'usuarios',
    'seccion'             => '',
    'usuarios'            => $usuarios,
    'etiquetasRoles'      => $etiquetasRoles,
    'seccionesPermitidas' => $seccionesPermitidas,
    'etiquetasSecciones'  => $etiquetasSecciones,
    'estadisticas'        => $estadisticas,
    'mensaje'             => $mensaje,
    'error'               => $error,
    'pestaña'             => $pestaña,
    'puedeEliminar'       => true,
    'puedeGestionarUsuarios' => true,
    'seccionesPermisos'   => $seccionesPermisos,
    'catalogoPermisos'    => $catalogoPermisos,
    'rolesPermisos'       => $rolesPermisos,
    'matrizPermisos'      => $matrizPermisos,
    'rolPermisosActivo'   => $rolPermisosActivo,
    'permisosActivosRol'  => $permisosActivosRol,
], 'app');
