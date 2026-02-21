<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Config;
use App\phpmailer\phpmailer\src\PHPMailer;
use App\phpmailer\phpmailer\src\Exception;

final class Mailer
{
    private array $config;

    public function __construct()
    {
        $this->config = Config::get('mail');
    }

    public function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        string $textBody
    ): bool {
        $mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = $this->config['host'];
    $mail->Port = $this->config['port'];

    if (!empty($this->config['smtpAuth'])) {
        $mail->SMTPAuth = true;
        $mail->Username = $this->config['username'];
        $mail->Password = $this->config['password'];
    } else {
        $mail->SMTPAuth = false;
		$mail->SMTPAutoTLS = false;
    }

    if (!empty($this->config['encryption'])) {
        $mail->SMTPSecure = $this->config['encryption'];
    }

    $mail->setFrom(
        $this->config['from_email'],
        $this->config['from_name']
    );

    $mail->addAddress($toEmail, $toName);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $htmlBody;
    $mail->AltBody = $textBody;
    $mail->CharSet = 'UTF-8';

    return $mail->send();
} catch (Exception $e) {
            error_log('Mailer error: ' . $mail->ErrorInfo);
            return false;
        }
    }
}