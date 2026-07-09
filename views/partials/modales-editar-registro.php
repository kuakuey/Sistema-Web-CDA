<?php
/**
 * Renderiza modales de edición acumulados en $modalesEditar.
 *
 * @var array<int, array<string, mixed>> $modalesEditar
 */
foreach ($modalesEditar ?? [] as $modalEditar):
    $modalEditarId = $modalEditar['id'];
    $tipoEditar = $modalEditar['tipo'];
    $filaEditar = $modalEditar['fila'];
    $redireccionEditar = $modalEditar['redireccion'];
    include __DIR__ . '/modal-editar-registro.php';
endforeach;
