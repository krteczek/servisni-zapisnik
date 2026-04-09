<?php
declare(strict_types=1);

namespace App\Services\Mail;

class MailView
{
    public static function render(string $template, array $data = []): string
    {
        extract($data);

        // content
        ob_start();
        require base_path("views/emails/{$template}.php");
        $content = ob_get_clean();

        // layout
        ob_start();
        require base_path("views/emails/layout.php");
        return ob_get_clean();
    }
}