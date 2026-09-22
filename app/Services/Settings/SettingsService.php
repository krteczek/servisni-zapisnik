<?php

declare(strict_types=1);

namespace App\Services\Settings;

use App\Services\Settings\BillingMode;
use App\Models\SettingsModel;

final class SettingsService
{
    private SettingsModel $model;

    public function __construct()
    {
        $this->model = new SettingsModel();
    }

    /**
     * Vrátí způsob fakturace aktuální firmy.
     *
     * @return string
     */
    public function billingMode(): string
    {
        $settings = $this->model->getBillingSettings();

        return (string) $settings['billing_mode'];
    }

    /**
     * Určuje, zda firma používá interní fakturaci.
     *
     * @return bool
     */
    public function isInternalBilling(): bool
    {
        return $this->billingMode() === BillingMode::INTERNAL;
    }

    /**
     * Určuje, zda firma používá externí účetnictví.
     *
     * @return bool
     */
    public function isExternalAccounting(): bool
    {
        return $this->billingMode() === BillingMode::EXTERNAL_ACCOUNTANT;
    }

    /**
     * Vrátí počet dnů splatnosti faktury.
     *
     * @return int
     */
    public function getInvoiceDueDays(): int
    {
        $settings = $this->model->getBillingSettings();

        return (int) $settings['invoice_due_days'];
    }

    /**
     * Vrátí počáteční číslo číselné řady faktur.
     *
     * @return int
     */
    public function getInvoiceNumberStart(): int
    {
        $settings = $this->model->getBillingSettings();

        return (int) $settings['invoice_number_start'];
    }

    /**
     * Vrátí formát čísla faktury.
     *
     * @return string
     */
    public function getInvoiceNumberFormat(): string
    {
        $settings = $this->model->getBillingSettings();

        return (string) $settings['invoice_number_format'];
    }

    /**
     * Nastaví formát čísla faktury.
     *
     * @param string $format
     * @return void
     */
    public function setInvoiceNumberFormat(string $format): void
    {
        if ($format === '') {
            throw new \InvalidArgumentException(
                'Formát čísla faktury nesmí být prázdný.'
            );
        }

        $settings = $this->model->getBillingSettings();

        $settings['invoice_number_format'] = $format;

        $this->model->updateBillingSettings($settings);
    }

    /**
     * Určuje, zda jsou potvrzena všechna nastavení fakturace,
     * která potvrzení vyžadují.
     *
     * @return bool
     */
    public function areInvoiceSettingsConfirmed(): bool
    {
        return $this->model->areRequiredSettingsConfirmed('billing');
    }

    /**
     * Určí, zda je konkrétní nastavení fakturace potvrzené.
     *
     * @param string $setting
     * @return bool
     */
    public function isInvoiceSettingConfirmed(
        string $setting
    ): bool {
        return $this->model->isConfirmed('billing', $setting);
    }

    
}