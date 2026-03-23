<?php
declare(strict_types=1);

namespace App\Services\Users;

use App\Services\Tokens\TokenType;
use App\Core\Config;
use App\Core\Url;


final class BuildMailService
{
    public static function buildRegistration(string $activationUrl): array
    {
        $subject = 'Dokončení registrace';
        $expiresMinutes = self::expiresHuman(TokenType::COMPANY_CREATE);
        $activationUrl = htmlspecialchars($activationUrl, ENT_QUOTES, 'UTF-8');
        $html = <<<HTML
            <h2>Dokončete registraci</h2>
            <p>Pro dokončení registrace do Bó systému klikněte na tlačítko níže pro aktivaci účtu:</p>
            <p>
                <a href="{$activationUrl}" 
                   style="padding:12px 20px;background:#2d6cdf;color:#fff;text-decoration:none;border-radius:6px;">
                   Aktivovat účet
                </a>
            </p>
            <p>Platnost odkazu je {$expiresMinutes} minut.</p>
            <p style="font-size:12px;color:#999;">
Pokud tlačítko nefunguje, použijte tento odkaz:<br>
{$activationUrl}
</p>
            <p>Pokud tato zpráva není určená Vám, tak ji, prosím, ignorujte.</p>
HTML;

        $text = <<<TXT
        
Dokončete registraci
--------------------

Klikněte na odkaz níže nebo ho zkopírujte do adresního řádku vašeho prohlížeče
a dokončete registraci vašeho firemního účtu:

{$activationUrl}

Platnost odkazu je {$expiresMinutes} minut.

Pokud tato zpráva není určená Vám, tak ji, prosím, ignorujte.

TXT;

        return [$subject, $html, $text];
    }

    public static function buildInvitation(string $activationUrl, string $companyName): array
    {
    	  //var_dump($activationUrl, $companyName);exit;
        $subject = 'Pozvánka do systému Bó';
        $expiresMinutes = self::expiresHuman(TokenType::INVITATION);
        $companyName = e($companyName);
        $activationUrl = e($activationUrl);
        $html = <<<HTML
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<title>Pozvánka do systému Bó</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:20px 0;">
  <tr>
    <td align="center">

      <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; padding:40px;">
        <tr>
          <td>

            <h2 style="margin-top:0; color:#333333;">
              Pozvánka do systému Bó
            </h2>

            <p style="color:#555555; line-height:1.6;">
              Dobrý den,
            </p>

            <p style="color:#555555; line-height:1.6;">
              společnost <strong>{$companyName}</strong> Vám vytvořila účet v systému Bó.
            </p>

            <p style="color:#555555; line-height:1.6;">
              Pro dokončení registrace klikněte na tlačítko níže:
            </p>

            <p style="text-align:center; margin:30px 0;">
              <a href="{$activationUrl}"
                 style="background-color:#2f6fed;
                        color:#ffffff;
                        text-decoration:none;
                        padding:14px 28px;
                        border-radius:6px;
                        display:inline-block;
                        font-weight:bold;">
                Aktivovat účet
              </a>
            </p>

            <p style="color:#777777; font-size:14px; line-height:1.6;">
              Platnost aktivačního odkazu je {$expiresMinutes} dní.
            </p>
            <p style="font-size:12px;color:#999;">
Pokud tlačítko nefunguje, použijte tento odkaz:<br>
{$activationUrl}
</p>

            <hr style="border:none; border-top:1px solid #eeeeee; margin:30px 0;">

            <p style="color:#999999; font-size:13px; line-height:1.6;">
              Pokud jste o vytvoření účtu nevěděli, kontaktujte prosím administrátora společnosti {$companyName}
              nebo zprávu ignorujte.
            </p>

          </td>
        </tr>
      </table>

    </td>
  </tr>
</table>

</body>
</html>

HTML;

        $text = <<<TXT
Dobrý den,

společnost {$companyName} Vám vytvořila účet v systému Bó.

Pro dokončení registrace a aktivaci účtu otevřete následující odkaz ve Vašem prohlížeči:

{$activationUrl}

Platnost aktivačního odkazu je {$expiresMinutes} dní.

Pokud jste o vytvoření účtu nevěděli, kontaktujte prosím administrátora společnosti {$companyName} nebo tuto zprávu ignorujte.

TXT;
    	  //var_dump($activationUrl, $companyName);exit;

        return [$subject, $html, $text];
    }


    
public static function buildPasswordRecovery(string $resetUrl, array $company, array $user): array
{
    $activationUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');
    $companyName   = htmlspecialchars($company['name'], ENT_QUOTES, 'UTF-8');

    $subject = 'Změna hesla – systém Bó';
    $expires = self::expiresHuman(TokenType::PASSWORD_RESET);

    $html = <<<HTML
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<title>Změna hesla</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:20px 0;">
<tr>
<td align="center">

<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; padding:40px;">
<tr>
<td>

<h2 style="margin-top:0; color:#333333;">
Změna hesla
</h2>

<p style="color:#555555; line-height:1.6;">
Dobrý den,
</p>

<p style="color:#555555; line-height:1.6;">
byla podána žádost o změnu hesla k Vašemu účtu v systému <strong>Bó</strong>
u společnosti <strong>{$companyName}</strong>.
</p>

<p style="color:#555555; line-height:1.6;">
Pro nastavení nového hesla klikněte na tlačítko níže:
</p>

<p style="text-align:center; margin:30px 0;">
<a href="{$activationUrl}"
   style="background-color:#2f6fed;
          color:#ffffff;
          text-decoration:none;
          padding:14px 28px;
          border-radius:6px;
          display:inline-block;
          font-weight:bold;">
Nastavit nové heslo
</a>
</p>

<p style="color:#777777; font-size:14px; line-height:1.6;">
Platnost odkazu je {$expires}.
</p>
            <p style="font-size:12px;color:#999;">
Pokud tlačítko nefunguje, použijte tento odkaz:<br>
{$activationUrl}
</p>

<hr style="border:none; border-top:1px solid #eeeeee; margin:30px 0;">

<p style="color:#999999; font-size:13px; line-height:1.6;">
Pokud jste o změnu hesla nežádali, tuto zprávu ignorujte.
Vaše heslo zůstane beze změny.
</p>

</td>
</tr>
</table>

</td>
</tr>
</table>

</body>
</html>
HTML;

    $text = <<<TXT
Změna hesla – systém Bó
-----------------------

Dobrý den,

byla podána žádost o změnu hesla k Vašemu účtu v systému Bó
u společnosti {$companyName}.

Pro nastavení nového hesla otevřete následující odkaz ve Vašem prohlížeči:

{$activationUrl}

Platnost odkazu je {$expires}.

Pokud jste o změnu hesla nežádali, tuto zprávu ignorujte.
Vaše heslo zůstane beze změny.

TXT;

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

public static function buildInfoAfterRegistration(array $companyData): array
{
    $companyName = htmlspecialchars($companyData['name'], ENT_QUOTES, 'UTF-8');
    $user        = htmlspecialchars($companyData['first_name'] . ' ' . $companyData['last_name'], ENT_QUOTES, 'UTF-8');
    $ico         = (int) $companyData['ico'];
    $email       = htmlspecialchars($companyData['email'], ENT_QUOTES, 'UTF-8');

    $loginUrl     = Url::base();
    $loginLink    = '<a href="' . $loginUrl . '">Bó systém: login</a>';

    $subject = 'Bó systém: Vaše firma byla vytvořena ✅';

    $html = <<<HTML
<p>Dobrý den, {$user},</p>

<p>vaše firma "<strong>{$companyName}</strong>" byla úspěšně vytvořena.</p>

<p>
Přihlašovací údaje:<br>
IČO: {$ico}<br>
Email: {$email}
</p>

<p>
➡️ Přihlásit se můžete zde:<br>
{$loginLink}
</p>

<p>
V systému je již vytvořena první zakázka a několik úkolů k ní.<br>
To Vám pomůže seznámit se s funkcemi Bó systému.
</p>
<p>
Co můžete udělat dále:
</p>
<ul>
<li>vytvářet zakázky</li>
<li>přidat kolegy</li>
<li>přidat úkoly k zakázkám</li>
<li>psát reporty k úkolům</li>
</ul>
<p>
Pokud jste tuto registraci neprovedli, kontaktujte nás.
</p>

<p>
—<br>
Bó<br>
servisní zápisník
</p>
HTML;

    $text = <<<TXT
Dobrý den, {$user},

vaše firma "{$companyName}" byla úspěšně vytvořena.

Přihlašovací údaje:
IČO: {$ico}
Email: {$email}

➡️ Přihlásit se můžete zde:
{$loginUrl}

V systému je již vytvořena první zakázka a několik úkolů k ní.
To Vám pomůže seznámit se s funkcemi Bó systému.

Co můžete udělat dále:
 - vytvořit zakázku
 - přidat kolegy
 - přidat úkoly
 - psát reporty k úkolům

Pokud jste tuto registraci neprovedli, kontaktujte nás.

—
Bó
servisní zápisník
TXT;

    return [$subject, $html, $text];
}}