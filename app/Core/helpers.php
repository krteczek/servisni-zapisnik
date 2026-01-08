<?php
declare(strict_types=1);

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

function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}