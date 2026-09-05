<?php

require_once 'includes/auth.php';
require_once 'includes/view.php';
require_once 'includes/estructura.php';
require_once 'includes/import_estructura.php';
require_once 'includes/submissions.php';
require_once 'includes/paginacion.php';

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

$pasosImportar = obtenerPasosImportEstructuraPermitidos($usuario['rol']);
$pasoImportar = isset($_GET['paso']) ? trim((string) $_GET['paso']) : ($pasosImportar[0] ?? 'miembros');
if ($pasoImportar === 'personas') {
    $pasoImportar = 'miembros';
}
if (!in_array($pasoImportar, $pasosImportar, true)) {
    $pasoImportar = $pasosImportar[0] ?? 'miembros';
}

if ($pestaña === 'importar' && isset($_GET['descargar']) && $_GET['descargar'] === 'plantilla') {
    $formato = isset($_GET['formato']) && $_GET['formato'] === 'csv' ? 'csv' : 'xls';
    enviarPlantillaImportEstructura($pasoImportar, $formato);
    exit;
}

$mensaje = null;
$error = null;

$resultadoImportEstructura = $_SESSION['import_estructura_resultado'] ?? null;
$errorImportEstructura = $_SESSION['import_estructura_error'] ?? null;
unset($_SESSION['import_estructura_resultado'], $_SESSION['import_estructura_error']);

if (is_array($errorImportEstructura)) {
    $error = (string) ($errorImportEstructura['mensaje'] ?? 'Error al importar.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    require_once 'includes/actividad_log.php';

    $pestanaPorAccion = [
        'crear_territorio'             => 'territorios',
        'actualizar_territorio'        => 'territorios',
        'asignar_pareja_territorio'    => 'territorios',
        'quitar_asignacion_territorio' => 'territorios',
        'crear_lider'                  => 'lideres',
        'actualizar_lider'             => 'lideres',
        'conectar_parentesco'          => 'lideres',
        'eliminar_parentesco'          => 'lideres',
        'eliminar_todos_lideres'       => 'lideres',
        'crear_casa'            => 'casas',
        'actualizar_casa'       => 'casas',
        'importar_estructura'   => 'importar',
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

            case 'asignar_pareja_territorio':
                $esposoId = (int) ($_POST['esposo_id'] ?? 0);
                $esposaId = (int) ($_POST['esposa_id'] ?? 0);
                $clavePareja = trim((string) ($_POST['pareja_clave'] ?? ''));
                if ($clavePareja !== '' && str_contains($clavePareja, ':')) {
                    [$esposoId, $esposaId] = array_map('intval', explode(':', $clavePareja, 2));
                }
                $cantidad = asignarParejaATerritorios(
                    (string) ($_POST['rol'] ?? ''),
                    $esposoId,
                    $esposaId,
                    $_POST['territorio_ids'] ?? []
                );
                $rolEtiqueta = etiquetaRolTerritorio(normalizarRolTerritorio((string) ($_POST['rol'] ?? '')));
                registrarActividadPorAccion(
                    'asignar_pareja_territorio',
                    0,
                    'Asignar ' . $rolEtiqueta . ' · ' . $cantidad . ' territorio(s)'
                );
                $mensaje = $rolEtiqueta . ' asignado a ' . $cantidad . ' territorio(s).';
                $pestaña = 'territorios';
                break;

            case 'quitar_asignacion_territorio':
                $territorioId = (int) ($_POST['territorio_id'] ?? 0);
                $rol = (string) ($_POST['rol'] ?? '');
                if ($territorioId <= 0) {
                    throw new InvalidArgumentException('Territorio no válido.');
                }
                quitarAsignacionTerritorio($territorioId, $rol);
                registrarActividadPorAccion(
                    'quitar_asignacion_territorio',
                    $territorioId,
                    'Quitar ' . etiquetaRolTerritorio(normalizarRolTerritorio($rol))
                );
                $mensaje = 'Asignación eliminada del territorio.';
                $pestaña = 'territorios';
                break;

            case 'crear_lider':
                if (trim($_POST['nombre'] ?? '') === '' || trim($_POST['apellido'] ?? '') === '') {
                    throw new InvalidArgumentException('Nombre y apellido del miembro son obligatorios.');
                }
                if (trim((string) ($_POST['genero'] ?? '')) === '') {
                    throw new InvalidArgumentException('Selecciona el género del miembro.');
                }
                crearLider($_POST);
                registrarActividadPorAccion(
                    'crear_lider',
                    0,
                    'Crear miembro · ' . trim((string) $_POST['nombre']) . ' ' . trim((string) $_POST['apellido'])
                );
                $mensaje = 'Miembro creado correctamente.';
                $pestaña = 'lideres';
                break;

            case 'conectar_parentesco':
                conectarParentescoMiembros(
                    (int) ($_POST['miembro_id'] ?? 0),
                    (int) ($_POST['pariente_id'] ?? 0),
                    (string) ($_POST['parentesco'] ?? '')
                );
                registrarActividadPorAccion('conectar_parentesco', (int) ($_POST['miembro_id'] ?? 0), 'Conectar parentesco');
                $mensaje = 'Parentesco conectado correctamente.';
                $pestaña = 'importar';
                break;

            case 'eliminar_parentesco':
                $miembroId = (int) ($_POST['miembro_id'] ?? 0);
                $parienteId = (int) ($_POST['pariente_id'] ?? 0);
                if (!eliminarParentescoMiembro($miembroId, $parienteId)) {
                    throw new InvalidArgumentException('No se pudo quitar el parentesco.');
                }
                registrarActividadPorAccion('eliminar_parentesco', $miembroId, 'Quitar parentesco');
                $mensaje = 'Parentesco eliminado.';
                $pestaña = 'lideres';
                break;

            case 'eliminar_todos_lideres':
                if (!puedeEliminarRegistros($usuario['rol'])) {
                    throw new InvalidArgumentException('No tienes permiso para eliminar miembros.');
                }
                $eliminados = eliminarTodosLideres();
                registrarActividadPorAccion(
                    'eliminar_todos_lideres',
                    0,
                    'Eliminar todos los miembros · ' . $eliminados
                );
                $mensaje = $eliminados === 0
                    ? 'No había miembros para eliminar.'
                    : 'Se eliminaron ' . $eliminados . ' miembro(s), sus parentescos, asignaciones y casas de vida.';
                $pestaña = 'importar';
                break;

            case 'actualizar_lider':
                $id = (int) ($_POST['id'] ?? 0);
                if ($id <= 0) {
                    throw new InvalidArgumentException('Miembro no válido.');
                }
                actualizarLider($id, $_POST);
                registrarActividadPorAccion('actualizar_lider', $id);
                $mensaje = 'Miembro actualizado.';
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

            case 'importar_estructura':
                $pasoImportar = trim((string) ($_POST['paso'] ?? $pasoImportar));
                if (!in_array($pasoImportar, $pasosImportar, true)) {
                    throw new InvalidArgumentException('No tienes permiso para importar este paso.');
                }

                $resultadoImport = procesarImportacionEstructura(
                    $_FILES['archivo'] ?? [],
                    $pasoImportar,
                    $usuario
                );
                $_SESSION['import_estructura_resultado'] = $resultadoImport;
                registrarActividad(
                    'importar_estructura',
                    'estructura',
                    $pasoImportar,
                    null,
                    'Importar ' . $pasoImportar . ' · ' . (int) ($resultadoImport['importados'] ?? 0) . ' importado(s), '
                        . (int) ($resultadoImport['duplicados'] ?? 0) . ' duplicado(s), '
                        . count($resultadoImport['errores'] ?? []) . ' error(es)',
                    $usuario,
                    $resultadoImport
                );
                header('Location: estructura.php?pestaña=importar&paso=' . urlencode($pasoImportar) . '&ok=1');
                exit;
        }
    } catch (InvalidArgumentException $e) {
        if ($accion === 'importar_estructura') {
            $_SESSION['import_estructura_error'] = ['mensaje' => $e->getMessage()];
            header('Location: estructura.php?pestaña=importar&paso=' . urlencode($pasoImportar) . '&error=1');
            exit;
        }
        $error = $e->getMessage();
    } catch (PDOException $e) {
        if ($accion === 'importar_estructura') {
            $_SESSION['import_estructura_error'] = ['mensaje' => 'No se pudieron importar los registros. Revisa el archivo e inténtalo de nuevo.'];
            header('Location: estructura.php?pestaña=importar&paso=' . urlencode($pasoImportar) . '&error=1');
            exit;
        }
        $error = 'No se pudo guardar. Verifica que existan miembros y territorios.';
    }
}

$territorios = obtenerTerritoriosConAsignaciones();
$lideres = obtenerLideres();
$casas = obtenerCasasVida();
$parejasParentesco = obtenerParejasParentesco();
$parentescoPorMiembro = obtenerParentescoPorMiembro();
$miembrosMasculinos = obtenerMiembrosPorGenero('masculino');
$miembrosFemeninos = obtenerMiembrosPorGenero('femenino');
$resumenParejas = obtenerResumenParejasTerritorio();
$conteoAsignaciones = obtenerConteoAsignacionesPorMiembro();
$totalMiembros = count($lideres);
$paginaMiembros = ajustarPaginaRegistros(parsearPaginaRegistros($_GET), $totalMiembros);
$lideresPagina = array_slice($lideres, calcularOffsetRegistros($paginaMiembros), obtenerRegistrosPorPagina());
$totalPaginasMiembros = calcularTotalPaginasRegistros($totalMiembros);
$modalEstructura = null;
if ($error !== null && isset($accion)) {
    if ($accion === 'crear_lider') {
        $modalEstructura = 'miembro';
    } elseif ($accion === 'conectar_parentesco') {
        $modalEstructura = 'parentesco';
    }
}
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
    'lideresPagina'       => $lideresPagina,
    'totalMiembros'       => $totalMiembros,
    'paginaMiembros'      => $paginaMiembros,
    'totalPaginasMiembros' => $totalPaginasMiembros,
    'casas'               => $casas,
    'parejasParentesco'   => $parejasParentesco,
    'parentescoPorMiembro' => $parentescoPorMiembro,
    'miembrosMasculinos'  => $miembrosMasculinos,
    'miembrosFemeninos'   => $miembrosFemeninos,
    'resumenParejas'      => $resumenParejas,
    'modalEstructura'     => $modalEstructura,
    'conteoAsignaciones'  => $conteoAsignaciones,
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
    'pasosImportar'       => $pasosImportar,
    'pasoImportar'        => $pasoImportar,
    'resultadoImportEstructura' => is_array($resultadoImportEstructura) ? $resultadoImportEstructura : null,
], 'app');
