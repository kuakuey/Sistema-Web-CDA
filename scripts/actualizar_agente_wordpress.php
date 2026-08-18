<?php

require_once __DIR__ . '/../includes/conexion.php';

$pdo = getConnection();

foreach (['inscripciones', 'presentaciones_ninos'] as $tabla) {
    if (!tablaExiste($pdo, $tabla)) {
        echo $tabla . ": no existe\n";
        continue;
    }

    $stmt = $pdo->prepare(
        "UPDATE `{$tabla}` SET agente_usuario = ? WHERE agente_usuario LIKE ?"
    );
    $stmt->execute(['Formulario Publico', 'WordPress/%']);

    $count = $pdo->prepare(
        "SELECT COUNT(*) FROM `{$tabla}` WHERE agente_usuario = ?"
    );
    $count->execute(['Formulario Publico']);
    echo $tabla . ': ' . (int) $count->fetchColumn() . " con Formulario Publico\n";
}
