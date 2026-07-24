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

    $usaFlujoPestanas = seccionUsaFlujoPestanas($seccion);
    $pestañaPorDefecto = $usaFlujoPestanas ? 'nuevos' : 'registros';
    $pestaña = isset($_GET['pestaña']) ? trim((string) $_GET['pestaña']) : $pestañaPorDefecto;
    $pestañasPermitidas = $usaFlujoPestanas ? ['nuevos', 'registros', 'nuevo'] : ['registros', 'nuevo'];
    if (!in_array($pestaña, $pestañasPermitidas, true)) {
        $pestaña = $pestañaPorDefecto;
    }

    $puedeRegistrar = $seccion === 'generales' ? false : puedeRegistrarEnSeccion($rol, $seccion);
    if ($pestaña === 'nuevo' && !$puedeRegistrar) {
        $pestaña = $pestañaPorDefecto;
    }

    $mensaje = null;
    if (isset($_GET['ok'])) {
        if ($pestaña === 'nuevo' && $seccion === 'presentaciones') {
            $cantidad = isset($_GET['cantidad']) ? max(1, (int) $_GET['cantidad']) : 1;
            $mensaje = $cantidad > 1
                ? $cantidad . ' presentaciones registradas correctamente.'
                : 'Presentación registrada correctamente.';
        } elseif ($pestaña === 'nuevo') {
            $mensaje = 'Registro guardado correctamente.';
        } else {
            $mensaje = 'Operación realizada correctamente.';
        }
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
    } else {
        $filtros = aplicarFiltrosFlujoPestana($seccion, $pestaña, $filtros);
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
    $filtrosNavegacion = $filtros;
    if ($usaFlujoPestanas && in_array($pestaña, ['nuevos', 'registros'], true)) {
        if ($seccion === 'bautismo') {
            $filtrosNavegacion['estado'] = '';
        } elseif ($seccion === 'conexion') {
            $filtrosNavegacion['contactado'] = 'todos';
        } elseif ($seccion === 'presentaciones' && $pestaña === 'registros') {
            $filtrosNavegacion['estado'] = '';
        }
    }
    $urlPaginaConFiltros = construirUrlRegistros($archivoPagina, $filtrosNavegacion, $pagina, $pestaña);
    $zonas = obtenerZonasConexion();
    $estadosPresentacion = obtenerEstadosPresentacion();
    $etiquetasEstadosPresentacion = obtenerEtiquetasEstadosPresentacion();
    $estadosPresentacionFiltro = $estadosPresentacion;
    if ($usaFlujoPestanas && $seccion === 'presentaciones' && $pestaña === 'nuevos') {
        $estadosPresentacionFiltro = [];
        foreach ($estadosPresentacion as $estadoPresentacion) {
            if ($estadoPresentacion !== 'presentado') {
                $estadosPresentacionFiltro[] = $estadoPresentacion;
            }
        }
    }

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
        'filtrosNavegacion'            => $filtrosNavegacion,
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
        'puedeGestionarEstadoBautismo'   => puedeGestionarEstadoBautismo($rol),
        'puedeGestionarUsuarios'       => puedeGestionarUsuarios($rol),
        'pestaña'                      => $pestaña,
        'usaFlujoPestanas'             => $usaFlujoPestanas,
        'mensaje'                      => $mensaje,
        'error'                        => $error,
        'zonas'                        => $zonas,
        'estadosPresentacion'          => $estadosPresentacion,
        'estadosPresentacionFiltro'    => $estadosPresentacionFiltro,
        'etiquetasEstadosPresentacion' => $etiquetasEstadosPresentacion,
        'estadosBautismo'              => obtenerEstadosBautismo(),
        'etiquetasEstadosBautismo'     => obtenerEtiquetasEstadosBautismo(),
        'esSuperadmin'                 => $rol === ROL_SUPERADMIN,
        'paginaActual'                 => $pagina,
        'totalPaginas'                 => $totalPaginas,
        'offsetRegistros'              => $offsetRegistros,
    ], 'app');
}
