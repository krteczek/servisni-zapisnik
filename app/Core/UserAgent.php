<?php
declare(strict_types=1);

namespace App\Core;

class UserAgent
{
    /**
     * @param string|null $ua User-Agent string
     * @return array<string, mixed>
     */
    public static function parse(?string $ua): array
    {
        if ($ua === null) {
            return [
                'device'  => 'unknown',
                'os'      => 'unknown',
                'browser' => 'unknown',
            ];
        }

        $ua = strtolower($ua);

        // =========================
        // BOT DETECTION (PRIORITA)
        // =========================
        $botMap = [
            'googlebot'    => 'google',
            'bingbot'      => 'bing',
            'seznambot'    => 'seznambot',
            'ahrefsbot'    => 'ahrefsbot',
            'semrushbot'   => 'semrushbot',
            'mj12bot'      => 'mj12bot',
            'duckduckbot'  => 'duckduckgo',
            'baiduspider'  => 'baidu',
            'yandexbot'    => 'yandex',
            'facebookbot'  => 'facebot',
            'alexa'        => 'ia_archiver',
            'sogou'        => 'sogou',
            'exabot'       => 'exabot',
            'yahoo'        => 'slurp',

            // fallback generic
            'generic'      => 'crawl',
        ];

        $bot = null;

        foreach ($botMap as $name => $needle) {
            if (str_contains($ua, $needle)) {
                $bot = $name;
                break;
            }
        }

        // 🔥 BOT má absolutní prioritu
        if ($bot !== null) {
            return [
                'device'  => 'bot',
                'bot'     => $bot,
                'os'      => 'unknown',
                'browser' => 'unknown',
            ];
        }

        // =========================
        // DEVICE
        // =========================
        if (
            str_contains($ua, 'mobile') ||
            str_contains($ua, 'iphone') ||
            str_contains($ua, 'android')
        ) {
            $device = 'mobile';
        } elseif (
            str_contains($ua, 'tablet') ||
            str_contains($ua, 'ipad')
        ) {
            $device = 'tablet';
        } else {
            $device = 'desktop';
        }

        // =========================
        // OS
        // =========================
        if (str_contains($ua, 'windows')) {
            $os = 'windows';
        } elseif (str_contains($ua, 'android')) {
            $os = 'android';
        } elseif (
            str_contains($ua, 'iphone') ||
            str_contains($ua, 'ios')
        ) {
            $os = 'ios';
        } elseif (str_contains($ua, 'mac')) {
            $os = 'mac';
        } elseif (str_contains($ua, 'linux')) {
            $os = 'linux';
        } else {
            $os = 'unknown';
        }

        // =========================
        // BROWSER
        // =========================
        if (str_contains($ua, 'edg')) {
            $browser = 'edge';
        } elseif (str_contains($ua, 'firefox')) {
            $browser = 'firefox';
        } elseif (str_contains($ua, 'chrome')) {
            $browser = 'chrome';
        } elseif (str_contains($ua, 'safari')) {
            $browser = 'safari';
        } elseif (
            str_contains($ua, 'opera') ||
            str_contains($ua, 'opr')
        ) {
            $browser = 'opera';
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