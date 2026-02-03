<?php
declare(strict_types=1);

namespace App\Core;


final class Mailer
{
    public static function sendActivation(
        string $email,
        string $token,
        string $tenant
    ): void {
    	  
        $link = Url::base() . Url::to("/activate?token=" . urlencode($token));

        $subject = 'Aktivace účtu';
        $message = <<<TEXT
Dobrý den,

byl vám vytvořen účet v aplikaci Servisní zápisník (firma: {$tenant}).

Pro aktivaci účtu a nastavení hesla klikněte na odkaz:
{$link}

Platnost odkazu je časově omezená.

Pokud jste tuto zprávu nečekali, ignorujte ji.
TEXT;

        self::send($email, $subject, $message);
    }

    public static function sendResetPassword(
        string $email,
        string $token,
        string $tenant
    ): void {
    	  $link = Url::base() . Url::to("/reset-password?token=" . urlencode($token));
//var_dump($link);exit;
        $subject = 'Obnova hesla';
        $message = <<<TEXT
Dobrý den,

byla vyžádána změna hesla k vašemu účtu v aplikaci Servisní zápisník (firma: {$tenant}).

Pro nastavení nového hesla klikněte na odkaz:
{$link}

Pokud jste o změnu nežádali, můžete tento e-mail ignorovat.
TEXT;

        self::send($email, $subject, $message);
    }

    /* =========================
       LOW-LEVEL SEND
       ========================= */

    private static function send(
        string $to,
        string $subject,
        string $message
    ): void {
    	//var_dump($domena);exit;
        $headers = [
            'From: Bó - Servisní zápisník <noreply@krteczek.cz>',
            'Reply-To: podpora@krteczek.cz',
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8'
        ];
        //echo '<pre>';
        //echo $to . '<br>';
        //echo $subject . '<br>';
        //echo implode("\r\n", $headers) . '<br>';
        
        //var_dump($message);
        //echo '</pre>';exit;

        if (!mail($to, $subject, $message, implode("\r\n", $headers))) {
            throw new \RuntimeException('Odeslání e-mailu selhalo.');
        }
        
    }
    
}
