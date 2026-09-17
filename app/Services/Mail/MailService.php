<?php
declare(strict_types=1);

namespace App\Services\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use App\Core\Config;

class MailService
{
    private PHPMailer $mailer;

    /**
     * @var array<string, mixed>
     */
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

    /**
     * @param string $toEmail
     * @param string $toName
     * @param string $subject
     * @param string $html
     * @param string $text
     * @return bool
     */
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