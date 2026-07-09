<?php

require_once __DIR__ . '/view.php';
require_once __DIR__ . '/filters.php';
require_once __DIR__ . '/rutas.php';
require_once __DIR__ . '/paginacion.php';
require_once __DIR__ . '/detalle_registro.php';

function renderizarPaginaRegistros(string $seccion): void
{
    requerirSesion();

    $usuario = obtenerUsuarioActual();
    $rol = $usuario['rol'];

    if (!puedeVerSeccion($rol, $seccion)) {
        header('Location: ' . obtenerUrlInicioPorRol($rol));
        exit;
    }

    $seccionesPermitidas = obtenerSeccionesPermitidas($rol);
    $etiquetasSecciones = obtenerEtiquetasSecciones();
    $archivoPagina = obtenerUrlSeccion($seccion);

    $pestaña = isset($_GET['pestaña']) ? trim((string) $_GET['pestaña']) : 'registros';
    if (!in_array($pestaña, ['registros', 'nuevo'], true)) {
        $pestaña = 'registros';
    }

    $puedeRegistrar = $seccion === 'generales' ? false : puedeRegistrarEnSeccion($rol, $seccion);
    if ($pestaña === 'nuevo' && !$puedeRegistrar) {
        $pestaña = 'registros';
    }

    $mensaje = null;
    if (isset($_GET['ok'])) {
        $mensaje = $pestaña === 'nuevo'
            ? 'Registro guardado correctamente.'
            : 'Operación realizada correctamente.';
    }
    if (isset($_GET['actualizado'])) {
        $mensaje = 'Registro actualizado correctamente.';
    }
    $error = isset($_GET['error']) ? (string) $_GET['error'] : null;

    $filtros = parsearFiltrosRegistros($_GET);
    if ($seccion === 'generales') {
        $hoy = date('Y-m-d');
        $filtros['buscar'] = '';
        $filtros['fecha_desde'] = $hoy;
        $filtros['fecha_hasta'] = $hoy;
        $filtros['zona'] = '';
        $filtros['contactado'] = 'todos';
        $pestaña = 'registros';
    }
    $tiposPermitidos = obtenerTiposInscripcionPermitidos($rol);
    $pagina = parsearPaginaRegistros($_GET);

    $puedeVerPresentaciones = puedeVerPresentaciones($rol);
    $puedeVerOfrendas = puedeVerOfrendas($rol);
    $puedeVerBautismo = puedeVerBautismo($rol);
    $puedeEliminar = puedeEliminarRegistros($rol);
    $puedeEditar = puedeEditarRegistros($rol);

    $errorBd = null;
    $estadisticas = [];
    $registros = [];
    $totalRegistros = 0;
    $tipoRegistro = 'inscripciones';
    $totalPaginas = 1;
    $offsetRegistros = 0;

    try {
        $estadisticas = obtenerEstadisticasPorRol($rol);

        switch ($seccion) {
            case 'generales':
                $totalRegistros = contarInscripcionesFiltradas($tiposPermitidos, null, $filtros);
                $tipoRegistro = 'inscripciones';
                break;

            case 'presentaciones':
                $totalRegistros = contarPresentacionesFiltradas($filtros);
                $tipoRegistro = 'presentaciones';
                break;

            default:
                if (in_array($seccion, $tiposPermitidos, true)) {
                    $totalRegistros = contarInscripcionesFiltradas($tiposPermitidos, $seccion, $filtros);
                    $tipoRegistro = 'inscripciones';
                }
                break;
        }

        $pagina = ajustarPaginaRegistros($pagina, $totalRegistros);
        $offsetRegistros = calcularOffsetRegistros($pagina);
        $totalPaginas = calcularTotalPaginasRegistros($totalRegistros);

        switch ($seccion) {
            case 'generales':
                $registros = buscarInscripciones($tiposPermitidos, null, $filtros, REGISTROS_POR_PAGINA, $offsetRegistros);
                break;

            case 'presentaciones':
                $registros = buscarPresentacionesNinos($filtros, REGISTROS_POR_PAGINA, $offsetRegistros);
                break;

            default:
                if (in_array($seccion, $tiposPermitidos, true)) {
                    $registros = buscarInscripciones($tiposPermitidos, $seccion, $filtros, REGISTROS_POR_PAGINA, $offsetRegistros);
                }
                break;
        }
    } catch (PDOException $e) {
        $errorBd = 'No se pudieron cargar los registros. Usa «Crear tablas» en el login si aún no existen.';
    }

    $etiquetasFormulario = obtenerEtiquetasTiposFormulario();
    $etiquetasRoles = obtenerEtiquetasRoles();
    $consultaFiltros = construirConsultaFiltros($filtros);
    $urlPaginaConFiltros = construirUrlRegistros($archivoPagina, $filtros, $pagina);
    $zonas = obtenerZonasConexion();
    $estadosPresentacion = obtenerEstadosPresentacion();
    $etiquetasEstadosPresentacion = obtenerEtiquetasEstadosPresentacion();

    view('dashboard/index', [
        'tituloPagina'                 => $etiquetasSecciones[$seccion] ?? 'Registros',
        'usuario'                      => $usuario,
        'seccionActiva'                => $seccion,
        'estadisticas'                 => $estadisticas,
        'seccion'                      => $seccion,
        'archivoPagina'                => $archivoPagina,
        'urlPaginaConFiltros'          => $urlPaginaConFiltros,
        'seccionesPermitidas'          => $seccionesPermitidas,
        'etiquetasSecciones'           => $etiquetasSecciones,
        'registros'                    => $registros,
        'totalRegistros'               => $totalRegistros,
        'tipoRegistro'                 => $tipoRegistro,
        'filtros'                      => $filtros,
        'consultaFiltros'              => $consultaFiltros,
        'etiquetasFormulario'          => $etiquetasFormulario,
        'etiquetasRoles'               => $etiquetasRoles,
        'errorBd'                      => $errorBd,
        'tiposPermitidos'              => $tiposPermitidos,
        'puedeVerPresentaciones'       => $puedeVerPresentaciones,
        'puedeVerOfrendas'             => $puedeVerOfrendas,
        'puedeVerBautismo'             => $puedeVerBautismo,
        'puedeEliminar'                => $puedeEliminar,
        'puedeEditar'                  => $puedeEditar,
        'puedeRegistrar'               => $puedeRegistrar,
        'puedeGestionarEstadoConexion'   => puedeGestionarEstadoConexion($rol),
        'puedeGestionarUsuarios'       => puedeGestionarUsuarios($rol),
        'pestaña'                      => $pestaña,
        'mensaje'                      => $mensaje,
        'error'                        => $error,
        'zonas'                        => $zonas,
        'estadosPresentacion'          => $estadosPresentacion,
        'etiquetasEstadosPresentacion' => $etiquetasEstadosPresentacion,
        'paginaActual'                 => $pagina,
        'totalPaginas'                 => $totalPaginas,
        'offsetRegistros'              => $offsetRegistros,
    ], 'app');
}
