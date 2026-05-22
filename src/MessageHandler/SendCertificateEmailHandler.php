<?php

namespace App\MessageHandler;

use App\Message\SendCertificateEmail;
use Psr\Log\LoggerInterface;

final class SendCertificateEmailHandler
{
    public function __construct(private readonly LoggerInterface $logger) {}

    public function __invoke(SendCertificateEmail $message): void
    {
        $this->logger->info('Sending certificate email', [
            'student' => $message->studentEmail,
            'course' => $message->courseTitle,
            'uuid' => $message->certificateUuid,
        ]);

        // TODO: inject MailerInterface when symfony/mailer is installed
        // $email = (new TemplatedEmail())
        //     ->to($message->studentEmail)
        //     ->subject('Votre certificat — ' . $message->courseTitle)
        //     ->htmlTemplate('emails/certificate.html.twig')
        //     ->context([
        //         'studentName' => $message->studentName,
        //         'courseTitle' => $message->courseTitle,
        //         'verificationUrl' => '/verify/' . $message->certificateUuid,
        //     ]);
        // $this->mailer->send($email);
    }
}
