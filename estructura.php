<?php

require_once 'includes/auth.php';
require_once 'includes/view.php';
require_once 'includes/estructura.php';
require_once 'includes/submissions.php';

requerirSesion();

if (!puedeGestionarEstructura(obtenerUsuarioActual()['rol'])) {
    header('Location: ' . obtenerUrlInicioPorRol(obtenerUsuarioActual()['rol']));
    exit;
}

$usuario = obtenerUsuarioActual();
$pestaña = isset($_GET['pestaña']) ? trim((string) $_GET['pestaña']) : 'territorios';
$pestañasPermitidas = ['territorios', 'casas', 'lideres'];

if (!in_array($pestaña, $pestañasPermitidas, true)) {
    $pestaña = 'territorios';
}

$mensaje = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    try {
        switch ($accion) {
            case 'crear_territorio':
                if (trim($_POST['nombre'] ?? '') === '') {
                    throw new InvalidArgumentException('El nombre del territorio es obligatorio.');
                }
                crearTerritorio($_POST['nombre']);
                $mensaje = 'Territorio creado correctamente.';
                $pestaña = 'territorios';
                break;

            case 'actualizar_territorio':
                $id = (int) ($_POST['id'] ?? 0);
                if ($id <= 0 || trim($_POST['nombre'] ?? '') === '') {
                    throw new InvalidArgumentException('Datos de territorio inválidos.');
                }
                actualizarTerritorio($id, $_POST['nombre']);
                $mensaje = 'Territorio actualizado.';
                $pestaña = 'territorios';
                break;

            case 'crear_lider':
                if (trim($_POST['nombre'] ?? '') === '' || trim($_POST['apellido'] ?? '') === '') {
                    throw new InvalidArgumentException('Nombre y apellido del líder son obligatorios.');
                }
                crearLider($_POST);
                $mensaje = 'Líder creado correctamente.';
                $pestaña = 'lideres';
                break;

            case 'actualizar_lider':
                $id = (int) ($_POST['id'] ?? 0);
                if ($id <= 0) {
                    throw new InvalidArgumentException('Líder no válido.');
                }
                actualizarLider($id, $_POST);
                $mensaje = 'Líder actualizado.';
                $pestaña = 'lideres';
                break;

            case 'crear_casa':
                if (trim($_POST['nombre'] ?? '') === '' || trim($_POST['direccion'] ?? '') === '') {
                    throw new InvalidArgumentException('Nombre y dirección de la casa son obligatorios.');
                }
                crearCasaVida($_POST);
                $mensaje = 'Casa de vida creada correctamente.';
                $pestaña = 'casas';
                break;

            case 'actualizar_casa':
                $id = (int) ($_POST['id'] ?? 0);
                if ($id <= 0) {
                    throw new InvalidArgumentException('Casa de vida no válida.');
                }
                actualizarCasaVida($id, $_POST);
                $mensaje = 'Casa de vida actualizada.';
                $pestaña = 'casas';
                break;
        }
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (PDOException $e) {
        $error = 'No se pudo guardar. Verifica que existan territorios y líderes.';
    }
}

$territorios = obtenerTerritorios();
$lideres = obtenerLideres();
$casas = obtenerCasasVida();
$etiquetasRoles = obtenerEtiquetasRoles();
$seccionesPermitidas = obtenerSeccionesPermitidas($usuario['rol']);
$etiquetasSecciones = obtenerEtiquetasSecciones();

try {
    $estadisticas = obtenerEstadisticasPorRol($usuario['rol']);
} catch (PDOException $e) {
    $estadisticas = [];
}

view('estructura/index', [
    'tituloPagina'        => 'Estructura CDV',
    'usuario'             => $usuario,
    'seccionActiva'       => 'estructura',
    'pestaña'             => $pestaña,
    'territorios'         => $territorios,
    'lideres'             => $lideres,
    'casas'               => $casas,
    'mensaje'             => $mensaje,
    'error'               => $error,
    'puedeEliminar'       => puedeEliminarRegistros($usuario['rol']),
    'puedeGestionarUsuarios' => puedeGestionarUsuarios($usuario['rol']),
    'etiquetasRoles'      => $etiquetasRoles,
    'seccionesPermitidas' => $seccionesPermitidas,
    'etiquetasSecciones'  => $etiquetasSecciones,
    'estadisticas'        => $estadisticas,
    'seccion'             => '',
], 'app');
