<?php

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Dotenv\Dotenv;

// Load .env file
$dotenv = Dotenv::createImmutable(ROOT_DIR);
$dotenv->load();

class MailerService {
    private $mailer;
    private $from;

    public function __construct() {
        // Load mail configuration from environment (best practice)
        $dsn = $_ENV['MAILER_DSN'] ?? 'smtp://user:pass@smtp.example.com:587';
        $this->from = $_ENV['MAIL_FROM'] ?? 'no-reply@yourdomain.com';

        $transport = Transport::fromDsn($dsn);
        $this->mailer = new Mailer($transport);
    }

    public function send(string $to, string $subject, string $html, string $text = ''): bool {
        $email = (new Email())
            ->from($this->from)
            ->to($to)
            ->subject($subject)
            ->html($html)
            ->text($text ?: strip_tags($html));

        $this->mailer->send($email);
        return true;
    }
}
