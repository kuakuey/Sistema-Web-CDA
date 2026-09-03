<?php

require_once 'includes/auth.php';
require_once 'includes/view.php';
require_once 'includes/submissions.php';
require_once 'includes/informes.php';
require_once 'includes/informe_pdf.php';
require_once 'includes/informe_excel.php';
require_once 'includes/eventos.php';
require_once 'includes/filters.php';
require_once 'includes/actividad_log.php';

requerirSesion();

$usuario = obtenerUsuarioActual();
$rol = $usuario['rol'];

$fechaDesde = isset($_GET['fecha_desde']) ? trim((string) $_GET['fecha_desde']) : '';
$fechaHasta = isset($_GET['fecha_hasta']) ? trim((string) $_GET['fecha_hasta']) : '';
$mostrarSinEntregar = isset($_GET['mostrar_sin_entregar']) && $_GET['mostrar_sin_entregar'] === '1';
$turno = isset($_GET['turno']) ? trim((string) $_GET['turno']) : 'todos';
$seccion = isset($_GET['seccion']) ? trim((string) $_GET['seccion']) : 'completo';
$formato = isset($_GET['formato']) ? trim((string) $_GET['formato']) : 'pdf';
$eventoId = isset($_GET['evento_id']) ? (int) $_GET['evento_id'] : 0;
$estadosPresentacionInforme = isset($_GET['estados_presentacion']) && is_array($_GET['estados_presentacion'])
    ? $_GET['estados_presentacion']
    : obtenerEstadosPresentacion();
$estadoBautismoInforme = isset($_GET['estado_bautismo']) ? trim((string) $_GET['estado_bautismo']) : 'todos';
$generar = isset($_GET['generar']);

$puedeGenerarInformesGenerales = puedeGenerarInforme($rol);
$puedeInformeEventos = puedeVerInformeEventos($rol);
$seccionSolicitada = normalizarSeccionInforme($seccion);
$descargaInformeEvento = $generar && $seccionSolicitada === 'eventos' && $puedeInformeEventos;

if (!$puedeGenerarInformesGenerales && !$puedeInformeEventos) {
    header('Location: ' . obtenerUrlInicioPorRol($rol));
    exit;
}

if ($generar && $seccionSolicitada !== 'eventos' && !$puedeGenerarInformesGenerales) {
    header('Location: ' . obtenerUrlInicioPorRol($rol));
    exit;
}

$seccionesPermitidas = obtenerSeccionesPermitidas($rol);
$etiquetasSecciones = obtenerEtiquetasSecciones();
$etiquetasRoles = obtenerEtiquetasRoles();

$error = isset($_GET['error']) ? trim((string) $_GET['error']) : null;
$errorBd = null;

$etiquetasSeccionInforme = obtenerEtiquetasSeccionInformeParaRol($rol);
$soloInformeEventos = !$puedeGenerarInformesGenerales && $puedeInformeEventos;

if ($soloInformeEventos) {
    $etiquetasSeccionInforme = ['eventos' => 'Eventos'];
    $seccion = 'eventos';
} elseif ($etiquetasSeccionInforme === []) {
    header('Location: ' . obtenerUrlInicioPorRol($rol));
    exit;
} else {
    $seccionNormalizada = normalizarSeccionInforme($seccion);

    if (!isset($etiquetasSeccionInforme[$seccionNormalizada])) {
        $seccion = (string) array_key_first($etiquetasSeccionInforme);
    }
}

try {
    $estadisticas = obtenerEstadisticasPorRol($rol);

    if ($generar) {
        $seccion = normalizarSeccionInforme($seccion);

        if ($seccion !== 'ofrendas') {
            $mostrarSinEntregar = false;
        }

        if (!puedeGenerarSeccionInforme($rol, $seccion)) {
            throw new InvalidArgumentException('No tienes permiso para generar este informe.');
        }

        if ($seccion !== 'eventos') {
            $eventoId = 0;
        } elseif ($eventoId > 0) {
            $eventoSeleccionado = obtenerEvento($eventoId);

            if (!$eventoSeleccionado || (int) ($eventoSeleccionado['habilitado'] ?? 0) !== 1) {
                throw new InvalidArgumentException('Selecciona un evento habilitado.');
            }
        }

        if ($seccion === 'presentaciones') {
            $informe = generarInformePresentaciones(
                $fechaDesde,
                $fechaHasta,
                $turno,
                $estadosPresentacionInforme
            );
        } elseif ($seccion === 'bautismos') {
            $informe = generarInformeBautismos(
                $fechaDesde,
                $fechaHasta,
                $turno,
                $estadoBautismoInforme
            );
        } elseif ($seccion === 'eventos') {
            if ($eventoId <= 0) {
                throw new InvalidArgumentException('Selecciona un evento para generar el informe.');
            }

            $informe = generarInformeEvento($eventoId, $fechaDesde, $fechaHasta, $turno);
        } else {
            $informe = generarInformeOfrendasYValores(
                $fechaDesde,
                $fechaHasta,
                $mostrarSinEntregar,
                $turno,
                'todos',
                $eventoId
            );
        }
        $informe['seccion_exportacion'] = $seccion;
        $formato = normalizarFormatoInforme($formato);

        registrarActividad(
            'generar_informe',
            'generar_informe',
            'informe',
            $eventoId > 0 ? $eventoId : null,
            'Generar informe · ' . $seccion . ' · ' . normalizarFormatoInforme($formato)
        );

        if (normalizarFormatoInforme($formato) === 'excel') {
            enviarInformeExcel($informe, $seccion);
        } else {
            enviarInformePdf($informe, $seccion);
        }
        exit;
    }
} catch (InvalidArgumentException $e) {
    $estadisticas = obtenerEstadisticasPorRol($rol);
    $error = $e->getMessage();
} catch (RuntimeException $e) {
    $estadisticas = obtenerEstadisticasPorRol($rol);
    $error = $e->getMessage();
} catch (PDOException $e) {
    $estadisticas = [];
    $errorBd = 'No se pudo generar el informe. Verifica que existan las tablas necesarias.';
}

view('informes/generar', [
    'tituloPagina'           => 'Generar informe',
    'usuario'                => $usuario,
    'seccionActiva'          => 'generar_informe',
    'seccion'                => '',
    'seccionesPermitidas'    => $seccionesPermitidas,
    'etiquetasSecciones'     => $etiquetasSecciones,
    'etiquetasRoles'         => $etiquetasRoles,
    'estadisticas'           => $estadisticas ?? [],
    'puedeGestionarUsuarios' => puedeGestionarUsuarios($rol),
    'fechaDesde'             => $fechaDesde,
    'fechaHasta'             => $fechaHasta,
    'mostrarSinEntregar'     => $mostrarSinEntregar,
    'turno'                  => normalizarTurnoInforme($turno),
    'seccion'                => normalizarSeccionInforme($seccion),
    'eventoId'               => $eventoId,
    'eventos'                => array_values(array_filter(
        obtenerEventos(),
        static function (array $evento): bool {
            return (int) ($evento['habilitado'] ?? 0) === 1;
        }
    )),
    'etiquetasTurno'         => obtenerEtiquetasTurnoInforme(),
    'etiquetasSeccionInforme'=> $etiquetasSeccionInforme,
    'soloInformeEventos'     => $soloInformeEventos,
    'etiquetasEstadosPresentacion' => obtenerEtiquetasEstadosPresentacion(),
    'estadosPresentacionInforme' => normalizarEstadosPresentacionInforme($estadosPresentacionInforme),
    'etiquetasEstadoBautismoInforme' => obtenerEtiquetasEstadoBautismoInforme(),
    'estadoBautismoInforme'  => normalizarEstadoBautismoInforme($estadoBautismoInforme),
    'error'                  => $error,
    'errorBd'                => $errorBd,
], 'app');
