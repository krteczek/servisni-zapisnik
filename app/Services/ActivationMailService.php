<?php
declare(strict_types=1);

namespace App\Services;

final class ActivationMailService
{
    public static function buildRegistration(string $activationUrl): array
    {
        $subject = 'Dokončení registrace';

        $html = "
            <h2>Dokončete registraci</h2>
            <p>Pro dokončení registrace do Bó systému klikněte na tlačítko níže pro aktivaci účtu:</p>
            <p>
                <a href=\"{$activationUrl}\" 
                   style=\"padding:12px 20px;background:#2d6cdf;color:#fff;text-decoration:none;border-radius:6px;\">
                   Aktivovat účet
                </a>
            </p>
            <p>Platnost odkazu je 15 minut.</p>
            <p>Pokud tato zpráva není určená Vám, tak ji, prosím, ignorujte.<p>
        ";

        $text = "
Dokončete registraci
--------------------

Klikněte na odkaz níže nebo ho zkopírujte do adresního řádku vašeho prohlížeče
a dokončete registraci vačeho firemního účtu:

{$activationUrl}

Platnost odkazu je 15 minut.

Pokud tato zpráva není určená Vám, tak ji, prosím, ignorujte.
        ";

        return [$subject, $html, $text];
    }

    public static function buildInvitation(string $activationUrl, $companyName ): array
    {
        $subject = 'Pozvánka do systému Bó';
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
              Platnost aktivačního odkazu je 7 dní.
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

        $text = "
Dobrý den,

společnost {$companyName} Vám vytvořila účet v systému Bó.

Pro dokončení registrace a aktivaci účtu otevřete následující odkaz ve Vašem prohlížeči:

{$activationUrl}

Platnost aktivačního odkazu je 7 dní.

Pokud jste o vytvoření účtu nevěděli, kontaktujte prosím administrátora společnosti {$companyName} nebo tuto zprávu ignorujte.
 ";

        return [$subject, $html, $text];
    }

}