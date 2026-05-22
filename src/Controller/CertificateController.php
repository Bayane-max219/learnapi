<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Certificate;
use App\Repository\CertificateRepository;
use App\Repository\EnrollmentRepository;
use App\Service\CertificatePdfService;
use App\Service\MercureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class CertificateController extends AbstractController
{
    public function __construct(
        private readonly CertificateRepository $certificateRepository,
        private readonly EnrollmentRepository $enrollmentRepository,
        private readonly CertificatePdfService $pdfService,
        private readonly MercureService $mercureService,
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('/certificates/{id}/download', name: 'certificate_download', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function download(int $id): Response
    {
        $certificate = $this->certificateRepository->find($id);
        if (!$certificate) {
            return $this->json(['message' => 'Certificate not found'], Response::HTTP_NOT_FOUND);
        }

        $user = $certificate->getStudent();
        if ($user !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $pdf = $this->pdfService->generateCertificatePdf(
            $certificate,
            $certificate->getStudent(),
            $certificate->getCourse(),
        );

        $filename = sprintf('certificate-%s.pdf', $certificate->getUuid());

        return new Response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        ]);
    }

    #[Route('/certificates/verify/{uuid}', name: 'certificate_verify', methods: ['GET'])]
    public function verify(string $uuid): JsonResponse
    {
        $certificate = $this->certificateRepository->findOneBy(['uuid' => $uuid]);
        if (!$certificate) {
            return $this->json([
                'valid' => false,
                'message' => 'Certificate not found or invalid',
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'valid' => true,
            'studentName' => $certificate->getStudent()?->getFirstName() . ' ' . $certificate->getStudent()?->getLastName(),
            'courseName' => $certificate->getCourse()?->getTitle(),
            'issuedAt' => $certificate->getIssuedAt()->format(\DateTimeInterface::ATOM),
            'verificationId' => $certificate->getUuid(),
        ]);
    }

    #[Route('/enrollments/{id}/complete', name: 'enrollment_complete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function completeEnrollment(int $id): JsonResponse
    {
        $enrollment = $this->enrollmentRepository->find($id);
        if (!$enrollment) {
            return $this->json(['message' => 'Enrollment not found'], Response::HTTP_NOT_FOUND);
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($enrollment->getStudent() !== $user) {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $enrollment->setProgressPercent(100);
        $enrollment->setStatus('completed');

        $existing = $this->certificateRepository->findOneBy([
            'student' => $user,
            'course' => $enrollment->getCourse(),
        ]);

        if ($existing) {
            return $this->json(['message' => 'Certificate already issued', 'uuid' => $existing->getUuid()]);
        }

        $certificate = new Certificate();
        $certificate->setStudent($user);
        $certificate->setCourse($enrollment->getCourse());

        $this->em->persist($certificate);
        $this->em->flush();

        $this->mercureService->publishCertificateIssued(
            $user->getId(),
            $enrollment->getCourse()?->getTitle() ?? '',
            $certificate->getUuid(),
        );

        return $this->json([
            'message' => 'Enrollment completed — certificate issued',
            'certificateId' => $certificate->getId(),
            'uuid' => $certificate->getUuid(),
            'courseName' => $enrollment->getCourse()?->getTitle(),
            'issuedAt' => $certificate->getIssuedAt()->format(\DateTimeInterface::ATOM),
            'downloadUrl' => sprintf('/api/certificates/%d/download', $certificate->getId()),
            'verifyUrl' => sprintf('/api/certificates/verify/%s', $certificate->getUuid()),
        ], Response::HTTP_CREATED);
    }

    #[Route('/my-certificates', name: 'my_certificates', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myCertificates(): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $certificates = $this->certificateRepository->findBy(['student' => $user]);

        $data = array_map(fn($cert) => [
            'id' => $cert->getId(),
            'uuid' => $cert->getUuid(),
            'courseName' => $cert->getCourse()?->getTitle(),
            'issuedAt' => $cert->getIssuedAt()->format(\DateTimeInterface::ATOM),
            'downloadUrl' => sprintf('/api/certificates/%d/download', $cert->getId()),
        ], $certificates);

        return $this->json($data);
    }
}
