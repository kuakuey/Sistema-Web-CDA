<?php

function render(string $view, array $data = []): string
{
    $path = __DIR__ . '/../views/' . $view . '.php';

    if (!is_file($path)) {
        throw new RuntimeException("Vista no encontrada: {$view}");
    }

    extract($data, EXTR_SKIP);

    ob_start();
    include $path;

    return ob_get_clean();
}

function view(string $view, array $data = [], ?string $layout = null): void
{
    $content = render($view, $data);

    if ($layout === null) {
        echo $content;
        return;
    }

    $layoutPath = __DIR__ . '/../views/layouts/' . $layout . '.php';

    if (!is_file($layoutPath)) {
        throw new RuntimeException("Layout no encontrado: {$layout}");
    }

    extract($data, EXTR_SKIP);

    include $layoutPath;
}
