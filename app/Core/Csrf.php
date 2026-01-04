<?php
declare(strict_types=1);

namespace App\Core;

class Csrf
{
    private const KEY = '_csrf';

    public static function token(): string
    {
        Session::start();
        
        $token = Session::get(self::KEY);
        
        if (empty($token)) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::KEY, $token);
        }

        return $token;
    }

    public static function check(string $token): bool
    {
        Session::start();
        
        $storedToken = Session::get(self::KEY);
        
        if (empty($storedToken)) {
            return false;
        }

        return hash_equals($storedToken, $token);
    }
    
    public static function verify(string $token): bool
    {
        return self::check($token);
    }
    
    public static function invalidate(): void
    {
        Session::forget(self::KEY);
    }
    
    public static function regenerate(): string
    {
        self::invalidate();
        return self::token();
    }
    
    public static function getField(): string
    {
        return sprintf(
            '<input type="hidden" name="_token" value="%s">',
            htmlspecialchars(self::token(), ENT_QUOTES)
        );
    }
}