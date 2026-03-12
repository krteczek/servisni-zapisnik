<?php
declare(strict_types=1);

namespace App\Core;

class UserAgent
{
    public static function parse(?string $ua): array
    {
        if (!$ua) {
            return [
                'device'  => 'unknown',
                'os'      => 'unknown',
                'browser' => 'unknown',
            ];
        }

        $ua = strtolower($ua);

        // device
        if (str_contains($ua, 'bot') || str_contains($ua, 'crawl') || str_contains($ua, 'spider')) {
            $device = 'bot';
        } elseif (str_contains($ua, 'mobile')) {
            $device = 'mobile';
        } elseif (str_contains($ua, 'tablet')) {
            $device = 'tablet';
        } else {
            $device = 'desktop';
        }

        // os
        if (str_contains($ua, 'windows')) {
            $os = 'windows';
        } elseif (str_contains($ua, 'android')) {
            $os = 'android';
        } elseif (str_contains($ua, 'iphone') || str_contains($ua, 'ios')) {
            $os = 'ios';
        } elseif (str_contains($ua, 'mac')) {
            $os = 'mac';
        } elseif (str_contains($ua, 'linux')) {
            $os = 'linux';
        } else {
            $os = 'unknown';
        }

        // browser
        if (str_contains($ua, 'firefox')) {
            $browser = 'firefox';
        } elseif (str_contains($ua, 'edg')) {
            $browser = 'edge';
        } elseif (str_contains($ua, 'chrome')) {
            $browser = 'chrome';
        } elseif (str_contains($ua, 'safari')) {
            $browser = 'safari';
        } else {
            $browser = 'unknown';
        }

        return [
            'device'  => $device,
            'os'      => $os,
            'browser' => $browser,
        ];
    }
}