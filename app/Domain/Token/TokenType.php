<?php
declare(strict_types=1);

namespace Domain\Token;

enum TokenType: string
{
    case CompanyRegistration = 'company_registration';
    case UserActivation = 'user_activation';
    case PasswordReset = 'password_reset';
}