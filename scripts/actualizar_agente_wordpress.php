<?php

require_once __DIR__ . '/../config/database.php';

$pdo = getConnection();
normalizarAgenteFormularioPublico($pdo);

foreach (['inscripciones', 'presentaciones_ninos'] as $tabla) {
    if (!tablaExiste($pdo, $tabla)) {
        echo $tabla . ": no existe\n";
        continue;
    }

    $count = $pdo->prepare(
        "SELECT COUNT(*) FROM `{$tabla}` WHERE agente_usuario = ?"
    );
    $count->execute(['Formulario Publico']);
    echo $tabla . ': ' . (int) $count->fetchColumn() . " con Formulario Publico\n";
}
