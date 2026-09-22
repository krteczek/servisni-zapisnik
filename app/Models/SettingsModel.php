<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Config;
use LogicException;

class SettingsModel extends BaseModel
{
    protected string $table = 'settings';
    protected string $connection = 'admin';
    protected bool $tenantAware = true;

    /** @var array<string, array<string, mixed>> */
    private array $cache = [];

    /**
     * Vrátí nastavení firmy.
     *
     * Automaticky:
     * - vytvoří chybějící nastavení z výchozích hodnot,
     * - doplní nové výchozí hodnoty,
     * - synchronizuje potvrzení podle konfigurace.
     *
     * @param string $key
     * @return array<string, mixed>
     */
    public function get(string $key): array
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $defaults = $this->getDefaults($key);
        $confirmationRequired = $this->getConfirmationRequired($key);

        $row = $this->firstWhere('setting_key', $key);

        if ($row === null) {
            $value = $defaults;
            $value['_confirmed'] = $confirmationRequired;

            $this->create([
                'setting_key' => $key,
                'value' => json_encode($value, JSON_THROW_ON_ERROR),
            ]);

            return $this->cache[$key] = $value;
        }

        $data = json_decode((string) $row['value'], true);

        if (!is_array($data)) {
            $data = [];
        }

        /*
         * Firemní data nesmí ovlivnit seznam potvrzovaných klíčů.
         * Ten vždy určuje konfigurace aplikace.
         */
        unset($data['_confirmation_required']);

        $merged = $this->mergeDefaults($defaults, $data);

        /*
         * Synchronizace potvrzení.
         *
         * Existující stav zachováme.
         * Nově zavedené potvrzované nastavení dostane
         * výchozí hodnotu z konfigurace.
         */
        $confirmed = [];

        if (
            isset($data['_confirmed'])
            && is_array($data['_confirmed'])
        ) {
            foreach ($data['_confirmed'] as $setting => $value) {
                if (is_string($setting) && is_bool($value)) {
                    $confirmed[$setting] = $value;
                }
            }
        }

        foreach ($confirmationRequired as $setting => $default) {
            if (!array_key_exists($setting, $confirmed)) {
                $confirmed[$setting] = $default;
            }
        }

        /*
         * Klíče, které už nejsou v konfiguraci jako potvrzované,
         * odstraníme z uloženého stavu.
         */
        $confirmed = array_intersect_key(
            $confirmed,
            $confirmationRequired
        );

        $merged['_confirmed'] = $confirmed;

        if ($merged !== $data) {
            $this->update((int) $row['id'], [
                'value' => json_encode($merged, JSON_THROW_ON_ERROR),
            ]);
        }

        return $this->cache[$key] = $merged;
    }

    /**
     * Uloží nastavení firmy.
     *
     * Pokud se změní hodnota, která vyžaduje potvrzení,
     * její potvrzení se automaticky zruší.
     *
     * @param string $key
     * @param array<string, mixed> $value
     * @return void
     */
    public function set(string $key, array $value): void
    {
        $current = $this->get($key);
        $confirmationRequired = $this->getConfirmationRequired($key);

        /*
         * _confirmed není běžná hodnota nastavení.
         * Stav potvrzení řídí SettingsModel.
         */
        unset($value['_confirmed']);
        unset($value['_confirmation_required']);

        $confirmed = [];

        if (
            isset($current['_confirmed'])
            && is_array($current['_confirmed'])
        ) {
            foreach ($current['_confirmed'] as $setting => $confirmedValue) {
                if (is_string($setting) && is_bool($confirmedValue)) {
                    $confirmed[$setting] = $confirmedValue;
                }
            }
        }

        foreach ($confirmationRequired as $setting => $default) {
            if (
                !array_key_exists($setting, $value)
                || !array_key_exists($setting, $current)
                || $value[$setting] !== $current[$setting]
            ) {
                $confirmed[$setting] = false;
            } elseif (!array_key_exists($setting, $confirmed)) {
                $confirmed[$setting] = $default;
            }
        }

        $value = $this->mergeDefaults(
            $this->getDefaults($key),
            $value
        );

        $value['_confirmed'] = $confirmed;

        $row = $this->firstWhere('setting_key', $key);

        $json = json_encode($value, JSON_THROW_ON_ERROR);

        if ($row === null) {
            $this->create([
                'setting_key' => $key,
                'value' => $json,
            ]);
        } else {
            $this->update((int) $row['id'], [
                'value' => $json,
            ]);
        }

        $this->cache[$key] = $value;
    }

    /**
     * Potvrdí konkrétní nastavení.
     *
     * @param string $key
     * @param string $setting
     * @return void
     */
    public function confirm(string $key, string $setting): void
    {
        $settings = $this->get($key);
        $confirmationRequired = $this->getConfirmationRequired($key);

        if (!array_key_exists($setting, $confirmationRequired)) {
            throw new LogicException(
                "Setting '{$setting}' does not require confirmation."
            );
        }

        if (!isset($settings['_confirmed'])) {
            $settings['_confirmed'] = [];
        }

        $settings['_confirmed'][$setting] = true;

        $row = $this->firstWhere('setting_key', $key);

        if ($row === null) {
            throw new LogicException(
                "Setting '{$key}' does not exist."
            );
        }

        $this->update((int) $row['id'], [
            'value' => json_encode($settings, JSON_THROW_ON_ERROR),
        ]);

        $this->cache[$key] = $settings;
    }

    /**
     * Určí, zda je konkrétní nastavení potvrzené.
     *
     * @param string $key
     * @param string $setting
     * @return bool
     */
    public function isConfirmed(
        string $key,
        string $setting
    ): bool {
        $settings = $this->get($key);

        return isset($settings['_confirmed'][$setting])
            && $settings['_confirmed'][$setting] === true;
    }

    /**
     * Určí, zda jsou potvrzena všechna nastavení,
     * která potvrzení vyžadují.
     *
     * @param string $key
     * @return bool
     */
    public function areRequiredSettingsConfirmed(
        string $key
    ): bool {
        $confirmationRequired = $this->getConfirmationRequired($key);

        foreach ($confirmationRequired as $setting => $default) {
            if (!$this->isConfirmed($key, $setting)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Vrátí výchozí hodnoty nastavení bez metadat potvrzení.
     *
     * @param string $key
     * @return array<string, mixed>
     */
    private function getDefaults(string $key): array
    {
        $defaults = Config::get(
            'defaultCompanySettings.' . $key
        );

        if (!is_array($defaults)) {
            throw new LogicException(
                "Unknown company setting: '{$key}'"
            );
        }

        unset($defaults['_confirmation_required']);

        return $defaults;
    }

    /**
     * Vrátí nastavení potvrzení z konfigurace.
     *
     * Hodnota každého klíče musí být bool.
     *
     * @param string $key
     * @return array<string, bool>
     */
    private function getConfirmationRequired(
        string $key
    ): array {
        $required = Config::get(
            'defaultCompanySettings.'
            . $key
            . '._confirmation_required'
        );

        if (!is_array($required)) {
            return [];
        }

        $result = [];

        foreach ($required as $setting => $default) {
            if (is_string($setting) && is_bool($default)) {
                $result[$setting] = $default;
            }
        }

        return $result;
    }

    /**
     * Sloučí firemní hodnoty s výchozími hodnotami.
     *
     * @param array<string, mixed> $defaults
     * @param array<string, mixed> $value
     * @return array<string, mixed>
     */
    private function mergeDefaults(
        array $defaults,
        array $value
    ): array {
        return array_replace_recursive($defaults, $value);
    }

    /**
     * Vrátí nastavení číslování zakázek.
     *
     * @return array<string, mixed>
     */
    public function getWorkOrderSettings(): array
    {
        return $this->get('work_order_numbering');
    }

    /**
     * Aktualizuje nastavení číslování zakázek.
     *
     * @param array<string, mixed> $data
     * @return void
     */
    public function updateWorkOrderSettings(
        array $data
    ): void {
        $this->set('work_order_numbering', $data);
    }

    /**
     * Vrátí nastavení fakturace.
     *
     * @return array<string, mixed>
     */
    public function getBillingSettings(): array
    {
        return $this->get('billing');
    }

    /**
     * Aktualizuje nastavení fakturace.
     *
     * @param array<string, mixed> $data
     * @return void
     */
    public function updateBillingSettings(
        array $data
    ): void {
        $this->set('billing', $data);
    }
}