<?php
declare(strict_types=1);

namespace App\Services\Users;

use App\Services\Tokens\TokenType;
use App\Core\Config;
use App\Core\Url;
use App\Services\Mail\MailView;

final class BuildMailService
{
   /** @var array<string, list<string>> $required */
	private static array $required = [
        'users.registration'          => ['activationUrl', 'expiresMinutes'],
        'users.invitation'            => ['companyName', 'activationUrl', 'expiresMinutes', ],
        'users.not-active'            => [],// nic nemá
        'users.reset-password'        => ['companyName', 'activationUrl', ],
        'users.InfoAfterRegistration' => ['user', 'companyName', 'ico', 'email', 'loginLink'],

	];

/**
 * @param array<string, list<string>> $data
 */
	private static function validate(string $template, array $data): void
	{
	    $required = self::$required[$template] ?? [];

	    foreach ($required as $key) {
	        if (!array_key_exists($key, $data)) {
	            throw new \InvalidArgumentException(
	                "Mail template '{$template}' requires '{$key}'"
	            );
	        }
	    }
	}

/**
 * @param string $template
 * @param array{
 *   activationUrl?: string,
 *   companyName?: string|null,
 *   expiresMinutes?: string,
 *   user?: string,
 *   ico?: string,
 *   email?: string,
 *   loginLink?: string,
 *   first_name?: string,
 *   last_name?: string
 * } $data
 * @return array<string, mixed> $data
 */


private static function enrichData(string $template, array $data): array
{

    // expirace podle typu template
    if (!isset($data['expiresMinutes'])) {
        $map = [
            'users.registration'   => TokenType::COMPANY_CREATE,
            'users.invitation'     => TokenType::INVITATION,
            'users.reset-password' => TokenType::PASSWORD_RESET,
        ];

        if (isset($map[$template])) {
            $data['expiresMinutes'] = self::expiresHuman($map[$template]);
        }
    }

    $data['loginLink'] = '<a href="' . Url::base() . '">Bó systém: login</a>';


    if (array_key_exists('first_name', $data) &&array_key_exists('last_name', $data))
    {
    	$data['user'] = $data['first_name'] . ' ' . $data['last_name'];
    }


    return $data;
}



/**
 * @param string $template
 * @param array{
 *   activationUrl?: string,
 *   companyName?: string|null,
 *   expiresMinutes?: string,
 *   user?: string,
 *   ico?: string,
 *   email?: string,
 *   loginLink?: string,
 *   first_name?: string,
 *   last_name?: string
 * } $data
 * @return array{0:string,1:string,2:string}
 */
public static function build(string $template, array $data = []): array
{

    $map = [
        'users.not-active'     => 'Informace o účtu',
        'users.reset-password' => 'Obnovení hesla',
        'users.invitation'     => 'Pozvánka do aplikace',
        'users.registration'   => 'Dokončení registrace',
        'users.registration-success' => 'Registrace dokončena',
    ];

    // 👉 AUTO DATA (dle template)
    $data = self::enrichData($template, $data);

	 self::validate($template, $data);

    $subject = $map[$template] ?? 'Zpráva z aplikace';
    $data['title'] = $subject;

    $html = MailView::renderHtml(str_replace('.', '/', $template), $data);
    $text = MailView::renderText(str_replace('.', '/', $template), $data);

    //$text = strip_tags($html);

    return [$subject, $html, $text];
}

public static function expiresHuman(string $type): string
{
    $minutes = (int) Config::get('tokenExpires.' . $type);

    if ($minutes < 60) {
        return "$minutes minut";
    }

    if ($minutes < 1440) {
        return floor($minutes / 60) . " hodin";
    }

    return floor($minutes / 1440) . " dní";
}
}