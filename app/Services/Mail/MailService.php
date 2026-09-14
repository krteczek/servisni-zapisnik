<?php
declare(strict_types=1);

namespace App\Services\Mail;

use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\Exception;
use App\Core\Config;

//posílání emailu pomocí phpMailer
class MailService
{
    private PHPMailer $mailer;
    private array $config = [];

    public function __construct()
    {
        $config = Config::get('mail');

        $this->mailer = new PHPMailer(true);

        $this->mailer->isSMTP();
        $this->mailer->Host       = $config['host'];
        $this->mailer->Port       = $config['port'];
        $this->mailer->SMTPAuth   = $config['smtpAuth'] ?? true;
        $this->mailer->Username   = $config['username'];
        $this->mailer->Password   = $config['password'];

			// kvuli ladění chyb
			if (Config::get('app.env') === 'dev') {
			    $this->mailer->SMTPDebug = 2;
			}
        if (($config['encryption'] ?? '') !== '') {
            $this->mailer->SMTPSecure = $config['encryption'];
        }

        if (isset($config['timeout'])) {
            $this->mailer->Timeout = $config['timeout'];
        }
        
        $this->mailer->CharSet = 'UTF-8';

        $this->config = $config;
    }

    public function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $html,
        string $text
    ): bool {

        $config = $this->config;

        $this->mailer->clearAllRecipients();
        $this->mailer->clearAttachments();
        $this->mailer->clearCustomHeaders();
        $this->mailer->setFrom($config['from_email'], $config['from_name']);
        $this->mailer->addAddress($toEmail, $toName);

        $this->mailer->Subject = $subject;
        $this->mailer->Body    = $html;
        $this->mailer->AltBody = $text;
        $this->mailer->isHTML(true);

        return $this->mailer->send();
    }
}