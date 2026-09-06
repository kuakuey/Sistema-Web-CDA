<?php

require_once __DIR__ . '/estructura.php';
require_once __DIR__ . '/texto.php';
require_once __DIR__ . '/roles.php';

/**
 * @return array<string, array<string, mixed>>
 */
function catalogoPasosImportEstructura(): array
{
    return [
        'miembros' => [
            'numero'           => 1,
            'clave'            => 'miembros',
            'etiqueta'         => 'Miembros',
            'pestana_permiso'  => 'lideres',
            'descripcion'      => 'Carga todos los miembros con su género. El parentesco se conecta después, cuando ya estén registrados.',
            'ayuda'            => 'Una fila por miembro. Nombres, apellidos y género son obligatorios. La cédula ayuda a evitar duplicados.',
            'columnas'         => [
                'nombre'   => 'Nombres',
                'apellido' => 'Apellidos',
                'genero'   => 'Genero',
                'cedula'   => 'Cedula',
                'celular'  => 'Celular',
                'email'    => 'Email',
                'notas'    => 'Notas',
            ],
            'requeridas'       => ['nombre', 'apellido', 'genero'],
            'archivo_xls'      => 'plantilla-estructura-miembros.xls',
            'archivo_csv'      => 'plantilla-estructura-miembros.csv',
        ],
        'territorios' => [
            'numero'           => 2,
            'clave'            => 'territorios',
            'etiqueta'         => 'Territorios',
            'pestana_permiso'  => 'territorios',
            'descripcion'      => 'Carga los territorios. En el siguiente paso les asignas coordinadores y encargados.',
            'ayuda'            => 'Una fila por territorio. Si el nombre ya existe, se omite para no duplicarlo.',
            'columnas'         => [
                'nombre' => 'Nombre',
            ],
            'requeridas'       => ['nombre'],
            'archivo_xls'      => 'plantilla-estructura-territorios.xls',
            'archivo_csv'      => 'plantilla-estructura-territorios.csv',
        ],
        'asignaciones' => [
            'numero'           => 3,
            'clave'            => 'asignaciones',
            'etiqueta'         => 'Asignaciones',
            'pestana_permiso'  => 'territorios',
            'descripcion'      => 'Asigna una pareja (esposo y esposa) como coordinadores o encargados de cada territorio.',
            'ayuda'            => 'Una fila por territorio y rol. Si la misma pareja coordinadora aparece en 6 filas, queda a cargo de 6 territorios.',
            'columnas'         => [
                'territorio'    => 'Territorio',
                'rol'           => 'Rol',
                'esposo'        => 'Esposo',
                'esposa'        => 'Esposa',
                'cedula_esposo' => 'Cedula esposo',
                'cedula_esposa' => 'Cedula esposa',
            ],
            'requeridas'       => ['territorio', 'rol'],
            'archivo_xls'      => 'plantilla-estructura-asignaciones.xls',
            'archivo_csv'      => 'plantilla-estructura-asignaciones.csv',
        ],
        'casas' => [
            'numero'           => 4,
            'clave'            => 'casas',
            'etiqueta'         => 'Casas de vida',
            'pestana_permiso'  => 'casas',
            'descripcion'      => 'Cada casa de vida tiene nombre, líder, dirección y, si aplica, colaborador y anfitrión.',
            'ayuda'            => 'Solo Ids. Obligatorios: Id territorio e Id lider. Id colaborador e Id anfitrion son opcionales.',
            'columnas'         => [
                'id_territorio'    => 'Id territorio',
                'nombre_casa'      => 'Nombre casa',
                'direccion'        => 'Direccion',
                'id_lider'         => 'Id lider',
                'id_colaborador'   => 'Id colaborador',
                'id_anfitrion'     => 'Id anfitrion',
            ],
            'requeridas'       => ['id_territorio', 'nombre_casa', 'direccion', 'id_lider'],
            'archivo_xls'      => 'plantilla-estructura-casas.xls',
            'archivo_csv'      => 'plantilla-estructura-casas.csv',
        ],
    ];
}

/**
 * @return array<string, mixed>
 */
function definirPasoImportEstructura(string $paso): array
{
    if ($paso === 'personas') {
        $paso = 'miembros';
    }

    $catalogo = catalogoPasosImportEstructura();
    if (!isset($catalogo[$paso])) {
        throw new InvalidArgumentException('Paso de importación no válido.');
    }

    return $catalogo[$paso];
}

/**
 * @return array<int, string>
 */
function obtenerPasosImportEstructuraPermitidos(string $rol): array
{
    $pasos = [];

    foreach (catalogoPasosImportEstructura() as $clave => $paso) {
        if (puedeGestionarEstructuraPestana($rol, (string) $paso['pestana_permiso'])) {
            $pasos[] = $clave;
        }
    }

    return $pasos;
}

function enviarPlantillaImportEstructura(string $paso, string $formato = 'xls'): void
{
    $info = definirPasoImportEstructura($paso);

    if ($formato === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $info['archivo_csv'] . '"');
        header('Cache-Control: max-age=0');
        echo "\xEF\xBB\xBF";

        $salida = fopen('php://output', 'w');
        if ($salida === false) {
            throw new RuntimeException('No se pudo generar la plantilla CSV.');
        }

        fputcsv($salida, array_values($info['columnas']), ';');
        fclose($salida);

        return;
    }

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $info['archivo_xls'] . '"');
    header('Cache-Control: max-age=0');

    echo "\xEF\xBB\xBF";
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
    echo construirWorkbookXmlPlantillaImportEstructura($info);
}

function enviarExportacionMiembrosEstructura(string $formato = 'xls'): void
{
    $filas = filasExportacionMiembrosEstructura();
    $encabezados = ['Id', 'Nombres', 'Apellidos', 'Genero', 'Cedula', 'Celular'];

    if ($formato === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="miembros-estructura.csv"');
        header('Cache-Control: max-age=0');
        echo "\xEF\xBB\xBF";

        $salida = fopen('php://output', 'w');
        if ($salida === false) {
            throw new RuntimeException('No se pudo generar el CSV de miembros.');
        }

        fputcsv($salida, $encabezados, ';');
        foreach ($filas as $fila) {
            fputcsv($salida, $fila, ';');
        }
        fclose($salida);

        return;
    }

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="miembros-estructura.xls"');
    header('Cache-Control: max-age=0');

    echo "\xEF\xBB\xBF";
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
    echo construirWorkbookXmlExportacionListaEstructura('Miembros', $encabezados, $filas);
}

function enviarExportacionTerritoriosEstructura(string $formato = 'xls'): void
{
    $filas = filasExportacionTerritoriosEstructura();
    $encabezados = ['Id', 'Nombre'];

    if ($formato === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="territorios-estructura.csv"');
        header('Cache-Control: max-age=0');
        echo "\xEF\xBB\xBF";

        $salida = fopen('php://output', 'w');
        if ($salida === false) {
            throw new RuntimeException('No se pudo generar el CSV de territorios.');
        }

        fputcsv($salida, $encabezados, ';');
        foreach ($filas as $fila) {
            fputcsv($salida, $fila, ';');
        }
        fclose($salida);

        return;
    }

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="territorios-estructura.xls"');
    header('Cache-Control: max-age=0');

    echo "\xEF\xBB\xBF";
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
    echo construirWorkbookXmlExportacionListaEstructura('Territorios', $encabezados, $filas);
}

/**
 * @return array<int, array{0: string, 1: string}>
 */
function filasExportacionTerritoriosEstructura(): array
{
    $filas = [];

    foreach (obtenerTerritorios() as $territorio) {
        $filas[] = [
            (string) (int) $territorio['id'],
            (string) ($territorio['nombre'] ?? ''),
        ];
    }

    return $filas;
}

/**
 * @return array<int, array{0: string, 1: string, 2: string, 3: string, 4: string, 5: string}>
 */
function filasExportacionMiembrosEstructura(): array
{
    $filas = [];

    foreach (obtenerLideres() as $miembro) {
        $filas[] = [
            (string) (int) $miembro['id'],
            (string) ($miembro['nombre'] ?? ''),
            (string) ($miembro['apellido'] ?? ''),
            (string) ($miembro['genero'] ?? ''),
            (string) ($miembro['cedula'] ?? ''),
            (string) ($miembro['celular'] ?? ''),
        ];
    }

    return $filas;
}

/**
 * @param array<int, string> $encabezados
 * @param array<int, array<int, string>> $filas
 */
function construirWorkbookXmlExportacionListaEstructura(string $hoja, array $encabezados, array $filas): string
{
    $xml = '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
        . ' xmlns:o="urn:schemas-microsoft-com:office:office"'
        . ' xmlns:x="urn:schemas-microsoft-com:office:excel"'
        . ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"'
        . ' xmlns:html="http://www.w3.org/TR/REC-html40">';
    $xml .= '<Styles>';
    $xml .= '<Style ss:ID="Header"><Font ss:Bold="1"/><Interior ss:Color="#D9E2F3" ss:Pattern="Solid"/></Style>';
    $xml .= '<Style ss:ID="Texto"><NumberFormat ss:Format="@"/></Style>';
    $xml .= '</Styles>';
    $xml .= '<Worksheet ss:Name="' . escaparXmlExcelImportEstructura($hoja) . '"><Table>';

    foreach (array_keys($encabezados) as $indice) {
        $ancho = $indice === 0 ? 60 : 140;
        $xml .= '<Column ss:Index="' . ($indice + 1) . '" ss:AutoFitWidth="0" ss:Width="' . $ancho . '" ss:StyleID="Texto"/>';
    }

    $xml .= filaXmlExcelImportEstructura($encabezados, 'Header');
    foreach ($filas as $fila) {
        $xml .= filaXmlExcelImportEstructura($fila);
    }

    $xml .= '</Table></Worksheet></Workbook>';

    return $xml;
}

/**
 * @param array<string, mixed> $info
 */
function construirWorkbookXmlPlantillaImportEstructura(array $info): string
{
    $xml = '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
        . ' xmlns:o="urn:schemas-microsoft-com:office:office"'
        . ' xmlns:x="urn:schemas-microsoft-com:office:excel"'
        . ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"'
        . ' xmlns:html="http://www.w3.org/TR/REC-html40">';
    $xml .= '<Styles>';
    $xml .= '<Style ss:ID="Header"><Font ss:Bold="1"/><Interior ss:Color="#D9E2F3" ss:Pattern="Solid"/></Style>';
    $xml .= '<Style ss:ID="Titulo"><Font ss:Bold="1" ss:Size="12"/></Style>';
    $xml .= '<Style ss:ID="Subtitulo"><Font ss:Bold="1" ss:Size="10"/></Style>';
    $xml .= '<Style ss:ID="Texto"><NumberFormat ss:Format="@"/></Style>';
    $xml .= '</Styles>';
    $xml .= construirHojaDatosXmlPlantillaImportEstructura($info);
    $xml .= construirHojaGuiaXmlPlantillaImportEstructura($info);
    if (($info['clave'] ?? '') === 'casas') {
        $xml .= construirHojaTerritoriosXmlPlantillaImportEstructura();
        $xml .= construirHojaMiembrosXmlPlantillaImportEstructura();
    }
    $xml .= '</Workbook>';

    return $xml;
}

/**
 * @param array<string, mixed> $info
 */
function construirHojaDatosXmlPlantillaImportEstructura(array $info): string
{
    $columnas = $info['columnas'];
    $xml = '<Worksheet ss:Name="Datos"><Table>';

    $indice = 1;
    foreach (array_keys($columnas) as $_clave) {
        $xml .= '<Column ss:Index="' . $indice . '" ss:AutoFitWidth="0" ss:Width="140" ss:StyleID="Texto"/>';
        $indice++;
    }

    $xml .= filaXmlExcelImportEstructura(array_values($columnas), 'Header');
    $xml .= '</Table></Worksheet>';

    return $xml;
}

/**
 * @param array<string, mixed> $info
 */
function construirHojaGuiaXmlPlantillaImportEstructura(array $info): string
{
    $xml = '<Worksheet ss:Name="Guía y ejemplos"><Table>';
    $xml .= '<Column ss:Index="1" ss:AutoFitWidth="0" ss:Width="180"/>';
    $xml .= '<Column ss:Index="2" ss:AutoFitWidth="0" ss:Width="140"/>';
    $xml .= '<Column ss:Index="3" ss:AutoFitWidth="0" ss:Width="280"/>';

    $xml .= filaXmlExcelImportEstructura(['PLANTILLA · ESTRUCTURA CDV · ' . strtoupper((string) $info['etiqueta'])], 'Titulo');
    $xml .= filaXmlExcelImportEstructura(['']);
    $xml .= filaXmlExcelImportEstructura([(string) $info['descripcion']], 'Subtitulo');
    $xml .= filaXmlExcelImportEstructura([(string) $info['ayuda']]);
    $xml .= filaXmlExcelImportEstructura(['']);
    $xml .= filaXmlExcelImportEstructura(['INSTRUCCIONES'], 'Subtitulo');

    $instrucciones = [
        '1. Complete una fila por registro en la pestaña «Datos».',
        '2. No cambie los nombres de las columnas de la fila 1.',
        '3. El sistema lee únicamente la pestaña «Datos».',
        '4. Si Excel convierte el archivo a .xlsx, guárdelo como CSV UTF-8 o use esta plantilla .xls sin convertirla.',
        '5. Importe los pasos en orden: personas, territorios y casas de vida.',
    ];
    if (($info['clave'] ?? '') === 'casas') {
        $instrucciones[] = '6. Copie el Id de «Territorios» en Id territorio y el Id de «Miembros» en Id lider. Id colaborador e Id anfitrion son opcionales.';
    }
    foreach ($instrucciones as $linea) {
        $xml .= filaXmlExcelImportEstructura([$linea]);
    }

    $xml .= filaXmlExcelImportEstructura(['']);
    $xml .= filaXmlExcelImportEstructura(['COLUMNAS'], 'Subtitulo');
    $xml .= filaXmlExcelImportEstructura(['Columna', 'Obligatorio', 'Notas'], 'Header');

    foreach (descripcionesColumnasPasoImportEstructura((string) $info['clave']) as $fila) {
        $xml .= filaXmlExcelImportEstructura($fila);
    }

    $xml .= filaXmlExcelImportEstructura(['']);
    $xml .= filaXmlExcelImportEstructura(['EJEMPLO'], 'Subtitulo');
    $xml .= filaXmlExcelImportEstructura(array_values($info['columnas']), 'Header');
    $xml .= filaXmlExcelImportEstructura(ejemploFilaPasoImportEstructura((string) $info['clave']));
    $xml .= '</Table></Worksheet>';

    return $xml;
}

function construirHojaTerritoriosXmlPlantillaImportEstructura(): string
{
    $xml = '<Worksheet ss:Name="Territorios"><Table>';
    $xml .= '<Column ss:Index="1" ss:AutoFitWidth="0" ss:Width="60" ss:StyleID="Texto"/>';
    $xml .= '<Column ss:Index="2" ss:AutoFitWidth="0" ss:Width="200" ss:StyleID="Texto"/>';
    $xml .= filaXmlExcelImportEstructura(['Id', 'Nombre'], 'Header');

    $territorios = obtenerTerritorios();
    foreach ($territorios as $territorio) {
        $xml .= filaXmlExcelImportEstructura([
            (string) (int) $territorio['id'],
            (string) ($territorio['nombre'] ?? ''),
        ]);
    }

    if ($territorios === []) {
        $xml .= filaXmlExcelImportEstructura(['', 'No hay territorios. Créalos o impórtalos primero.']);
    }

    $xml .= '</Table></Worksheet>';

    return $xml;
}

function construirHojaMiembrosXmlPlantillaImportEstructura(): string
{
    $xml = '<Worksheet ss:Name="Miembros"><Table>';
    $xml .= '<Column ss:Index="1" ss:AutoFitWidth="0" ss:Width="60" ss:StyleID="Texto"/>';
    $xml .= '<Column ss:Index="2" ss:AutoFitWidth="0" ss:Width="180" ss:StyleID="Texto"/>';
    $xml .= '<Column ss:Index="3" ss:AutoFitWidth="0" ss:Width="140" ss:StyleID="Texto"/>';
    $xml .= '<Column ss:Index="4" ss:AutoFitWidth="0" ss:Width="100" ss:StyleID="Texto"/>';
    $xml .= '<Column ss:Index="5" ss:AutoFitWidth="0" ss:Width="120" ss:StyleID="Texto"/>';
    $xml .= filaXmlExcelImportEstructura(['Id', 'Nombres', 'Apellidos', 'Cedula', 'Celular'], 'Header');

    $miembros = obtenerLideres();
    foreach ($miembros as $miembro) {
        $xml .= filaXmlExcelImportEstructura([
            (string) (int) $miembro['id'],
            (string) ($miembro['nombre'] ?? ''),
            (string) ($miembro['apellido'] ?? ''),
            (string) ($miembro['cedula'] ?? ''),
            (string) ($miembro['celular'] ?? ''),
        ]);
    }

    if ($miembros === []) {
        $xml .= filaXmlExcelImportEstructura(['', 'No hay miembros. Créalos o impórtalos primero.']);
    }

    $xml .= '</Table></Worksheet>';

    return $xml;
}

/**
 * @return array<int, array{0: string, 1: string, 2: string}>
 */
function descripcionesColumnasPasoImportEstructura(string $paso): array
{
    return match ($paso) {
        'miembros' => [
            ['Nombres', 'Sí', 'Nombre de pila. Se normaliza a formato título.'],
            ['Apellidos', 'Sí', 'Apellidos del miembro.'],
            ['Genero', 'Sí', 'masculino o femenino.'],
            ['Cedula', 'No', 'Documento. Si se repite, la fila se omite.'],
            ['Celular', 'No', 'Número de contacto.'],
            ['Email', 'No', 'Correo electrónico.'],
            ['Notas', 'No', 'Texto libre.'],
        ],
        'territorios' => [
            ['Nombre', 'Sí', 'Nombre del territorio. Si ya existe, se omite.'],
        ],
        'asignaciones' => [
            ['Territorio', 'Sí', 'Debe coincidir con un territorio ya creado.'],
            ['Rol', 'Sí', 'coordinador o encargado.'],
            ['Esposo', 'Sí*', 'Nombre completo. Obligatorio si no hay cédula del esposo.'],
            ['Esposa', 'Sí*', 'Nombre completo. Obligatorio si no hay cédula de la esposa.'],
            ['Cedula esposo', 'Sí*', 'Forma más segura de relacionar al esposo.'],
            ['Cedula esposa', 'Sí*', 'Forma más segura de relacionar a la esposa.'],
        ],
        'casas' => [
            ['Id territorio', 'Sí', 'Id del territorio. Cópialo de la pestaña Territorios o del Excel exportado.'],
            ['Nombre casa', 'Sí', 'Nombre de la casa de vida.'],
            ['Direccion', 'Sí', 'Dirección o punto de referencia de la casa.'],
            ['Id lider', 'Sí', 'Id del miembro líder. Cópialo de la pestaña Miembros o del Excel exportado.'],
            ['Id colaborador', 'No', 'Id del miembro colaborador. Opcional.'],
            ['Id anfitrion', 'No', 'Id del miembro anfitrión. Opcional.'],
        ],
        default => [],
    };
}

/**
 * @return array<int, string>
 */
function ejemploFilaPasoImportEstructura(string $paso): array
{
    return match ($paso) {
        'miembros' => ['Juan Carlos', 'Pérez Gómez', 'masculino', '1234567890', '3001234567', 'juan@correo.com', 'Coordinador'],
        'territorios' => ['Norte'],
        'asignaciones' => ['Norte', 'coordinador', 'Juan Carlos Pérez Gómez', 'Ana María Pérez Gómez', '1234567890', '0987654321'],
        'casas' => ['1', 'Casa Esperanza', 'Cra 10 #20-30', '12', '15', '18'],
        default => [],
    };
}

/**
 * @param array<int, string> $valores
 */
function filaXmlExcelImportEstructura(array $valores, ?string $estiloId = null): string
{
    $attrs = $estiloId !== null ? ' ss:StyleID="' . escaparXmlExcelImportEstructura($estiloId) . '"' : '';
    $xml = '<Row' . $attrs . '>';
    foreach ($valores as $valor) {
        $xml .= '<Cell><Data ss:Type="String">' . escaparXmlExcelImportEstructura((string) $valor) . '</Data></Cell>';
    }
    $xml .= '</Row>';

    return $xml;
}

function escaparXmlExcelImportEstructura(string $texto): string
{
    return htmlspecialchars($texto, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function claveTextoImportEstructura(string $texto): string
{
    return normalizarEncabezadoImportEstructura($texto);
}

function normalizarCedulaImportEstructura(?string $cedula): string
{
    $cedula = trim((string) $cedula);
    if ($cedula === '') {
        return '';
    }

    return (string) preg_replace('/[^0-9A-Za-z]/', '', $cedula);
}

function normalizarEncabezadoImportEstructura(string $texto): string
{
    $texto = trim($texto);
    $texto = function_exists('mb_strtolower') ? mb_strtolower($texto, 'UTF-8') : strtolower($texto);
    $texto = strtr($texto, [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ñ' => 'n',
    ]);
    $texto = (string) preg_replace('/[^a-z0-9]+/', ' ', $texto);

    return trim((string) preg_replace('/\s+/', ' ', $texto));
}

/**
 * @return array<string, array<int, string>>
 */
function aliasEncabezadosPasoImportEstructura(string $paso): array
{
    if ($paso === 'personas') {
        $paso = 'miembros';
    }

    return match ($paso) {
        'miembros' => [
            'nombre'   => ['nombre', 'nombres', 'nombre persona', 'nombres persona', 'nombre miembro'],
            'apellido' => ['apellido', 'apellidos', 'apellido persona', 'apellidos persona'],
            'genero'   => ['genero', 'sexo', 'genero miembro'],
            'cedula'   => ['cedula', 'documento', 'identificacion', 'cc', 'dni'],
            'celular'  => ['celular', 'telefono', 'tel', 'movil', 'whatsapp'],
            'email'    => ['email', 'correo', 'correo electronico', 'mail'],
            'notas'    => ['notas', 'observacion', 'observaciones', 'nota'],
        ],
        'territorios' => [
            'nombre' => ['nombre', 'territorio', 'nombre territorio', 'zona'],
        ],
        'asignaciones' => [
            'territorio'    => ['territorio', 'nombre territorio', 'zona'],
            'rol'           => ['rol', 'cargo', 'tipo'],
            'esposo'        => ['esposo', 'coordinador', 'encargado', 'nombre esposo'],
            'esposa'        => ['esposa', 'coordinadora', 'encargada', 'nombre esposa'],
            'cedula_esposo' => ['cedula esposo', 'documento esposo', 'cc esposo'],
            'cedula_esposa' => ['cedula esposa', 'documento esposa', 'cc esposa'],
        ],
        'casas' => [
            'id_territorio'      => ['id territorio', 'territorio id', 'id del territorio'],
            'territorio'         => ['territorio', 'nombre territorio', 'zona'],
            'nombre_casa'        => ['nombre casa', 'casa', 'casa de vida', 'nombre', 'nombre cdv', 'cdv'],
            'direccion'          => ['direccion', 'dir', 'ubicacion', 'direccion casa'],
            'id_lider'           => ['id lider', 'lider id', 'id del lider'],
            'lider'              => ['lider', 'nombre lider', 'lider casa', 'nombre completo lider'],
            'cedula_lider'       => ['cedula lider', 'documento lider', 'cc lider'],
            'id_colaborador'     => ['id colaborador', 'colaborador id', 'id del colaborador', 'id colider', 'colider id'],
            'colaborador'        => ['colaborador', 'nombre colaborador', 'colaborador casa', 'colider'],
            'cedula_colaborador' => ['cedula colaborador', 'documento colaborador', 'cc colaborador'],
            'id_anfitrion'       => ['id anfitrion', 'anfitrion id', 'id del anfitrion'],
            'anfitrion'          => ['anfitrion', 'nombre anfitrion', 'anfitrion casa', 'host'],
            'cedula_anfitrion'   => ['cedula anfitrion', 'documento anfitrion', 'cc anfitrion'],
        ],
        default => [],
    };
}

/**
 * @param array<int, string> $encabezados
 * @return array<string, int>
 */
function mapearEncabezadosImportEstructura(string $paso, array $encabezados): array
{
    $alias = aliasEncabezadosPasoImportEstructura($paso);
    $mapa = [];

    foreach ($encabezados as $indice => $encabezado) {
        $normalizado = normalizarEncabezadoImportEstructura((string) $encabezado);
        if ($normalizado === '') {
            continue;
        }

        foreach ($alias as $clave => $nombres) {
            if (isset($mapa[$clave])) {
                continue;
            }
            if (in_array($normalizado, $nombres, true)) {
                $mapa[$clave] = (int) $indice;
            }
        }
    }

    return $mapa;
}

/**
 * @param array<string, mixed> $archivo
 * @return array{importados: int, duplicados: int, errores: array<int, array{fila: int, mensaje: string}>, paso: string}
 */
function procesarImportacionEstructura(array $archivo, string $paso, array $usuario): array
{
    if ($paso === 'personas') {
        $paso = 'miembros';
    }

    $info = definirPasoImportEstructura($paso);

    if (!puedeGestionarEstructuraPestana((string) ($usuario['rol'] ?? ''), (string) $info['pestana_permiso'])) {
        throw new InvalidArgumentException('No tienes permiso para importar este paso.');
    }

    $errorArchivo = (int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($errorArchivo !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('Selecciona un archivo Excel o CSV válido.');
    }

    $ruta = (string) ($archivo['tmp_name'] ?? '');
    $nombre = (string) ($archivo['name'] ?? 'archivo');
    $lectura = leerFilasArchivoImportEstructura($ruta, $nombre, $paso);
    $filas = $lectura['filas'];

    if ($filas === []) {
        throw new InvalidArgumentException('El archivo no tiene filas de datos para importar. Revisa la pestaña «Datos».');
    }

    return match ($paso) {
        'miembros'     => importarPersonasEstructura($filas),
        'territorios'  => importarTerritoriosEstructura($filas),
        'asignaciones' => importarAsignacionesEstructura($filas),
        'casas'        => importarCasasEstructura($filas),
        default        => throw new InvalidArgumentException('Paso de importación no válido.'),
    };
}

/**
 * @param array<int, array<string, string>> $filas
 * @return array{importados: int, duplicados: int, errores: array<int, array{fila: int, mensaje: string}>, paso: string}
 */
function importarPersonasEstructura(array $filas): array
{
    $indice = indiceLideresImportEstructura();
    $importados = 0;
    $duplicados = 0;
    $errores = [];

    $pdo = getConnection();
    $pdo->beginTransaction();

    try {
        foreach ($filas as $fila) {
            $numeroFila = (int) ($fila['_fila'] ?? 0);

            try {
                $datos = [
                    'nombre'   => trim((string) ($fila['nombre'] ?? '')),
                    'apellido' => trim((string) ($fila['apellido'] ?? '')),
                    'genero'   => trim((string) ($fila['genero'] ?? '')),
                    'cedula'   => trim((string) ($fila['cedula'] ?? '')),
                    'celular'  => trim((string) ($fila['celular'] ?? '')),
                    'email'    => trim((string) ($fila['email'] ?? '')),
                    'notas'    => trim((string) ($fila['notas'] ?? '')),
                ];

                if ($datos['nombre'] === '' || $datos['apellido'] === '') {
                    throw new InvalidArgumentException('Nombres y apellidos son obligatorios.');
                }

                $cedulaClave = normalizarCedulaImportEstructura($datos['cedula']);
                $nombreClave = claveTextoImportEstructura($datos['nombre'] . ' ' . $datos['apellido']);

                if ($cedulaClave !== '' && isset($indice['cedula'][$cedulaClave])) {
                    $duplicados++;
                    continue;
                }

                if ($cedulaClave === '' && isset($indice['nombre'][$nombreClave])) {
                    $duplicados++;
                    continue;
                }

                $id = crearLider($datos);
                $creado = [
                    'id'       => $id,
                    'nombre'   => $datos['nombre'],
                    'apellido' => $datos['apellido'],
                    'cedula'   => $datos['cedula'],
                ];

                if ($cedulaClave !== '') {
                    $indice['cedula'][$cedulaClave] = $creado;
                }
                $indice['nombre'][$nombreClave] = $creado;
                $importados++;
            } catch (InvalidArgumentException $e) {
                $errores[] = ['fila' => $numeroFila, 'mensaje' => $e->getMessage()];
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    return [
        'paso'        => 'miembros',
        'importados'  => $importados,
        'duplicados'  => $duplicados,
        'errores'     => $errores,
    ];
}

/**
 * @param array<int, array<string, string>> $filas
 * @return array{importados: int, duplicados: int, errores: array<int, array{fila: int, mensaje: string}>, paso: string}
 */
function importarAsignacionesEstructura(array $filas): array
{
    $territorios = [];
    foreach (obtenerTerritorios() as $territorio) {
        $territorios[claveTextoImportEstructura((string) $territorio['nombre'])] = $territorio;
    }

    $lideres = indiceLideresImportEstructura();
    $existentes = [];
    foreach (obtenerAsignacionesTerritorio() as $asignacion) {
        $existentes[(int) $asignacion['territorio_id'] . '|' . $asignacion['rol'] . '|' . $asignacion['pareja']] = (int) $asignacion['miembro_id'];
    }

    $importados = 0;
    $duplicados = 0;
    $errores = [];

    $pdo = getConnection();
    $pdo->beginTransaction();

    try {
        foreach ($filas as $fila) {
            $numeroFila = (int) ($fila['_fila'] ?? 0);

            try {
                $territorioNombre = trim((string) ($fila['territorio'] ?? ''));
                $rol = trim((string) ($fila['rol'] ?? ''));
                $esposoNombre = trim((string) ($fila['esposo'] ?? ''));
                $esposaNombre = trim((string) ($fila['esposa'] ?? ''));
                $cedulaEsposo = normalizarCedulaImportEstructura($fila['cedula_esposo'] ?? '');
                $cedulaEsposa = normalizarCedulaImportEstructura($fila['cedula_esposa'] ?? '');

                if ($territorioNombre === '' || $rol === '') {
                    throw new InvalidArgumentException('Territorio y rol son obligatorios.');
                }

                $territorio = $territorios[claveTextoImportEstructura($territorioNombre)] ?? null;
                if ($territorio === null) {
                    throw new InvalidArgumentException('Territorio no encontrado: ' . $territorioNombre);
                }

                $esposo = resolverMiembroImportEstructura($lideres, $cedulaEsposo, $esposoNombre);
                $esposa = resolverMiembroImportEstructura($lideres, $cedulaEsposa, $esposaNombre);

                if ($esposo === null) {
                    throw new InvalidArgumentException('Esposo no encontrado.');
                }
                if ($esposa === null) {
                    throw new InvalidArgumentException('Esposa no encontrada.');
                }

                $claveEsposo = (int) $territorio['id'] . '|' . normalizarRolTerritorio($rol) . '|esposo';
                $claveEsposa = (int) $territorio['id'] . '|' . normalizarRolTerritorio($rol) . '|esposa';
                $yaIgual = ($existentes[$claveEsposo] ?? 0) === (int) $esposo['id']
                    && ($existentes[$claveEsposa] ?? 0) === (int) $esposa['id'];

                if ($yaIgual) {
                    $duplicados++;
                    continue;
                }

                asegurarParentescoEsposos((int) $esposo['id'], (int) $esposa['id']);
                asignarParejaATerritorios($rol, (int) $esposo['id'], (int) $esposa['id'], [(int) $territorio['id']]);
                $existentes[$claveEsposo] = (int) $esposo['id'];
                $existentes[$claveEsposa] = (int) $esposa['id'];
                $importados++;
            } catch (InvalidArgumentException $e) {
                $errores[] = ['fila' => $numeroFila, 'mensaje' => $e->getMessage()];
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    return [
        'paso'       => 'asignaciones',
        'importados' => $importados,
        'duplicados' => $duplicados,
        'errores'    => $errores,
    ];
}

/**
 * @param array{cedula: array<string, array<string, mixed>>, nombre: array<string, array<string, mixed>>} $indice
 * @return array<string, mixed>|null
 */
/**
 * @param array{cedula: array<string, array<string, mixed>>, nombre: array<string, array<string, mixed>>} $indice
 * @param array<string, string> $fila
 * @return array<string, mixed>
 */
function resolverMiembroPorIdImportEstructura(array $indice, int $id, string $etiqueta): array
{
    if ($id <= 0) {
        throw new InvalidArgumentException('El Id del ' . $etiqueta . ' es obligatorio.');
    }

    if (!isset($indice['id'][$id])) {
        throw new InvalidArgumentException($etiqueta . ' no encontrado: Id ' . $id);
    }

    return $indice['id'][$id];
}

function resolverMiembroRolImportEstructura(array $indice, array $fila, string $rol, string $etiqueta): array
{
    $id = (int) ($fila['id_' . $rol] ?? 0);
    $nombre = trim((string) ($fila[$rol] ?? ''));
    $cedula = normalizarCedulaImportEstructura($fila['cedula_' . $rol] ?? '');

    if ($id <= 0 && preg_match('/^\d+$/', $nombre) === 1) {
        $id = (int) $nombre;
        $nombre = '';
    }

    if ($id <= 0 && $cedula === '' && $nombre === '') {
        throw new InvalidArgumentException('Indica el Id del ' . $etiqueta . ' (o su nombre / cédula).');
    }

    $miembro = resolverMiembroImportEstructura($indice, $cedula, $nombre, $id);
    if ($miembro === null) {
        $referencia = $id > 0 ? ('Id ' . $id) : ($cedula !== '' ? $cedula : $nombre);
        throw new InvalidArgumentException($etiqueta . ' no encontrado: ' . $referencia);
    }

    return $miembro;
}

/**
 * @param array{cedula: array<string, array<string, mixed>>, nombre: array<string, array<string, mixed>>} $indice
 * @return array<string, mixed>|null
 */
function resolverMiembroImportEstructura(array $indice, string $cedula, string $nombre, int $id = 0): ?array
{
    if ($id > 0 && isset($indice['id'][$id])) {
        return $indice['id'][$id];
    }

    if ($cedula !== '' && isset($indice['cedula'][$cedula])) {
        return $indice['cedula'][$cedula];
    }

    if ($nombre !== '') {
        return $indice['nombre'][claveTextoImportEstructura($nombre)] ?? null;
    }

    return null;
}

/**
 * @param array<int, array<string, string>> $filas
 * @return array{importados: int, duplicados: int, errores: array<int, array{fila: int, mensaje: string}>, paso: string}
 */
function importarTerritoriosEstructura(array $filas): array
{
    $indice = [];
    foreach (obtenerTerritorios() as $territorio) {
        $indice[claveTextoImportEstructura((string) $territorio['nombre'])] = $territorio;
    }

    $importados = 0;
    $duplicados = 0;
    $errores = [];

    $pdo = getConnection();
    $pdo->beginTransaction();

    try {
        foreach ($filas as $fila) {
            $numeroFila = (int) ($fila['_fila'] ?? 0);

            try {
                $nombre = trim((string) ($fila['nombre'] ?? ''));
                if ($nombre === '') {
                    throw new InvalidArgumentException('El nombre del territorio es obligatorio.');
                }

                $clave = claveTextoImportEstructura($nombre);
                if (isset($indice[$clave])) {
                    $duplicados++;
                    continue;
                }

                $id = crearTerritorio($nombre);
                $indice[$clave] = ['id' => $id, 'nombre' => $nombre];
                $importados++;
            } catch (InvalidArgumentException $e) {
                $errores[] = ['fila' => $numeroFila, 'mensaje' => $e->getMessage()];
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    return [
        'paso'        => 'territorios',
        'importados'  => $importados,
        'duplicados'  => $duplicados,
        'errores'     => $errores,
    ];
}

/**
 * @param array<int, array<string, string>> $filas
 * @return array{importados: int, duplicados: int, errores: array<int, array{fila: int, mensaje: string}>, paso: string}
 */
function importarCasasEstructura(array $filas): array
{
    $territoriosPorId = [];
    foreach (obtenerTerritorios() as $territorio) {
        $territoriosPorId[(int) $territorio['id']] = $territorio;
    }

    $lideres = indiceLideresImportEstructura();

    $casasExistentes = [];
    foreach (obtenerCasasVida() as $casa) {
        $clave = (int) $casa['territorio_id'] . '|' . claveTextoImportEstructura((string) $casa['nombre']);
        $casasExistentes[$clave] = $casa;
    }

    $importados = 0;
    $duplicados = 0;
    $errores = [];

    $pdo = getConnection();
    $pdo->beginTransaction();

    try {
        foreach ($filas as $fila) {
            $numeroFila = (int) ($fila['_fila'] ?? 0);

            try {
                $nombreCasa = trim((string) ($fila['nombre_casa'] ?? ''));
                $direccion = trim((string) ($fila['direccion'] ?? ''));
                $territorioId = (int) ($fila['id_territorio'] ?? 0);

                if ($nombreCasa === '' || $direccion === '') {
                    throw new InvalidArgumentException('Nombre de la casa y dirección son obligatorios.');
                }

                if ($territorioId <= 0) {
                    throw new InvalidArgumentException('El Id del territorio es obligatorio.');
                }

                $territorio = $territoriosPorId[$territorioId] ?? null;
                if ($territorio === null) {
                    throw new InvalidArgumentException('Territorio no encontrado: Id ' . $territorioId);
                }

                $lider = resolverMiembroPorIdImportEstructura($lideres, (int) ($fila['id_lider'] ?? 0), 'Líder');
                $colaboradorId = (int) ($fila['id_colaborador'] ?? 0);
                $anfitrionId = (int) ($fila['id_anfitrion'] ?? 0);
                $colaborador = $colaboradorId > 0
                    ? resolverMiembroPorIdImportEstructura($lideres, $colaboradorId, 'Colaborador')
                    : null;
                $anfitrion = $anfitrionId > 0
                    ? resolverMiembroPorIdImportEstructura($lideres, $anfitrionId, 'Anfitrión')
                    : null;

                $claveCasa = (int) $territorio['id'] . '|' . claveTextoImportEstructura($nombreCasa);
                if (isset($casasExistentes[$claveCasa])) {
                    $duplicados++;
                    continue;
                }

                $id = crearCasaVida([
                    'territorio_id'  => (int) $territorio['id'],
                    'lider_id'       => (int) $lider['id'],
                    'colaborador_id' => $colaborador !== null ? (int) $colaborador['id'] : 0,
                    'anfitrion_id'   => $anfitrion !== null ? (int) $anfitrion['id'] : 0,
                    'nombre'         => $nombreCasa,
                    'direccion'      => $direccion,
                ]);

                $casasExistentes[$claveCasa] = ['id' => $id];
                $importados++;
            } catch (InvalidArgumentException $e) {
                $errores[] = ['fila' => $numeroFila, 'mensaje' => $e->getMessage()];
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    return [
        'paso'        => 'casas',
        'importados'  => $importados,
        'duplicados'  => $duplicados,
        'errores'     => $errores,
    ];
}

/**
 * @return array{cedula: array<string, array<string, mixed>>, nombre: array<string, array<string, mixed>>}
 */
function indiceLideresImportEstructura(): array
{
    $porCedula = [];
    $porNombre = [];
    $porId = [];

    foreach (obtenerLideres() as $lider) {
        $porId[(int) $lider['id']] = $lider;

        $cedula = normalizarCedulaImportEstructura((string) ($lider['cedula'] ?? ''));
        if ($cedula !== '') {
            $porCedula[$cedula] = $lider;
        }

        $porNombre[claveTextoImportEstructura(nombreCompletoLider($lider))] = $lider;
    }

    return [
        'id'     => $porId,
        'cedula' => $porCedula,
        'nombre' => $porNombre,
    ];
}

/**
 * @return array{filas: array<int, array<string, string>>, diagnostico: array<string, mixed>}
 */
function leerFilasArchivoImportEstructura(string $rutaTemporal, string $nombreOriginal, string $paso): array
{
    $diagnostico = [
        'archivo'             => $nombreOriginal,
        'formato'             => 'desconocido',
        'hoja_usada'          => '',
        'encabezados_archivo' => [],
        'columnas_mapeadas'   => [],
        'columnas_faltantes'  => [],
    ];

    if ($rutaTemporal === '' || !is_readable($rutaTemporal)) {
        throw new InvalidArgumentException('No se pudo leer el archivo subido.');
    }

    $muestra = (string) file_get_contents($rutaTemporal, false, null, 0, 8192);
    if ($muestra === '') {
        throw new InvalidArgumentException('El archivo está vacío.');
    }

    if (str_starts_with($muestra, 'PK')) {
        throw new InvalidArgumentException('Formato .xlsx no compatible. Guarda el archivo como .xls (plantilla) o .csv.');
    }

    if (str_starts_with($muestra, "\xD0\xCF\x11\xE0")) {
        throw new InvalidArgumentException('Formato Excel binario no compatible. Guarda como .csv o usa la plantilla .xls descargada.');
    }

    $info = definirPasoImportEstructura($paso);

    if (esArchivoSpreadsheetMlImportEstructura($muestra)) {
        $diagnostico['formato'] = 'spreadsheetml';
        $matriz = leerMatrizSpreadsheetMlImportEstructura($rutaTemporal, $diagnostico);
    } else {
        $diagnostico['formato'] = 'csv';
        $diagnostico['hoja_usada'] = 'CSV';
        $matriz = leerMatrizCsvImportEstructura($rutaTemporal);
    }

    if ($matriz === []) {
        throw new InvalidArgumentException('No se encontraron filas en el archivo.');
    }

    $encabezados = array_shift($matriz);
    $diagnostico['encabezados_archivo'] = $encabezados;
    $mapa = mapearEncabezadosImportEstructura($paso, $encabezados);
    $diagnostico['columnas_mapeadas'] = array_keys($mapa);
    $diagnostico['columnas_faltantes'] = array_values(array_diff($info['requeridas'], array_keys($mapa)));

    if ($mapa === []) {
        throw new InvalidArgumentException('No se reconocieron las columnas. Usa los encabezados de la plantilla.');
    }

    if ($diagnostico['columnas_faltantes'] !== []) {
        $etiquetas = [];
        foreach ($diagnostico['columnas_faltantes'] as $clave) {
            $etiquetas[] = $info['columnas'][$clave] ?? $clave;
        }
        throw new InvalidArgumentException('Faltan columnas obligatorias: ' . implode(', ', $etiquetas) . '.');
    }

    $filas = [];
    foreach ($matriz as $indice => $valores) {
        $numeroFila = $indice + 2;
        $asociativa = [];
        $hayValor = false;

        foreach ($mapa as $clave => $columna) {
            $valor = trim((string) ($valores[$columna] ?? ''));
            $asociativa[$clave] = $valor;
            if ($valor !== '') {
                $hayValor = true;
            }
        }

        if (!$hayValor) {
            continue;
        }

        $asociativa['_fila'] = (string) $numeroFila;
        $filas[] = $asociativa;
    }

    return [
        'filas'       => $filas,
        'diagnostico' => $diagnostico,
    ];
}

function esArchivoSpreadsheetMlImportEstructura(string $muestra): bool
{
    return stripos($muestra, 'urn:schemas-microsoft-com:office:spreadsheet') !== false
        || stripos($muestra, '<?mso-application') !== false
        || (stripos($muestra, '<Workbook') !== false && stripos($muestra, '<Worksheet') !== false);
}

/**
 * @param array<string, mixed> $diagnostico
 * @return array<int, array<int, string>>
 */
function leerMatrizSpreadsheetMlImportEstructura(string $ruta, array &$diagnostico): array
{
    $xml = file_get_contents($ruta);
    if ($xml === false) {
        throw new InvalidArgumentException('No se pudo leer el archivo Excel.');
    }

    if (str_starts_with($xml, "\xEF\xBB\xBF")) {
        $xml = substr($xml, 3);
    }

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    if (!$dom->loadXML($xml, LIBXML_NOERROR | LIBXML_NOWARNING)) {
        throw new InvalidArgumentException('El archivo Excel no tiene un formato válido. Usa la plantilla o un CSV.');
    }

    $xpath = new DOMXPath($dom);
    /** @var DOMNodeList<DOMElement> $hojas */
    $hojas = $xpath->query('//*[local-name()="Worksheet"]');
    if ($hojas === false || $hojas->length === 0) {
        throw new InvalidArgumentException('No se encontró ninguna hoja en el archivo Excel.');
    }

    $hojaDatos = null;
    foreach ($hojas as $hoja) {
        if (!$hoja instanceof DOMElement) {
            continue;
        }

        $nombre = nombreHojaSpreadsheetMlImportEstructura($hoja);
        if (strcasecmp($nombre, 'Datos') === 0) {
            $hojaDatos = $hoja;
            $diagnostico['hoja_usada'] = $nombre;
            break;
        }
    }

    if ($hojaDatos === null) {
        foreach ($hojas as $hoja) {
            if (!$hoja instanceof DOMElement) {
                continue;
            }
            $nombre = nombreHojaSpreadsheetMlImportEstructura($hoja);
            if (stripos($nombre, 'guía') !== false || stripos($nombre, 'guia') !== false) {
                continue;
            }
            $hojaDatos = $hoja;
            $diagnostico['hoja_usada'] = $nombre !== '' ? $nombre : 'Hoja 1';
            break;
        }
    }

    if ($hojaDatos === null) {
        throw new InvalidArgumentException('No se encontró la hoja «Datos» en el archivo.');
    }

    return extraerMatrizFilasSpreadsheetMlImportEstructura($hojaDatos);
}

function nombreHojaSpreadsheetMlImportEstructura(DOMElement $hoja): string
{
    if ($hoja->hasAttribute('ss:Name')) {
        return trim($hoja->getAttribute('ss:Name'));
    }

    if ($hoja->hasAttribute('Name')) {
        return trim($hoja->getAttribute('Name'));
    }

    foreach ($hoja->attributes ?? [] as $attr) {
        if (strcasecmp($attr->localName ?? '', 'Name') === 0) {
            return trim((string) ($attr->nodeValue ?? ''));
        }
    }

    return '';
}

/**
 * @return array<int, array<int, string>>
 */
function extraerMatrizFilasSpreadsheetMlImportEstructura(DOMElement $hoja): array
{
    $filas = [];

    foreach ($hoja->getElementsByTagName('*') as $nodo) {
        if (!$nodo instanceof DOMElement || strcasecmp($nodo->localName ?? $nodo->tagName, 'Row') !== 0) {
            continue;
        }

        $celdas = extraerCeldasFilaSpreadsheetMlImportEstructura($nodo);
        if ($celdas === []) {
            continue;
        }

        $filas[] = $celdas;
    }

    return $filas;
}

/**
 * @return array<int, string>
 */
function extraerCeldasFilaSpreadsheetMlImportEstructura(DOMElement $fila): array
{
    $valores = [];
    $columna = 0;

    foreach ($fila->childNodes as $nodo) {
        if (!$nodo instanceof DOMElement) {
            continue;
        }
        if (strcasecmp($nodo->localName ?? $nodo->tagName, 'Cell') !== 0) {
            continue;
        }

        $indiceAttr = '';
        if ($nodo->hasAttribute('ss:Index')) {
            $indiceAttr = $nodo->getAttribute('ss:Index');
        } elseif ($nodo->hasAttribute('Index')) {
            $indiceAttr = $nodo->getAttribute('Index');
        }

        if ($indiceAttr !== '') {
            $columna = max(0, (int) $indiceAttr - 1);
        }

        $texto = '';
        foreach ($nodo->getElementsByTagName('*') as $hijo) {
            if ($hijo instanceof DOMElement && strcasecmp($hijo->localName ?? $hijo->tagName, 'Data') === 0) {
                $texto = trim((string) preg_replace('/\s+/u', ' ', $hijo->textContent ?? ''));
                break;
            }
        }

        $valores[$columna] = $texto;
        $columna++;
    }

    if ($valores === []) {
        return [];
    }

    $maximo = max(array_keys($valores));
    $densas = [];
    for ($i = 0; $i <= $maximo; $i++) {
        $densas[] = $valores[$i] ?? '';
    }

    return $densas;
}

/**
 * @return array<int, array<int, string>>
 */
function leerMatrizCsvImportEstructura(string $ruta): array
{
    $contenido = file_get_contents($ruta);
    if ($contenido === false) {
        throw new InvalidArgumentException('No se pudo leer el archivo CSV.');
    }

    if (str_starts_with($contenido, "\xEF\xBB\xBF")) {
        $contenido = substr($contenido, 3);
    }

    if (str_starts_with($contenido, "\xFF\xFE") || str_starts_with($contenido, "\xFE\xFF")) {
        $convertido = function_exists('mb_convert_encoding')
            ? @mb_convert_encoding($contenido, 'UTF-8', 'UTF-16')
            : false;
        if (is_string($convertido) && $convertido !== '') {
            $contenido = $convertido;
        }
    } elseif (function_exists('mb_check_encoding') && !mb_check_encoding($contenido, 'UTF-8')) {
        $convertido = function_exists('mb_convert_encoding')
            ? @mb_convert_encoding($contenido, 'UTF-8', 'Windows-1252')
            : false;
        if (is_string($convertido) && $convertido !== '') {
            $contenido = $convertido;
        }
    }

    $lineas = preg_split('/\R/u', $contenido);
    if (!is_array($lineas)) {
        $lineas = preg_split('/\R/', $contenido) ?: [];
    }

    $lineas = array_values(array_filter($lineas, static fn (string $linea): bool => trim($linea) !== ''));
    if ($lineas === []) {
        return [];
    }

    $delimitador = substr_count($lineas[0], ';') > substr_count($lineas[0], ',') ? ';' : ',';
    $matriz = [];

    foreach ($lineas as $linea) {
        $matriz[] = str_getcsv($linea, $delimitador);
    }

    return $matriz;
}
