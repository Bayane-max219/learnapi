<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Enrollment;
use App\Repository\CertificateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class ProgressStateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
        private readonly CertificateRepository $certificateRepo,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Enrollment
    {
        /** @var Enrollment $data */
        $user = $this->security->getUser();

        if ($data->getStudent()?->getId() !== $user?->getId()) {
            throw new AccessDeniedHttpException('You can only update your own progress');
        }

        if ($data->getProgressPercent() >= 100) {
            $data->markCompleted();

            $existingCert = $this->certificateRepo->findOneBy([
                'student' => $user,
                'course' => $data->getCourse(),
            ]);

            if ($existingCert === null) {
                $cert = new \App\Entity\Certificate();
                $cert->setStudent($user);
                $cert->setCourse($data->getCourse());
                $this->em->persist($cert);
            }
        }

        $this->em->persist($data);
        $this->em->flush();

        return $data;
    }
}
