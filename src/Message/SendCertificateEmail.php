<?php

namespace App\Message;

final class SendCertificateEmail
{
    public function __construct(
        public readonly int $certificateId,
        public readonly string $studentEmail,
        public readonly string $studentName,
        public readonly string $courseTitle,
        public readonly string $certificateUuid,
    ) {}
}
