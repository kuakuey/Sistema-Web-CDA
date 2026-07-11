<?php

require_once __DIR__ . '/submissions.php';
require_once __DIR__ . '/valores_adicionales.php';
require_once __DIR__ . '/consejerias.php';
require_once __DIR__ . '/transporte_aniversario.php';
require_once __DIR__ . '/permisos.php';

const ROL_SUPERADMIN = 'superadmin';
const ROL_ADMIN = 'administrador';
const ROL_CONTADOR = 'counter';
const ROL_CONEXION = 'conexion';

function obtenerEtiquetasRoles(): array
{
    return [
        ROL_SUPERADMIN => 'Superadmin',
        ROL_ADMIN      => 'Administrador',
        ROL_CONTADOR   => 'Counter',
        ROL_CONEXION   => "Conexi\u{00F3}n",
    ];
}

function esRolValido(string $rol): bool
{
    return array_key_exists(normalizarRolUsuario($rol), obtenerEtiquetasRoles());
}

function normalizarRolUsuario(string $rol): string
{
    if ($rol === 'contador') {
        return ROL_CONTADOR;
    }

    return $rol;
}

function obtenerTiposInscripcionPermitidos(string $rol): array
{
    $tipos = ['escol', 'academia', 'bautismo', 'conexion'];
    $permitidos = [];

    foreach ($tipos as $tipo) {
        if (tienePermisoSeccion($rol, $tipo)) {
            $permitidos[] = $tipo;
        }
    }

    return $permitidos;
}

function puedeVerPresentaciones(string $rol): bool
{
    return tienePermisoSeccion($rol, 'presentaciones');
}

function puedeVerOfrendas(string $rol): bool
{
    return tienePermisoSeccion($rol, 'ofrendas');
}

function puedeVerValoresAdicionales(string $rol): bool
{
    return tienePermisoSeccion($rol, 'valores_adicionales');
}

function puedeVerConsejerias(string $rol): bool
{
    return tienePermisoSeccion($rol, 'consejeria');
}

function puedeRegistrarConsejerias(string $rol): bool
{
    return puedeVerConsejerias($rol);
}

function puedeAsignarCitaConsejeria(string $rol): bool
{
    return puedeVerConsejerias($rol);
}

function puedeVerTransporteAniversario(string $rol): bool
{
    return tienePermisoSeccion($rol, 'transporte_aniversario');
}

function puedeRegistrarTransporteAniversario(string $rol): bool
{
    return tienePermisoDetalle($rol, 'transporte_aniversario', 'nuevo');
}

function puedeVerReporteTransporteAniversario(string $rol): bool
{
    return tienePermisoDetalle($rol, 'transporte_aniversario', 'reporte');
}

function puedeRegistrarEnSeccion(string $rol, string $seccion): bool
{
    if ($seccion === 'generales') {
        return false;
    }

    $mapaNuevo = [
        'escol'               => 'nuevo',
        'academia'            => 'nuevo',
        'bautismo'            => 'nuevo',
        'conexion'            => 'nuevo',
        'presentaciones'      => 'nuevo',
        'ofrendas'            => 'nuevo',
        'valores_adicionales' => 'nuevo',
        'consejeria'          => 'nuevo',
        'transporte_aniversario' => 'nuevo',
    ];

    if (isset($mapaNuevo[$seccion])) {
        return tienePermisoDetalle($rol, $seccion, $mapaNuevo[$seccion]);
    }

    return false;
}

function puedeEditarValoresAdicionales(string $rol): bool
{
    return $rol === ROL_SUPERADMIN;
}

function puedeRegistrarValoresAdicionales(string $rol): bool
{
    return tienePermisoDetalle($rol, 'valores_adicionales', 'nuevo');
}

function puedeGestionarTiposValorAdicional(string $rol): bool
{
    return tienePermisoDetalle($rol, 'valores_adicionales', 'tipos');
}

function puedeGenerarInforme(string $rol): bool
{
    return tienePermisoSeccion($rol, 'generar_informe');
}

function puedeRegistrarOfrendas(string $rol): bool
{
    return tienePermisoDetalle($rol, 'ofrendas', 'nuevo');
}

function puedeAccederPortalPlugin(string $rol): bool
{
    return puedeVerPresentaciones($rol) || puedeRegistrarOfrendas($rol);
}

function puedeVerBautismo(string $rol): bool
{
    return tienePermisoSeccion($rol, 'bautismo');
}

function puedeGestionarUsuarios(string $rol): bool
{
    return $rol === ROL_SUPERADMIN;
}

function puedeEliminarRegistros(string $rol): bool
{
    return $rol === ROL_SUPERADMIN;
}

function puedeEditarRegistros(string $rol): bool
{
    return $rol === ROL_SUPERADMIN;
}

function puedeVerTipoFormulario(string $rol, string $tipo): bool
{
    return in_array($tipo, obtenerTiposInscripcionPermitidos($rol), true);
}

function puedeGestionarEstructura(string $rol): bool
{
    return tienePermisoSeccion($rol, 'estructura');
}

function puedeGestionarEventos(string $rol): bool
{
    return tienePermisoSeccion($rol, 'eventos');
}

function puedeAgregarEventos(string $rol): bool
{
    return tienePermisoDetalle($rol, 'eventos', 'agregar');
}

function puedeRegistrarEventos(string $rol): bool
{
    return tienePermisoDetalle($rol, 'eventos', 'registrar');
}

function puedeVerTablaEventos(string $rol): bool
{
    return tienePermisoDetalle($rol, 'eventos', 'tabla');
}

function puedeVerCatalogoEventos(string $rol): bool
{
    return tienePermisoDetalle($rol, 'eventos', 'catalogo');
}

function puedeVerInformeEventos(string $rol): bool
{
    return tienePermisoDetalle($rol, 'eventos', 'informe');
}

function puedeGestionarEstadoConexion(string $rol): bool
{
    return in_array($rol, [ROL_SUPERADMIN, ROL_ADMIN, ROL_CONEXION], true);
}

function puedeVerSeccion(string $rol, string $seccion): bool
{
    if ($seccion === 'generales') {
        return tienePermisoDetalle($rol, 'generales', 'ver')
            || tienePermisoSeccion($rol, 'generales')
            || obtenerTiposInscripcionPermitidos($rol) !== []
            || puedeVerPresentaciones($rol);
    }

    if ($seccion === 'ofrendas') {
        return puedeVerOfrendas($rol);
    }

    if ($seccion === 'presentaciones') {
        return puedeVerPresentaciones($rol);
    }

    return in_array($seccion, obtenerTiposInscripcionPermitidos($rol), true);
}

function obtenerEtiquetasSecciones(): array
{
    return [
        'generales'           => 'Registros generales',
        'escol'               => 'Escol',
        'academia'            => 'Academia',
        'bautismo'            => 'Bautismo',
        'conexion'            => "Conexi\u{00F3}n",
        'presentaciones'      => "Presentaci\u{00F3}n ni\u{00F1}os",
        'ofrendas'            => 'Ofrendas',
        'valores_adicionales' => 'Valores adicionales',
        'eventos'             => 'Eventos',
        'consejeria'          => "Consejer\u{00ED}a",
        'transporte_aniversario' => 'Transporte Aniversario',
        'generar_informe'     => 'Generar informe',
        'estructura'          => 'Estructura CDV',
        'usuarios'            => 'Usuarios',
    ];
}

function obtenerIconosSecciones(): array
{
    return [
        'generales'           => 'bi-grid',
        'escol'               => 'bi-book',
        'academia'            => 'bi-mortarboard',
        'bautismo'            => 'bi-droplet',
        'conexion'            => 'bi-person-plus',
        'presentaciones'      => 'bi-people',
        'ofrendas'            => 'bi-cash-stack',
        'valores_adicionales' => 'bi-wallet2',
        'eventos'             => 'bi-calendar-event',
        'consejeria'          => 'bi-chat-heart',
        'transporte_aniversario' => 'bi-bus-front',
        'generar_informe'     => 'bi-file-earmark-bar-graph',
        'estructura'          => 'bi-diagram-3',
        'usuarios'            => 'bi-person-gear',
    ];
}

/**
 * Orden fijo de las secciones en el menú lateral.
 *
 * @return array<int, string>
 */
function obtenerOrdenMenuSidebar(): array
{
    return [
        'generales',
        'escol',
        'academia',
        'bautismo',
        'conexion',
        'presentaciones',
        'ofrendas',
        'eventos',
        'valores_adicionales',
        'consejeria',
        'transporte_aniversario',
        'generar_informe',
        'estructura',
        'usuarios',
    ];
}

function puedeVerItemMenuSidebar(string $rol, string $clave): bool
{
    if ($clave === 'generales') {
        return tienePermisoDetalle($rol, 'generales', 'ver')
            || tienePermisoSeccion($rol, 'generales')
            || obtenerTiposInscripcionPermitidos($rol) !== [];
    }

    if ($clave === 'usuarios') {
        return puedeGestionarUsuarios($rol);
    }

    return tienePermisoSeccion($rol, $clave);
}

/**
 * @return array<int, string>
 */
function obtenerItemsMenuSidebar(string $rol): array
{
    $items = [];

    foreach (obtenerOrdenMenuSidebar() as $clave) {
        if (puedeVerItemMenuSidebar($rol, $clave)) {
            $items[] = $clave;
        }
    }

    return $items;
}

function obtenerUrlMenuSidebar(string $clave): string
{
    require_once __DIR__ . '/rutas.php';

    $mapa = array_merge(obtenerMapaUrlsSecciones(), [
        'ofrendas'            => 'ofrendas.php',
        'valores_adicionales' => 'valores-adicionales.php',
        'eventos'             => 'eventos.php',
        'consejeria'          => 'consejeria.php',
        'transporte_aniversario' => 'transporte-aniversario.php',
        'generar_informe'     => 'generar-informe.php',
        'estructura'          => 'estructura.php',
        'usuarios'            => 'usuarios.php',
    ]);

    return $mapa[$clave] ?? 'registros-generales.php';
}

function obtenerContadorMenuSidebar(string $clave, array $estadisticas): ?int
{
    if ($clave === 'generales') {
        return (int) ($estadisticas['total_inscripciones'] ?? 0);
    }

    if ($clave === 'consejeria') {
        return isset($estadisticas['consejerias'])
            ? (int) $estadisticas['consejerias']
            : null;
    }

    if ($clave === 'transporte_aniversario') {
        return isset($estadisticas['transporte_aniversario'])
            ? (int) $estadisticas['transporte_aniversario']
            : null;
    }

    if (isset($estadisticas[$clave])) {
        return (int) $estadisticas[$clave];
    }

    return null;
}

/**
 * Secciones de registros/inscripciones visibles para el rol (orden del menú).
 *
 * @return array<int, string>
 */
function obtenerSeccionesPermitidas(string $rol): array
{
    $seccionesRegistro = ['generales', 'escol', 'academia', 'bautismo', 'conexion', 'presentaciones'];
    $permitidas = [];

    foreach ($seccionesRegistro as $clave) {
        if (puedeVerItemMenuSidebar($rol, $clave)) {
            $permitidas[] = $clave;
        }
    }

    return $permitidas;
}

function obtenerEstadisticasPorRol(string $rol): array
{
    $estadisticas = [
        'total_inscripciones' => 0,
        'escol'               => 0,
        'academia'            => 0,
        'bautismo'            => 0,
        'conexion'            => 0,
        'presentaciones'      => 0,
        'ofrendas'            => 0,
        'valores_adicionales' => 0,
        'consejerias'         => 0,
        'transporte_aniversario' => 0,
        'total'               => 0,
    ];

    foreach (obtenerTiposInscripcionPermitidos($rol) as $tipo) {
        $cantidad = contarInscripciones($tipo);
        $estadisticas[$tipo] = $cantidad;
        $estadisticas['total_inscripciones'] += $cantidad;
    }

    if (puedeVerPresentaciones($rol)) {
        $estadisticas['presentaciones'] = contarPresentacionesNinos();
    }

    if (puedeVerOfrendas($rol)) {
        $estadisticas['ofrendas'] = contarOfrendas();
    }

    if (puedeVerValoresAdicionales($rol)) {
        $estadisticas['valores_adicionales'] = contarValoresAdicionales();
    }

    if (puedeVerConsejerias($rol)) {
        $estadisticas['consejerias'] = contarConsejerias();
    }

    if (puedeVerTransporteAniversario($rol)) {
        $estadisticas['transporte_aniversario'] = contarTransporteAniversario();
    }

    $estadisticas['total'] = $estadisticas['total_inscripciones']
        + $estadisticas['presentaciones']
        + $estadisticas['ofrendas'];

    return $estadisticas;
}
