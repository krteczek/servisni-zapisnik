<?php
declare(strict_types=1);

namespace App\Services\Onboarding;

use App\Core\Transaction;
use App\Core\DatabaseScope;
use App\Core\LoggerHolder;
use Throwable;
use App\Services\Users\CompanyRegistrationService;
use App\Services\Mail\MailService;
use App\Services\Users\BuildMailService;
use App\Models\CompanyModel;
use App\Services\Tokens\TokenService;

class OnboardingService
{
    public function run(array $load): array
    {
    	  $d          = $load['data'];
    	  $token      = $load['token'];
    	  $type       = $load['type'];
        // 1️⃣ ADMIN část (MUST SUCCEED)
			try {
			    $adminResult = Transaction::run(
			        function () use ($d, $token, $type) {

			            $tokenData = (new TokenService())->consume($token, $type);

			            if ($tokenData['ok'] === false) {
			                return $tokenData;
			            }

			            // ✅ žádný array_merge
			            // jen explicitní složení kontraktu

			            $data = [
			                'email'      => $tokenData['email'],
			                'first_name' => $d['first_name'],
			                'last_name'  => $d['last_name'],
			                'password'   => $d['password'],
			                'name'       => $d['name'],
			                'ico'        => $d['ico'],
			            ];

			            return (new CompanyRegistrationService())->completeAdmin($data);
			        },
			        'admin'
			    );

			    if ($adminResult['ok'] === false) {
			        return $adminResult;
			    }

			} catch (Throwable $e) {
			    LoggerHolder::get()->error('OnboardingService.run-admin: failed', [
			                'message' => $e->getMessage(),
			                'file'    => $e->getFile(),
			                'line'    => $e->getLine(),
			                'trace'   => $e->getTraceAsString(),
			                'data'    => json_encode($d),

			    ]);

			    return [
			        'ok' => false,
			        'error' => 'Admin onboarding failed',
			    ];
			}

			// 🔥 TADY MUSÍŠ CHECKNOUT RESULT
			if ($adminResult["ok"] === false) {
			    return $adminResult;
			}

//var_dump($adminResult); exit;
        $adminData = $adminResult['data'];
        $companyId = $adminData['company_id'];
        $dbName    = $adminData['db_name'];

        $data['company_id'] = $adminData['company_id'];
        $data['db_name']    = $adminData['db_name'];
        $data['user_id']    = $adminData['user_id'];
        $data['team_id']    = $adminData['team_id'];

        // 2️⃣ WORK část (MAY FAIL)
        try {
            $workResult = DatabaseScope::work($dbName, function () use ($data) {
                return Transaction::run(
                    function () use ($data) {
                        return (new CompanyRegistrationService())->completeWork($data);
                    },
                    'work'
                );
            });

            // ✅ success
            $this->markOnboardingDone($companyId);
            $response = $adminResult;

        } catch (Throwable $e) {

            // ❌ fail (ale admin část zůstává)
            $this->markOnboardingFailed($companyId, $e);

            LoggerHolder::get()->error('OnboardingService.Work failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
                'data'    => json_encode($data),
            ]);

            return [
                'ok'   => true,
                'data' => $adminData,
            ];
        }

        // 📧 mail
        try {
            [$subject, $htmlBody, $textBody] = BuildMailService::buildInfoAfterRegistration($response);

            (new MailService())->send(
                toEmail: $response['data']['email'],
                toName: $response['data']['first_name'] . ' ' . $response['data']['last_name'],
                subject: $subject,
                html: $htmlBody,
                text: $textBody
            );
        } catch (Throwable $e) {
            LoggerHolder::get()->warning('OnboardingService Mail po registraci uživatele selhal', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
                'data'    => json_encode($response['data']),
            ]);
        }

        return $response;
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