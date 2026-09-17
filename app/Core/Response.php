<?php
declare(strict_types=1);

namespace App\Core;

final class Response
{
    /**
     * @param array<string, mixed> $data
     * @param int $status
     * @return void
     */
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function html(string $content, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');

        echo $content;
        exit;
    }

    public static function download(string $content, string $filename, string $contentType): void
    {
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo $content;
        exit;
    }

    public static function pdf(string $binaryPdf, string $filename = 'export.pdf'): void
    {
        self::download($binaryPdf, $filename, 'application/pdf');
    }

    public static function applySecurityHeaders(): void
    {
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');

        header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; script-src 'self'; style-src 'self' 'unsafe-inline'");

        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }
}