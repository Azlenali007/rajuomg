<?php
declare(strict_types=1);

namespace App\Core;

/**
 * View Renderer
 */
class View
{
    public static function render(string $template, array $data = [], ?string $layout = 'user'): void
    {
        extract($data);
        
        $baseDir = dirname(__DIR__, 2) . '/resources/views/';
        $viewFile = $baseDir . $template . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo "Error: View template [{$template}] not found.";
            return;
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === 'user') {
            require $baseDir . 'layouts/user_layout.php';
        } elseif ($layout === 'admin') {
            require $baseDir . 'layouts/admin_layout.php';
        } else {
            echo $content;
        }
    }

    public static function escape(?string $str): string
    {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}
