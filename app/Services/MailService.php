<?php
declare(strict_types=1);

namespace App\Core;



final class MailService
{
    public function sendActivation(array $user, string $token): void {}
    public function sendPasswordReset(array $user, string $token): void {}
}