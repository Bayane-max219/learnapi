<?php

namespace App\Message;

final class SendEnrollmentConfirmationEmail
{
    public function __construct(
        public readonly int $enrollmentId,
        public readonly string $studentEmail,
        public readonly string $studentName,
        public readonly string $courseTitle,
    ) {}
}
