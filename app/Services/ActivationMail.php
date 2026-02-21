<?php
declare(strict_types=1);

namespace App\Services;

final class ActivationMail
{
    public static function build(string $activationUrl): array
    {
        $subject = 'Dokončení registrace';

        $html = "
            <h2>Dokončete registraci</h2>
            <p>Klikněte na tlačítko níže pro aktivaci účtu:</p>
            <p>
                <a href=\"{$activationUrl}\" 
                   style=\"padding:12px 20px;background:#2d6cdf;color:#fff;text-decoration:none;border-radius:6px;\">
                   Aktivovat účet
                </a>
            </p>
            <p>Platnost odkazu je 15 minut.</p>
        ";

        $text = "
Dokončete registraci:

{$activationUrl}

Platnost odkazu je 15 minut.
        ";

        return [$subject, $html, $text];
    }
}