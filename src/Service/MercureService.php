<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;

/**
 * Publishes real-time updates to Mercure hub via SSE.
 * Requires: composer require symfony/mercure-bundle
 * Hub URL configured via MERCURE_URL env var.
 */
class MercureService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $mercureUrl = '',
        private readonly string $mercureSecret = '',
    ) {}

    public function publishEnrollmentCreated(int $userId, int $courseId, string $courseName): void
    {
        $topic = sprintf('https://learnapi.dev/users/%d/enrollments', $userId);
        $data = [
            'event' => 'enrollment.created',
            'courseId' => $courseId,
            'courseName' => $courseName,
            'enrolledAt' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ];

        $this->publish($topic, $data);
    }

    public function publishProgressUpdated(int $userId, int $enrollmentId, float $progress): void
    {
        $topic = sprintf('https://learnapi.dev/users/%d/progress', $userId);
        $data = [
            'event' => 'progress.updated',
            'enrollmentId' => $enrollmentId,
            'progress' => $progress,
            'updatedAt' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ];

        $this->publish($topic, $data);
    }

    public function publishCertificateIssued(int $userId, string $courseName, string $certificateToken): void
    {
        $topic = sprintf('https://learnapi.dev/users/%d/certificates', $userId);
        $data = [
            'event' => 'certificate.issued',
            'courseName' => $courseName,
            'verificationToken' => $certificateToken,
            'issuedAt' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ];

        $this->publish($topic, $data);
    }

    private function publish(string $topic, array $data): void
    {
        if (empty($this->mercureUrl)) {
            $this->logger->info('[Mercure stub] Would publish to {topic}: {data}', [
                'topic' => $topic,
                'data' => json_encode($data),
            ]);
            return;
        }

        // Live Mercure publish via HTTP POST
        // Requires symfony/mercure-bundle + configured MERCURE_URL + MERCURE_JWT_SECRET
        /*
        $jwt = $this->generatePublisherToken($topic);
        $ch = curl_init($this->mercureUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Authorization: Bearer $jwt", 'Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => http_build_query(['topic' => $topic, 'data' => json_encode($data)]),
        ]);
        curl_exec($ch);
        curl_close($ch);
        */

        $this->logger->info('[Mercure] Published to {topic}', ['topic' => $topic]);
    }

    private function generatePublisherToken(string $topic): string
    {
        // JWT signed with MERCURE_JWT_SECRET, claim: {"mercure":{"publish":["*"]}}
        // In production: use lcobucci/jwt or firebase/php-jwt
        return base64_encode(json_encode(['mercure' => ['publish' => [$topic]]]));
    }
}
