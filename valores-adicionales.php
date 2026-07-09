<?php

require_once 'includes/auth.php';
require_once 'includes/view.php';
require_once 'includes/submissions.php';
require_once 'includes/filters.php';
require_once 'includes/paginacion.php';
require_once 'includes/valores_adicionales.php';
require_once 'includes/detalle_registro.php';

requerirSesion();

$usuario = obtenerUsuarioActual();
$rol = $usuario['rol'];

if (!puedeVerValoresAdicionales($rol)) {
    header('Location: ' . obtenerUrlInicioPorRol($rol));
    exit;
}

$seccionesPermitidas = obtenerSeccionesPermitidas($rol);
$etiquetasSecciones = obtenerEtiquetasSecciones();
$etiquetasRoles = obtenerEtiquetasRoles();
$puedeGestionarTipos = puedeGestionarTiposValorAdicional($rol);

$filtros = parsearFiltrosRegistros($_GET);
$pagina = parsearPaginaRegistros($_GET);
$consultaFiltros = http_build_query(array_filter([
    'buscar'      => $filtros['buscar'],
    'fecha_desde' => $filtros['fecha_desde'],
    'fecha_hasta' => $filtros['fecha_hasta'],
    'tipo_valor'  => $filtros['tipo_valor'],
    'monto_min'   => $filtros['monto_min'],
    'monto_max'   => $filtros['monto_max'],
]));

$pestaña = isset($_GET['pestaña']) ? trim((string) $_GET['pestaña']) : 'registros';
$pestañasPermitidas = ['registros'];

if (puedeRegistrarValoresAdicionales($rol)) {
    $pestañasPermitidas[] = 'nuevo';
}

if ($puedeGestionarTipos) {
    $pestañasPermitidas[] = 'tipos';
}

if (!in_array($pestaña, $pestañasPermitidas, true)) {
    $pestaña = 'registros';
}

$mensaje = null;
$error = isset($_GET['error']) ? (string) $_GET['error'] : null;

if (isset($_GET['ok'])) {
    if ($pestaña === 'tipos') {
        $mensaje = 'Tipo de valor adicional guardado correctamente.';
    } elseif ($pestaña === 'nuevo') {
        $mensaje = 'Valor adicional registrado correctamente.';
    } else {
        $mensaje = 'Operación realizada correctamente.';
    }
}

if (isset($_GET['actualizado'])) {
    $mensaje = 'Registro actualizado correctamente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $puedeGestionarTipos) {
    $accion = $_POST['accion'] ?? '';

    try {
        switch ($accion) {
            case 'crear_tipo_valor':
                if (trim($_POST['etiqueta'] ?? '') === '') {
                    throw new InvalidArgumentException('La etiqueta del tipo es obligatoria.');
                }
                crearTipoValorAdicional($_POST['etiqueta']);
                header('Location: valores-adicionales.php?pestaña=tipos&ok=1');
                exit;

            case 'actualizar_tipo_valor':
                $id = (int) ($_POST['id'] ?? 0);
                if ($id <= 0 || trim($_POST['etiqueta'] ?? '') === '') {
                    throw new InvalidArgumentException('Datos de tipo inválidos.');
                }
                actualizarTipoValorAdicional($id, $_POST['etiqueta']);
                header('Location: valores-adicionales.php?pestaña=tipos&ok=1');
                exit;
        }
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
        $pestaña = 'tipos';
    } catch (PDOException $e) {
        $error = 'No se pudo guardar el tipo. Verifica que exista la tabla tipos_valor_adicional.';
        $pestaña = 'tipos';
    }
}

$errorBd = null;
$registros = [];
$totalRegistros = 0;
$totalPaginas = 1;
$offsetRegistros = 0;
$tiposValor = [];
$filasTipos = [];

try {
    $estadisticas = obtenerEstadisticasPorRol($rol);
    $tiposValor = obtenerTiposValorAdicional();

    if ($pestaña === 'tipos' && $puedeGestionarTipos) {
        $filasTipos = obtenerFilasTiposValorAdicional();
    } else {
        $totalRegistros = contarValoresAdicionalesFiltrados($filtros);
        $pagina = ajustarPaginaRegistros($pagina, $totalRegistros);
        $offsetRegistros = calcularOffsetRegistros($pagina);
        $totalPaginas = calcularTotalPaginasRegistros($totalRegistros);
        $registros = buscarValoresAdicionales($filtros, REGISTROS_POR_PAGINA, $offsetRegistros);
    }
} catch (PDOException $e) {
    $estadisticas = [];
    $errorBd = 'No se pudieron cargar los registros. Usa «Crear tablas» en el login si aún no existen.';
}

view('valores-adicionales/index', [
    'tituloPagina'           => 'Valores adicionales',
    'usuario'                => $usuario,
    'seccionActiva'          => 'valores_adicionales',
    'seccion'                => '',
    'seccionesPermitidas'    => $seccionesPermitidas,
    'etiquetasSecciones'     => $etiquetasSecciones,
    'etiquetasRoles'         => $etiquetasRoles,
    'estadisticas'           => $estadisticas ?? [],
    'puedeGestionarUsuarios' => puedeGestionarUsuarios($rol),
    'puedeEliminar'          => puedeEliminarRegistros($rol),
    'puedeEditar'            => puedeEditarRegistros($rol),
    'puedeRegistrar'         => puedeRegistrarValoresAdicionales($rol),
    'puedeGestionarTipos'    => $puedeGestionarTipos,
    'tiposValor'             => $tiposValor,
    'filasTipos'             => $filasTipos,
    'registros'              => $registros,
    'totalRegistros'         => $totalRegistros,
    'filtros'                => $filtros,
    'consultaFiltros'        => $consultaFiltros,
    'mensaje'                => $mensaje,
    'error'                  => $error,
    'errorBd'                => $errorBd,
    'pestaña'                => $pestaña,
    'paginaActual'           => $pagina,
    'totalPaginas'           => $totalPaginas,
    'offsetRegistros'        => $offsetRegistros,
    'archivoPagina'          => 'valores-adicionales.php',
], 'app');
