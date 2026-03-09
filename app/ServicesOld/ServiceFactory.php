<?php
declare(strict_types=1);

namespace App\Services;

use App\Services\RateLimiterService;
use App\Services\AuthTokenService;
use App\Services\MailService;
use App\Services\RegistrationService;
use App\Models\UserModel;
use App\Models\CompanyModel;
use App\Models\RegistrationRequestModel;
use App\Models\TaskModel;
use App\Models\WorkOrderModel;
use App\Models\TeamModel;
use App\Models\EmailRateLimitModel;


final class ServiceFactory
{
    public static function passwordResetRequest(): PasswordResetRequestService
    {
        $erlm = new EmailRateLimitModel();
        return new PasswordResetRequestService(
            new RateLimiterService($erlm),
            new AuthTokenService(),
            new UserModel(),
            new CompanyModel(),
            new MailService()
        );
    }

    public static function registrationService(): RegistrationService
    {
        return new RegistrationService(

        new RegistrationTokenService(),
        new RegistrationRequestModel(),
        new CompanyModel(),
        new UserModel(),
        new TaskModel(),
        new WorkOrderModel(),
        new TeamModel()

        );
    }

}