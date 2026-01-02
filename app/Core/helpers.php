<?php
declare(strict_types=1);

function redirect(string $path, int $code = 302): never
{
    header('Location: ' . BASE_PATH . $path, true, $code);
    exit;
}

function redirectBack(): never
{
    $url = $_SERVER['HTTP_REFERER'] ?? '/';
    redirect($url);
}

function redirectWithMessage(string $path, string $message): never
{
    $_SESSION['flash'] = $message;
    redirect($path);
}
