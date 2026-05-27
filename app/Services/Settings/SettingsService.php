<?php
declare(strict_types=1);

namespace App\Services\Settings;

use App\Models\SettingsModel;
use App\Services\Settings\BillingMode;

class SettingsService
{
    public const BILLING_INTERNAL = BillingMode::INTERNAL; // 'internal';
    public const BILLING_EXTERNAL = BillingMode::EXTERNAL_ACCOUNTANT; // 'external_accountant';

    private SettingsModel $model;

    public function __construct()
    {
        $this->model = new SettingsModel();
    }

    public function billingMode(): string
    {
        $settings = $this->model->getBillingSettings();

        return $settings['billing_mode'];
    }

    public function isInternalBilling(): bool
    {
        return $this->billingMode() === self::BILLING_INTERNAL;
    }

    public function isExternalAccounting(): bool
    {
        return $this->billingMode() === self::BILLING_EXTERNAL;
    }
}