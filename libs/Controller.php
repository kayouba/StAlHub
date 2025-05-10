<?php
namespace Core;

abstract class Controller
{
    /**
     * Rend une vue views/{name}.php en injectant $params.
     */
    protected function render(string $view, array $params = []): void
    {
        extract($params, EXTR_SKIP);
        ob_start();
        require __DIR__ . "/../views/{$view}.php";
        $content = ob_get_clean();
        require __DIR__ . "/../views/layouts/default.php";
    }
}
