<?php
declare(strict_types=1);

namespace App\Services\Ares;

use App\Core\Session;

final class AresSession
{
    private const KEY = 'company_ares';

    /**
     * Uloží ARES data pro konkrétní objekt.
     *
     * @param string $type Typ objektu, např. company nebo contact.
     * @param int $id ID objektu.
     * @param string $ico IČO.
     * @param array<string, mixed> $data ARES data.
     */
    public static function set(
        string $type,
        int $id,
        string $ico,
        array $data
    ): void {
        $ares = Session::get(self::KEY);

        if (!is_array($ares)) {
            $ares = [];
        }

        if (!isset($ares[$type]) || !is_array($ares[$type])) {
            $ares[$type] = [];
        }

        $ares[$type][$id] = [
            'ico'       => $ico,
            'loaded_at' => date('Y-m-d H:i:s'),
            'data'      => $data,
        ];

        Session::set(self::KEY, $ares);
    }

    /**
     * Vrátí ARES data pro konkrétní objekt.
     *
     * @return array{ico: string, loaded_at: string, data: array<string, mixed>}|null
     */
    public static function get(
        string $type,
        int $id
    ): ?array {
        $ares = Session::get(self::KEY);

        if (!is_array($ares)) {
            return null;
        }

        if (
            !isset($ares[$type])
            || !is_array($ares[$type])
            || !isset($ares[$type][$id])
            || !is_array($ares[$type][$id])
        ) {
            return null;
        }

        $entry = $ares[$type][$id];

        if (
            !isset($entry['ico'])
            || !is_string($entry['ico'])
            || !isset($entry['loaded_at'])
            || !is_string($entry['loaded_at'])
            || !isset($entry['data'])
            || !is_array($entry['data'])
        ) {
            return null;
        }

        return [
            'ico'       => $entry['ico'],
            'loaded_at' => $entry['loaded_at'],
            'data'      => $entry['data'],
        ];
    }

    /**
     * Odstraní ARES data konkrétního objektu.
     */
    public static function forget(
        string $type,
        int $id
    ): void {
        $ares = Session::get(self::KEY);

        if (!is_array($ares)) {
            return;
        }

        if (
            isset($ares[$type])
            && is_array($ares[$type])
        ) {
            unset($ares[$type][$id]);

            if ($ares[$type] === []) {
                unset($ares[$type]);
            }
        }

        if ($ares === []) {
            Session::forget(self::KEY);
            return;
        }

        Session::set(self::KEY, $ares);
    }
}
