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
$pestañasPermitidas = obtenerPestanasEstructuraPermitidas($usuario['rol']);

if ($pestañasPermitidas === []) {
    header('Location: ' . obtenerUrlInicioPorRol($usuario['rol']));
    exit;
}

$pestaña = isset($_GET['pestaña']) ? trim((string) $_GET['pestaña']) : $pestañasPermitidas[0];

if (!in_array($pestaña, $pestañasPermitidas, true)) {
    $pestaña = $pestañasPermitidas[0];
}

$mensaje = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    require_once 'includes/actividad_log.php';

    $pestanaPorAccion = [
        'crear_territorio'      => 'territorios',
        'actualizar_territorio' => 'territorios',
        'crear_lider'           => 'lideres',
        'actualizar_lider'      => 'lideres',
        'crear_casa'            => 'casas',
        'actualizar_casa'       => 'casas',
    ];

    try {
        if (isset($pestanaPorAccion[$accion]) && !puedeGestionarEstructuraPestana($usuario['rol'], $pestanaPorAccion[$accion])) {
            throw new InvalidArgumentException('No tienes permiso para esta acción.');
        }

        switch ($accion) {
            case 'crear_territorio':
                if (trim($_POST['nombre'] ?? '') === '') {
                    throw new InvalidArgumentException('El nombre del territorio es obligatorio.');
                }
                crearTerritorio($_POST['nombre']);
                registrarActividadPorAccion('crear_territorio', 0, 'Crear territorio · ' . trim((string) $_POST['nombre']));
                $mensaje = 'Territorio creado correctamente.';
                $pestaña = 'territorios';
                break;

            case 'actualizar_territorio':
                $id = (int) ($_POST['id'] ?? 0);
                if ($id <= 0 || trim($_POST['nombre'] ?? '') === '') {
                    throw new InvalidArgumentException('Datos de territorio inválidos.');
                }
                actualizarTerritorio($id, $_POST['nombre']);
                registrarActividadPorAccion('actualizar_territorio', $id);
                $mensaje = 'Territorio actualizado.';
                $pestaña = 'territorios';
                break;

            case 'crear_lider':
                if (trim($_POST['nombre'] ?? '') === '' || trim($_POST['apellido'] ?? '') === '') {
                    throw new InvalidArgumentException('Nombre y apellido del líder son obligatorios.');
                }
                crearLider($_POST);
                registrarActividadPorAccion(
                    'crear_lider',
                    0,
                    'Crear líder · ' . trim((string) $_POST['nombre']) . ' ' . trim((string) $_POST['apellido'])
                );
                $mensaje = 'Líder creado correctamente.';
                $pestaña = 'lideres';
                break;

            case 'actualizar_lider':
                $id = (int) ($_POST['id'] ?? 0);
                if ($id <= 0) {
                    throw new InvalidArgumentException('Líder no válido.');
                }
                actualizarLider($id, $_POST);
                registrarActividadPorAccion('actualizar_lider', $id);
                $mensaje = 'Líder actualizado.';
                $pestaña = 'lideres';
                break;

            case 'crear_casa':
                if (trim($_POST['nombre'] ?? '') === '' || trim($_POST['direccion'] ?? '') === '') {
                    throw new InvalidArgumentException('Nombre y dirección de la casa son obligatorios.');
                }
                crearCasaVida($_POST);
                registrarActividadPorAccion('crear_casa', 0, 'Crear casa de vida · ' . trim((string) $_POST['nombre']));
                $mensaje = 'Casa de vida creada correctamente.';
                $pestaña = 'casas';
                break;

            case 'actualizar_casa':
                $id = (int) ($_POST['id'] ?? 0);
                if ($id <= 0) {
                    throw new InvalidArgumentException('Casa de vida no válida.');
                }
                actualizarCasaVida($id, $_POST);
                registrarActividadPorAccion('actualizar_casa', $id);
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
    'pestañasPermitidas'  => $pestañasPermitidas,
], 'app');
