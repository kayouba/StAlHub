<?php
namespace App;

class View
{
    public static function render(string $template, array $data = [], string $layout = 'default'): void
    {
        extract($data);

        // Capture le rendu de la vue
        ob_start();
        require __DIR__ . "/views/{$template}.php"; // ✅ plus de ../../
        $content = ob_get_clean();

        // Inclure le layout global
        require __DIR__ . "/views/layouts/{$layout}.php";
    }
}
