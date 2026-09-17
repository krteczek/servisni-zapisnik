<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\UserAgent;
class Request
{
    public static function ip(): ?string
    {
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }

    public static function userAgent(): ?string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? null;
    }
    public static function ua(): ?string
    {
        return self::userAgent();
    }

    /**
     * @return array<string, mixed>
     */
    public static function UAParse(): array
    {
    	return UserAgent::parse(self::userAgent());

    }
    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

}