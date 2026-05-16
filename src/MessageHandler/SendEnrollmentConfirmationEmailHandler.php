<?php

namespace App\MessageHandler;

use App\Message\SendEnrollmentConfirmationEmail;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendEnrollmentConfirmationEmailHandler
{
    public function __construct(private readonly LoggerInterface $logger) {}

    public function __invoke(SendEnrollmentConfirmationEmail $message): void
    {
        $this->logger->info('Sending enrollment confirmation email', [
            'student' => $message->studentEmail,
            'course' => $message->courseTitle,
        ]);

        // TODO: inject MailerInterface and send real email when symfony/mailer is installed
        // $email = (new TemplatedEmail())
        //     ->to($message->studentEmail)
        //     ->subject('Inscription confirmée — ' . $message->courseTitle)
        //     ->htmlTemplate('emails/enrollment_confirmation.html.twig')
        //     ->context(['studentName' => $message->studentName, 'courseTitle' => $message->courseTitle]);
        // $this->mailer->send($email);
    }
}
