<?php

require_once 'includes/auth.php';
require_once 'includes/view.php';
require_once 'includes/submissions.php';
require_once 'includes/filters.php';
require_once 'includes/paginacion.php';
require_once 'includes/eventos.php';
require_once 'includes/valores_adicionales.php';
require_once 'includes/detalle_registro.php';

requerirSesion();

$usuario = obtenerUsuarioActual();
$rol = $usuario['rol'];

if (!puedeGestionarEventos($rol)) {
    header('Location: ' . obtenerUrlInicioPorRol($rol));
    exit;
}

$pestaña = isset($_GET['pestaña']) ? trim((string) $_GET['pestaña']) : 'tabla';
$pestañasPermitidas = [];

if (puedeVerTablaEventos($rol)) {
    $pestañasPermitidas[] = 'tabla';
}

if (puedeRegistrarEventos($rol)) {
    $pestañasPermitidas[] = 'registrar';
}

if (puedeAgregarEventos($rol)) {
    $pestañasPermitidas[] = 'agregar';
}

if (puedeVerCatalogoEventos($rol)) {
    $pestañasPermitidas[] = 'catalogo';
}

if (puedeVerInformeEventos($rol)) {
    $pestañasPermitidas[] = 'informe';
}

if ($pestañasPermitidas === []) {
    header('Location: ' . obtenerUrlInicioPorRol($rol));
    exit;
}

if (!in_array($pestaña, $pestañasPermitidas, true)) {
    $pestaña = $pestañasPermitidas[0];
}

if (isset($_GET['generar']) && $_GET['generar'] === '1') {
    if (!puedeVerInformeEventos($rol)) {
        header('Location: eventos.php?pestaña=informe&error=' . urlencode('No tienes permiso para generar informes.'));
        exit;
    }

    try {
        require_once 'includes/informe_evento_pdf.php';
        $informe = generarInformeEvento((int) ($_GET['evento_id'] ?? 0));
        enviarInformeEventoPdf($informe);
        exit;
    } catch (InvalidArgumentException $e) {
        header('Location: eventos.php?pestaña=informe&error=' . urlencode($e->getMessage()));
        exit;
    } catch (RuntimeException $e) {
        header('Location: eventos.php?pestaña=informe&error=' . urlencode($e->getMessage()));
        exit;
    }
}

$filtros = parsearFiltrosRegistros($_GET);
$pagina = parsearPaginaRegistros($_GET);

$mensaje = null;
if (isset($_GET['ok'])) {
    if ($pestaña === 'registrar') {
        $mensaje = 'Participante registrado correctamente.';
    } elseif ($pestaña === 'agregar') {
        $mensaje = 'Evento creado correctamente.';
    } elseif ($pestaña === 'catalogo') {
        $mensaje = 'Evento actualizado correctamente.';
    } else {
        $mensaje = 'Operación realizada correctamente.';
    }
}
$error = isset($_GET['error']) ? (string) $_GET['error'] : null;
$errorBd = null;
$eventos = [];
$eventosHabilitados = [];
$registros = [];
$totalRegistros = 0;
$totalPaginas = 1;
$offsetRegistros = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    try {
        switch ($accion) {
            case 'crear_evento':
                if (!puedeAgregarEventos($rol)) {
                    throw new InvalidArgumentException('No tienes permiso para agregar eventos.');
                }

                crearEvento([
                    'nombre'               => $_POST['nombre'] ?? '',
                    'fecha'                => $_POST['fecha'] ?? '',
                    'tipo_cobro'           => $_POST['tipo_cobro'] ?? 'pago',
                    'valor'                => $_POST['valor'] ?? 0,
                    'habilitado'           => isset($_POST['habilitado']) ? 1 : 0,
                    'requiere_numeracion'  => isset($_POST['requiere_numeracion']) ? 1 : 0,
                ]);
                header('Location: eventos.php?pestaña=agregar&ok=1');
                exit;

            case 'actualizar_evento_catalogo':
                if (!puedeAgregarEventos($rol)) {
                    throw new InvalidArgumentException('No tienes permiso para editar eventos.');
                }

                actualizarEventoCatalogo((int) ($_POST['id'] ?? 0), [
                    'nombre'               => $_POST['nombre'] ?? '',
                    'fecha'                => $_POST['fecha'] ?? '',
                    'tipo_cobro'           => $_POST['tipo_cobro'] ?? 'pago',
                    'valor'                => $_POST['valor'] ?? 0,
                    'habilitado'           => isset($_POST['habilitado']) ? 1 : 0,
                    'requiere_numeracion'  => isset($_POST['requiere_numeracion']) ? 1 : 0,
                ]);
                header('Location: eventos.php?pestaña=catalogo&ok=1');
                exit;
        }
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (PDOException $e) {
        $error = 'No se pudo guardar. Verifica que exista la tabla eventos.';
    }
}

try {
    $eventos = obtenerEventos();
    $eventosHabilitados = obtenerEventosHabilitados();

    if ($pestaña === 'tabla') {
        $totalRegistros = contarRegistrosEventos($filtros);
        $pagina = ajustarPaginaRegistros($pagina, $totalRegistros);
        $offsetRegistros = calcularOffsetRegistros($pagina);
        $totalPaginas = calcularTotalPaginasRegistros($totalRegistros);
        $registros = buscarRegistrosEventos($filtros, REGISTROS_POR_PAGINA, $offsetRegistros);
    }
} catch (PDOException $e) {
    $errorBd = 'No se pudieron cargar los eventos. Usa «Crear tablas» en el login si aún no existen.';
}

$etiquetasRoles = obtenerEtiquetasRoles();
$seccionesPermitidas = obtenerSeccionesPermitidas($rol);
$etiquetasSecciones = obtenerEtiquetasSecciones();

try {
    $estadisticas = obtenerEstadisticasPorRol($rol);
} catch (PDOException $e) {
    $estadisticas = [];
}

view('eventos/index', [
    'tituloPagina'           => 'Eventos',
    'usuario'                => $usuario,
    'seccionActiva'          => 'eventos',
    'seccion'                => '',
    'seccionesPermitidas'    => $seccionesPermitidas,
    'etiquetasSecciones'     => $etiquetasSecciones,
    'etiquetasRoles'         => $etiquetasRoles,
    'estadisticas'           => $estadisticas ?? [],
    'puedeGestionarUsuarios' => puedeGestionarUsuarios($rol),
    'puedeEliminar'          => puedeEliminarRegistros($rol),
    'puedeEditar'            => puedeEditarRegistros($rol),
    'puedeAgregar'           => puedeAgregarEventos($rol),
    'puedeRegistrar'         => puedeRegistrarEventos($rol),
    'puedeVerTabla'          => puedeVerTablaEventos($rol),
    'puedeVerCatalogo'       => puedeVerCatalogoEventos($rol),
    'puedeVerInforme'        => puedeVerInformeEventos($rol),
    'eventoInformeSeleccionado' => isset($_GET['evento_id']) ? (int) $_GET['evento_id'] : 0,
    'eventos'                => $eventos,
    'eventosHabilitados'     => $eventosHabilitados,
    'registros'              => $registros,
    'totalRegistros'         => $totalRegistros,
    'filtros'                => $filtros,
    'pestaña'                => $pestaña,
    'mensaje'                => $mensaje,
    'error'                  => $error,
    'errorBd'                => $errorBd,
    'paginaActual'           => $pagina,
    'totalPaginas'           => $totalPaginas,
    'offsetRegistros'        => $offsetRegistros,
    'archivoPagina'          => 'eventos.php',
    'formasPagoEvento'       => obtenerFormasPagoEvento(),
], 'app');
