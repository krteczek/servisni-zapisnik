<?php
declare(strict_types=1);

namespace App\Services\Onboarding;

use App\Core\Transaction;
use App\Core\DatabaseScope;
use App\Core\LoggerHolder;
use App\Core\Types;
use Throwable;
use PDO;

use App\Services\Users\CompanyRegistrationService;
use App\Services\Mail\MailService;
use App\Services\Users\BuildMailService;
use App\Models\CompanyModel;
use App\Services\Tokens\TokenService;

/** @phpstan-import-type OnboardingResult from Types */

class OnboardingService
{
    /**
     * @param array{data: array<string, mixed>, token: string, type: string} $load
     * @return OnboardingResult
     */
    public function run(array $load): array
    {
        $d     = $load['data'];
        $token = $load['token'];
        $type  = $load['type'];

        // Prázdná data pro error stavy
        $emptyData = [
            'company_id'        => 0,
            'db_name'           => '',
            'user_id'           => 0,
            'team_id'           => 0,
            'email'             => '',
            'first_name'        => '',
            'last_name'         => '',
            'company_name'      => '',
            'slug'              => '',
            'session_version'   => 0,
            'global_role'       => '',
        ];

        /*
         * ==========================
         * 1️⃣ ADMIN část (MUST SUCCEED)
         * ==========================
         */
        try {
            $adminResult = Transaction::run(
                function () use ($d, $token, $type) {

                    $tokenData = (new TokenService())->consume($token, $type);

                    if ($tokenData['ok'] === false) {
                        return $tokenData;  // TokenConsumeError
                    }

                    $data = [
                        'email'      => $tokenData['data']['email'],
                        'first_name' => $d['first_name'],
                        'last_name'  => $d['last_name'],
                        'password'   => $d['password'],
                        'name'       => $d['name'],
                        'ico'        => $d['ico'],
                    ];

                    $service = new CompanyRegistrationService();
                    return $service->completeAdmin($data);
                },
                'admin'
            );

            // Normalizace: když token selhal, převeď na OnboardingResult
            if ($adminResult['ok'] === false) {
                return [
                    'ok'     => false,
                    'result' => 'Token invalid or admin onboarding failed',
                    'data'   => $emptyData,
                ];
            }

        } catch (Throwable $e) {
            LoggerHolder::get()->error('OnboardingService.run-admin: failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'data'    => json_encode($d),
            ]);

            return [
                'ok'     => false,
                'result' => 'Admin onboarding failed',
                'data'   => $emptyData,
            ];
        }

        /*
         * ==========================
         * DATA
         * ==========================
         */
        $adminData = $adminResult['data'];
        $companyId = $adminData['company_id'];
        $dbName    = $adminData['db_name'];

        $data = [
            'company_id' => $companyId,
            'db_name'    => $dbName,
            'user_id'    => $adminData['user_id'],
            'team_id'    => $adminData['team_id'],
        ];

        /*
         * ==========================
         * 2️⃣ WORK část (MAY FAIL)
         * ==========================
         */
        try {
            DatabaseScope::work($dbName, function () use ($data) {
                return Transaction::run(
                    function () use ($data) {
                        $service = new CompanyRegistrationService();
                        return $service->completeWork($data);
                    },
                    'work'
                );
            });

            $this->markOnboardingDone($companyId);

        } catch (Throwable $e) {
            $this->markOnboardingFailed($companyId, $e);

            LoggerHolder::get()->error('OnboardingService.run-work: failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'data'    => json_encode($data),
            ]);
        }

        /*
         * ==========================
         * 📧 MAIL (nesmí shodit flow)
         * ==========================
         */
        try {
            [$subject, $html, $text] = BuildMailService::build('users.InfoAfterRegistration', $adminResult);

            (new MailService())->send(
                toEmail: $adminResult['data']['email'],
                toName:  $adminResult['data']['first_name'] . ' ' . $adminResult['data']['last_name'],
                subject: $subject,
                html:    $html,
                text:    $text
            );

        } catch (Throwable $e) {
            LoggerHolder::get()->warning('OnboardingService.mail failed', [
                'message' => $e->getMessage(),
            ]);
        }

        return [
            'ok'     => true,
            'result' => 'Onboarding completed',
            'data'   => [
                'company_id'      => $companyId,
                'db_name'         => $dbName,
                'user_id'         => $adminData['user_id'],
                'team_id'         => $adminData['team_id'],
                'email'           => $adminData['email'],
                'first_name'      => $adminData['first_name'],
                'last_name'       => $adminData['last_name'],
                'company_name'    => $adminData['company_name'],
                'slug'            => $adminData['slug'],
                'session_version' => $adminData['session_version'],
                'global_role'     => $adminData['global_role'],
            ],
        ];
    }

    private function markOnboardingDone(int $companyId): void
    {
        (new CompanyModel())->update($companyId, [
            'onboarding_status' => 'done',
            'onboarding_error'  => null,
        ]);
    }

    private function markOnboardingFailed(int $companyId, Throwable $e): void
    {
        (new CompanyModel())->update($companyId, [
            'onboarding_status' => 'failed',
            'onboarding_error'  => $e->getMessage(),
        ]);
    }
}